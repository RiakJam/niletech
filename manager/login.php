<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
header('Cache-Control: private, no-store');
$existing=app_user();
if ($existing && ((manager_admin_email()!=='' && strcasecmp((string)$existing['email'],manager_admin_email())===0) || (manager_demo_enabled() && !empty($_SESSION['manager_demo']) && strcasecmp((string)$existing['email'],manager_demo_email())===0))) { header('Location: ./'); exit; }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    app_verify_csrf();
    if (($_POST['mode'] ?? '')==='demo' && manager_demo_enabled()) {
        session_regenerate_id(true);
        $_SESSION['user_id']=manager_demo_user_id();
        $_SESSION['manager_demo']=true;
        header('Location: ./',true,303); exit;
    }
    $email=strtolower(trim((string)($_POST['email'] ?? '')));
    $admin=manager_admin_email();
    if ($admin!=='' && strcasecmp($email,$admin)===0) {
        $q=app_db()->prepare('SELECT id,password FROM users WHERE email=?');
        $q->execute([$email]);
        $account=$q->fetch();
        if ($account && password_verify((string)($_POST['password'] ?? ''),(string)$account['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']=(int)$account['id'];
            unset($_SESSION['manager_demo']);
            header('Location: ./',true,303); exit;
        }
    }
    $error='This manager email or password is incorrect.';
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Manager sign in | Nileteck</title><link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__.'/style.css') ?>"></head><body class="manager-login-body"><main class="manager-login-wrap"><a class="manager-login-home" href="../">← Nileteck home</a><section class="manager-login-card"><span class="manager-brand-mark">N</span><span class="manager-eyebrow">NILETECK MANAGER</span><h1>Welcome back.</h1><p>Sign in to manage marketplace activity and see how the business is growing.</p><?php if ($error): ?><div class="manager-alert" role="alert"><?= app_h($error) ?></div><?php endif; ?><form method="post" class="manager-login-form"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><label>Email address<input type="email" name="email" autocomplete="username" placeholder="manager@example.com" required value="<?= app_h((string)($_POST['email'] ?? '')) ?>"></label><label>Password<input type="password" name="password" autocomplete="current-password" placeholder="Enter your password" required></label><button type="submit" name="mode" value="login">Sign in to manager</button></form><?php if (manager_demo_enabled()): ?><div class="manager-login-divider">or explore the interface</div><form method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><button class="manager-demo-button" type="submit" name="mode" value="demo">Open demo manager</button></form><small class="manager-login-hint">Demo account: <?= app_h(manager_demo_email()) ?> · sample data only</small><?php endif; ?></section></main></body></html>
