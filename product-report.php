<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/app.php';
require_once __DIR__ . '/includes/marketplace-analytics.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !marketplace_same_origin_request()) {
    http_response_code(405);
    exit('Method not allowed.');
}
app_verify_csrf();
if (trim((string)($_POST['website'] ?? '')) !== '') {
    http_response_code(400);
    exit('Invalid request.');
}
$productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
$reason = trim((string)($_POST['reason'] ?? ''));
if (!$productId || mb_strlen($reason) < 10 || mb_strlen($reason) > 2000) {
    http_response_code(422);
    exit('Please provide a reason of 10–2000 characters.');
}
$db = app_db();
$query = $db->prepare("SELECT p.slug AS product_slug, c.slug AS page_slug FROM page_posts p JOIN creator_pages c ON c.id=p.page_id WHERE p.id=? AND p.status='published' AND c.section='marketplace'");
$query->execute([$productId]);
$product = $query->fetch();
if (!$product) {
    http_response_code(404);
    exit('Product unavailable.');
}
$visitor = marketplace_visitor_id();
$query = $db->prepare('SELECT COUNT(*) FROM product_reports WHERE visitor_id=? AND created_at >= NOW() - INTERVAL 1 HOUR');
$query->execute([$visitor]);
if ((int)$query->fetchColumn() >= 5) {
    http_response_code(429);
    exit('Please wait before submitting another report.');
}
$db->prepare('INSERT INTO product_reports (product_id, reason, visitor_id) VALUES (?,?,?)')->execute([$productId, $reason, $visitor]);
header('Location: ' . app_page_url('marketplace', $product['page_slug'], $product['product_slug']) . '?report=sent', true, 303);
