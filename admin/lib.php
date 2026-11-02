<?php
// CMS Lite — admin: shared functions
// Authentication is done UPSTREAM (Apache .htaccess, Caddy basic_auth or
// Tinyauth): this code contains none. Without that protection, anyone can
// modify the site. See README, "Protecting the editor: mandatory".

declare(strict_types=1);

const HTML_MARKER = '<!--html-->';

// CMS Lite version. 4.5.2: written here (same value as the VERSION file at the
// repository root) instead of being read from VERSION: with Docker, that file was
// mounted on its own in the container, and a "git pull" replacing it was only
// seen after a restart. admin/ is mounted as a directory, so this follows the pull.
const CMS_VERSION = '4.7.2';
function cms_version(): string {
    return CMS_VERSION;
}

// ---------------------------------------------------------------------------
// Locations, detected automatically (4.4.0: the served site is public/):
//   - admin/ INSIDE the served site (classic hosting: the contents of public/ are
//     the web root, www/ for example, and admin/ is copied into it)
//         → the site is the parent directory, ".." from the browser
//   - admin/ NEXT TO public/ (repository as is, local trial, Docker)
//         → the site is ../public
// In both cases, config/, src/, vendor/ and data/ sit next to the served site.
// The database is the one named by config/config.ini ("db" key), as for the
// site itself: a single source of truth.
// The environment variables CMS_WWW, CMS_DB and CMS_PUBLIC_URL, when set
// (compose.yml), take precedence over detection.
// ---------------------------------------------------------------------------
$__inside = is_file(dirname(__DIR__) . '/page.php');

define('CMS_WWW', getenv('CMS_WWW') ?: ($__inside ? dirname(__DIR__) : dirname(__DIR__) . '/public'));
// Reading config/config.ini, shared with the site (4.5.0): src/ sits next to the
// served site, like config/ (with Docker, mounted in the editor container).
require_once dirname(CMS_WWW) . '/src/config_ini.php';
define('CMS_DB',  getenv('CMS_DB')  ?: db_path_from_config_ini(CMS_WWW));
// Address of the public site as seen from the browser: "View page" link,
// site CSS and images in the editor.
// Always absolute: a relative URL would depend on how the editor address was
// typed ("admin" or "admin/").
define('CMS_PUBLIC_URL', rtrim(getenv('CMS_PUBLIC_URL') ?: url_resolve(admin_path(), $__inside ? '..' : '../public'), '/'));
unset($__inside);

function url_resolve(string $base, string $rel): string {
    $parts = array_values(array_filter(explode('/', $base), 'strlen'));
    foreach (explode('/', $rel) as $seg) {
        if ($seg === '..') array_pop($parts);
        elseif ($seg !== '' && $seg !== '.') $parts[] = $seg;
    }
    return '/' . implode('/', $parts);
}

// config/config.ini (or config.ini.example): next to the served site, outside the web root.
function config_ini_value(string $www, string $key): ?string {
    return cms_config_value(dirname($www) . '/config', $key);
}

function db_path_from_config_ini(string $www): string {
    $p = config_ini_value($www, 'db');
    if ($p !== null && $p !== '') {
        return str_starts_with($p, '/') ? $p : $www . '/' . $p;   // relative to the served site, as in config.php
    }
    return dirname($www) . '/data/cms_lite.db';
}

// PHP errors never displayed, except with "debug = 1" in config.ini. Headers
// recommended by OWASP (Secure Headers Project): the editor cannot be shown in
// a frame of another site (anti-clickjacking).
ini_set('display_errors', config_ini_value(CMS_WWW, 'debug') === '1' ? '1' : '0');
ini_set('log_errors', '1');
// Time zone of uploaded image names (4.5.0, optional "timezone" key)
date_default_timezone_set(config_ini_value(CMS_WWW, 'timezone') ?: 'Europe/Brussels');
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header("Content-Security-Policy: frame-ancestors 'none'");
    header('Referrer-Policy: same-origin');
}
// Editor session and anti-CSRF token: before any output (4.5.1). In 4.5.0 the
// session started in the middle of the page; without output buffering (the
// Docker image), PHP refused it and the token stayed empty.
csrf_token();

define('UPLOAD_DIR',     'images/uploads');   // relative to CMS_WWW, and as is in src attributes
define('ATTACH_DIR',     'files');            // attachments (PDF, documents), same
define('MAX_WIDTH',      1600);

// Accepted attachments (actual type → extension)
const ATTACH_TYPES = [
    'application/pdf' => 'pdf',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'       => 'xlsx',
    'application/vnd.oasis.opendocument.text'        => 'odt',
    'application/vnd.oasis.opendocument.spreadsheet' => 'ods',
    // Audio and video
    'audio/mpeg'  => 'mp3',
    'audio/mp4'   => 'm4a',
    'audio/x-m4a' => 'm4a',
    'video/mp4'   => 'mp4',
    'video/webm'  => 'webm',
    'audio/webm'  => 'webm',
];
const AUDIO_EXT = ['mp3', 'm4a'];                     // player embedded in the page
const VIDEO_EXT = ['mp4', 'webm'];                    // 4.7.0: embedded video player, same
const MAX_IMAGE_BYTES = 25 * 1048576;               // images: resized in memory

// ---------------------------------------------------------------------------
// Interface language. Files in lang/ (fr.php = reference).
// Order of choice: cookie set by the selector in the header bar, then browser
// language, then French. A missing key falls back to French.
// ---------------------------------------------------------------------------
const LANG_COOKIE = 'cms_lite_lang';

function languages(): array {
    static $list = null;
    if ($list === null) {
        $list = [];
        foreach (glob(__DIR__ . '/lang/*.php') ?: [] as $f) {
            $code = basename($f, '.php');
            if (preg_match('/^[a-z]{2}$/', $code)) $list[$code] = (require $f)['_name'] ?? $code;
        }
        ksort($list);
    }
    return $list;
}

function lang(): string {
    static $lang = null;
    if ($lang !== null) return $lang;
    $avail = languages();
    $c = $_COOKIE[LANG_COOKIE] ?? '';
    if (isset($avail[$c])) return $lang = $c;
    // Accept-Language: "nl-BE,nl;q=0.9,fr;q=0.8" -> first available language, by preference
    $best = []; 
    foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '') as $part) {
        $p = explode(';q=', trim($part));
        $code = strtolower(substr(trim($p[0]), 0, 2));
        if (isset($avail[$code])) $best[$code] = max($best[$code] ?? 0, isset($p[1]) ? (float)$p[1] : 1.0);
    }
    arsort($best);
    return $lang = (array_key_first($best) ?? 'fr');
}

function strings(): array {
    static $s = null;
    if ($s === null) {
        $fr = require __DIR__ . '/lang/fr.php';
        $s  = lang() === 'fr' ? $fr : array_merge($fr, require __DIR__ . '/lang/' . lang() . '.php');
    }
    return $s;
}

// t('key') or t('key', value, …): the %s are replaced in order
function t(string $key, string ...$args): string {
    $s = strings()[$key] ?? $key;
    return $args ? vsprintf($s, $args) : $s;
}

function lang_select(): string {
    $out = '<select id="lang" class="adm-theme" aria-label="' . h(t('lang_label')) . '">';
    foreach (languages() as $code => $name) {
        $out .= '<option value="' . h($code) . '"' . ($code === lang() ? ' selected' : '') . '>' . h($name) . '</option>';
    }
    return $out . '</select>';
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . CMS_DB, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA busy_timeout = 5000');
        // Default journal (DELETE), not WAL: the public container reads the database read-only.
    }
    return $pdo;
}

function h(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function page_url(int $id): string {
    return CMS_PUBLIC_URL . '/' . page_target($id);
}

// URL of the editor directory (ends with /), computed from the running script:
// correct whether the typed address is ".../admin", ".../admin/" or
// ".../admin/index.php".
function admin_path(): string {
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/') . '/';
}

function admin_url(): string {
    $path = admin_path();
    // With the HOST ("//admin.example.be/..."): the editor declares <base href> to the
    // public site, and when that is on another host or port, an address starting
    // with "/" would be resolved there, not in the editor.
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return ($host !== '' ? '//' . $host : '') . $path;
}

function page_target(int $id): string {
    return 'page.php?id=' . $id;
}

// The main menu (t_menu, one level) is managed by the editor.
// Sub-menus (t_sub_menu) and HOMEPAGE are still set by hand in DB Browser:
// a page they lead to cannot be deleted from the editor.
function page_is_protected(int $id): bool {
    $q = db()->prepare("SELECT (SELECT COUNT(*) FROM t_sub_menu WHERE target = :t)
                             + (SELECT COUNT(*) FROM t_parameters WHERE ref = 'HOMEPAGE' AND TRIM(descr) = :t)");
    $q->execute([':t' => page_target($id)]);
    return (int)$q->fetchColumn() > 0;
}

function is_editor_page(string $txt): bool {
    return str_starts_with(ltrim($txt), HTML_MARKER);
}

function strip_marker(string $txt): string {
    $txt = ltrim($txt);
    return is_editor_page($txt) ? ltrim(substr($txt, strlen(HTML_MARKER))) : $txt;
}

// Any request that changes something must come from the editor itself.
// The Tinyauth cookie is sent automatically with requests: without this check,
// a third-party site could trigger a POST on behalf of the editor user (CSRF).
function require_same_origin(): void {
    $o    = parse_url($_SERVER['HTTP_ORIGIN'] ?? '');
    $auth = isset($o['host']) ? $o['host'] . (isset($o['port']) ? ':' . $o['port'] : '') : '';
    if ($auth === '' || strcasecmp($auth, $_SERVER['HTTP_HOST'] ?? '') !== 0) {
        json_out(['error' => t('err_origin')], 403);
    }
}

// ---------------------------------------------------------------------------
// Anti-CSRF token (4.5.0), in addition to the origin check above: the primary
// defence recommended by OWASP (CSRF Prevention Cheat Sheet, "synchronizer
// token"). The token is kept in the session, published in <meta name="csrf-token">
// on the editor pages, and sent back by the JavaScript in the X-CSRF-Token header
// of every call to api.php. The session is closed at once (no lock during
// file uploads).
// ---------------------------------------------------------------------------
function admin_session_start(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    session_name('cms_lite_admin');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $https,
                               'httponly' => true, 'samesite' => 'Strict']);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

// Read once per request, as soon as lib.php is loaded (see below): the session
// must start before any HTML output (4.5.1).
function csrf_token(): string {
    static $t = null;
    if ($t !== null) return $t;
    admin_session_start();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    $t = $_SESSION['csrf'];
    session_write_close();
    return $t;
}

function csrf_meta(): string {
    return '<meta name="csrf-token" content="' . h(csrf_token()) . '">';
}

function require_csrf(): void {
    $good = csrf_token();
    $sent = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if ($good === '' || !hash_equals($good, $sent)) {
        json_out(['error' => t('err_csrf')], 403);
    }
}

// Version suffix for CSS and JS: the address changes when the file changes,
// and the browser reloads it without clearing its cache.
function v(string $file): string {
    $t = @filemtime($file);
    return $t ? '?v=' . $t : '';
}

// Site style sheets, as declared in HTML_META_CSS: the editor loads exactly
// the same ones as the public pages.
function site_stylesheets(): array {
    $meta = (string)db()->query("SELECT descr FROM t_parameters WHERE ref = 'HTML_META_CSS'")->fetchColumn();
    preg_match_all('/<link\b[^>]*rel=["\']?stylesheet[^>]*>/i', $meta, $links);
    $hrefs = [];
    foreach ($links[0] as $link) {
        if (preg_match('/href=["\']([^"\']+)["\']/i', $link, $m)) $hrefs[] = $m[1];
    }
    return $hrefs ?: ['css/site.css'];   // same default as nav.php
}

// <link> to a site style sheet; $prefix = '' if the page declares <base href>
function site_css_link(string $href, string $prefix = ''): string {
    $local = !preg_match('#^([a-z]+:)?//#i', $href) && !str_contains($href, '?');
    $url = ($local ? $prefix : '') . $href . ($local ? v(CMS_WWW . '/' . $href) : '');
    return '<link rel="stylesheet" href="' . h($url) . '">';
}

// Editor appearance: Auto (follows the system) / Light / Dark, remembered by the
// browser. The script sits in <head> to apply the choice before rendering
// (no flash). The public site always stays on Auto.
function theme_head_script(): string {
    return <<<'HTML'
<script>
(function () {
  var KEY = 'cms-lite-admin-theme';
  function apply(t) {
    if (t === 'light' || t === 'dark') document.documentElement.dataset.theme = t;
    else delete document.documentElement.dataset.theme;
  }
  var saved = 'auto';
  try { saved = localStorage.getItem(KEY) || 'auto'; } catch (e) {}
  apply(saved);
  document.addEventListener('DOMContentLoaded', function () {
    var sel = document.getElementById('theme');
    if (!sel) return;
    sel.value = saved;
    sel.addEventListener('change', function () {
      apply(sel.value);
      try { localStorage.setItem(KEY, sel.value); } catch (e) {}
    });
    var lang = document.getElementById('lang');
    if (lang) lang.addEventListener('change', function () {
      document.cookie = 'cms_lite_lang=' + lang.value + '; path=/; max-age=31536000; SameSite=Lax'
                      + (location.protocol === 'https:' ? '; Secure' : '');
      location.reload();
    });
  });
})();
</script>
HTML;
}

function theme_select(): string {
    return '<select id="theme" class="adm-theme" aria-label="' . h(t('theme_label')) . '">'
         . '<option value="auto">' . h(t('theme_auto')) . '</option>'
         . '<option value="light">' . h(t('theme_light')) . '</option>'
         . '<option value="dark">' . h(t('theme_dark')) . '</option>'
         . '</select>';
}

// Maximum upload size, according to the effective PHP configuration
function ini_bytes(string $key): int {
    $v = trim((string)ini_get($key));
    $n = (int)$v;
    return match (strtolower(substr($v, -1))) { 'g' => $n << 30, 'm' => $n << 20, 'k' => $n << 10, default => $n };
}
function upload_limit_text(): string {
    $b = min(ini_bytes('upload_max_filesize'), ini_bytes('post_max_size'));
    return $b >= 1048576 ? round($b / 1048576) . ' ' . t('unit_mb') : round($b / 1024) . ' ' . t('unit_kb');
}

function json_out(array $data, int $code = 200): never {
    while (ob_get_level() > 0) ob_end_clean();
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// ---------------------------------------------------------------------------
// Cleaning of the received HTML: allow list of tags, attributes and classes.
// Tiptap only produces what is listed here; anything else necessarily comes
// from elsewhere (forged request, bug) and is removed.
// ---------------------------------------------------------------------------
const ALLOWED = [
    'p' => [], 'br' => [], 'hr' => [],
    'h2' => [], 'h3' => [], 'h4' => [],
    'strong' => [], 'em' => [], 'u' => [], 's' => [], 'code' => [],
    'pre' => [], 'blockquote' => [],
    'ul' => [], 'ol' => ['start', 'type'], 'li' => [],
    'a'   => ['href', 'target', 'rel'],
    'img' => ['src', 'alt', 'title', 'class'],
    'div' => ['class', 'data-type'],
    // Collapsible blocks (native HTML). No "open" attribute: on the site, a
    // collapsible block always appears closed, whatever its state in the editor.
    'details' => ['class'], 'summary' => [],
    // Editor tables (no style or width: the site CSS decides)
    'table' => ['class'], 'thead' => [], 'tbody' => [], 'tr' => [],
    'th' => ['colspan', 'rowspan'], 'td' => ['colspan', 'rowspan'],
    // Editor audio player (file from files/ only)
    'audio' => ['src', 'controls', 'preload', 'class'],
    // Editor video player (4.7.0; file from files/ only, never autoplay)
    'video' => ['src', 'controls', 'preload', 'class'],
];
const ALLOWED_CLASSES = [
    'div' => ['row', 'column', 'one-half', 'one-third', 'two-thirds',
              'ed-callout', 'ed-callout-info', 'ed-callout-note', 'ed-callout-success', 'ed-callout-warning', 'ed-callout-danger'],
    'details' => ['ed-details'],
    'img' => ['ed-img', 'ed-s', 'ed-m', 'ed-l', 'ed-left', 'ed-center', 'ed-right', 'ed-block-left', 'ed-block-right'],
    'table' => ['ed-table'],
    'audio' => ['ed-audio'],
    'video' => ['ed-video'],
];
const DROP_WITH_CONTENT = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'template', 'svg', 'math'];

function sanitize_html(string $html): string {
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>',
                   LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
    libxml_clear_errors();
    $root = $doc->getElementById('__root');
    if (!$root) return '';
    clean_children($root);
    $out = '';
    foreach ($root->childNodes as $child) $out .= $doc->saveHTML($child);
    return $out;
}

function clean_children(DOMNode $node): void {
    foreach (iterator_to_array($node->childNodes) as $child) {
        if ($child instanceof DOMText) continue;
        if (!$child instanceof DOMElement) { $node->removeChild($child); continue; } // comments, etc.

        $tag = strtolower($child->tagName);
        if (in_array($tag, DROP_WITH_CONTENT, true)) { $node->removeChild($child); continue; }

        clean_children($child);

        if (!array_key_exists($tag, ALLOWED)) {            // unknown tag: keep its content
            while ($child->firstChild) $node->insertBefore($child->firstChild, $child);
            $node->removeChild($child);
            continue;
        }
        foreach (iterator_to_array($child->attributes) as $attr) {
            $name = strtolower($attr->name);
            $val  = $attr->value;
            $keep = in_array($name, ALLOWED[$tag], true);
            if ($keep && $name === 'class') {
                $val  = implode(' ', array_intersect(preg_split('/\s+/', trim($val)), ALLOWED_CLASSES[$tag] ?? []));
                $keep = $val !== '';
            }
            if ($keep && $name === 'data-type') $keep = $val === 'detailsContent';
            if ($keep && ($name === 'colspan' || $name === 'rowspan')) $keep = (bool)preg_match('/^[1-9][0-9]?$/', $val);
            if ($keep && $name === 'href') $keep = safe_href($val);
            if ($keep && $name === 'src')  $keep = safe_src($val);
            if ($keep) $child->setAttribute($attr->name, $val);
            else       $child->removeAttribute($attr->name);
        }
        if ($tag === 'a') set_link_target($child);
        if ($tag === 'img' && !$child->hasAttribute('src')) $node->removeChild($child);
        if ($tag === 'audio' || $tag === 'video') {
            if (!str_starts_with($child->getAttribute('src'), ATTACH_DIR . '/')) { $node->removeChild($child); continue; }
            $child->setAttribute('controls', '');
            $child->setAttribute('preload', 'metadata');
            while ($child->firstChild) $child->removeChild($child->firstChild);
        }
    }
}

// Where links open, decided here rather than in the editor (applies to links
// inserted, pasted or created automatically):
//   - page of the site                     → same tab
//   - attachment, external site            → new tab
//   - mailto:, tel:, anchor                → not applicable
function set_link_target(DOMElement $a): void {
    $a->removeAttribute('target');
    $a->removeAttribute('rel');
    $href = trim($a->getAttribute('href'));
    if ($href === '') return;

    $newTab = str_starts_with($href, ATTACH_DIR . '/');
    if (preg_match('#^https?://#i', $href)) {
        $host = strtolower((string)parse_url($href, PHP_URL_HOST));
        $site = strtolower((string)(parse_url(CMS_PUBLIC_URL, PHP_URL_HOST) ?: parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST)));
        $newTab = $host !== $site;
    }
    if ($newTab) {
        $a->setAttribute('target', '_blank');
        $a->setAttribute('rel', 'noopener');
    }
}

function safe_href(string $url): bool {
    $url = trim($url);
    // 4.5.2: no control characters (libxml drops some invisible bytes when
    // serialising: "\x01javascript:" became "javascript:")
    if (preg_match('/[\x00-\x1F\x7F]/', $url)) return false;
    if ($url === '' || $url[0] === '#' || $url[0] === '/') return true;
    $scheme = parse_url($url, PHP_URL_SCHEME);
    if ($scheme === null || $scheme === false) return !preg_match('/^\s*[a-z0-9+.-]+:/i', $url); // relative
    return in_array(strtolower($scheme), ['http', 'https', 'mailto', 'tel'], true);
}

function safe_src(string $url): bool {
    if (preg_match('/[\x00-\x1F\x7F]/', $url)) return false;   // 4.5.2
    // 4.6.0: all site images (images/..., not only images/uploads/), so that a
    // page taken over from an older site keeps its pictures. Plain relative path
    // only: no ".." (even encoded), no backslash.
    $rel = str_starts_with($url, 'images/') || str_starts_with($url, ATTACH_DIR . '/');
    return $rel && !preg_match('/\.\.|%2e|\\\\/i', $url)
        || preg_match('#^https?://#i', $url) === 1;
}
