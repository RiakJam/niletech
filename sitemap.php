<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/app.php';
$db=app_db();
$host=strtolower(preg_replace('/:\d+$/','',(string)($_SERVER['HTTP_HOST'] ?? '')) ?? '');
$businessSlug=null;
if (preg_match('/^([a-z][a-z0-9-]{2,59})\.nileteck\.com$/',$host,$m) && !in_array($m[1],['www','mail','email','admin','api','blog','forum','marketplace','events','dashboard','support','help','account','signin','signup'],true)) $businessSlug=$m[1];
if ($businessSlug) {
    $q=$db->prepare("SELECT d.slug,p.slug AS product_slug FROM business_domains d LEFT JOIN page_posts p ON p.page_id=d.page_id AND p.status='published' WHERE d.slug=?");
    $q->execute([$businessSlug]); $rows=$q->fetchAll();
} else {
    $rows=$db->query("SELECT d.slug,p.slug AS product_slug FROM business_domains d LEFT JOIN page_posts p ON p.page_id=d.page_id AND p.status='published'")->fetchAll();
}
header('Content-Type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
$seen=[];
foreach ($rows as $row) {
    $base='https://'.$row['slug'].'.nileteck.com/';
    if (!isset($seen[$base])) { echo '<url><loc>'.htmlspecialchars($base,ENT_XML1,'UTF-8').'</loc></url>'; $seen[$base]=true; }
    if ($row['product_slug']) echo '<url><loc>'.htmlspecialchars($base.rawurlencode($row['product_slug']),ENT_XML1,'UTF-8').'</loc></url>';
}
echo '</urlset>';
