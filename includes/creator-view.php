
<div class="creator-toolbar"><button class="workspace-primary" type="button" data-open-modal="create-page">+ Create a page</button></div>
<dialog class="workspace-modal" id="create-page" aria-labelledby="create-page-title"><div class="workspace-modal-inner"><button class="workspace-modal-close" type="button" data-close-modal aria-label="Close dialog">×</button><div class="workspace-editor creator-panel creator-create-panel">
  <h2 id="create-page-title">Create a page</h2><?php if (($_POST['action'] ?? '') === 'page'): foreach ($errors as $error): ?><p class="workspace-error" role="alert"><?= app_h($error) ?></p><?php endforeach; endif; ?>
  <form class="workspace-form creator-page-form" method="post">
    <input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>">
    <input type="hidden" name="action" value="page">
    <label>Section<select name="section" required><?php foreach ($sections as $key => $label): ?><option value="<?= app_h($key) ?>" <?= (($_POST['action'] ?? '') === 'page' ? ($_POST['section'] ?? '') : ($_GET['section'] ?? '')) === $key ? 'selected' : '' ?>><?= app_h($label) ?></option><?php endforeach; ?></select></label>
    <label>Page name<input name="title" maxlength="120" required placeholder="Your page name" value="<?= app_h((string)(($_POST['action'] ?? '') === 'page' ? ($_POST['title'] ?? '') : '')) ?>"></label>
    <label>About this page<textarea name="description" maxlength="500" required rows="3" placeholder="What is this page about?"><?= app_h((string)(($_POST['action'] ?? '') === 'page' ? ($_POST['description'] ?? '') : '')) ?></textarea></label>
    <button class="workspace-primary" type="submit">Create page ↗</button>
  </form>
</div></div></dialog>
<?php if ($page): ?>
<section class="workspace-editor creator-preview-panel" id="page-preview" aria-labelledby="page-preview-title">
  <div class="creator-preview-heading">
    <div><span class="workspace-eyebrow">PAGE PREVIEW</span><h2 id="page-preview-title">Your page in the dashboard</h2></div>
    <a class="workspace-all-link" href="<?= app_h(app_page_url($page['section'], $page['slug'])) ?>" target="_blank" rel="noopener">Open public page ↗</a>
  </div>
  <div class="creator-page-preview">
    <span class="workspace-eyebrow"><?= app_h(strtoupper($sections[$page['section']])) ?></span>
    <h3><?= app_h($page['title']) ?></h3>
    <p><?= app_h($page['description']) ?></p>
    <span class="creator-preview-address"><?= app_h(app_page_url($page['section'], $page['slug'])) ?></span>
    <div class="creator-preview-entries">
      <h4>Published entries</h4>
      <?php $publishedPosts = array_values(array_filter($posts, static fn(array $item): bool => $item['status'] === 'published')); ?>
      <?php if (!$publishedPosts): ?><p>No published entries yet. Add an entry below to build this page.</p><?php endif; ?>
      <?php foreach ($publishedPosts as $item): ?>
        <article>
          <strong><?= app_h($item['title']) ?></strong>
          <p><?= app_h(mb_strimwidth(preg_replace('/\s+/', ' ', $item['body']) ?? '', 0, 180, '…')) ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
  <a class="workspace-primary" href="dashboard?view=pages&amp;page=<?= (int)$page['id'] ?>#editor">+ Add an entry</a>
</section>
<?php endif; ?>
<div class="workspace-editor creator-panel"><h2>Your pages</h2><?php if (!$pages): ?><p>No pages yet. Create your first page here.</p><?php endif; ?><?php foreach ($pages as $item): ?><article class="creator-item"><span class="workspace-eyebrow"><?= app_h($sections[$item['section']]) ?></span><h3><a href="dashboard?view=pages&amp;page=<?= (int)$item['id'] ?>"><?= app_h($item['title']) ?></a></h3><p><?= app_h($item['description']) ?></p><a href="<?= app_h(app_page_url($item['section'],$item['slug'])) ?>">View public page ↗</a></article><?php endforeach; ?></div>
<?php if ($page): ?><div class="creator-toolbar"><a class="workspace-primary" href="dashboard?view=pages&amp;page=<?= (int)$page['id'] ?>#editor">+ <?= $page['section'] === 'blog' ? 'New article' : ($page['section'] === 'events' ? 'New event' : 'Add an entry') ?></a></div><dialog class="workspace-modal" id="editor" aria-labelledby="editor-title"><div class="workspace-modal-inner"><button class="workspace-modal-close" type="button" data-close-modal aria-label="Close dialog">×</button><div class="workspace-editor creator-panel"><span class="workspace-eyebrow"><?= app_h(strtoupper($sections[$page['section']])) ?></span><h2 id="editor-title"><?= $editing ? 'Edit entry' : ($page['section'] === 'blog' ? 'Create article' : ($page['section'] === 'events' ? 'Create event' : 'Add an entry')) ?> for <?= app_h($page['title']) ?></h2><?php if (($_POST['action'] ?? '') === 'post'): foreach ($errors as $error): ?><p class="workspace-error" role="alert"><?= app_h($error) ?></p><?php endforeach; endif; ?><p>Public page: <a href="<?= app_h(app_page_url($page['section'],$page['slug'])) ?>"><?= app_h(app_page_url($page['section'],$page['slug'])) ?></a></p><form class="workspace-form" method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="action" value="post"><input type="hidden" name="page_id" value="<?= (int)$page['id'] ?>"><input type="hidden" name="post_id" value="<?= (int)($editing['id'] ?? 0) ?>"><label>Title<input name="title" maxlength="160" required placeholder="Entry title" value="<?= app_h($editing['title'] ?? '') ?>"></label><label>Entry address<input name="slug" maxlength="80" placeholder="Leave blank to use the title" value="<?= app_h($editing['slug'] ?? '') ?>"></label><label><?= $page['section'] === 'marketplace' ? 'Product or service details' : 'Content' ?><textarea name="body" rows="8" maxlength="30000" required placeholder="Write your content here"><?= app_h($editing['body'] ?? '') ?></textarea></label><?php if (in_array($page['section'], ['marketplace','ebook','elearning'], true)): ?><label>Price (optional, enter an amount only)<input name="price" inputmode="decimal" placeholder="0.00" value="<?= app_h($editing['price'] ?? '') ?>"></label><?php endif; ?><?php if ($page['section'] === 'events'): ?><label>Date and time<input type="datetime-local" name="event_date" required title="Choose the event date and time" value="<?= app_h($editing['event_date'] ?? '') ?>"></label><label>Venue or online location<input name="venue" maxlength="200" required placeholder="Venue or meeting link" value="<?= app_h($editing['venue'] ?? '') ?>"></label><?php endif; ?><label>Status<select name="status"><option value="draft" <?= ($editing['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option><option value="published" <?= ($editing['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option></select></label><button class="workspace-primary" type="submit">Save entry ↗</button></form></div></div></dialog><div class="workspace-editor creator-panel"><h2>Entries</h2><?php if (!$posts): ?><p>No entries yet.</p><?php endif; ?><?php foreach ($posts as $post): ?><article class="creator-item"><span class="workspace-eyebrow"><?= app_h(strtoupper($post['status'])) ?></span><h3><?= app_h($post['title']) ?></h3><a href="dashboard?view=pages&amp;page=<?= (int)$page['id'] ?>&amp;edit=<?= (int)$post['id'] ?>#editor">Edit</a><?php if ($post['status'] === 'published'): ?> · <a href="<?= app_h(app_page_url($page['section'],$page['slug'],$post['slug'])) ?>">View</a><?php endif; ?><form method="post" class="creator-delete"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="page_id" value="<?= (int)$page['id'] ?>"><input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>"><button type="submit">Delete</button></form></article><?php endforeach; ?></div><?php endif; ?>
