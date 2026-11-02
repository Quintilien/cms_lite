<?php
// CMS Lite — admin: editing a page (labels: lang/*.php)
declare(strict_types=1);
require __DIR__ . '/lib.php';

$pdo = db();
$id  = (int)($_GET['id'] ?? 0);
$page = ['title' => '', 'pending' => 0, 'txt' => HTML_MARKER . "\n<p></p>",
         'categ' => '', 'categ_pagenum' => 0, 'author' => '', 'date' => ''];
$inMenu = true;   // a new page appears in the menu unless told otherwise

if ($id > 0) {
    $q = $pdo->prepare('SELECT title, pending, txt, categ, categ_pagenum, author, date FROM t_pages WHERE id = ?');
    $q->execute([$id]);
    $page = $q->fetch();
    if (!$page) { http_response_code(404); exit(h(t('page_not_found'))); }
    $m = $pdo->prepare('SELECT COUNT(*) FROM t_menu WHERE target = ?');
    $m->execute([page_target($id)]);
    $inMenu = (int)$m->fetchColumn() > 0;
}
$editable = is_editor_page((string)$page['txt']);
// Pages of the site, for the link dialog (titles sorted, published and drafts)
$sitePages = $pdo->query('SELECT id, title FROM t_pages ORDER BY title COLLATE NOCASE')->fetchAll();
$adm = admin_url();
// 4.5.0: suggestions for category and author (values already in use)
$categs  = $pdo->query("SELECT DISTINCT categ FROM t_pages WHERE categ <> '' ORDER BY categ COLLATE NOCASE")->fetchAll(PDO::FETCH_COLUMN);
$authors = $pdo->query("SELECT DISTINCT author FROM t_pages WHERE author <> '' ORDER BY author COLLATE NOCASE")->fetchAll(PDO::FETCH_COLUMN);
$hasProps = trim((string)$page['categ']) !== '' || trim((string)$page['author']) !== '' || trim((string)$page['date']) !== '';

// Toolbar button: command, label, tooltip (optional)
function tb(string $cmd, string $label, ?string $tip = null, bool $html = false): string {
    return '<button data-cmd="' . h($cmd) . '"' . ($tip !== null ? ' title="' . h(t($tip)) . '"' : '') . '>'
         . ($html ? t($label) : h(t($label))) . '</button>';
}
?>
<!doctype html>
<html lang="<?= h(lang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?= theme_head_script() ?>
<?= csrf_meta() ?>
<title><?= h($id ? t('title_edit', (string)$page['title']) : t('new_page')) ?></title>
<!-- Relative URLs (site CSS, images and attachments in the text, "View page"
     link) point to the public site; the editor's own URLs are absolute. -->
<base href="<?= h(CMS_PUBLIC_URL) ?>/">
<?php foreach (site_stylesheets() as $css) echo site_css_link($css), "\n"; ?>
<link rel="stylesheet" href="<?= h($adm) ?>admin.css<?= v(__DIR__ . '/admin.css') ?>">
</head>
<body class="adm">
<header class="adm-bar">
  <a href="<?= h($adm) ?>index.php"><?= h(t('back_pages')) ?></a>
  <span id="status" class="adm-status" role="status" aria-live="polite"></span>
  <span class="adm-bar-right">
    <?= lang_select() ?>
    <?= theme_select() ?>
    <a id="view-link" href="<?= h($id ? page_target($id) : '#') ?>" target="cms-apercu" <?= $id ? '' : 'hidden' ?>><?= h(t('view_page')) ?></a>
    <?php if ($editable): ?><button id="save" class="button-primary"><?= h(t('save')) ?></button><?php endif ?>
  </span>
</header>

<?php if (!$editable): ?>
<main class="adm-main">
  <h1><?= h($page['title']) ?></h1>
  <p><?= h(t('classic_notice')) ?></p>
</main>
<?php else: ?>
<main class="adm-main adm-edit">
  <input type="hidden" id="page-id" value="<?= $id ?>">
  <input type="text" id="title" class="adm-title" placeholder="<?= h(t('title_placeholder')) ?>" value="<?= h($page['title']) ?>" aria-label="<?= h(t('title_placeholder')) ?>" autocomplete="off">
  <div class="adm-options">
    <label><input type="checkbox" id="in-menu" <?= $inMenu ? 'checked' : '' ?>> <?= h(t('in_menu')) ?></label>
    <label><input type="checkbox" id="draft" <?= (int)$page['pending'] === 1 ? 'checked' : '' ?>> <?= h(t('draft')) ?></label>
  </div>

  <details class="adm-props">
    <summary><?= h(t('props_title')) ?><?php if ($hasProps): ?> <span class="adm-muted">— <?= h(implode(' · ', array_filter([trim((string)$page['categ']), trim((string)$page['author']), trim((string)$page['date'])], 'strlen'))) ?></span><?php endif ?></summary>
    <div class="adm-props-grid">
      <label for="prop-categ"><?= h(t('props_categ')) ?></label>
      <input type="text" id="prop-categ" list="dl-categ" maxlength="200" autocomplete="off" value="<?= h((string)$page['categ']) ?>">
      <label for="prop-pagenum"><?= h(t('props_pagenum')) ?></label>
      <input type="number" id="prop-pagenum" min="0" step="1" value="<?= (int)$page['categ_pagenum'] ?>">
      <label for="prop-author"><?= h(t('props_author')) ?></label>
      <input type="text" id="prop-author" list="dl-author" maxlength="200" autocomplete="off" value="<?= h((string)$page['author']) ?>">
      <label for="prop-date"><?= h(t('props_date')) ?></label>
      <input type="text" id="prop-date" maxlength="40" autocomplete="off" placeholder="<?= h(t('props_date_ph')) ?>" value="<?= h((string)$page['date']) ?>">
    </div>
    <p class="adm-hint"><?= h(t('props_hint')) ?></p>
    <datalist id="dl-categ"><?php foreach ($categs as $c): ?><option value="<?= h((string)$c) ?>"><?php endforeach ?></datalist>
    <datalist id="dl-author"><?php foreach ($authors as $a): ?><option value="<?= h((string)$a) ?>"><?php endforeach ?></datalist>
  </details>

  <div id="toolbar" class="adm-toolbar" role="toolbar" aria-label="<?= h(t('toolbar_label')) ?>">
    <span class="grp">
      <?= tb('p', 'tb_text', 'tb_text_tip') ?>
      <?= tb('h2', 'tb_h2', 'tb_h2') ?>
      <?= tb('h3', 'tb_h3', 'tb_h3') ?>
      <?= tb('h4', 'tb_h4', 'tb_h4') ?>
    </span>
    <span class="grp">
      <button data-cmd="bold" title="<?= h(t('tb_bold_tip')) ?>"><b><?= h(t('tb_bold')) ?></b></button>
      <button data-cmd="italic" title="<?= h(t('tb_italic_tip')) ?>"><i><?= h(t('tb_italic')) ?></i></button>
      <button data-cmd="underline" title="<?= h(t('tb_underline_tip')) ?>"><u><?= h(t('tb_underline')) ?></u></button>
      <?= tb('link', 'tb_link', 'tb_link') ?>
    </span>
    <span class="grp">
      <?= tb('ul', 'tb_ul', 'tb_ul_tip') ?>
      <?= tb('ol', 'tb_ol', 'tb_ol_tip') ?>
      <?= tb('quote', 'tb_quote', 'tb_quote') ?>
      <?= tb('code', 'tb_code', 'tb_code_tip') ?>
      <?= tb('codeblock', 'tb_codeblock', 'tb_codeblock_tip') ?>
      <?= tb('hr', 'tb_hr', 'tb_hr_tip') ?>
    </span>
    <span class="grp">
      <?= tb('image', 'tb_image', 'tb_image_tip') ?>
      <?= tb('attach', 'tb_attach', 'tb_attach_tip') ?>
      <?= tb('table', 'tb_table', 'tb_table_tip') ?>
      <?= tb('callout', 'tb_callout', 'tb_callout_tip') ?>
      <?= tb('details', 'tb_details', 'tb_details_tip') ?>
      <select id="columns" aria-label="<?= h(t('columns_label')) ?>">
        <option value=""><?= h(t('col_insert')) ?></option>
        <option value="1/2+1/2"><?= h(t('col_2')) ?></option>
        <option value="1/3+1/3+1/3"><?= h(t('col_3')) ?></option>
        <option value="2/3+1/3"><?= h(t('col_wide_narrow')) ?></option>
        <option value="1/3+2/3"><?= h(t('col_narrow_wide')) ?></option>
      </select>
      <button data-cmd="unsetColumns" title="<?= h(t('col_remove_tip')) ?>" hidden><?= h(t('col_remove')) ?></button>
    </span>
    <span class="grp">
      <button data-cmd="undo" title="<?= h(t('undo_tip')) ?>">↶</button>
      <button data-cmd="redo" title="<?= h(t('redo_tip')) ?>">↷</button>
    </span>
    <div id="image-tools" class="adm-image-tools" hidden>
      <span class="grp">
        <?= tb('ed-s', 'img_s') ?>
        <?= tb('ed-m', 'img_m') ?>
        <?= tb('ed-l', 'img_l') ?>
      </span>
      <span class="grp">
        <?= tb('ed-block-left', 'img_block_left', 'img_block_left_tip') ?>
        <?= tb('ed-center', 'img_center', 'img_center_tip') ?>
        <?= tb('ed-block-right', 'img_block_right', 'img_block_right_tip') ?>
        <span class="sep"></span>
        <?= tb('ed-left', 'img_left', 'img_left_tip') ?>
        <?= tb('ed-right', 'img_right', 'img_right_tip') ?>
      </span>
    </div>
    <div id="callout-tools" class="adm-image-tools" hidden>
      <span class="grp">
        <?= tb('callout-info', 'callout_info') ?>
        <?= tb('callout-note', 'callout_note') ?>
        <?= tb('callout-success', 'callout_success') ?>
        <?= tb('callout-warning', 'callout_warning') ?>
        <?= tb('callout-danger', 'callout_danger') ?>
        <span class="sep"></span>
        <?= tb('calloutRemove', 'callout_remove', 'callout_remove_tip') ?>
      </span>
    </div>
    <div id="details-tools" class="adm-image-tools" hidden>
      <span class="grp">
        <?= tb('detailsRemove', 'details_remove', 'details_remove_tip') ?>
      </span>
    </div>
    <div id="table-tools" class="adm-image-tools" hidden>
      <span class="grp">
        <?= tb('tblRowAdd', 'tbl_row_add') ?>
        <?= tb('tblColAdd', 'tbl_col_add') ?>
        <?= tb('tblRowDel', 'tbl_row_del') ?>
        <?= tb('tblColDel', 'tbl_col_del') ?>
        <span class="sep"></span>
        <?= tb('tblHeader', 'tbl_header', 'tbl_header_tip') ?>
        <span class="sep"></span>
        <?= tb('tblDelete', 'tbl_delete') ?>
      </span>
    </div>
    <input type="file" id="file" accept="image/jpeg,image/png,image/webp" multiple hidden>
    <input type="file" id="attach-file" accept=".pdf,.docx,.xlsx,.odt,.ods,.mp3,.m4a,.mp4,.webm" multiple hidden>
  </div>

  <div id="editor" class="ed-page" data-admin="<?= h($adm) ?>"></div>
</main>

<dialog id="link-dialog" class="adm-dialog">
  <form id="link-form" method="dialog">
    <h2><?= h(t('link_title')) ?></h2>
    <label for="link-url"><?= h(t('link_url_label')) ?></label>
    <input type="text" id="link-url" autocomplete="off" spellcheck="false">
    <p class="adm-hint"><?= h(t('link_hint')) ?></p>
    <label for="link-page"><?= h(t('link_or_page')) ?></label>
    <select id="link-page">
      <option value=""><?= h(t('link_choose')) ?></option>
<?php foreach ($sitePages as $sp): ?>
      <option value="<?= h(page_target((int)$sp['id'])) ?>"><?= h($sp['title']) ?></option>
<?php endforeach ?>
    </select>
    <div class="adm-dialog-buttons">
      <button type="button" id="link-remove" hidden><?= h(t('link_remove')) ?></button>
      <span></span>
      <button type="button" id="link-cancel"><?= h(t('link_cancel')) ?></button>
      <button type="submit" class="button-primary"><?= h(t('link_apply')) ?></button>
    </div>
  </form>
</dialog>

<script type="application/json" id="initial-content"><?=
  json_encode(strip_marker((string)$page['txt']), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE)
?></script>
<script type="application/json" id="site-pages"><?=
  json_encode(array_map(fn($p) => ['id' => (int)$p['id'], 'title' => (string)$p['title']], $sitePages), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE)
?></script>
<script type="application/json" id="i18n"><?=
  json_encode(strings(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE)
?></script>
<script src="<?= h($adm) ?>editor.js<?= v(__DIR__ . '/editor.js') ?>"></script>
<?php endif ?>
</body>
</html>
