<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/app.php';

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Use the Follow button on the store page.']);
    exit;
}
app_verify_csrf();
$pageId = filter_var($_POST['page_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
$email = strtolower(trim((string)($_POST['email'] ?? '')));
$consent = (string)($_POST['consent'] ?? '') === '1';
$db = app_db();
$query = $db->prepare("SELECT id FROM creator_pages WHERE id=? AND section='marketplace'");
$query->execute([$pageId]);
if (!$query->fetchColumn() || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$consent) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Enter a valid email address and agree to receive marketing emails.']);
    exit;
}
$token = bin2hex(random_bytes(24));
$db->prepare('INSERT INTO email_contacts (business_id,email,unsubscribe_token) VALUES (?,?,?) ON DUPLICATE KEY UPDATE active=1')->execute([$pageId, $email, $token]);
echo json_encode(['success' => true, 'message' => 'You are now following this store.']);
