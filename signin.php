<?php

declare(strict_types=1);
require_once __DIR__ . '/includes/app.php';
if (app_user()) {
    header('Location: dashboard');
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    app_verify_csrf();
    if (isset($_POST['demo'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = app_demo_user_id();
        header('Location: dashboard');
        exit;
    }
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $query = app_db()->prepare('SELECT id,password FROM users WHERE email = ?');
    $query->execute([$email]);
    $user = $query->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify((string)($_POST['password'] ?? ''), $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        header('Location: dashboard');
        exit;
    }
    $error = 'Email or password is incorrect.';
}
$pageTitle = 'Sign in | Nileteck';
$hideSiteChrome = true;
require __DIR__ . '/layout/header.php';
?>
<main id="main-content" class="auth-main"><section class="auth-page"><div class="auth-card"><a class="auth-brand" href="<?= app_h($siteHrefPrefix) ?>" aria-label="Nileteck home"><img src="images/Logo.png" width="78" height="78" alt="Nileteck"></a><span class="auth-kicker">WELCOME BACK</span><h1>Sign in to Nileteck</h1><p class="auth-subtitle">Manage your storefront, listings, and audience.</p>
<?php if (isset($_GET['google'])): ?><p class="form-error" role="alert"><?= app_h(match ((string)$_GET['google']) { 'unavailable'=>'Google sign-in is not configured yet. Please use email or the demo account.', 'existing'=>'That email already has an account. Sign in with your password.', 'cancelled'=>'Google sign-in was cancelled.', default=>'Google sign-in could not be completed. Please try again.' }) ?></p><?php endif; ?>
<form class="app-form auth-form" method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><?php if ($error): ?><p class="form-error" role="alert"><?= app_h($error) ?></p><?php endif; ?><label>Email address<input name="email" type="email" required autocomplete="email" placeholder="you@example.com" value="<?= app_h($email ?? '') ?>"></label><label>Password<span class="auth-password-field"><input id="signin-password" name="password" type="password" required autocomplete="current-password" placeholder="Enter your password"><button class="auth-password-toggle" type="button" data-toggle-password="signin-password" aria-label="Show password" aria-pressed="false">Show</button></span></label><button class="button button-primary auth-submit" type="submit">Sign in</button></form>
<div class="auth-divider"><span>OR CONTINUE WITH GOOGLE</span></div><a class="auth-google-button" href="google-auth?mode=signin"><span class="auth-google-mark" aria-hidden="true">G</span>Continue with Google</a>
<div class="auth-demo-divider"><span>OR EXPLORE FIRST</span></div><form class="demo-form" method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><button class="button button-quiet" type="submit" name="demo" value="1">Explore with a demo account</button></form>
<p class="auth-switch">New to Nileteck? <a href="signup">Create an account</a></p></div></section></main>
<?php require __DIR__ . '/layout/footer.php'; ?>