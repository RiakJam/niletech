<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/app.php';
require_once __DIR__ . '/includes/marketplace.php';
require_once __DIR__ . '/includes/seller-plans.php';
$categories = marketplace_categories();
$categoryGroups = marketplace_category_groups();
$viewer = app_user();
$countries = marketplace_african_countries();
$countryLocked = isset($countries[$viewer['country_code'] ?? '']);
$countryChoice = is_string($_GET['country'] ?? null) ? $_GET['country'] : null;
$savedCountry = is_string($_COOKIE['nileteck_market_country'] ?? null) ? $_COOKIE['nileteck_market_country'] : null;
if (!$countryLocked && $countryChoice !== null && ($countryChoice === 'all' || isset($countries[$countryChoice]))) setcookie('nileteck_market_country', $countryChoice, ['expires' => time() + 31536000, 'path' => '/', 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
$countrySelection = $countryLocked ? $viewer['country_code'] : ($countryChoice ?? $savedCountry ?? 'all');
$country = isset($countries[$countrySelection]) ? $countrySelection : '';
$region = trim(is_string($_GET['region'] ?? null) ? $_GET['region'] : '');
$locality = trim(is_string($_GET['locality'] ?? null) ? $_GET['locality'] : '');
if (mb_strlen($region) > 100) $region = mb_substr($region, 0, 100);
if (mb_strlen($locality) > 100) $locality = mb_substr($locality, 0, 100);
$priceCurrency = marketplace_african_countries()[$country][1] ?? '';
$category = (string)($_GET['category'] ?? '');
if ($category !== '' && !isset($categories[$category])) $category = '';
$activeCategoryGroup = $category !== '' ? marketplace_category_parent($category) : null;
$search = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($search) > 100) $search = mb_substr($search, 0, 100);
$minPrice = trim((string)($_GET['min_price'] ?? ''));
$maxPrice = trim((string)($_GET['max_price'] ?? ''));
$priceError = '';
$validAmount = static fn(string $value): bool => $value === '' || (bool)preg_match('/^\d{1,12}(?:\.\d{1,3})?$/', $value);
if (!$validAmount($minPrice) || !$validAmount($maxPrice) || ($minPrice !== '' && $maxPrice !== '' && (float)$minPrice > (float)$maxPrice)) {
    $priceError = 'Enter valid amounts, with the minimum no higher than the maximum.';
    $minPrice = $maxPrice = '';
}
$hasFilters = $category !== '' || $search !== '' || (!$countryLocked && $country !== '') || $region !== '' || $locality !== '' || $minPrice !== '' || $maxPrice !== '';
$conditions = ["p.status='published'", "c.section='marketplace'"];
$params = [];
if ($category !== '') {
    $categoryKeys = marketplace_category_filter_keys($category);
    $conditions[] = 'p.category IN (' . implode(',', array_fill(0, count($categoryKeys), '?')) . ')';
    array_push($params, ...$categoryKeys);
}
if ($search !== '') { $conditions[] = '(p.title LIKE ? OR p.body LIKE ?)'; $params[] = '%' . $search . '%'; $params[] = '%' . $search . '%'; }
if ($country !== '') { $conditions[] = 'p.country_code=?'; $params[] = $country; }
if ($region !== '') { $conditions[] = 'p.region LIKE ?'; $params[] = '%' . $region . '%'; }
if ($locality !== '') { $conditions[] = 'p.locality LIKE ?'; $params[] = '%' . $locality . '%'; }
if (($minPrice !== '' || $maxPrice !== '') && $priceCurrency === '') { $priceError = 'Choose a country to compare prices.'; $minPrice = $maxPrice = ''; }
if (($minPrice !== '' || $maxPrice !== '') && $priceCurrency !== '') { $conditions[] = 'p.currency_code=?'; $params[] = $priceCurrency; }
if ($minPrice !== '') { $conditions[] = 'p.price IS NOT NULL AND CAST(p.price AS DECIMAL(15,3))>=?'; $params[] = $minPrice; }
if ($maxPrice !== '') { $conditions[] = 'p.price IS NOT NULL AND CAST(p.price AS DECIMAL(15,3))<=?'; $params[] = $maxPrice; }
$order = 'p.created_at DESC,p.id DESC';
if ($country !== '' && $country === ($viewer['country_code'] ?? null) && !empty($viewer['region'])) {
    $order = 'CASE WHEN p.region=? AND p.locality=? THEN 0 WHEN p.region=? THEN 1 ELSE 2 END,' . $order;
    $params[] = $viewer['region']; $params[] = $viewer['locality'] ?? ''; $params[] = $viewer['region'];
}
$sql = "SELECT p.*,c.slug AS business_slug,c.title AS business_title,d.slug AS domain_slug,CASE WHEN m.current_period_end>UTC_TIMESTAMP() THEN m.tier ELSE 'free' END AS seller_tier FROM page_posts p JOIN creator_pages c ON c.id=p.page_id LEFT JOIN business_domains d ON d.page_id=c.id LEFT JOIN seller_memberships m ON m.user_id=c.user_id WHERE " . implode(' AND ', $conditions) . ' ORDER BY ' . $order . ' LIMIT 60';
$query = app_db()->prepare($sql); $query->execute($params); $products = $query->fetchAll();
$displayProducts = $hasFilters ? $products : array_slice($products, 0, 6);
$recommended = array_slice($products, 6, 12);
if (!$recommended) $recommended = array_slice($products, 0, 6);
$sitePath = rtrim(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/')), '/.');
$siteHrefPrefix = $assetHrefPrefix = ($sitePath === '' ? '/' : $sitePath . '/');
$mainSite = rtrim(app_config('NILETECK_MAIN_URL', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $sitePath), '/');
$marketUrl = $siteHrefPrefix . 'p/marketplace';
$nicheFinderUrl = $siteHrefPrefix . 'niche-finder';
$clearPriceQuery = array_filter(['category'=>$category,'q'=>$search,'country'=>$countryLocked ? '' : ($country ?: 'all'),'region'=>$region,'locality'=>$locality], static fn($value): bool => $value !== '');
$clearPriceUrl = $marketUrl . ($clearPriceQuery ? '?' . http_build_query($clearPriceQuery) : '');
$categoryUrl = static function(string $key) use ($marketUrl, $search, $country, $countryLocked, $region, $locality, $minPrice, $maxPrice): string {
    $query = array_filter(['category'=>$key,'q'=>$search,'country'=>$countryLocked ? '' : ($country ?: 'all'),'region'=>$region,'locality'=>$locality,'min_price'=>$minPrice,'max_price'=>$maxPrice], static fn($value): bool => $value !== '');
    return $marketUrl . ($query ? '?' . http_build_query($query) : '');
};
$locationCountryCounts = [];
$locationCountQuery = app_db()->query("SELECT p.country_code, COUNT(*) AS total FROM page_posts p JOIN creator_pages c ON c.id=p.page_id WHERE p.status='published' AND c.section='marketplace' AND p.country_code IS NOT NULL GROUP BY p.country_code");
foreach ($locationCountQuery->fetchAll() as $row) $locationCountryCounts[$row['country_code']] = (int)$row['total'];
$locationRegions = [];
$locationAreas = [];
$locationCountryTotal = $country !== '' ? ($locationCountryCounts[$country] ?? 0) : (int)app_db()->query("SELECT COUNT(*) FROM page_posts p JOIN creator_pages c ON c.id=p.page_id WHERE p.status='published' AND c.section='marketplace'")->fetchColumn();
if ($country !== '') {
    foreach (marketplace_country_regions($country) as $regionName) $locationRegions[$regionName] = 0;
    $locationQuery = app_db()->prepare("SELECT p.region, p.locality, COUNT(*) AS total FROM page_posts p JOIN creator_pages c ON c.id=p.page_id WHERE p.status='published' AND c.section='marketplace' AND p.country_code=? GROUP BY p.region,p.locality");
    $locationQuery->execute([$country]);
    foreach ($locationQuery->fetchAll() as $row) {
        $regionName = trim((string)$row['region']);
        if ($regionName === '') continue;
        $total = (int)$row['total'];
        $locationRegions[$regionName] = ($locationRegions[$regionName] ?? 0) + $total;
        $areaName = trim((string)$row['locality']);
        if ($areaName !== '') $locationAreas[$regionName][$areaName] = ($locationAreas[$regionName][$areaName] ?? 0) + $total;
    }
    if ($countryLocked && !empty($viewer['region'])) {
        $profileRegion = trim((string)$viewer['region']);
        $locationRegions[$profileRegion] ??= 0;
        if (!empty($viewer['locality'])) $locationAreas[$profileRegion][trim((string)$viewer['locality'])] ??= 0;
    }
    uksort($locationRegions, 'strnatcasecmp');
    foreach ($locationAreas as &$areas) uksort($areas, 'strnatcasecmp');
    unset($areas);
}
$locationPickerData = ['locked'=>$countryLocked,'country'=>$country,'total'=>$locationCountryTotal,'countries'=>array_map(static fn($code, $details) => ['code'=>$code,'name'=>$details[0],'count'=>$locationCountryCounts[$code] ?? 0], array_keys($countries), array_values($countries)),'regions'=>$locationRegions,'areas'=>$locationAreas];
$locationPickerJson = json_encode($locationPickerData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
$locationLabel = $country === '' ? 'All African countries' : ($locality !== '' ? $locality . ', ' : ($region !== '' ? $region . ', ' : 'All ')) . $countries[$country][0];
$pageTitle = ($category !== '' ? $categories[$category][0] . ' | ' : '') . 'Marketplace | Nileteck';
$pageDescription = 'Explore listings from Nileteck businesses by category and price.';
require __DIR__ . '/layout/marketplace-document-start.php';
?>
<main id="main-content" class="marketplace-page"><div class="container marketplace-wrap">
  <div class="marketplace-breadcrumb"><a href="<?= app_h($siteHrefPrefix) ?>">Home</a><span aria-hidden="true">›</span><strong>Marketplace</strong></div>
  <div class="marketplace-location-bar"><button class="marketplace-location-trigger" type="button" data-open-location aria-haspopup="dialog" aria-controls="marketplace-location-dialog"><span class="marketplace-location-pin" aria-hidden="true">⌖</span><span><strong><?= app_h($locationLabel) ?></strong><small><?= number_format($locationCountryTotal) ?> <?= $locationCountryTotal === 1 ? 'listing' : 'listings' ?> in <?= $country === '' ? 'Africa' : app_h($countries[$country][0]) ?></small></span><span aria-hidden="true">⌄</span></button><button class="marketplace-price-trigger<?= ($minPrice !== '' || $maxPrice !== '') ? ' is-active' : '' ?>" type="button" data-open-price aria-haspopup="dialog" aria-controls="marketplace-price-dialog"><span class="marketplace-price-symbol" aria-hidden="true">¤</span><span><strong>Price</strong><small><?php if ($minPrice !== '' || $maxPrice !== ''): ?><?= app_h($priceCurrency) ?> <?= app_h($minPrice !== '' ? $minPrice : '0') ?> – <?= app_h($maxPrice !== '' ? $maxPrice : 'Any') ?><?php else: ?>Any price<?= $priceCurrency !== '' ? ' · ' . app_h($priceCurrency) : '' ?><?php endif; ?></small></span><span aria-hidden="true">⌄</span></button><nav class="marketplace-auth-actions" aria-label="Account"><?php if ($viewer): ?><a class="marketplace-auth-primary" href="<?= app_h($siteHrefPrefix) ?>dashboard">My Account</a><?php else: ?><a class="marketplace-auth-secondary" href="<?= app_h($siteHrefPrefix) ?>signin">Sign in</a><a class="marketplace-auth-primary" href="<?= app_h($siteHrefPrefix) ?>signup">Sign up</a><?php endif; ?></nav></div>
  <dialog id="marketplace-location-dialog" class="marketplace-location-dialog" aria-labelledby="marketplace-location-title"><div class="marketplace-location-modal-head"><div><h2 id="marketplace-location-title">Choose a location</h2><p><?= $countryLocked ? 'Browse places in your account country.' : 'Find listings in your country or a nearby area.' ?></p></div><button type="button" data-close-location aria-label="Close location picker">×</button></div><div class="marketplace-location-modal-tools"><button type="button" class="marketplace-location-back" data-location-back hidden>← Back</button><label class="marketplace-location-search"><span aria-hidden="true">⌕</span><input type="search" data-location-search aria-label="Find state, city or district" placeholder="Find state, city or district"></label></div><div class="marketplace-location-scroll"><div class="marketplace-location-modal-body" data-location-body tabindex="0" aria-label="Locations"></div><button class="marketplace-location-scroll-cue is-up" type="button" data-location-scroll="-1" aria-label="Show earlier locations" hidden><span aria-hidden="true">⌃</span></button><button class="marketplace-location-scroll-cue is-down" type="button" data-location-scroll="1" aria-label="Show more locations" hidden><span aria-hidden="true">⌄</span></button></div><div class="marketplace-location-modal-foot" data-location-foot></div></dialog><script type="application/json" id="marketplace-location-data"><?= $locationPickerJson ?></script>
  <dialog id="marketplace-price-dialog" class="marketplace-price-dialog" aria-labelledby="marketplace-price-title" data-price-error="<?= $priceError !== '' ? 'true' : 'false' ?>"><div class="marketplace-price-modal-head"><div><h2 id="marketplace-price-title">Filter by price</h2><p><?= $priceCurrency !== '' ? 'Enter a price range in ' . app_h($priceCurrency) . '.' : 'Choose a country in the location picker to filter by price.' ?></p></div><button type="button" data-close-price aria-label="Close price filter">×</button></div><?php if ($priceError): ?><p class="marketplace-price-error" role="alert"><?= app_h($priceError) ?></p><?php endif; ?><form class="marketplace-amount-filter" action="<?= app_h($marketUrl) ?>" method="get"><?php if ($category !== ''): ?><input type="hidden" name="category" value="<?= app_h($category) ?>"><?php endif; ?><?php if ($search !== ''): ?><input type="hidden" name="q" value="<?= app_h($search) ?>"><?php endif; ?><?php if (!$countryLocked): ?><input type="hidden" name="country" value="<?= app_h($country ?: 'all') ?>"><?php endif; ?><?php if ($region !== ''): ?><input type="hidden" name="region" value="<?= app_h($region) ?>"><?php endif; ?><?php if ($locality !== ''): ?><input type="hidden" name="locality" value="<?= app_h($locality) ?>"><?php endif; ?><?php if ($priceCurrency !== ''): ?><p class="marketplace-amount-currency">Prices in <?= app_h($priceCurrency) ?></p><?php else: ?><p class="marketplace-amount-currency">Choose a country above to compare prices.</p><?php endif; ?><div class="marketplace-price-fields"><label>Min<input type="number" name="min_price" min="0" step="0.01" value="<?= app_h($minPrice) ?>" placeholder="0"></label><label>Max<input type="number" name="max_price" min="0" step="0.01" value="<?= app_h($maxPrice) ?>" placeholder="Any"></label></div><button type="submit" <?= $priceCurrency === '' ? 'disabled' : '' ?>>Apply price</button><?php if ($minPrice !== '' || $maxPrice !== ''): ?><a href="<?= app_h($clearPriceUrl) ?>" data-clear-price>Clear price</a><?php endif; ?></form></dialog>
  <div class="marketplace-intro"><div><span class="eyebrow">NILETECK MARKETPLACE</span><h1>Find something great.</h1><p>Explore listings from businesses on Nileteck.</p></div><div class="marketplace-actions"><form class="marketplace-search" action="<?= app_h($marketUrl) ?>" method="get" role="search"><label class="marketplace-visually-hidden" for="marketplace-query">Search listings</label><input id="marketplace-query" name="q" type="search" value="<?= app_h($search) ?>" placeholder="Search listings"><?php if ($category !== ''): ?><input type="hidden" name="category" value="<?= app_h($category) ?>"><?php endif; ?><?php if ($minPrice !== ''): ?><input type="hidden" name="min_price" value="<?= app_h($minPrice) ?>"><?php endif; ?><?php if ($maxPrice !== ''): ?><input type="hidden" name="max_price" value="<?= app_h($maxPrice) ?>"><?php endif; ?><?php if (!$countryLocked): ?><input type="hidden" name="country" value="<?= app_h($country ?: 'all') ?>"><?php endif; ?><?php if ($region !== ''): ?><input type="hidden" name="region" value="<?= app_h($region) ?>"><?php endif; ?><?php if ($locality !== ''): ?><input type="hidden" name="locality" value="<?= app_h($locality) ?>"><?php endif; ?><button type="submit">Search</button></form><a class="button button-quiet marketplace-niche-button" href="<?= app_h($nicheFinderUrl) ?>">✦ Niche Finder</a><a class="button button-primary" href="<?= app_h($siteHrefPrefix) ?>dashboard?view=marketplace">Post a listing <span aria-hidden="true">↗</span></a></div></div>
  <div class="marketplace-main-layout"><aside class="marketplace-categories" aria-label="Marketplace filters"><h2>Categories</h2><div class="marketplace-category-scroll"><button class="marketplace-category-scroll-control is-up" type="button" data-scroll-categories="-1" aria-label="Show earlier categories" hidden><span aria-hidden="true">⌃</span></button><nav class="marketplace-category-list" aria-label="Product categories" tabindex="0">
    <a class="marketplace-category <?= $category === '' ? 'is-active' : '' ?>" <?= $category === '' ? 'aria-current="page"' : '' ?> href="<?= app_h($categoryUrl('')) ?>"><span class="category-icon" aria-hidden="true"><?= marketplace_category_icon('all') ?></span><span>All listings</span></a>
    <?php foreach ($categoryGroups as $key => $details): ?>
      <div class="marketplace-category-group"><button class="marketplace-category <?= $activeCategoryGroup === $key ? 'is-active' : '' ?>" type="button" data-open-category="<?= app_h($key) ?>" aria-haspopup="dialog" aria-controls="marketplace-category-dialog" aria-label="Show <?= app_h($details[0]) ?> subcategories"><span class="category-icon" aria-hidden="true"><?= marketplace_category_icon($key) ?></span><span class="marketplace-category-label"><?= app_h($details[0]) ?></span><span class="marketplace-category-chevron" aria-hidden="true">›</span></button></div>
    <?php endforeach; ?>
  </nav><button class="marketplace-category-scroll-control is-down" type="button" data-scroll-categories="1" aria-label="Show more categories" hidden><span aria-hidden="true">⌄</span></button></div></aside>
  <dialog class="marketplace-category-dialog" id="marketplace-category-dialog" aria-labelledby="marketplace-category-dialog-title"><div class="marketplace-category-dialog-head"><div><span class="eyebrow">BROWSE CATEGORIES</span><h2 id="marketplace-category-dialog-title" data-category-dialog-title>Choose a subcategory</h2><p>Select what you want to explore.</p></div><button type="button" data-close-category aria-label="Close subcategories">×</button></div><div class="marketplace-category-dialog-body"><?php foreach ($categoryGroups as $key => $details): ?><div class="marketplace-category-panel" data-category-panel="<?= app_h($key) ?>" hidden><a class="marketplace-category-option <?= $category === $key ? 'is-active' : '' ?>" href="<?= app_h($categoryUrl($key)) ?>"><span class="marketplace-subcategory-icon" aria-hidden="true"><?= marketplace_category_icon($key) ?></span><span>All <?= app_h($details[0]) ?></span><span aria-hidden="true">↗</span></a><?php foreach ($details[2] as $childKey => $childLabel): ?><a class="marketplace-category-option <?= $category === $childKey ? 'is-active' : '' ?>" href="<?= app_h($categoryUrl($childKey)) ?>"><span class="marketplace-subcategory-icon" aria-hidden="true"><?= marketplace_category_icon($childKey) ?></span><span><?= app_h($childLabel) ?></span><span aria-hidden="true">↗</span></a><?php endforeach; ?></div><?php endforeach; ?></div></dialog>
  <div class="marketplace-content"><?php if ($priceError): ?><p class="marketplace-filter-error" role="alert"><?= app_h($priceError) ?></p><?php endif; ?><section class="marketplace-section" id="whats-new" aria-labelledby="new-title"><div class="marketplace-section-head"><div><span class="marketplace-section-icon" aria-hidden="true">✦</span><h2 id="new-title"><?= $hasFilters ? ($category !== '' ? app_h($categories[$category][0]) : 'Filtered products') : "What's New" ?></h2></div><span><?= count($products) ?> <?= count($products) === 1 ? 'listing' : 'listings' ?></span></div><?php if ($displayProducts): ?><div class="marketplace-product-grid"><?php foreach ($displayProducts as $product): require __DIR__ . '/includes/marketplace-card.php'; endforeach; ?></div><?php else: ?><div class="marketplace-empty"><h3>No listings match your filters</h3><p>Try another category, search term, or amount.</p><a href="<?= app_h($marketUrl) ?>">Clear all filters ↗</a></div><?php endif; ?></section>
  <?php if (!$hasFilters): ?><section class="marketplace-section" id="recommended" aria-labelledby="recommended-title"><div class="marketplace-section-head"><div><span class="marketplace-section-icon" aria-hidden="true">♡</span><h2 id="recommended-title">You May Also Like</h2></div></div><?php if ($recommended): ?><div class="marketplace-product-grid"><?php foreach ($recommended as $product): require __DIR__ . '/includes/marketplace-card.php'; endforeach; ?></div><?php else: ?><div class="marketplace-empty"><h3>More discoveries are on the way</h3><p>Check back as businesses add new products.</p></div><?php endif; ?></section><?php endif; ?></div></div>
</div></main></body></html>
