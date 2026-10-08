<?php if (isset($_GET['created'])): ?><p class="workspace-success" role="status"><?= $view === 'events' ? 'Event' : 'Article' ?> saved.</p><?php endif; ?>
<?php if (!$activePages): ?>
<dialog class="workspace-modal" id="section-page-modal" aria-labelledby="section-page-title"><div class="workspace-modal-inner"><button class="workspace-modal-close" type="button" data-close-modal aria-label="Close dialog">×</button>
<h2 id="section-page-title">Create <?= $view === 'events' ? 'events' : 'blog' ?> page</h2><p>Create a page for your <?= $view === 'events' ? 'events' : 'articles' ?>. You can add your <?= $view === 'events' ? 'event' : 'article' ?> immediately after.</p>
<?php if (($_POST['action'] ?? '') === 'page'): foreach ($errors as $error): ?><p class="workspace-error" role="alert"><?= app_h($error) ?></p><?php endforeach; endif; ?>
<form class="workspace-form" method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="action" value="page"><input type="hidden" name="section" value="<?= app_h($activeSection) ?>">
<label>Page name<input name="title" maxlength="120" required value="<?= app_h((string)($_POST['title'] ?? '')) ?>"></label><label>About this page<textarea name="description" maxlength="500" required rows="3"><?= app_h((string)($_POST['description'] ?? '')) ?></textarea></label><button class="workspace-primary" type="submit">Create page</button></form></div></dialog>
<?php else: ?>
<dialog class="workspace-modal" id="entry-modal" aria-labelledby="entry-modal-title"><div class="workspace-modal-inner"><button class="workspace-modal-close" type="button" data-close-modal aria-label="Close dialog">×</button>
<h2 id="entry-modal-title">Create <?= $view === 'events' ? 'event' : 'article' ?></h2>
<?php if (($_POST['action'] ?? '') === 'post'): foreach ($errors as $error): ?><p class="workspace-error" role="alert"><?= app_h($error) ?></p><?php endforeach; endif; ?>
<form class="workspace-form" method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="action" value="post"><input type="hidden" name="post_id" value="0">
<label><?= $view === 'events' ? 'Events page' : 'Blog page' ?><select name="page_id" required><?php foreach ($activePages as $creatorPage): ?><option value="<?= (int)$creatorPage['id'] ?>" <?= (int)($_POST['page_id'] ?? 0) === (int)$creatorPage['id'] ? 'selected' : '' ?>><?= app_h($creatorPage['title']) ?></option><?php endforeach; ?></select></label>
<label>Title<input name="title" maxlength="160" required value="<?= app_h((string)(($_POST['action'] ?? '') === 'post' ? ($_POST['title'] ?? '') : '')) ?>"></label>
<label>Entry address<input name="slug" maxlength="80" placeholder="Leave blank to use the title" value="<?= app_h((string)(($_POST['action'] ?? '') === 'post' ? ($_POST['slug'] ?? '') : '')) ?>"></label>
<label>Content<textarea name="body" rows="8" maxlength="30000" required><?= app_h((string)(($_POST['action'] ?? '') === 'post' ? ($_POST['body'] ?? '') : '')) ?></textarea></label>
<?php if ($view === 'events'): ?><label>Date and time<input type="datetime-local" name="event_date" required value="<?= app_h((string)($_POST['event_date'] ?? '')) ?>"></label><label>Venue or online location<input name="venue" maxlength="200" required value="<?= app_h((string)($_POST['venue'] ?? '')) ?>"></label><?php endif; ?>
<label>Status<select name="status"><option value="draft" <?= ($_POST['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option><option value="published" <?= ($_POST['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option></select></label><button class="workspace-primary" type="submit">Save <?= $view === 'events' ? 'event' : 'article' ?></button></form></div></dialog>
<?php endif; ?>
