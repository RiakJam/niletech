<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/app.php';
require_once __DIR__ . '/../includes/seller-plans.php';
require_once __DIR__ . '/../includes/manager-config.php';

function manager_demo_email(): string { return 'manager-demo@example.com'; }
function manager_demo_user_id(): int {
    $db=app_db();
    $email=manager_demo_email();
    $q=$db->prepare('SELECT id FROM users WHERE email=?');
    $q->execute([$email]);
    if ($id=$q->fetchColumn()) return (int)$id;
    $q=$db->prepare('INSERT INTO users (name,email,password) VALUES (?,?,?)');
    $q->execute(['Nileteck Manager Demo',$email,password_hash(bin2hex(random_bytes(32)),PASSWORD_DEFAULT)]);
    return (int)$db->lastInsertId();
}
function manager_access(): array {
    header('Cache-Control: private, no-store');
    $user=app_user();
    if (!$user) { header('Location: login'); exit; }
    $admin=manager_admin_email();
    if ($admin!=='' && strcasecmp((string)$user['email'],$admin)===0) return [$user,false];
    if (manager_demo_enabled() && !empty($_SESSION['manager_demo']) && strcasecmp((string)$user['email'],manager_demo_email())===0) return [$user,true];
    header('Location: login');
    exit;
}
function manager_page(int $requested,int $max): int { return max(1,min($max,$requested)); }
function manager_metric(PDO $db,string $sql): int { return (int)$db->query($sql)->fetchColumn(); }
function manager_header(string $title,string $active,array $user,bool $demo): void {
    $name=app_h((string)$user['name']);
    $titleSafe=app_h($title);
    $initial=app_h(mb_strtoupper(mb_substr((string)$user['name'],0,1)));
    $links=['index'=>'Overview','users'=>'Users','products'=>'Products','revenue'=>'Revenue'];
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><meta name="theme-color" content="#0044b9"><title>'.$titleSafe.' | Nileteck Manager</title><link rel="stylesheet" href="style.css?v='.filemtime(__DIR__.'/style.css').'"></head><body class="manager-body"><div class="manager-shell"><aside class="manager-sidebar"><a class="manager-brand" href="./"><span class="manager-brand-mark">N</span><span><strong>Nileteck</strong><small>MANAGER</small></span></a><div class="manager-sidebar-label">WORKSPACE</div><nav class="manager-nav" aria-label="Manager navigation">';
    foreach ($links as $url=>$label) echo '<a href="'.$url.'"'.($active===$url?' aria-current="page"':'').'>'.app_h($label).'</a>';
    echo '</nav><div class="manager-sidebar-foot"><a href="../dashboard">Main dashboard ↗</a><a href="../p/marketplace">Marketplace ↗</a></div></aside><div class="manager-content"><header class="manager-topbar"><button class="manager-menu" type="button" aria-label="Open manager navigation" aria-expanded="false">☰</button><div class="manager-topbar-title"><span>MARKETPLACE OPERATIONS</span><strong>'.$titleSafe.'</strong></div><div class="manager-account"><span class="manager-avatar">'.$initial.'</span><span><strong>'.$name.'</strong><small>'.($demo?'Demo access':'Administrator').'</small></span></div><a class="manager-signout" href="../logout">Sign out</a></header><main class="manager-main">';
    if ($demo) echo '<div class="manager-demo-banner" role="status"><strong>Demo preview</strong><span>These are sample figures. Review actions and private customer data are unavailable.</span></div>';
}
function manager_footer(): void { echo '</main></div></div><script src="manager.js?v='.filemtime(__DIR__.'/manager.js').'" defer></script></body></html>'; }
