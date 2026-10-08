<?php
declare(strict_types=1);
require_once __DIR__ . '/data.php';
[$user,$demo]=manager_access();
$db=app_db();
$notice='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    app_verify_csrf();
    if ($demo) { http_response_code(403); exit('Demo access is read only.'); }
    $id=filter_var($_POST['product_id'] ?? null,FILTER_VALIDATE_INT) ?: 0;
    $decision=(string)($_POST['decision'] ?? '');
    if ($id>0 && in_array($decision,['published','rejected'],true)) {
        $q=$db->prepare("UPDATE page_posts p JOIN creator_pages c ON c.id=p.page_id SET p.status=? WHERE p.id=? AND p.status='pending' AND c.section='marketplace'");
        $q->execute([$decision,$id]);
        $notice=$q->rowCount()?'updated':'unchanged';
    }
    header('Location: products?status=pending&notice='.$notice,true,303);exit;
}
$status=(string)($_GET['status'] ?? 'pending');
if (!in_array($status,['pending','published','draft','rejected','all'],true)) $status='pending';
$stats=manager_stats($db,$demo);
if ($demo) { $rows=array_values(array_filter(manager_sample_products(),static fn($row)=>$status==='all'||$row['status']===$status));$total=count($rows);$page=1; }
else {
    $where=$status==='all'?'':" AND p.status=?";
    $base=" FROM page_posts p JOIN creator_pages c ON c.id=p.page_id JOIN users u ON u.id=c.user_id WHERE c.section='marketplace'".$where;
    $count=$db->prepare('SELECT COUNT(*)'.$base);$count->execute($status==='all'?[]:[$status]);$total=(int)$count->fetchColumn();
    $page=manager_page(filter_var($_GET['page'] ?? 1,FILTER_VALIDATE_INT) ?: 1,max(1,(int)ceil($total/20)));
    $q=$db->prepare('SELECT p.id,p.title,p.body,p.region,p.locality,p.status,p.price,p.currency_code,p.created_at,c.title AS store_title,u.name AS seller'.$base.' ORDER BY p.created_at DESC,p.id DESC LIMIT 20 OFFSET '.(($page-1)*20));
    $q->execute($status==='all'?[]:[$status]);$rows=$q->fetchAll();
}
manager_header('Products','products',$user,$demo);
?><div class="manager-heading"><div><span class="manager-eyebrow">LISTING MANAGEMENT</span><h1>Products</h1><p>Track marketplace listings and review products waiting to go live.</p></div><span class="manager-date"><?= number_format($stats['pending']) ?> awaiting review</span></div><?php if (isset($_GET['notice'])): ?><div class="manager-success" role="status"><?= $_GET['notice']==='updated'?'Product review saved.':'Product was already reviewed.' ?></div><?php endif; ?><nav class="manager-tabs" aria-label="Product status"><?php foreach(['pending'=>'To review','all'=>'All products','published'=>'Published','draft'=>'Drafts','rejected'=>'Rejected'] as $key=>$label): ?><a href="?status=<?= $key ?>" <?= $status===$key?'aria-current="page"':'' ?>><?= $label ?><?= $key==='pending'?' <span>'.number_format($stats['pending']).'</span>':'' ?></a><?php endforeach; ?></nav><section class="manager-panel"><div class="manager-section-head"><div><span class="manager-eyebrow">MARKETPLACE</span><h2><?= app_h($status==='all'?'All products':ucfirst($status).' products') ?></h2></div><small><?= number_format($total) ?> listings</small></div><div class="manager-table-scroll"><table class="manager-table"><thead><tr><th>Product</th><th>Seller / store</th><th>Price</th><th>Status</th><th>Posted</th><?php if($status==='pending'&&!$demo): ?><th>Review</th><?php endif; ?></tr></thead><tbody><?php foreach($rows as $item): ?><tr><td><strong><?= app_h($item['title']) ?></strong><?php if($status==='pending'&&!$demo): ?><details class="manager-product-details"><summary>Read details</summary><p><?= nl2br(app_h($item['body'])) ?></p><small><?= app_h(trim((string)$item['region'].' · '.(string)$item['locality'],' ·')) ?></small></details><?php endif; ?></td><td><?= app_h($item['seller']) ?><small><?= app_h($item['store_title']) ?></small></td><td><?= app_h($item['currency_code']) ?> <?= $item['price']!==null&&$item['price']!==''?app_h($item['price']):'—' ?></td><td><span class="manager-status manager-status-<?= app_h($item['status']) ?>"><?= app_h(ucfirst($item['status'])) ?></span></td><td><?= app_h(substr((string)$item['created_at'],0,10)) ?></td><?php if($status==='pending'&&!$demo): ?><td><form class="manager-review-actions" method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>"><button name="decision" value="published" type="submit">Approve</button><button class="manager-reject" name="decision" value="rejected" type="submit">Reject</button></form></td><?php endif; ?></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="6">No products in this view.</td></tr><?php endif; ?></tbody></table></div><?php if(!$demo && $total>20): ?><nav class="manager-pagination" aria-label="Product pages"><?php if($page>1): ?><a href="?status=<?= app_h($status) ?>&amp;page=<?= $page-1 ?>">← Previous</a><?php endif; ?><span>Page <?= $page ?> of <?= (int)ceil($total/20) ?></span><?php if($page<ceil($total/20)): ?><a href="?status=<?= app_h($status) ?>&amp;page=<?= $page+1 ?>">Next →</a><?php endif; ?></nav><?php endif; ?></section><?php manager_footer(); ?>
