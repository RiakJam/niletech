<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/seller-plans.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); exit; }
$body=file_get_contents('php://input');
$signature=(string)($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '');
$secret=app_config('PAYSTACK_SECRET_KEY');
if ($secret==='' || !is_string($body) || strlen($body)>262144 || !preg_match('/^[a-f0-9]{128}$/i',$signature) || !hash_equals(hash_hmac('sha512',$body,$secret),strtolower($signature))) { http_response_code(403); exit; }
$event=json_decode($body,true);
if (!is_array($event)) { http_response_code(400); exit; }
$db=app_db();
$data=$event['data'] ?? [];
try {
    switch ($event['event'] ?? '') {
        case 'charge.success':
            $reference=(string)($data['reference'] ?? '');
            $subscriptionCode=(string)($data['subscription']['subscription_code'] ?? '');
            if ($reference!=='') seller_plan_process_charge($db,$reference,$subscriptionCode ?: null);
            break;
        case 'subscription.create':
            $code=(string)($data['subscription_code'] ?? '');
            $email=(string)($data['customer']['email'] ?? '');
            $planCode=(string)($data['plan']['plan_code'] ?? '');
            if ($code!=='' && filter_var($email,FILTER_VALIDATE_EMAIL) && $planCode!=='') {
                $db->prepare('INSERT INTO seller_plan_subscription_links (subscription_code,customer_email,plan_code,email_token,customer_code) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE email_token=VALUES(email_token),customer_code=VALUES(customer_code)')->execute([$code,$email,$planCode,$data['email_token'] ?? null,$data['customer']['customer_code'] ?? null]);
                $q=$db->prepare('SELECT m.user_id FROM seller_memberships m JOIN users u ON u.id=m.user_id WHERE u.email=? AND m.paystack_plan_code=? AND m.current_period_end>UTC_TIMESTAMP()');
                $q->execute([$email,$planCode]);
                if ($userId=$q->fetchColumn()) $db->prepare('UPDATE seller_memberships SET paystack_subscription_code=?,paystack_email_token=?,paystack_customer_code=? WHERE user_id=? AND paystack_plan_code=? AND paystack_subscription_code IS NULL')->execute([$code,$data['email_token'] ?? null,$data['customer']['customer_code'] ?? null,$userId,$planCode]);
            }
            break;
        case 'invoice.update':
            if (!empty($data['paid']) && ($data['status'] ?? '')==='success') {
                $reference=(string)($data['transaction']['reference'] ?? '');
                $code=(string)($data['subscription']['subscription_code'] ?? '');
                if ($reference!=='' && $code!=='') seller_plan_process_charge($db,$reference,$code);
            }
            break;
        case 'subscription.not_renew':
        case 'subscription.disable':
            $code=(string)($data['subscription_code'] ?? '');
            if ($code!=='') $db->prepare('UPDATE seller_memberships SET auto_renew=0 WHERE paystack_subscription_code=?')->execute([$code]);
            break;
    }
} catch (Throwable $e) { http_response_code(500); exit; }
http_response_code(200);
