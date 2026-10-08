<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/app.php';
require_once __DIR__ . '/includes/manager-config.php';
$user=app_require_user();
$admin=manager_admin_email();
if ($admin==='' || strcasecmp($user['email'],$admin)!==0) { http_response_code(403); exit('Access denied.'); }
$db=app_db();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    app_verify_csrf();
    $id=filter_var($_POST['product_id'] ?? null,FILTER_VALIDATE_INT) ?: 0;
    $decision=(string)($_POST['decision'] ?? '');
    if ($id && in_array($decision,['published','rejected'],true)) {
        $q=$db->prepare("UPDATE page_posts SET status=? WHERE id=? AND status='pending'");
        $q->execute([$decision,$id]);
    }
    header('Location: seller-moderation',true,303); exit;
}
$queue=$db->query("SELECT p.id,p.title,p.body,p.created_at,c.title AS store_title,u.email FROM page_posts p JOIN creator_pages c ON c.id=p.page_id JOIN users u ON u.id=c.user_id WHERE p.status='pending' AND c.section='marketplace' ORDER BY p.created_at ASC,p.id ASC LIMIT 100")->fetchAll();
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Listing review | Nileteck</title><link rel="stylesheet" href="src/seller-plans.css?v=<?= filemtime(__DIR__.'/src/seller-plans.css') ?>"></head><body class="seller-plans-page"><main class="seller-plans-wrap"><a class="seller-plans-back" href="dashboard">← Dashboard</a><header><h1>Listings awaiting review</h1><p><?= count($queue) ?> in this queue</p></header><?php foreach ($queue as $item): ?><article class="seller-review-item"><h2><?= app_h($item['title']) ?></h2><p><?= nl2br(app_h($item['body'])) ?></p><small><?= app_h($item['store_title']) ?> · <?= app_h($item['email']) ?> · <?= app_h($item['created_at']) ?></small><div class="seller-review-actions"><form method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>"><button name="decision" value="published" type="submit">Approve</button><button name="decision" value="rejected" type="submit">Reject</button></form></div></article><?php endforeach; ?><?php if (!$queue): ?><p>No listings need review.</p><?php endif; ?></main></body></html>
