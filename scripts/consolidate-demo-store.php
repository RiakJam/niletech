<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only.'); }
require_once __DIR__ . '/../includes/app.php';
$db = app_db();
$ownerId = app_demo_user_id();
$db->beginTransaction();
try {
    $q=$db->prepare("SELECT id,user_id FROM creator_pages WHERE section='marketplace' AND slug='nileteck-demo' FOR UPDATE");
    $q->execute(); $productStore=$q->fetch();
    if (!$productStore) throw new RuntimeException('The demo product store is missing.');
    $productStoreId=(int)$productStore['id'];
    $q=$db->prepare("SELECT id FROM creator_pages WHERE user_id=? AND section='marketplace' AND id<>? AND slug=? FOR UPDATE");
    $q->execute([$ownerId,$productStoreId,'store-' . $ownerId]);
    $emptyStoreId=(int)($q->fetchColumn() ?: 0);
    if ($emptyStoreId) {
        $q=$db->prepare('SELECT COUNT(*) FROM page_posts WHERE page_id=?'); $q->execute([$emptyStoreId]);
        if ((int)$q->fetchColumn() !== 0) throw new RuntimeException('The second demo store has products; review before consolidating.');
        $q=$db->prepare('SELECT whatsapp_phone,call_phone FROM store_contact_settings WHERE page_id=?'); $q->execute([$emptyStoreId]); $phones=$q->fetch();
        if ($phones) $db->prepare('INSERT INTO store_contact_settings (page_id,whatsapp_phone,call_phone) VALUES (?,?,?) ON DUPLICATE KEY UPDATE whatsapp_phone=COALESCE(whatsapp_phone,VALUES(whatsapp_phone)),call_phone=COALESCE(call_phone,VALUES(call_phone))')->execute([$productStoreId,$phones['whatsapp_phone'],$phones['call_phone']]);
        $q=$db->prepare('SELECT visits FROM store_analytics WHERE page_id=?'); $q->execute([$emptyStoreId]); $visits=$q->fetchColumn();
        if ($visits !== false) $db->prepare('INSERT INTO store_analytics (page_id,visits) VALUES (?,?) ON DUPLICATE KEY UPDATE visits=visits+VALUES(visits)')->execute([$productStoreId,$visits]);
        $q=$db->prepare('SELECT visitor_id,first_seen,last_seen FROM store_visitors WHERE page_id=?'); $q->execute([$emptyStoreId]);
        $visitorInsert=$db->prepare('INSERT INTO store_visitors (page_id,visitor_id,first_seen,last_seen) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE first_seen=LEAST(first_seen,VALUES(first_seen)),last_seen=GREATEST(last_seen,VALUES(last_seen))');
        foreach ($q->fetchAll() as $visitor) $visitorInsert->execute([$productStoreId,$visitor['visitor_id'],$visitor['first_seen'],$visitor['last_seen']]);
        $db->prepare('UPDATE store_messages SET page_id=? WHERE page_id=?')->execute([$productStoreId,$emptyStoreId]);
        $db->prepare('UPDATE email_contacts SET business_id=? WHERE business_id=?')->execute([$productStoreId,$emptyStoreId]);
        $db->prepare('UPDATE email_campaigns SET business_id=? WHERE business_id=?')->execute([$productStoreId,$emptyStoreId]);
        $db->prepare('DELETE FROM creator_pages WHERE id=?')->execute([$emptyStoreId]);
    }
    $db->prepare('UPDATE creator_pages SET user_id=?,title=? WHERE id=?')->execute([$ownerId,'Nileteck Demo Store',$productStoreId]);
    $db->commit();
    echo "Demo dashboard now owns the product store.\n";
} catch (Throwable $error) {
    if ($db->inTransaction()) $db->rollBack();
    throw $error;
}
