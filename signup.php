<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/app.php';
if (app_user()) { header('Location: dashboard'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    app_verify_csrf();
    $name = trim((string)($_POST['name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $country = (string)($_POST['country_code'] ?? '');
    $region = trim((string)($_POST['region'] ?? ''));
    $locality = trim((string)($_POST['locality'] ?? ''));
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10) $error = 'Enter a name, valid email and password of at least 10 characters.';
    elseif (!marketplace_location_valid($country, $region, $locality)) $error = 'Choose your country and enter your city or region and local area.';
    elseif (app_is_demo_email($email)) $error = 'This email is reserved for the demo account.';
    else {
        try {
            $query = app_db()->prepare('INSERT INTO users (name,email,password,country_code,region,locality) VALUES (?,?,?,?,?,?)');
            $query->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $country, $region, $locality]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)app_db()->lastInsertId();
            setcookie('nileteck_market_country', '', ['expires' => time() - 3600, 'path' => '/', 'samesite' => 'Lax']);
            header('Location: dashboard'); exit;
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000') throw $e;
            $error = 'An account with this email already exists.';
        }
    }
}
$pageTitle = 'Create an account | Nileteck';
$hideSiteChrome = true;
require __DIR__ . '/layout/header.php';
?>
<main id="main-content" class="auth-main auth-signup-main"><section class="auth-page"><div class="auth-card"><a class="auth-brand" href="<?= app_h($siteHrefPrefix) ?>" aria-label="Nileteck home"><img src="images/Logo.png" width="78" height="78" alt="Nileteck"></a><span class="auth-kicker">JOIN NILETECK</span><h1>Create your account</h1><p class="auth-subtitle">Start your storefront and reach buyers near you.</p>
<?php if (isset($_GET['google'])): ?><p class="form-error" role="alert"><?= app_h(match ((string)$_GET['google']) { 'unavailable'=>'Google sign-up is not configured yet. Please use the form below.', 'existing'=>'That email already has an account. Sign in with your password.', 'cancelled'=>'Google sign-up was cancelled.', default=>'Google sign-up could not be completed. Please try again.' }) ?></p><?php endif; ?>
<form class="app-form auth-form" method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><?php if ($error): ?><p class="form-error" role="alert"><?= app_h($error) ?></p><?php endif; ?><div class="auth-form-pair auth-identity-pair"><label>Full name<input name="name" required maxlength="100" autocomplete="name" placeholder="Your full name" value="<?= app_h($name ?? '') ?>"></label><label>Email address<input name="email" type="email" required autocomplete="email" placeholder="you@example.com" value="<?= app_h($email ?? '') ?>"></label></div><div class="auth-country-field"><label for="signup-country">Country</label><select id="signup-country" name="country_code" required data-country-select><option value="">Choose your country</option><?php foreach (marketplace_african_countries() as $code => $details): ?><option value="<?= app_h($code) ?>" <?= ($country ?? '') === $code ? 'selected' : '' ?>><?= app_h($details[0]) ?></option><?php endforeach; ?></select><button class="auth-country-trigger" type="button" data-country-open aria-haspopup="dialog" aria-controls="auth-country-dialog" hidden><span data-country-label>Choose your country</span><span aria-hidden="true">⌄</span></button><span class="auth-country-error" data-country-error hidden>Choose your country to continue.</span></div><div class="auth-form-pair"><label>City or region<input name="region" required maxlength="100" autocomplete="address-level2" placeholder="Nairobi" value="<?= app_h($region ?? '') ?>"></label><label>Area or neighborhood<input name="locality" required maxlength="100" autocomplete="address-level3" placeholder="Kabiria" value="<?= app_h($locality ?? '') ?>"></label></div><label>Password<span class="auth-password-field"><input id="signup-password" name="password" type="password" required minlength="10" autocomplete="new-password" placeholder="At least 10 characters"><button class="auth-password-toggle" type="button" data-toggle-password="signup-password" aria-label="Show password" aria-pressed="false">Show</button></span></label><button class="button button-primary auth-submit" type="submit">Create account</button></form>
<dialog class="auth-country-dialog" id="auth-country-dialog" aria-labelledby="auth-country-title"><div class="auth-country-head"><div><h2 id="auth-country-title">Choose your country</h2><p>Search and select where you are based.</p></div><button type="button" data-country-close aria-label="Close country selector">×</button></div><label class="auth-country-search"><span aria-hidden="true">⌕</span><input type="search" data-country-search placeholder="Search country name" autocomplete="off" aria-label="Search country name"></label><div class="auth-country-options" data-country-options></div></dialog>
<div class="auth-divider"><span>OR CONTINUE WITH GOOGLE</span></div><a class="auth-google-button" href="google-auth?mode=signup"><span class="auth-google-mark" aria-hidden="true">G</span>Continue with Google</a><p class="auth-switch">Already have an account? <a href="signin">Sign in</a></p></div></section></main>
<?php require __DIR__ . '/layout/footer.php'; ?>
