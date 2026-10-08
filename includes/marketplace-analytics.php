<?php
declare(strict_types=1);

function marketplace_visitor_id(): string {
    $user = app_user();
    if ($user) return substr(hash('sha256', 'nileteck-account:' . (int)$user['id']), 0, 32);
    $existing = (string)($_COOKIE['nileteck_visitor'] ?? '');
    if (preg_match('/^[a-f0-9]{32}$/', $existing)) return $existing;
    $id = bin2hex(random_bytes(16));
    setcookie('nileteck_visitor', $id, ['expires'=>time()+31536000, 'path'=>'/', 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'httponly'=>true, 'samesite'=>'Lax']);
    $_COOKIE['nileteck_visitor'] = $id;
    return $id;
}

function marketplace_analytics_automated_request(): bool {
    return (bool)preg_match('/(?:bot|crawler|spider|headlesschrome|curl|wget|python-requests|python-urllib)/i', (string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
}

function marketplace_record_visit(PDO $db, int $pageId, ?int $productId = null): void {
    $visitor = marketplace_visitor_id();
    $db->prepare('INSERT INTO store_analytics (page_id,visits) VALUES (?,1) ON DUPLICATE KEY UPDATE visits=visits+1')->execute([$pageId]);
    $db->prepare('INSERT INTO store_analytics_daily (page_id,event_day,visits) VALUES (?,CURRENT_DATE,1) ON DUPLICATE KEY UPDATE visits=visits+1')->execute([$pageId]);
    $db->prepare('INSERT INTO store_visitors (page_id,visitor_id) VALUES (?,?) ON DUPLICATE KEY UPDATE last_seen=CURRENT_TIMESTAMP')->execute([$pageId,$visitor]);
    $db->prepare('INSERT IGNORE INTO store_daily_visitors (page_id,visitor_id,event_day) VALUES (?,?,CURRENT_DATE)')->execute([$pageId,$visitor]);
    if ($productId !== null) {
        $db->prepare('INSERT INTO product_analytics (product_id,views) VALUES (?,1) ON DUPLICATE KEY UPDATE views=views+1')->execute([$productId]);
        $db->prepare('INSERT INTO product_analytics_daily (product_id,event_day,views) VALUES (?,CURRENT_DATE,1) ON DUPLICATE KEY UPDATE views=views+1')->execute([$productId]);
    }
}

function marketplace_same_origin_request(): bool {
    $origin = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
    if ($origin === '') return true;
    $host = parse_url($origin, PHP_URL_HOST);
    return is_string($host) && strcasecmp($host, (string)($_SERVER['HTTP_HOST'] ?? '')) === 0;
}
