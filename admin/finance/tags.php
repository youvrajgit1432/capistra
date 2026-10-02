<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/admin.php';

$user = capistra_admin_guard();
capistra_guard_module('tags');
$pdo = capistra_pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!capistra_csrf_verify($_POST['csrf_token'] ?? null)) {
        capistra_flash_set('error', 'Security token mismatch. Please try again.');
    } else {
        try {
            $id = capistra_tag_save([
                'id'    => (int) ($_POST['id'] ?? 0),
                'name'  => capistra_post_string('name'),
                'color' => capistra_post_string('color'),
            ]);
            capistra_audit($pdo, 'save', 'tag', $id, capistra_post_string('name'));
            capistra_flash_set('success', 'Tag saved.');
        } catch (Throwable $e) {
            capistra_flash_set('error', 'Could not save tag: ' . $e->getMessage());
        }
    }
    header('Location: tags.php');
    exit;
}

$tags = capistra_tags();

capistra_layout_header('Tags', 'tags.php', '../../');
?>
<h1>Tags</h1>
<p class="hint">Tags supplement categories — e.g. College, Farm, Personal, Travel, Family. Transactions may have several.</p>
<?php capistra_flash_render(); ?>
<div class="grid grid--2">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th scope="col">Tag</th><th scope="col">Colour</th></tr></thead>
      <tbody>
      <?php foreach ($tags as $t): ?>
        <tr><th scope="row"><?= e((string) $t['name']) ?></th><td><span class="badge" style="background:<?= e((string) ($t['color'] ?? '#94a3b8')) ?>;color:#fff"><?= e((string) ($t['color'] ?? '')) ?></span></td></tr>
      <?php endforeach; ?>
      <?php if ($tags === []): ?><tr><td class="empty-state" colspan="2">No tags yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <div class="card">
    <h2>Add tag</h2>
    <form method="post" action="tags.php">
      <?= capistra_csrf_field() ?>
      <div class="form-row"><label for="name">Name</label><input id="name" name="name" required maxlength="80"></div>
      <div class="form-row"><label for="color">Colour</label><input id="color" name="color" placeholder="#0F172A"></div>
      <button class="btn btn--primary" type="submit">Save tag</button>
    </form>
  </div>
</div>
<?php capistra_layout_footer(); ?>
