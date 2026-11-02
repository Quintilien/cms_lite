<?php
// CMS Lite — admin: list of pages
declare(strict_types=1);
require __DIR__ . '/lib.php';

$pdo   = db();
$home  = trim((string)$pdo->query("SELECT descr FROM t_parameters WHERE ref = 'HOMEPAGE'")->fetchColumn());
$pages = $pdo->query('SELECT id, title, pending, txt FROM t_pages ORDER BY title COLLATE NOCASE')->fetchAll();
$menu  = $pdo->query('SELECT id, item, target FROM t_menu ORDER BY display_order, id')->fetchAll();
$inMenu = array_column($menu, 'target');
$inSub  = $pdo->query('SELECT target FROM t_sub_menu')->fetchAll(PDO::FETCH_COLUMN);
$adm    = admin_url();   // URL absolues : voir lib.php
?>
<!doctype html>
<html lang="<?= h(lang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?= theme_head_script() ?>
<?= csrf_meta() ?>
<title><?= h(t('pages_title')) ?></title>
<?php foreach (site_stylesheets() as $css) echo site_css_link($css, CMS_PUBLIC_URL . '/'), "\n"; ?>
<link rel="stylesheet" href="<?= h($adm) ?>admin.css<?= v(__DIR__ . '/admin.css') ?>">
</head>
<body class="adm">
<header class="adm-bar">
  <h1><?= h(t('pages_title')) ?></h1>
  <span class="adm-bar-right">
    <?= lang_select() ?>
    <?= theme_select() ?>
    <a class="button button-primary" href="<?= h($adm) ?>edit.php"><?= h(t('new_page')) ?></a>
  </span>
</header>

<main class="adm-main">
  <p id="status" class="adm-status" role="status"></p>

  <table class="adm-pages">
    <thead><tr><th><?= h(t('col_title')) ?></th><th><?= h(t('col_state')) ?></th><th><?= h(t('col_menu')) ?></th><th></th></tr></thead>
    <tbody>
    <?php foreach ($pages as $p):
        $id      = (int)$p['id'];
        $editable = is_editor_page((string)$p['txt']);
        $isHome  = $home === page_target($id);
        $protected = $isHome || in_array(page_target($id), $inSub, true); ?>
      <tr>
        <td>
          <?php if ($editable): ?>
            <a href="<?= h($adm) ?>edit.php?id=<?= $id ?>"><?= h($p['title']) ?></a>
          <?php else: ?>
            <?= h($p['title']) ?>
          <?php endif ?>
          <?php if ($isHome): ?><span class="adm-tag"><?= h(t('tag_home')) ?></span><?php endif ?>
        </td>
        <td>
          <?php if (!$editable): ?>
            <span class="adm-muted" title="<?= h(t('state_classic_tip')) ?>"><?= h(t('state_classic')) ?></span>
          <?php elseif ((int)$p['pending'] === 1): ?>
            <span class="adm-draft"><?= h(t('state_draft')) ?></span>
          <?php else: ?>
            <?= h(t('state_published')) ?>
          <?php endif ?>
        </td>
        <td><?= in_array(page_target($id), $inMenu, true) ? h(t('yes')) : '' ?></td>
        <td class="adm-actions">
          <a href="<?= h(page_url($id)) ?>" target="cms-apercu"><?= h(t('view')) ?></a>
          <?php if ($editable): ?>
            <a href="<?= h($adm) ?>edit.php?id=<?= $id ?>"><?= h(t('edit')) ?></a>
            <?php if (!$protected): ?>
              <button type="button" class="adm-link adm-danger" data-delete="<?= $id ?>" data-title="<?= h($p['title']) ?>"><?= h(t('delete')) ?></button>
            <?php endif ?>
          <?php endif ?>
        </td>
      </tr>
    <?php endforeach ?>
    <?php if (!$pages): ?>
      <tr><td colspan="4" class="adm-muted"><?= h(t('no_pages', t('new_page'))) ?></td></tr>
    <?php endif ?>
    </tbody>
  </table>

  <h2 class="adm-section"><?= h(t('menu_order')) ?></h2>
  <ol class="adm-menu">
  <?php foreach ($menu as $i => $m): ?>
    <li>
      <span><?= h($m['item']) ?></span>
      <span class="adm-move">
        <button type="button" data-move="<?= (int)$m['id'] ?>" data-dir="up"   aria-label="<?= h(t('move_up')) ?>"   <?= $i === 0 ? 'disabled' : '' ?>>↑</button>
        <button type="button" data-move="<?= (int)$m['id'] ?>" data-dir="down" aria-label="<?= h(t('move_down')) ?>" <?= $i === count($menu) - 1 ? 'disabled' : '' ?>>↓</button>
      </span>
    </li>
  <?php endforeach ?>
  </ol>
  <p class="adm-version">CMS Lite <?= h(cms_version()) ?></p>
</main>

<script>
const T = <?= json_encode(['unexpected_response' => t('unexpected_response'), 'server_error' => t('server_error'), 'confirm_delete' => t('confirm_delete')], JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?>;
async function post(action, data) {
  const fd = new FormData()
  for (const [k, v] of Object.entries(data)) fd.append(k, v)
  const r = await fetch(<?= json_encode($adm) ?> + 'api.php?action=' + action, { method: 'POST', body: fd,
    headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content } })
  const text = await r.text()
  let j
  try { j = JSON.parse(text) } catch {
    throw new Error(T.unexpected_response.replace('%s', r.status) + ' : ' + text.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 160))
  }
  if (!r.ok || j.error) throw new Error(j.error || T.server_error.replace('%s', r.status))
  return j
}
function fail(e) {
  const s = document.getElementById('status')
  s.textContent = e.message
  s.classList.add('err')
}
document.querySelectorAll('[data-delete]').forEach(b => b.addEventListener('click', async () => {
  if (!confirm(T.confirm_delete.replace('%s', b.dataset.title))) return
  try { await post('delete', { id: b.dataset.delete }); location.reload() } catch (e) { fail(e) }
}))
document.querySelectorAll('[data-move]').forEach(b => b.addEventListener('click', async () => {
  try { await post('menu_move', { id: b.dataset.move, dir: b.dataset.dir }); location.reload() } catch (e) { fail(e) }
}))
</script>
</body>
</html>
