<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only.'); }
require_once __DIR__ . '/../includes/app.php';
$db = app_db();
$userId = app_demo_user_id();
$db->beginTransaction();
try {
    $db->prepare("UPDATE users SET country_code=COALESCE(country_code,'KE'),region=COALESCE(region,'Nairobi'),locality=COALESCE(locality,'Kabiria') WHERE id=?")->execute([$userId]);
    $slug = 'nileteck-demo';
    $query = $db->prepare("SELECT id,user_id FROM creator_pages WHERE section='marketplace' AND slug=?");
    $query->execute([$slug]); $page = $query->fetch();
    if ($page && (int)$page['user_id'] !== $userId) throw new RuntimeException('The demo store address is already in use.');
    if ($page) $pageId = (int)$page['id'];
    else {
        $query = $db->prepare("INSERT INTO creator_pages (user_id,section,slug,title,description) VALUES (?,'marketplace',?,?,?)");
        $query->execute([$userId,$slug,'Nileteck Demo Store','Sample products for exploring the Nileteck Marketplace. These are demonstration listings and are not for sale.']);
        $pageId = (int)$db->lastInsertId();
    }
    $products = [
        ['blue-wireless-headphones','Blue Wireless Headphones','audio','2799',1,"Over-ear wireless headphones with cushioned ear cups and a foldable design.\nColour: navy blue.\nExample price: KSh 2,799.\nThis is a demo listing for preview purposes and is not available for purchase."],
        ['nova-smartphone-128gb','Nova Smartphone 128GB','phones','15999',2,"A modern smartphone with a large display, dual camera design and 128GB of sample storage.\nColour: midnight blue.\nExample price: KSh 15,999.\nThis is a demo listing for preview purposes and is not available for purchase."],
        ['everyday-suede-sneakers','Everyday Suede Sneakers','shoes','1800',3,"Comfortable casual sneakers with a cream canvas upper and tan suede details.\nStyle: everyday low-top.\nExample price: KSh 1,800.\nThis is a demo listing for preview purposes and is not available for purchase."],
        ['silver-smart-watch','Silver Smart Watch','watches','3499',4,"A minimalist smart watch with a silver mesh strap and a clean dark display.\nStyle: modern everyday watch.\nExample price: KSh 3,499.\nThis is a demo listing for preview purposes and is not available for purchase."],
        ['classic-leather-backpack','Classic Leather Backpack','bags','2499',5,"A tan backpack with a front pocket, top handle and classic buckle detail.\nMaterial: leather-look finish.\nExample price: KSh 2,499.\nThis is a demo listing for preview purposes and is not available for purchase."],
        ['compact-coffee-maker','Compact Coffee Maker','appliances','5499',6,"A compact countertop coffee maker with a glass carafe and easy controls.\nFinish: white with natural wood accents.\nExample price: KSh 5,499.\nThis is a demo listing for preview purposes and is not available for purchase."],
    ];
    // Preserve product changes made from the dashboard on later seed runs.
    $query = $db->prepare("INSERT IGNORE INTO page_posts (page_id,slug,title,body,price,currency_code,country_code,region,locality,category,image_path,status) VALUES (?,?,?,?,?,'KES','KE','Nairobi','Kabiria',?,?,'published')");
    $fillLocation = $db->prepare("UPDATE page_posts SET country_code=COALESCE(country_code,'KE'),region=COALESCE(region,'Nairobi'),locality=COALESCE(locality,'Kabiria') WHERE page_id=? AND slug=? AND image_path LIKE 'demo:%'");
    foreach ($products as [$productSlug,$title,$category,$price,$cell,$body]) {
        $query->execute([$pageId,$productSlug,$title,$body,$price,$category,'demo:' . $cell]);
        $fillLocation->execute([$pageId,$productSlug]);
    }
    $db->commit();
    echo "Demo product catalog checked; existing edits were preserved.\n";
} catch (Throwable $error) {
    if ($db->inTransaction()) $db->rollBack();
    throw $error;
}
