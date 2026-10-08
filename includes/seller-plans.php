<?php
declare(strict_types=1);
require_once __DIR__ . '/app.php';

function seller_plan_prices(): array { return ['basic'=>100000,'premium'=>250000,'platinum'=>450000]; }
function seller_plan_label(string $tier): string { return ['basic'=>'Basic','premium'=>'Premium','platinum'=>'Platinum'][$tier] ?? 'Free'; }
function seller_plan_membership(PDO $db, int $userId): ?array {
    $q=$db->prepare('SELECT * FROM seller_memberships WHERE user_id=?');
    $q->execute([$userId]);
    return $q->fetch() ?: null;
}
function seller_plan_tier(?array $membership): string {
    return $membership && $membership['current_period_end'] > gmdate('Y-m-d H:i:s') ? $membership['tier'] : 'free';
}
function seller_plan_badge(string $tier): string {
    return $tier === 'free' ? '' : '<span class="seller-plan-badge seller-plan-badge-' . app_h($tier) . '">' . app_h(seller_plan_label($tier)) . ' seller</span>';
}
function seller_plan_active_subscription(?array $membership): ?array {
    if (!$membership || empty($membership['auto_renew'])) return null;
    $code=(string)($membership['paystack_subscription_code'] ?? '');
    $token=(string)($membership['paystack_email_token'] ?? '');
    if ($code !== '' && $token !== '') return ['code'=>$code,'token'=>$token];
    $customer=(string)($membership['paystack_customer_code'] ?? '');
    if ($customer === '') throw new RuntimeException('Could not identify the current subscription.');
    $details=seller_plan_api('GET','customer/' . rawurlencode($customer));
    foreach (($details['subscriptions'] ?? []) as $subscription) {
        if (!in_array(($subscription['status'] ?? ''),['active','attention'],true)) continue;
        $code=(string)($subscription['subscription_code'] ?? '');
        if ($code === '') throw new RuntimeException('Could not identify the current subscription.');
        $full=seller_plan_api('GET','subscription/' . rawurlencode($code));
        if (($full['plan']['plan_code'] ?? '') !== $membership['paystack_plan_code']) continue;
        $token=(string)($full['email_token'] ?? $subscription['email_token'] ?? '');
        if ($code !== '' && $token !== '') return ['code'=>$code,'token'=>$token];
        throw new RuntimeException('Could not identify the current subscription.');
    }
    return null;
}
function seller_plan_api(string $method, string $path, ?array $payload=null): array {
    $secret=app_config('PAYSTACK_SECRET_KEY');
    if ($secret === '' || !extension_loaded('curl')) throw new RuntimeException('Paystack is not configured.');
    $curl=curl_init('https://api.paystack.co/' . ltrim($path,'/'));
    curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>['Authorization: Bearer ' . $secret,'Content-Type: application/json'],CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>20]);
    if ($payload !== null) curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($payload,JSON_THROW_ON_ERROR));
    $body=curl_exec($curl);
    $status=(int)curl_getinfo($curl,CURLINFO_HTTP_CODE);
    curl_close($curl);
    $answer=is_string($body) ? json_decode($body,true) : null;
    if ($status < 200 || $status >= 300 || !is_array($answer) || empty($answer['status'])) {
        $message=strtolower((string)($answer['message'] ?? ''));
        if ($status===400 && str_contains($message,'email')) throw new InvalidArgumentException('checkout_email');
        error_log('Paystack request failed: HTTP ' . $status . ' on ' . $method . ' ' . $path);
        throw new RuntimeException('Paystack could not complete this request.');
    }
    return $answer['data'] ?? [];
}
function seller_plan_code(PDO $db,string $tier): array {
    $prices=seller_plan_prices();
    if (!isset($prices[$tier])) throw new InvalidArgumentException('Invalid plan.');
    $db->prepare('INSERT IGNORE INTO seller_plan_catalog (tier,amount_subunit,currency_code) VALUES (?,?,?)')->execute([$tier,$prices[$tier],'KES']);
    $q=$db->prepare('SELECT * FROM seller_plan_catalog WHERE tier=?'); $q->execute([$tier]); $row=$q->fetch();
    if ($row['paystack_plan_code']) return $row;
    $plan=seller_plan_api('POST','plan',['name'=>'Nileteck Marketplace ' . seller_plan_label($tier),'interval'=>'monthly','amount'=>$prices[$tier],'currency'=>'KES']);
    if (empty($plan['plan_code']) || (int)($plan['amount'] ?? 0) !== $prices[$tier] || ($plan['currency'] ?? '') !== 'KES' || ($plan['interval'] ?? '') !== 'monthly') throw new RuntimeException('Paystack returned a plan that does not match the selected price.');
    $db->prepare('UPDATE seller_plan_catalog SET paystack_plan_code=? WHERE tier=? AND paystack_plan_code IS NULL')->execute([$plan['plan_code'],$tier]);
    $q->execute([$tier]); return $q->fetch();
}
function seller_plan_process_charge(PDO $db,string $reference,?string $subscriptionCode=null): bool {
    if (!preg_match('/^[a-zA-Z0-9_-]{4,80}$/',$reference)) return false;
    $verified=seller_plan_api('GET','transaction/verify/' . rawurlencode($reference));
    if (($verified['status'] ?? '') !== 'success' || ($verified['reference'] ?? '') !== $reference) return false;
    $db->beginTransaction();
    try {
        $q=$db->prepare('SELECT * FROM seller_plan_checkouts WHERE reference=? FOR UPDATE'); $q->execute([$reference]); $checkout=$q->fetch();
        if ($checkout) {
            if ($checkout['status'] === 'paid') { $db->commit(); return true; }
            $userId=(int)$checkout['user_id'];
            $planCode=$checkout['paystack_plan_code'];
            $tier=$checkout['tier'];
            $q=$db->prepare('SELECT email FROM users WHERE id=?'); $q->execute([$userId]); $email=$q->fetchColumn();
            if ((int)($verified['amount'] ?? -1)!==(int)$checkout['amount_subunit'] || ($verified['currency'] ?? '')!==$checkout['currency_code'] || strcasecmp((string)($verified['customer']['email'] ?? ''),(string)$email)!==0) { $db->rollBack(); return false; }
        } else {
            $q=$db->prepare('SELECT m.*,u.email FROM seller_memberships m JOIN users u ON u.id=m.user_id WHERE m.paystack_subscription_code=? FOR UPDATE');
            $q->execute([$subscriptionCode]); $membership=$q->fetch();
            if (!$membership || !$subscriptionCode) { $db->rollBack(); return false; }
            $userId=(int)$membership['user_id']; $tier=$membership['tier']; $planCode=$membership['paystack_plan_code'];
            if ((int)($verified['amount'] ?? -1)!==(int)seller_plan_prices()[$tier] || ($verified['currency'] ?? '')!=='KES' || strcasecmp((string)($verified['customer']['email'] ?? ''),$membership['email'])!==0) { $db->rollBack(); return false; }
        }
        $q=$db->prepare('SELECT 1 FROM seller_plan_charges WHERE reference=?'); $q->execute([$reference]);
        if ($q->fetchColumn()) { $db->commit(); return true; }
        $paidAt=gmdate('Y-m-d H:i:s',strtotime((string)($verified['paid_at'] ?? 'now')) ?: time());
        $end=gmdate('Y-m-d H:i:s',strtotime('+1 month',strtotime($paidAt)));
        $db->prepare('INSERT INTO seller_plan_charges (reference,user_id,amount_subunit,currency_code,paid_at) VALUES (?,?,?,?,?)')->execute([$reference,$userId,(int)$verified['amount'],$verified['currency'],$paidAt]);
        if ($checkout) {
            if (!$subscriptionCode) {
                $link=$db->prepare('SELECT subscription_code,email_token,customer_code FROM seller_plan_subscription_links WHERE customer_email=? AND plan_code=? AND created_at>=? ORDER BY created_at DESC LIMIT 1');
                $link->execute([$email,$planCode,$checkout['created_at']]);
                $matched=$link->fetch();
                if ($matched) $subscriptionCode=$matched['subscription_code'];
            }
            $db->prepare("UPDATE seller_plan_checkouts SET status='paid',paid_at=? WHERE reference=?")->execute([$paidAt,$reference]);
            $db->prepare('INSERT INTO seller_memberships (user_id,tier,paystack_plan_code,paystack_subscription_code,paystack_email_token,paystack_customer_code,current_period_end) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE tier=VALUES(tier),paystack_plan_code=VALUES(paystack_plan_code),paystack_subscription_code=VALUES(paystack_subscription_code),paystack_email_token=VALUES(paystack_email_token),paystack_customer_code=VALUES(paystack_customer_code),current_period_end=VALUES(current_period_end),auto_renew=1')->execute([$userId,$tier,$planCode,$subscriptionCode,$matched['email_token'] ?? null,$verified['customer']['customer_code'] ?? null,$end]);
        } else {
            $db->prepare('UPDATE seller_memberships SET current_period_end=GREATEST(current_period_end,?),auto_renew=1 WHERE user_id=?')->execute([$end,$userId]);
        }
        $db->commit(); return true;
    } catch (Throwable $error) { if ($db->inTransaction()) $db->rollBack(); throw $error; }
}
