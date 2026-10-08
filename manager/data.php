<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

function manager_stats(PDO $db,bool $demo): array {
    if ($demo) return ['users'=>1284,'products'=>3680,'pending'=>14,'basic'=>86,'premium'=>42,'platinum'=>11,'free'=>1145,'revenue'=>735000,'this_month'=>92500];
    $counts=$db->query("SELECT COUNT(*) AS users,COALESCE(SUM(CASE WHEN m.current_period_end>UTC_TIMESTAMP() AND m.tier='basic' THEN 1 ELSE 0 END),0) AS basic,COALESCE(SUM(CASE WHEN m.current_period_end>UTC_TIMESTAMP() AND m.tier='premium' THEN 1 ELSE 0 END),0) AS premium,COALESCE(SUM(CASE WHEN m.current_period_end>UTC_TIMESTAMP() AND m.tier='platinum' THEN 1 ELSE 0 END),0) AS platinum FROM users u LEFT JOIN seller_memberships m ON m.user_id=u.id")->fetch();
    $paid=(int)$counts['basic']+(int)$counts['premium']+(int)$counts['platinum'];
    $products=$db->query("SELECT COUNT(*) AS total,COALESCE(SUM(p.status='pending'),0) AS pending FROM page_posts p JOIN creator_pages c ON c.id=p.page_id WHERE c.section='marketplace'")->fetch();
    $revenue=$db->query("SELECT COALESCE(SUM(CASE WHEN currency_code='KES' THEN amount_subunit ELSE 0 END),0) AS total,COALESCE(SUM(CASE WHEN currency_code='KES' AND paid_at>=DATE_FORMAT(UTC_TIMESTAMP(),'%Y-%m-01') THEN amount_subunit ELSE 0 END),0) AS this_month FROM seller_plan_charges")->fetch();
    return ['users'=>(int)$counts['users'],'products'=>(int)$products['total'],'pending'=>(int)$products['pending'],'basic'=>(int)$counts['basic'],'premium'=>(int)$counts['premium'],'platinum'=>(int)$counts['platinum'],'free'=>(int)$counts['users']-$paid,'revenue'=>(int)$revenue['total']/100,'this_month'=>(int)$revenue['this_month']/100];
}
function manager_sample_users(): array { return [
    ['name'=>'Amina Kariuki','email'=>'amina.kariuki@example.com','country_code'=>'KE','created_at'=>'2026-10-08','tier'=>'premium','products'=>8],
    ['name'=>'Daniel Okoro','email'=>'daniel.okoro@example.com','country_code'=>'NG','created_at'=>'2026-10-07','tier'=>'basic','products'=>3],
    ['name'=>'Lerato Mokoena','email'=>'lerato.mokoena@example.com','country_code'=>'ZA','created_at'=>'2026-10-06','tier'=>'free','products'=>1],
    ['name'=>'Fatima Mwinyi','email'=>'fatima.mwinyi@example.com','country_code'=>'TZ','created_at'=>'2026-10-05','tier'=>'platinum','products'=>16],
    ['name'=>'Kwame Mensah','email'=>'kwame.mensah@example.com','country_code'=>'GH','created_at'=>'2026-10-04','tier'=>'free','products'=>0],
]; }
function manager_sample_products(): array { return [
    ['id'=>1,'title'=>'Samsung Galaxy A55','status'=>'pending','store_title'=>'Amina Tech','seller'=>'Amina Kariuki','created_at'=>'2026-10-08','price'=>'32000','currency_code'=>'KES'],
    ['id'=>2,'title'=>'Modern 2 Bedroom Apartment','status'=>'pending','store_title'=>'Homefinder','seller'=>'Daniel Okoro','created_at'=>'2026-10-07','price'=>'85000','currency_code'=>'KES'],
    ['id'=>3,'title'=>'Handwoven Basket Set','status'=>'published','store_title'=>'Savanna Crafts','seller'=>'Lerato Mokoena','created_at'=>'2026-10-06','price'=>'2400','currency_code'=>'KES'],
]; }
