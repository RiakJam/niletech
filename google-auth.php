<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/app.php';
app_session();
$mode = ($_GET['mode'] ?? '') === 'signup' ? 'signup' : 'signin';
$clientId = app_config('GOOGLE_CLIENT_ID');
$clientSecret = app_config('GOOGLE_CLIENT_SECRET');
$redirectUri = app_config('GOOGLE_REDIRECT_URI');
$fail = static function(string $reason, string $destination = 'signin'): never {
    header('Location: ' . $destination . '?google=' . rawurlencode($reason));
    exit;
};
if ($clientId === '' || $clientSecret === '' || $redirectUri === '') $fail('unavailable', $mode);
if (!function_exists('curl_init')) $fail('unavailable', $mode);
if (!isset($_GET['code']) && !isset($_GET['error'])) {
    $state = bin2hex(random_bytes(24));
    $verifier = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    $_SESSION['google_oauth'] = ['state'=>$state,'verifier'=>$verifier,'mode'=>$mode,'created'=>time()];
    $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id'=>$clientId,
        'redirect_uri'=>$redirectUri,
        'response_type'=>'code',
        'scope'=>'openid email profile',
        'state'=>$state,
        'code_challenge'=>$challenge,
        'code_challenge_method'=>'S256',
        'prompt'=>'select_account',
    ], '', '&', PHP_QUERY_RFC3986));
    exit;
}
$pending = $_SESSION['google_oauth'] ?? null;
unset($_SESSION['google_oauth']);
$destination = is_array($pending) && ($pending['mode'] ?? '') === 'signup' ? 'signup' : 'signin';
if (!is_array($pending) || !hash_equals((string)$pending['state'], (string)($_GET['state'] ?? '')) || time() - (int)$pending['created'] > 600) $fail('invalid', $destination);
if (isset($_GET['error'])) $fail('cancelled', $destination);
$code = (string)($_GET['code'] ?? '');
if ($code === '' || strlen($code) > 2048) $fail('invalid', $destination);
$http = static function(string $url, array $options): ?array {
    $handle = curl_init($url);
    curl_setopt_array($handle, $options + [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>12, CURLOPT_CONNECTTIMEOUT=>5, CURLOPT_SSL_VERIFYPEER=>true, CURLOPT_SSL_VERIFYHOST=>2]);
    $response = curl_exec($handle);
    $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    curl_close($handle);
    if (!is_string($response) || $status !== 200) return null;
    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : null;
};
$token = $http('https://oauth2.googleapis.com/token', [CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>http_build_query([
    'code'=>$code,
    'client_id'=>$clientId,
    'client_secret'=>$clientSecret,
    'redirect_uri'=>$redirectUri,
    'grant_type'=>'authorization_code',
    'code_verifier'=>$pending['verifier'],
]), CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded']]);
$accessToken = is_array($token) ? (string)($token['access_token'] ?? '') : '';
if ($accessToken === '') $fail('failed', $destination);
$profile = $http('https://openidconnect.googleapis.com/v1/userinfo', [CURLOPT_HTTPHEADER=>['Authorization: Bearer ' . $accessToken]]);
$sub = is_array($profile) ? (string)($profile['sub'] ?? '') : '';
$email = is_array($profile) ? strtolower(trim((string)($profile['email'] ?? ''))) : '';
$name = is_array($profile) ? trim((string)($profile['name'] ?? '')) : '';
if ($sub === '' || strlen($sub) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($profile['email_verified'])) $fail('failed', $destination);
$db = app_db();
$query = $db->prepare('SELECT id FROM users WHERE google_sub=?');
$query->execute([$sub]);
$userId = (int)($query->fetchColumn() ?: 0);
$newAccount = false;
if (!$userId) {
    $query = $db->prepare('SELECT id FROM users WHERE email=?');
    $query->execute([$email]);
    if ($query->fetchColumn()) $fail('existing', $destination);
    $name = mb_substr($name !== '' ? $name : explode('@', $email)[0], 0, 100);
    if (mb_strlen($name) < 2) $name = 'Nileteck Member';
    try {
        $query = $db->prepare('INSERT INTO users (name,email,google_sub,password,password_is_set) VALUES (?,?,?,?,0)');
        $query->execute([$name,$email,$sub,password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT)]);
        $userId = (int)$db->lastInsertId();
        $newAccount = true;
    } catch (PDOException $error) {
        if ($error->getCode() !== '23000') throw $error;
        $fail('existing', $destination);
    }
}
session_regenerate_id(true);
$_SESSION['user_id'] = $userId;
header('Location: ' . ($newAccount ? 'dashboard?view=settings' : 'dashboard'));
exit;
