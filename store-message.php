<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/app.php';
require_once __DIR__ . '/includes/marketplace-analytics.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !marketplace_same_origin_request()) { http_response_code(405); exit('Method not allowed.'); }
app_verify_csrf();
if (trim((string)($_POST['website'] ?? '')) !== '') { http_response_code(400); exit('Invalid request.'); }
$pageId = filter_var($_POST['page_id'] ?? null, FILTER_VALIDATE_INT);
$productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$name = trim((string)($_POST['sender_name'] ?? ''));
$email = trim((string)($_POST['sender_email'] ?? ''));
$body = trim((string)($_POST['body'] ?? ''));
if (!$pageId || mb_strlen($name) < 2 || mb_strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 254 || mb_strlen($body) < 10 || mb_strlen($body) > 5000) { http_response_code(422); exit('Check your name, email address, and message.'); }
$db = app_db();
$q=$db->prepare("SELECT c.slug,u.email AS owner_email FROM creator_pages c JOIN users u ON u.id=c.user_id WHERE c.id=? AND c.section='marketplace'");
$q->execute([$pageId]); $page=$q->fetch();
if (!$page || $page['owner_email'] === 'marketplace-demo@nileteck.invalid') { http_response_code(404); exit('Store unavailable.'); }
$productSlug = null;
if ($productId) {
    $q=$db->prepare("SELECT slug FROM page_posts WHERE id=? AND page_id=? AND status='published'");
    $q->execute([$productId,$pageId]); $productSlug=$q->fetchColumn();
    if (!$productSlug) { http_response_code(404); exit('Product unavailable.'); }
}
$visitor=marketplace_visitor_id();
$q=$db->prepare('SELECT COUNT(*) FROM store_messages WHERE page_id=? AND visitor_id=? AND created_at >= NOW() - INTERVAL 1 HOUR');
$q->execute([$pageId,$visitor]);
if ((int)$q->fetchColumn() >= 5) { http_response_code(429); exit('Please wait before sending another message.'); }
$db->prepare('INSERT INTO store_messages (page_id,product_id,sender_name,sender_email,body,visitor_id) VALUES (?,?,?,?,?,?)')->execute([$pageId,$productId,$name,$email,$body,$visitor]);
$url=app_page_url('marketplace',$page['slug'],$productSlug ?: null);
header('Location: ' . $url . '?message=sent', true, 303);
