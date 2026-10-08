<?php
declare(strict_types=1);
require_once __DIR__ . '/data.php';
[$user,$demo]=manager_access();
$db=app_db();
$stats=manager_stats($db,$demo);
$recent=$demo?manager_sample_products():$db->query("SELECT p.title,p.status,p.created_at,c.title AS store_title FROM page_posts p JOIN creator_pages c ON c.id=p.page_id WHERE c.section='marketplace' ORDER BY p.created_at DESC,p.id DESC LIMIT 5")->fetchAll();
manager_header('Overview','index',$user,$demo);
?><div class="manager-heading"><div><span class="manager-eyebrow">BUSINESS AT A GLANCE</span><h1>Good to see you, <?= app_h(explode(' ',trim((string)$user['name']))[0]) ?>.</h1><p>Monitor your marketplace, memberships, and payments from one place.</p></div><span class="manager-date"><?= app_h(date('l, j F Y')) ?></span></div>
<section class="manager-metrics" aria-label="Marketplace totals">
  <a class="manager-metric" href="users"><span>Registered users</span><strong><?= number_format($stats['users']) ?></strong><small>Across all account plans ↗</small></a>
  <a class="manager-metric" href="products"><span>Total products</span><strong><?= number_format($stats['products']) ?></strong><small>All listing statuses ↗</small></a>
  <a class="manager-metric manager-metric-attention" href="products?status=pending"><span>Awaiting review</span><strong><?= number_format($stats['pending']) ?></strong><small>Listings to check ↗</small></a>
  <a class="manager-metric manager-metric-revenue" href="revenue"><span>Revenue collected</span><strong>KSh <?= number_format($stats['revenue']) ?></strong><small>Confirmed seller plan payments ↗</small></a>
</section>
<section class="manager-panel manager-plan-panel"><div class="manager-section-head"><div><span class="manager-eyebrow">MEMBERSHIPS</span><h2>Accounts by plan</h2></div><a href="users">View users ↗</a></div><div class="manager-plan-grid"><?php foreach (['free'=>'Free','basic'=>'Basic','premium'=>'Premium','platinum'=>'Platinum'] as $tier=>$label): ?><div class="manager-plan manager-plan-<?= $tier ?>"><span><?= $label ?></span><strong><?= number_format($stats[$tier]) ?></strong><small><?= $tier==='free'?'No active paid plan':'Active monthly members' ?></small></div><?php endforeach; ?></div></section>
<div class="manager-two-column"><section class="manager-panel"><div class="manager-section-head"><div><span class="manager-eyebrow">LATEST ACTIVITY</span><h2>Recent products</h2></div><a href="products">All products ↗</a></div><div class="manager-activity-list"><?php foreach ($recent as $item): ?><div class="manager-activity"><span class="manager-activity-icon">▣</span><span><strong><?= app_h($item['title']) ?></strong><small><?= app_h($item['store_title']) ?> · <?= app_h(substr((string)$item['created_at'],0,10)) ?></small></span><em class="manager-status manager-status-<?= app_h($item['status']) ?>"><?= app_h(ucfirst($item['status'])) ?></em></div><?php endforeach; ?><?php if (!$recent): ?><p class="manager-empty">No products have been posted yet.</p><?php endif; ?></div></section><section class="manager-panel manager-spotlight"><span class="manager-eyebrow">THIS MONTH</span><h2>KSh <?= number_format($stats['this_month']) ?></h2><p>Confirmed plan payments since the start of the month.</p><a href="revenue">Explore revenue →</a><div class="manager-spotlight-shape" aria-hidden="true"></div></section></div>
<?php manager_footer(); ?>
