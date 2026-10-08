<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/seller-plans.php';
$jsonCheckout=($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')==='XMLHttpRequest' && str_contains((string)($_SERVER['HTTP_ACCEPT'] ?? ''),'application/json');
if ($jsonCheckout) {
    $sessionUser=app_user();
    if (!$sessionUser) {
        http_response_code(401);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        echo json_encode(['error'=>'Your session expired. Please sign in and try again.','signin_url'=>'signin'],JSON_THROW_ON_ERROR);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD']==='POST' && !hash_equals(app_csrf(),(string)($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        echo json_encode(['error'=>'This page expired. Refresh it and try again.'],JSON_THROW_ON_ERROR);
        exit;
    }
}
$user=app_require_user();
$db=app_db();
$membership=seller_plan_membership($db,(int)$user['id']);
$current=seller_plan_tier($membership);
$isDemoAccount=app_is_demo_email((string)$user['email']);
$demoCheckoutBlocked=$isDemoAccount && !str_starts_with(app_config('PAYSTACK_SECRET_KEY'),'sk_test_');
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    app_verify_csrf();
    $action=(string)($_POST['action'] ?? '');
    if ($action==='checkout') {
        $tier=(string)($_POST['tier'] ?? '');
        if (!isset(seller_plan_prices()[$tier])) $error='Choose a valid plan.';
        elseif ($demoCheckoutBlocked) $error='The shared demo account cannot start a real payment. Create your own account or sign in to subscribe.';
        elseif ($current===$tier) $error='This is already your current plan.';
        else {
            try {
                $plan=seller_plan_code($db,$tier);
                $reference='nileteck_' . bin2hex(random_bytes(16));
                $db->prepare('INSERT INTO seller_plan_checkouts (reference,user_id,tier,amount_subunit,currency_code,paystack_plan_code) VALUES (?,?,?,?,?,?)')->execute([$reference,$user['id'],$tier,$plan['amount_subunit'],$plan['currency_code'],$plan['paystack_plan_code']]);
                $callback=app_page_url('marketplace','store-1');
                $callback=preg_replace('~/p/marketplace/store-1$~','/seller-plans?reference=' . rawurlencode($reference),$callback);
                $checkout=seller_plan_api('POST','transaction/initialize',['email'=>$user['email'],'amount'=>(int)$plan['amount_subunit'],'currency'=>'KES','plan'=>$plan['paystack_plan_code'],'reference'=>$reference,'callback_url'=>$callback,'channels'=>['card']]);
                $url=(string)($checkout['authorization_url'] ?? '');
                if (!preg_match('~^https://checkout\.paystack\.com/[a-zA-Z0-9_-]+$~',$url)) throw new RuntimeException('Paystack checkout link was not valid.');
                if ($current!=='free' && !empty($membership['auto_renew'])) {
                    $oldSubscription=seller_plan_active_subscription($membership);
                    if ($oldSubscription) {
                        seller_plan_api('POST','subscription/disable',['code'=>$oldSubscription['code'],'token'=>$oldSubscription['token']]);
                        $db->prepare('UPDATE seller_memberships SET paystack_subscription_code=?,paystack_email_token=?,auto_renew=0 WHERE user_id=? AND paystack_plan_code=?')->execute([$oldSubscription['code'],$oldSubscription['token'],$user['id'],$membership['paystack_plan_code']]);
                    } else {
                        $db->prepare('UPDATE seller_memberships SET auto_renew=0 WHERE user_id=? AND paystack_plan_code=?')->execute([$user['id'],$membership['paystack_plan_code']]);
                    }
                }
                if ($jsonCheckout) {
                    $accessCode=(string)($checkout['access_code'] ?? '');
                    if (!preg_match('/^[a-zA-Z0-9_-]{8,100}$/',$accessCode)) throw new RuntimeException('Paystack access code was not valid.');
                    header('Content-Type: application/json; charset=UTF-8');
                    header('Cache-Control: no-store');
                    echo json_encode(['access_code'=>$accessCode,'reference'=>$reference,'authorization_url'=>$url],JSON_THROW_ON_ERROR);
                    exit;
                }
                header('Location: ' . $url, true, 303); exit;
            } catch (Throwable $e) { $error=$e instanceof InvalidArgumentException && $e->getMessage()==='checkout_email' ? 'Paystack rejected the email on this account. Sign in with a valid personal email address.' : 'Checkout could not start. Your current plan remains active; please try again later.'; }
        }
    } elseif ($action==='cancel' && $membership && $membership['paystack_subscription_code'] && $membership['paystack_email_token']) {
        try {
            seller_plan_api('POST','subscription/disable',['code'=>$membership['paystack_subscription_code'],'token'=>$membership['paystack_email_token']]);
            $db->prepare('UPDATE seller_memberships SET auto_renew=0 WHERE user_id=?')->execute([$user['id']]);
            header('Location: seller-plans?cancelled=1'); exit;
        } catch (Throwable $e) { $error='Could not cancel renewal. Please try again later.'; }
    }
}
if ($jsonCheckout && $error!=='') {
    http_response_code(422);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store');
    echo json_encode(['error'=>$error],JSON_THROW_ON_ERROR);
    exit;
}
if (isset($_GET['reference'])) {
    $reference=(string)$_GET['reference'];
    $q=$db->prepare('SELECT 1 FROM seller_plan_checkouts WHERE reference=? AND user_id=?'); $q->execute([$reference,$user['id']]);
    if ($q->fetchColumn()) {
        try { if (seller_plan_process_charge($db,$reference)) { header('Location: seller-plans?paid=1'); exit; } } catch (Throwable $e) {}
        $error='Payment is not confirmed yet. If you paid, refresh this page shortly.';
    }
}
$membership=seller_plan_membership($db,(int)$user['id']);
$current=seller_plan_tier($membership);
$q=$db->prepare("SELECT COUNT(*) FROM page_posts p JOIN creator_pages c ON c.id=p.page_id WHERE c.user_id=? AND c.section='marketplace' AND p.status<>'rejected'");
$q->execute([$user['id']]); $listingCount=(int)$q->fetchColumn();
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Seller plans | Nileteck</title><link rel="stylesheet" href="src/site.css?v=<?= filemtime(__DIR__.'/src/site.css') ?>"><link rel="stylesheet" href="src/seller-plans.css?v=<?= filemtime(__DIR__.'/src/seller-plans.css') ?>"><script src="https://js.paystack.co/v2/inline.js" defer></script><script src="src/seller-plans.js?v=<?= filemtime(__DIR__.'/src/seller-plans.js') ?>" defer></script></head><body class="seller-plans-page"><main class="seller-plans-wrap"><a class="seller-plans-back" href="dashboard?view=products">← Back to products</a><header><span>SELLER MEMBERSHIPS</span><h1>Choose your marketplace plan</h1><p>Every seller can list for free. Paid plans give you unlimited listings, immediate publishing, and a badge on your store and products.</p></header><?php if ($error): ?><p class="seller-plans-alert" role="alert"><?= app_h($error) ?></p><?php endif; ?><?php if ($demoCheckoutBlocked): ?><p class="seller-plans-alert">The demo account is shared and cannot be billed. <a href="signup">Create your own account</a> to choose a plan.</p><?php endif; ?><?php if ($isDemoAccount && !$demoCheckoutBlocked): ?><p class="seller-plans-success">Demo checkout is in Paystack test mode. No real payment will be taken.</p><?php endif; ?><?php if (isset($_GET['paid'])): ?><p class="seller-plans-success" role="status">Payment confirmed. Your seller badge is active.</p><?php endif; ?><?php if (isset($_GET['cancelled'])): ?><p class="seller-plans-success" role="status">Monthly renewal cancelled. Your benefits remain until the paid period ends.</p><?php endif; ?><p class="seller-plans-checkout-status" role="status" aria-live="polite" hidden></p><div class="seller-plans-current"><strong>Current plan: <?= app_h(seller_plan_label($current)) ?></strong><span><?= $current==='free' ? ($listingCount > 5 ? $listingCount.' existing listings · free limit: 5' : $listingCount.' of 5 free listings used') : 'Active until '.app_h(date('j M Y',strtotime($membership['current_period_end']))).(empty($membership['auto_renew']) ? ' · renewal off' : ' · renews monthly') ?></span><?php if ($current!=='free' && $membership['auto_renew'] && $membership['paystack_email_token']): ?><form method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="action" value="cancel"><button class="seller-plans-cancel" type="submit">Cancel monthly renewal</button></form><?php endif; ?></div><div class="seller-plans-grid"><article class="seller-plans-card"><h2>Free</h2><strong>KSh 0</strong><p>Up to 5 listings. New or edited listings are reviewed before going live.</p><span class="seller-plans-selected">Available to everyone</span></article><?php foreach (seller_plan_prices() as $tier=>$amount): ?><article class="seller-plans-card seller-plans-card-<?= app_h($tier) ?>"><h2><?= app_h(seller_plan_label($tier)) ?></h2><strong>KSh <?= number_format($amount/100) ?><small> / month</small></strong><p>Unlimited listings, immediate publishing, and a <?= app_h(seller_plan_label($tier)) ?> seller badge.</p><?php if ($current===$tier): ?><span class="seller-plans-selected">Your current plan</span><?php elseif ($demoCheckoutBlocked): ?><a class="seller-plans-signup" href="signup">Create account to subscribe</a><?php else: ?><form method="post" data-paystack-checkout><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="action" value="checkout"><input type="hidden" name="tier" value="<?= app_h($tier) ?>"><button type="submit"><?= $current==='free' ? 'Choose' : (array_search($tier,array_keys(seller_plan_prices()),true) > array_search($current,array_keys(seller_plan_prices()),true) ? 'Upgrade to' : 'Downgrade to') ?> <?= app_h(seller_plan_label($tier)) ?></button></form><?php endif; ?></article><?php endforeach; ?></div><p class="seller-plans-note">Changing plans charges the full new monthly price immediately, starts a new monthly period, and stops renewal of the previous plan. There is no prorated credit. If you leave checkout without paying, your current benefits remain until their paid period ends. Monthly payments renew automatically by card until you cancel. Paystack processes the payment. Existing published listings stay visible when a membership ends; future free listings follow the free limit and review process.</p></main></body></html>
