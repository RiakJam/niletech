<?php
declare(strict_types=1);
require_once __DIR__ . '/data.php';
[$user,$demo]=manager_access();
$db=app_db();$stats=manager_stats($db,$demo);
$page=manager_page(filter_var($_GET['page'] ?? 1,FILTER_VALIDATE_INT) ?: 1,max(1,(int)ceil($stats['users']/20)));
$offset=($page-1)*20;
if ($demo) $rows=manager_sample_users();
else {
    $q=$db->prepare("SELECT u.name,u.email,u.country_code,u.created_at,CASE WHEN m.current_period_end>UTC_TIMESTAMP() THEN m.tier ELSE 'free' END AS tier,(SELECT COUNT(*) FROM page_posts p JOIN creator_pages c ON c.id=p.page_id WHERE c.user_id=u.id AND c.section='marketplace') AS products FROM users u LEFT JOIN seller_memberships m ON m.user_id=u.id ORDER BY u.created_at DESC,u.id DESC LIMIT 20 OFFSET ?");
    $q->bindValue(1,$offset,PDO::PARAM_INT);$q->execute();$rows=$q->fetchAll();
}
manager_header('Users','users',$user,$demo);
?><div class="manager-heading"><div><span class="manager-eyebrow">ACCOUNTS</span><h1>Users</h1><p>See who has joined and which seller plans are active.</p></div></div><div class="manager-summary-strip"><span><strong><?= number_format($stats['users']) ?></strong> total users</span><span><strong><?= number_format($stats['free']) ?></strong> free</span><span><strong><?= number_format($stats['basic']+$stats['premium']+$stats['platinum']) ?></strong> paid</span></div><section class="manager-panel"><div class="manager-section-head"><div><span class="manager-eyebrow">DIRECTORY</span><h2>Recent accounts</h2></div></div><div class="manager-table-scroll"><table class="manager-table"><thead><tr><th>User</th><th>Country</th><th>Plan</th><th>Products</th><th>Joined</th></tr></thead><tbody><?php foreach($rows as $item): ?><tr><td><strong><?= app_h($item['name']) ?></strong><small><?= app_h($item['email']) ?></small></td><td><?= app_h($item['country_code'] ?: '—') ?></td><td><span class="manager-tier manager-tier-<?= app_h($item['tier']) ?>"><?= app_h(seller_plan_label($item['tier'])) ?></span></td><td><?= (int)$item['products'] ?></td><td><?= app_h(substr((string)$item['created_at'],0,10)) ?></td></tr><?php endforeach; ?><?php if (!$rows): ?><tr><td colspan="5">No accounts found.</td></tr><?php endif; ?></tbody></table></div><?php if (!$demo && $stats['users']>20): ?><nav class="manager-pagination" aria-label="User pages"><?php if($page>1): ?><a href="?page=<?= $page-1 ?>">← Previous</a><?php endif; ?><span>Page <?= $page ?> of <?= (int)ceil($stats['users']/20) ?></span><?php if($page<ceil($stats['users']/20)): ?><a href="?page=<?= $page+1 ?>">Next →</a><?php endif; ?></nav><?php endif; ?></section><?php manager_footer(); ?>
