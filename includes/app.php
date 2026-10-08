<?php
declare(strict_types=1);
require_once __DIR__ . '/geo.php';

function app_env(): array {
    static $values;
    if (is_array($values)) return $values;
    $values = [];
    $path = __DIR__ . '/../.env';
    if (!is_file($path)) return $values;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $key)) continue;
        $value = trim($value);
        if (strlen($value) >= 2 && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) $value = substr($value, 1, -1);
        $values[$key] = $value;
    }
    return $values;
}
function app_config(string $key, string $default = ''): string {
    $environment = getenv($key);
    if ($environment !== false) return $environment;
    return app_env()[$key] ?? $default;
}
function app_evoting_url(): string {
    $url = app_config('NILETECK_EVOTING_URL', '/e-voting/');
    return preg_match('~^https://[a-z0-9.-]+(?::[0-9]+)?(?:/[^\s]*)?$~i', $url) ? $url : '/e-voting/';
}
function app_db(): PDO {
    static $db;
    if ($db instanceof PDO) return $db;
    if (!extension_loaded('pdo_mysql')) throw new RuntimeException('The pdo_mysql extension is required.');
    $host = app_config('DB_HOST', '127.0.0.1');
    $port = app_config('DB_PORT', '3306');
    $name = app_config('DB_NAME');
    $user = app_config('DB_USER');
    if ($name === '' || $user === '') throw new RuntimeException('Set DB_NAME and DB_USER in .env.');
    if (!ctype_digit($port) || (int)$port < 1 || (int)$port > 65535) throw new RuntimeException('DB_PORT must be a valid port.');
    $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8mb4';
    $db = new PDO($dsn, $user, app_config('DB_PASSWORD'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $db;
}
function app_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_name('nileteck_session');
    session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax', 'path' => '/']);
    session_start();
}
function app_csrf(): string {
    app_session();
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}
function app_verify_csrf(): void {
    if (!hash_equals(app_csrf(), (string)($_POST['csrf'] ?? ''))) { http_response_code(400); exit('Invalid request token.'); }
}
function app_user(): ?array {
    app_session();
    if (empty($_SESSION['user_id'])) return null;
    $query = app_db()->prepare('SELECT id, name, email, google_sub, password_is_set, country_code, region, locality FROM users WHERE id = ?');
    $query->execute([$_SESSION['user_id']]);
    return $query->fetch(PDO::FETCH_ASSOC) ?: null;
}
function app_require_user(): array {
    $user = app_user();
    if (!$user) { header('Location: signin'); exit; }
    return $user;
}
function app_h(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'); }
function app_demo_email(): string { return 'demo-account@example.com'; }
function app_is_demo_email(string $email): bool { return in_array(strtolower($email), [app_demo_email(), 'demo-account@nileteck.invalid'], true); }
function app_demo_user_id(): int {
    $db = app_db();
    $email = app_demo_email();
    $existing = $db->prepare('SELECT id FROM users WHERE email=?');
    $existing->execute([$email]);
    if ($id = $existing->fetchColumn()) return (int)$id;
    $existing->execute(['demo-account@nileteck.invalid']);
    if ($id = $existing->fetchColumn()) {
        $db->prepare('UPDATE users SET email=? WHERE id=?')->execute([$email,$id]);
        return (int)$id;
    }
    $query = $db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)');
    $query->execute(['Nileteck Demo', $email, password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT)]);
    return (int)$db->lastInsertId();
}
function app_sections(): array { return ['marketplace' => 'Marketplace']; }
function app_slug(string $value): string {
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}
function app_page_url(string $section, string $slug, ?string $post = null): string {
    $base = rtrim(app_config('NILETECK_PUBLIC_BASE_URL'), '/');
    if ($base === '') {
        $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://';
        $base = $scheme . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $path = rtrim(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/')), '/.');
        if ($path !== '') $base .= $path;
    }
    return $base . '/p/marketplace/' . rawurlencode($slug) . ($post ? '/' . rawurlencode($post) : '');
}
