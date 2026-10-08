<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/app.php';
require_once __DIR__ . '/includes/marketplace-analytics.php';
header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !marketplace_same_origin_request()) { http_response_code(405); exit; }
if (marketplace_analytics_automated_request()) { http_response_code(204); exit; }
$event = (string)($_POST['event'] ?? '');
$productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
if (!in_array($event, ['impression','click'], true) || !$productId) { http_response_code(400); exit; }
$db = app_db();
$q = $db->prepare("SELECT p.id,c.user_id FROM page_posts p JOIN creator_pages c ON c.id=p.page_id WHERE p.id=? AND p.status='published' AND c.section='marketplace'");
$q->execute([$productId]);
$product = $q->fetch();
if (!$product) { http_response_code(404); exit; }
if ((int)(app_user()['id'] ?? 0) === (int)$product['user_id']) { http_response_code(204); exit; }
if ($event === 'click') {
    $db->prepare('INSERT INTO product_analytics (product_id,clicks) VALUES (?,1) ON DUPLICATE KEY UPDATE clicks=clicks+1')->execute([$productId]);
    $db->prepare('INSERT INTO product_analytics_daily (product_id,event_day,clicks) VALUES (?,CURRENT_DATE,1) ON DUPLICATE KEY UPDATE clicks=clicks+1')->execute([$productId]);
} else {
    $visitor = marketplace_visitor_id();
    $q=$db->prepare('INSERT IGNORE INTO product_impressions (product_id,visitor_id,event_day) VALUES (?,?,CURRENT_DATE)');
    $q->execute([$productId,$visitor]);
    if ($q->rowCount()) {
        $db->prepare('INSERT INTO product_analytics (product_id,impressions) VALUES (?,1) ON DUPLICATE KEY UPDATE impressions=impressions+1')->execute([$productId]);
        $db->prepare('INSERT INTO product_analytics_daily (product_id,event_day,impressions) VALUES (?,CURRENT_DATE,1) ON DUPLICATE KEY UPDATE impressions=impressions+1')->execute([$productId]);
    }
}
http_response_code(204);
