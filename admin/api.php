<?php
// CMS Lite — admin: actions (JSON)
declare(strict_types=1);
require __DIR__ . '/lib.php';

// Responses are JSON: a displayed PHP warning (display_errors, common locally)
// would make them unreadable. Errors go to the log, and any stray output
// is discarded by json_out().
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ob_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => t('err_method')], 405);
require_same_origin();
require_csrf();   // 4.5.0: token, in addition to the origin check

// Upload larger than post_max_size: PHP then empties $_FILES and $_POST without any other sign
if (empty($_FILES) && empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > ini_bytes('post_max_size')
    && in_array($_GET['action'] ?? '', ['upload', 'attach'], true)) {
    json_out(['error' => t('err_file_too_big', upload_limit_text())], 413);
}

try {
    match ($_GET['action'] ?? '') {
        'save'      => action_save(),
        'delete'    => action_delete(),
        'upload'    => action_upload(),
        'attach'    => action_attach(),
        'menu_move' => action_menu_move(),
        default     => json_out(['error' => t('err_action')], 400),
    };
} catch (Throwable $e) {
    error_log('cms-admin: ' . $e->getMessage());
    json_out(['error' => t('err_internal', date('H:i:s'))], 500);   // 4.5.2: details in the log only
}

// ---------------------------------------------------------------------------
// Save a page.
// 4.5.0: besides title, txt and pending, the category (categ), the order within
// the category (categ_pagenum), the author and the date, when sent. These fields
// are plain text: extra spaces removed, < and > characters refused.
// Menu: one level only (t_menu). Sub-menus are never touched.
// ---------------------------------------------------------------------------
function action_save(): never {
    $in = json_decode(file_get_contents('php://input') ?: '', true);
    if (!is_array($in)) json_out(['error' => t('err_data')], 400);

    $id     = (int)($in['id'] ?? 0);
    $title  = trim(preg_replace('/\s+/u', ' ', (string)($in['title'] ?? '')));
    $draft  = !empty($in['draft']) ? 1 : 0;
    $inMenu = !empty($in['in_menu']);
    $html   = sanitize_html((string)($in['html'] ?? ''));

    if ($title === '') json_out(['error' => t('title_required')], 400);
    if (mb_strlen($title) > 200) json_out(['error' => t('err_title_long')], 400);

    // Properties (4.5.0): only those that are sent (an old editor.js still in
    // the browser cache therefore erases nothing).
    $props = [];
    foreach (['categ' => 200, 'author' => 200, 'date' => 40] as $k => $max) {
        if (!array_key_exists($k, $in)) continue;
        $v = trim(preg_replace('/\s+/u', ' ', (string)$in[$k]));
        if (mb_strlen($v) > $max) json_out(['error' => t('err_prop_long', t('props_' . $k), (string)$max)], 400);
        $props[$k] = $v;
    }
    if (array_key_exists('categ_pagenum', $in)) {
        $props['categ_pagenum'] = max(0, (int)$in['categ_pagenum']);
    }
    foreach ([$title, ...array_filter($props, 'is_string')] as $v) {
        if (preg_match('/[<>]/', $v)) json_out(['error' => t('err_text_chars')], 400);
    }

    $txt = HTML_MARKER . "\n" . $html;
    $pdo = db();
    $pdo->beginTransaction();

    if ($id > 0) {
        $old = $pdo->prepare('SELECT title, txt FROM t_pages WHERE id = ?');
        $old->execute([$id]);
        $row = $old->fetch();
        if (!$row) { $pdo->rollBack(); json_out(['error' => t('err_page_gone')], 404); }
        if (!is_editor_page((string)$row['txt'])) {
            $pdo->rollBack();
            json_out(['error' => t('err_classic_edit')], 409);
        }
        $pdo->prepare('UPDATE t_pages SET title = ?, txt = ?, pending = ? WHERE id = ?')
            ->execute([$title, $txt, $draft, $id]);
        save_props($id, $props);
        // Menu label: follows the title, unless it was customised by hand
        $pdo->prepare('UPDATE t_menu SET item = ? WHERE target = ? AND item = ?')
            ->execute([$title, page_target($id), $row['title']]);
    } else {
        $pdo->prepare('INSERT INTO t_pages (title, txt, pending) VALUES (?, ?, ?)')
            ->execute([$title, $txt, $draft]);
        $id = (int)$pdo->lastInsertId();
        save_props($id, $props);
    }

    set_in_menu($id, $title, $inMenu && !$draft);
    $pdo->commit();
    // Relative URL: edit.php declares <base href> to the public site
    json_out(['ok' => true, 'id' => $id, 'url' => page_target($id)]);
}

// Column names: only those of this closed list (never taken from the request).
function save_props(int $id, array $props): void {
    $cols = array_intersect(array_keys($props), ['categ', 'categ_pagenum', 'author', 'date']);
    if (!$cols) return;
    $set = implode(', ', array_map(fn($c) => "$c = ?", $cols));
    $vals = array_map(fn($c) => $props[$c], $cols);
    db()->prepare("UPDATE t_pages SET $set WHERE id = ?")->execute([...$vals, $id]);
}

// A draft never appears in the menu (the link would lead to an empty page).
function set_in_menu(int $id, string $title, bool $wanted): void {
    $pdo = db();
    $q = $pdo->prepare('SELECT COUNT(*) FROM t_menu WHERE target = ?');
    $q->execute([page_target($id)]);
    $present = (int)$q->fetchColumn() > 0;

    if ($wanted && !$present) {
        $max = (int)$pdo->query('SELECT COALESCE(MAX(display_order), 0) FROM t_menu')->fetchColumn();
        $pdo->prepare('INSERT INTO t_menu (item, target, display_order) VALUES (?, ?, ?)')
            ->execute([$title, page_target($id), $max + 1]);
    } elseif (!$wanted && $present) {
        $pdo->prepare('DELETE FROM t_menu WHERE target = ?')->execute([page_target($id)]);
    }
}

// ---------------------------------------------------------------------------
// Delete a page, with its main menu entry.
// Refused if a sub-menu or HOMEPAGE leads to it: those are set in DB Browser.
// Images and attachments are not deleted: they may be used elsewhere.
// ---------------------------------------------------------------------------
function action_delete(): never {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) json_out(['error' => t('err_page_missing')], 400);
    if (page_is_protected($id)) {
        json_out(['error' => t('err_protected')], 409);
    }
    $pdo = db();
    $q = $pdo->prepare('SELECT txt FROM t_pages WHERE id = ?');
    $q->execute([$id]);
    $txt = $q->fetchColumn();
    if ($txt !== false && !is_editor_page((string)$txt)) {
        json_out(['error' => t('err_classic_manage')], 409);
    }
    $pdo->beginTransaction();
    $pdo->prepare('DELETE FROM t_pages WHERE id = ?')->execute([$id]);
    $pdo->prepare('DELETE FROM t_menu WHERE target = ?')->execute([page_target($id)]);
    $pdo->commit();
    json_out(['ok' => true]);
}

// ---------------------------------------------------------------------------
// Reorder the main menu: swap with the neighbouring entry.
// Applies to all entries of t_menu, including those created by hand
// (they can be moved, never renamed or deleted here).
// ---------------------------------------------------------------------------
function action_menu_move(): never {
    $id  = (int)($_POST['id'] ?? 0);
    $dir = ($_POST['dir'] ?? '') === 'up' ? -1 : 1;

    $pdo = db();
    $pdo->beginTransaction();
    $ids = array_map('intval', $pdo->query('SELECT id FROM t_menu ORDER BY display_order, id')->fetchAll(PDO::FETCH_COLUMN));
    $i = array_search($id, $ids, true);
    if ($i === false) { $pdo->rollBack(); json_out(['error' => t('err_menu_entry')], 404); }
    $j = $i + $dir;
    if ($j >= 0 && $j < count($ids)) {
        [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
    }
    // Full renumbering: also fixes any duplicate display_order values
    $upd = $pdo->prepare('UPDATE t_menu SET display_order = ? WHERE id = ?');
    foreach ($ids as $order => $mid) $upd->execute([$order, $mid]);
    $pdo->commit();
    json_out(['ok' => true]);
}

// ---------------------------------------------------------------------------
// Image upload: actual type checked, orientation corrected, width limited,
// file name generated (the original name is never used).
// ---------------------------------------------------------------------------
function action_upload(): never {
    $f = $_FILES['file'] ?? null;
    if (!$f || !is_array($f) || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $code = $f['error'] ?? UPLOAD_ERR_NO_FILE;
        $msg = in_array($code, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
            ? t('err_image_too_big', upload_limit_text()) : t('err_no_image');
        json_out(['error' => $msg], 400);
    }

    if (!function_exists('imagecreatefromjpeg')) {
        json_out(['error' => t('err_gd')], 500);
    }
    if ((int)$f['size'] > MAX_IMAGE_BYTES) {
        json_out(['error' => t('err_image_too_big', round(MAX_IMAGE_BYTES / 1048576) . ' ' . t('unit_mb'))], 413);
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if ($ext === null) json_out(['error' => t('err_image_format')], 415);

    $img = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($f['tmp_name']),
        'image/png'  => @imagecreatefrompng($f['tmp_name']),
        'image/webp' => @imagecreatefromwebp($f['tmp_name']),
    };
    if (!$img) json_out(['error' => t('err_image_broken')], 422);

    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($f['tmp_name']);
        $rot  = [3 => 180, 6 => -90, 8 => 90][(int)($exif['Orientation'] ?? 1)] ?? 0;
        if ($rot) { $r = imagerotate($img, $rot, 0); if ($r) $img = $r; }
    }

    if (imagesx($img) > MAX_WIDTH) {
        $h = (int)round(imagesy($img) * MAX_WIDTH / imagesx($img));
        $r = imagecreatetruecolor(MAX_WIDTH, $h);
        imagealphablending($r, false);
        imagesavealpha($r, true);
        imagecopyresampled($r, $img, 0, 0, 0, 0, MAX_WIDTH, $h, imagesx($img), imagesy($img));
        $img = $r;
    } else {
        imagesavealpha($img, true);
    }

    $dir = CMS_WWW . '/' . UPLOAD_DIR;
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) json_out(['error' => t('err_image_dir')], 500);
    $name = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    $tmp  = $dir . '/.' . $name . '.tmp';

    $ok = match ($ext) {
        'jpg'  => imagejpeg($img, $tmp, 85),
        'png'  => imagepng($img, $tmp, 6),
        'webp' => imagewebp($img, $tmp, 85),
    };
    unset($img);   // (imagedestroy() has had no effect since PHP 8.0 and is deprecated in 8.5)
    if (!$ok || !rename($tmp, $dir . '/' . $name)) {
        @unlink($tmp);
        json_out(['error' => t('err_image_save')], 500);
    }
    json_out(['ok' => true, 'src' => UPLOAD_DIR . '/' . $name]);
}

// ---------------------------------------------------------------------------
// Attachment: PDF, office document, audio or video, stored in public/files/.
// The extension is derived from the actual content, never from the sent name:
// a .php or .html cannot be dropped. The readable name is kept (preceded by
// the date) because it is the one the visitor will see when downloading.
// ---------------------------------------------------------------------------

function action_attach(): never {
    $f = $_FILES['file'] ?? null;
    if (!$f || !is_array($f) || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $code = $f['error'] ?? UPLOAD_ERR_NO_FILE;
        $msg = in_array($code, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
            ? t('err_file_too_big', upload_limit_text()) : t('err_no_file');
        json_out(['error' => $msg], 400);
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext  = ATTACH_TYPES[$mime] ?? null;
    // Some libmagic versions recognise a .docx/.xlsx only as "zip":
    // the claimed extension is then accepted, if it is one of the list and the file really is a zip.
    if ($ext === null && $mime === 'application/zip') {
        $claimed = strtolower(pathinfo((string)$f['name'], PATHINFO_EXTENSION));
        if (in_array($claimed, ['docx', 'xlsx', 'odt', 'ods'], true)) $ext = $claimed;
    }
    // An .m4a (audio only) is sometimes detected as "video/mp4": the claimed extension is then trusted
    if ($ext === 'mp4' && strtolower(pathinfo((string)$f['name'], PATHINFO_EXTENSION)) === 'm4a') $ext = 'm4a';
    if ($ext === null) json_out(['error' => t('err_attach_format')], 415);

    // Readable name: no accents or special characters, 60 characters at most
    $base = pathinfo((string)$f['name'], PATHINFO_FILENAME);
    // (no iconv //TRANSLIT: the musl libc of the Alpine image does not do it)
    $base_ascii = strtr($base, [
        'à'=>'a','â'=>'a','ä'=>'a','á'=>'a','ã'=>'a','å'=>'a','ç'=>'c','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
        'î'=>'i','ï'=>'i','í'=>'i','ì'=>'i','ñ'=>'n','ô'=>'o','ö'=>'o','ó'=>'o','ò'=>'o','õ'=>'o','ø'=>'o',
        'û'=>'u','ù'=>'u','ü'=>'u','ú'=>'u','ÿ'=>'y','æ'=>'ae','œ'=>'oe','ß'=>'ss',
        'À'=>'A','Â'=>'A','Ä'=>'A','Á'=>'A','Ç'=>'C','É'=>'E','È'=>'E','Ê'=>'E','Ë'=>'E','Î'=>'I','Ï'=>'I',
        'Ô'=>'O','Ö'=>'O','Û'=>'U','Ù'=>'U','Ü'=>'U','Æ'=>'AE','Œ'=>'OE',
    ]);
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $base_ascii), '-'));
    $slug = substr($slug, 0, 60) ?: 'document';

    $dir = CMS_WWW . '/' . ATTACH_DIR;
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) json_out(['error' => t('err_attach_dir')], 500);
    $name = date('Y-m-d') . '-' . $slug . '.' . $ext;
    for ($n = 2; file_exists($dir . '/' . $name); $n++) {
        $name = date('Y-m-d') . '-' . $slug . '-' . $n . '.' . $ext;
    }
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
        json_out(['error' => t('err_attach_save')], 500);
    }
    @chmod($dir . '/' . $name, 0644);

    $size  = (int)$f['size'];
    $human = $size >= 1048576
        ? number_format($size / 1048576, 1, t('_dec'), ' ') . ' ' . t('unit_mb')
        : max(1, (int)round($size / 1024)) . ' ' . t('unit_kb');
    json_out([
        'ok'    => true,
        'kind'  => in_array($ext, AUDIO_EXT, true) ? 'audio' : (in_array($ext, VIDEO_EXT, true) ? 'video' : 'file'),
        'href'  => ATTACH_DIR . '/' . $name,
        'label' => trim(preg_replace('/[_-]+/', ' ', $base)) . ' (' . strtoupper($ext) . ', ' . $human . ')',
    ]);
}
