<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/app.php';
require_once __DIR__ . '/includes/marketplace.php';

$sitePath = rtrim(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/')), '/.');
$siteHrefPrefix = $assetHrefPrefix = $sitePath === '' ? '/' : $sitePath . '/';
$groups = marketplace_category_groups();
$viewer = app_user();
$countries = marketplace_african_countries();
$countryChoice = is_string($_GET['country'] ?? null) ? $_GET['country'] : null;
if ($countryChoice !== null && $countryChoice !== 'all' && !isset($countries[$countryChoice])) $countryChoice = 'all';
if ($countryChoice !== null) setcookie('nileteck_market_country', $countryChoice, ['expires' => time() + 31536000, 'path' => '/', 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
$savedCountry = is_string($_COOKIE['nileteck_market_country'] ?? null) ? $_COOKIE['nileteck_market_country'] : null;
$countrySelection = $countryChoice ?? $savedCountry ?? ($viewer['country_code'] ?? 'all');
$country = isset($countries[$countrySelection]) ? $countrySelection : '';
$selectedGroup = (string)($_GET['category'] ?? '');
if (!isset($groups[$selectedGroup])) $selectedGroup = '';
$search = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($search) > 80) $search = mb_substr($search, 0, 80);
$sort = (string)($_GET['sort'] ?? 'most');
if (!in_array($sort, ['fewest', 'most', 'name'], true)) $sort = 'most';
$view = (string)($_GET['view'] ?? 'grid');
if (!in_array($view, ['grid', 'list'], true)) $view = 'grid';
$favoritesOnly = (string)($_GET['favorites'] ?? '') === '1';
$favorites = array_fill_keys(array_filter(explode(',', (string)($_COOKIE['nileteck_niche_favorites'] ?? '')), static fn($key): bool => (bool)preg_match('/^[a-z_]+$/', $key)), true);
$counts = [];
$categorySellers = [];
$sellerCount = 0;
$dataAvailable = true;
try {
    $query = app_db()->prepare("SELECT p.category, COUNT(*) AS listing_count, COUNT(DISTINCT p.page_id) AS seller_count FROM page_posts p JOIN creator_pages c ON c.id=p.page_id WHERE p.status='published' AND c.section='marketplace'" . ($country !== '' ? ' AND p.country_code=?' : '') . ' GROUP BY p.category');
    $query->execute($country !== '' ? [$country] : []);
    foreach ($query->fetchAll() as $row) {
        $counts[(string)$row['category']] = (int)$row['listing_count'];
        $categorySellers[(string)$row['category']] = (int)$row['seller_count'];
    }
    $sellerQuery = app_db()->prepare("SELECT COUNT(DISTINCT p.page_id) FROM page_posts p JOIN creator_pages c ON c.id=p.page_id WHERE p.status='published' AND c.section='marketplace'" . ($country !== '' ? ' AND p.country_code=?' : ''));
    $sellerQuery->execute($country !== '' ? [$country] : []);
    $sellerCount = (int)$sellerQuery->fetchColumn();
} catch (PDOException | RuntimeException $error) {
    $dataAvailable = false;
    error_log('Niche Finder listings unavailable: ' . $error->getMessage());
}
$allNiches = [];
foreach ($groups as $groupKey => $group) {
    foreach ($group[2] as $key => $label) $allNiches[] = ['key' => $key, 'label' => $label, 'group_key' => $groupKey, 'group' => $group[0], 'count' => $counts[$key] ?? 0, 'sellers' => $categorySellers[$key] ?? 0];
}
$niches = array_values(array_filter($allNiches, static function(array $niche) use ($selectedGroup, $search, $favoritesOnly, $favorites): bool {
    return ($selectedGroup === '' || $niche['group_key'] === $selectedGroup)
        && ($search === '' || mb_stripos($niche['label'] . ' ' . $niche['group'], $search) !== false)
        && (!$favoritesOnly || isset($favorites[$niche['key']]));
}));
usort($niches, static function(array $a, array $b) use ($sort): int {
    if ($sort === 'name') return strcasecmp($a['label'], $b['label']);
    return ($sort === 'most' ? $b['count'] <=> $a['count'] : $a['count'] <=> $b['count']) ?: strcasecmp($a['label'], $b['label']);
});
$perPage = 16;
$totalPages = max(1, (int)ceil(count($niches) / $perPage));
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
$page = min($page, $totalPages);
$visibleNiches = array_slice($niches, ($page - 1) * $perPage, $perPage);
$totalListings = array_sum($counts);
$activeNiches = count(array_filter($allNiches, static fn(array $niche): bool => $niche['count'] > 0));
$openNiches = count($allNiches) - $activeNiches;
$baseQuery = array_filter(['q' => $search, 'category' => $selectedGroup, 'country' => $country ?: 'all', 'sort' => $sort, 'view' => $view, 'favorites' => $favoritesOnly ? '1' : ''], static fn($value): bool => $value !== '');
$url = static fn(array $changes = []): string => $siteHrefPrefix . 'niche-finder?' . http_build_query(array_filter(array_merge($baseQuery, $changes), static fn($value): bool => $value !== ''));
$pageTitle = 'Niche Finder | Nileteck Marketplace';
$pageDescription = 'Compare current listing competition across Nileteck marketplace categories.';
require __DIR__ . '/layout/marketplace-document-start.php';
?>
<main id="main-content" class="niche-page">
  <div class="container niche-breadcrumb"><a href="<?= app_h($siteHrefPrefix) ?>">Home</a><span>›</span><a href="<?= app_h($siteHrefPrefix) ?>p/marketplace">Marketplace</a><span>›</span><strong>Niche Finder</strong></div>
  <section class="niche-hero"><div class="container niche-hero-inner"><span class="niche-hero-kicker">✦ &nbsp; MARKETPLACE INSIGHTS FOR SELLERS</span><h1>Niche Finder</h1><p>Spot categories with room to grow. Compare live listings and discover where your products could stand out<?= $country !== '' ? ' in ' . app_h($countries[$country][0]) : ' across Africa' ?>.</p><div class="niche-stats"><div><span class="niche-stat-icon">▣</span><strong><?= number_format($totalListings) ?></strong><small>Active listings</small></div><div><span class="niche-stat-icon">⊞</span><strong><?= number_format(count($allNiches)) ?></strong><small>Categories to explore</small></div><div><span class="niche-stat-icon">✧</span><strong><?= number_format($openNiches) ?></strong><small>With no listings yet</small></div><div><span class="niche-stat-icon">♙</span><strong><?= number_format($sellerCount) ?></strong><small>Active sellers</small></div></div></div></section>
  <section class="container niche-explore" aria-labelledby="niche-heading">
    <div class="niche-explore-top"><div><span class="eyebrow">EXPLORE THE MARKET</span><h2 id="niche-heading">Explore niches</h2></div><div class="niche-view-actions"><a class="niche-favorites-link <?= $favoritesOnly ? 'is-active' : '' ?>" href="<?= app_h($url(['favorites' => $favoritesOnly ? '' : '1', 'page' => ''])) ?>">♡ Favorites</a><div class="niche-view-toggle" aria-label="View style"><a href="<?= app_h($url(['view' => 'grid', 'page' => ''])) ?>" class="<?= $view === 'grid' ? 'is-active' : '' ?>" aria-label="Grid view" <?= $view === 'grid' ? 'aria-current="page"' : '' ?>>▦</a><a href="<?= app_h($url(['view' => 'list', 'page' => ''])) ?>" class="<?= $view === 'list' ? 'is-active' : '' ?>" aria-label="List view" <?= $view === 'list' ? 'aria-current="page"' : '' ?>>☷</a></div></div></div>
    <div class="niche-sort"><span>Sort:</span><a class="<?= $sort === 'fewest' ? 'is-active' : '' ?>" href="<?= app_h($url(['sort' => 'fewest', 'page' => ''])) ?>">Fewest listings</a><a class="<?= $sort === 'most' ? 'is-active' : '' ?>" href="<?= app_h($url(['sort' => 'most', 'page' => ''])) ?>">Most listings</a><a class="<?= $sort === 'name' ? 'is-active' : '' ?>" href="<?= app_h($url(['sort' => 'name', 'page' => ''])) ?>">A–Z</a></div>
    <div class="niche-search-prompt"><strong>What do you want to sell? <span aria-hidden="true">👇</span></strong><span>Search a product or explore a category below</span></div>
    <form class="niche-search-form" method="get" action="<?= app_h($siteHrefPrefix) ?>niche-finder"><label class="marketplace-visually-hidden" for="niche-search">Search niches</label><span aria-hidden="true">⌕</span><input id="niche-search" type="search" name="q" value="<?= app_h($search) ?>" placeholder="Search niches, products, or categories"><?php if ($selectedGroup !== ''): ?><input type="hidden" name="category" value="<?= app_h($selectedGroup) ?>"><?php endif; ?><input type="hidden" name="country" value="<?= app_h($country ?: 'all') ?>"><input type="hidden" name="sort" value="<?= app_h($sort) ?>"><input type="hidden" name="view" value="<?= app_h($view) ?>"><?php if ($favoritesOnly): ?><input type="hidden" name="favorites" value="1"><?php endif; ?><button type="submit">Search</button></form>
    <nav class="niche-category-chips" aria-label="Niche categories"><a class="<?= $selectedGroup === '' ? 'is-active' : '' ?>" href="<?= app_h($url(['category' => '', 'page' => ''])) ?>">All</a><?php foreach (array_slice($groups, 0, 5, true) as $key => $group): ?><a class="<?= $selectedGroup === $key ? 'is-active' : '' ?>" href="<?= app_h($url(['category' => $key, 'page' => ''])) ?>"><?= app_h($group[0]) ?></a><?php endforeach; ?><label class="niche-more-category"><span class="marketplace-visually-hidden">More categories</span><select aria-label="More categories" data-niche-category-select><option value="">More categories ▾</option><?php foreach (array_slice($groups, 5, null, true) as $key => $group): ?><option value="<?= app_h($url(['category' => $key, 'page' => ''])) ?>" <?= $selectedGroup === $key ? 'selected' : '' ?>><?= app_h($group[0]) ?></option><?php endforeach; ?></select></label></nav>
    <form class="niche-country-filter" method="get" action="<?= app_h($siteHrefPrefix) ?>niche-finder"><label>Market country<select name="country"><option value="all" <?= $country === '' ? 'selected' : '' ?>>All African countries</option><?php foreach ($countries as $code => $details): ?><option value="<?= app_h($code) ?>" <?= $country === $code ? 'selected' : '' ?>><?= app_h($details[0]) ?></option><?php endforeach; ?></select></label><?php if ($search !== ''): ?><input type="hidden" name="q" value="<?= app_h($search) ?>"><?php endif; ?><?php if ($selectedGroup !== ''): ?><input type="hidden" name="category" value="<?= app_h($selectedGroup) ?>"><?php endif; ?><input type="hidden" name="sort" value="<?= app_h($sort) ?>"><input type="hidden" name="view" value="<?= app_h($view) ?>"><?php if ($favoritesOnly): ?><input type="hidden" name="favorites" value="1"><?php endif; ?><button type="submit">Show niches</button></form>
    <div class="niche-results-meta"><span>Showing <strong><?= $visibleNiches ? (($page - 1) * $perPage + 1) . '–' . (($page - 1) * $perPage + count($visibleNiches)) : '0' ?></strong> of <strong><?= count($niches) ?></strong> categories</span><span>Competition reflects active sellers</span></div>
    <?php if (!$dataAvailable): ?><div class="niche-empty">Listing data is temporarily unavailable. Please try again shortly.</div><?php elseif (!$visibleNiches): ?><div class="niche-empty"><h3>No niches found</h3><p>Try another search or category<?php if ($favoritesOnly): ?>, or save a niche using its heart button<?php endif; ?>.</p><a href="<?= app_h($siteHrefPrefix) ?>niche-finder">Browse all niches →</a></div><?php else: ?>
      <div class="niche-results <?= $view === 'list' ? 'is-list' : 'is-grid' ?>"><?php foreach ($visibleNiches as $niche): ?><article class="niche-result-card"><button type="button" class="niche-card-open" data-niche-open="<?= app_h($niche['key']) ?>" aria-label="View details for <?= app_h($niche['label']) ?>"></button><div class="niche-result-main"><span class="niche-result-icon" aria-hidden="true"><?= marketplace_category_icon($niche['key']) ?></span><div><span class="niche-result-group"><?= app_h($niche['group']) ?></span><h3><?= app_h($niche['label']) ?></h3></div></div><button type="button" class="niche-save <?= isset($favorites[$niche['key']]) ? 'is-saved' : '' ?>" data-niche-favorite="<?= app_h($niche['key']) ?>" aria-label="<?= isset($favorites[$niche['key']]) ? 'Remove' : 'Save' ?> <?= app_h($niche['label']) ?> <?= isset($favorites[$niche['key']]) ? 'from' : 'to' ?> favorites" aria-pressed="<?= isset($favorites[$niche['key']]) ? 'true' : 'false' ?>">♥</button><div class="niche-result-metrics"><span class="niche-pill <?= $niche['sellers'] === 0 ? 'is-open' : ($niche['sellers'] >= 3 ? 'is-high' : 'is-low') ?>"><?= $niche['sellers'] === 0 ? 'No competition' : ($niche['sellers'] >= 3 ? 'High competition' : 'Low competition') ?></span><span class="niche-count"><strong><?= $niche['count'] ?></strong> <?= $niche['count'] === 1 ? 'listing' : 'listings' ?></span></div><div class="niche-result-foot"><span><?= $niche['count'] === 0 ? 'Be the first to list here' : 'Explore what sellers offer' ?></span><a href="<?= app_h($siteHrefPrefix) ?>p/marketplace?category=<?= rawurlencode($niche['key']) ?>&amp;country=<?= app_h($country ?: 'all') ?>">View niche →</a></div></article><?php endforeach; ?></div>
      <?php if ($totalPages > 1): ?><nav class="niche-pagination" aria-label="Niche pages"><?php if ($page > 1): ?><a href="<?= app_h($url(['page' => (string)($page - 1)])) ?>">← Previous</a><?php endif; ?><?php for ($number = max(1, $page - 2); $number <= min($totalPages, $page + 2); $number++): ?><a href="<?= app_h($url(['page' => (string)$number])) ?>" <?= $number === $page ? 'aria-current="page"' : '' ?>><?= $number ?></a><?php endfor; ?><?php if ($page < $totalPages): ?><a href="<?= app_h($url(['page' => (string)($page + 1)])) ?>">Next →</a><?php endif; ?></nav><?php endif; ?>
    <?php endif; ?>
    <div class="niche-disclaimer">Competition uses active sellers in each niche: none, low (1–2 sellers), or high (3+ sellers). Buyer demand, search volume, and trends are not measured by Nileteck yet.</div>
  </section>
  <section class="niche-info-section niche-how-section" aria-labelledby="niche-how-title"><div class="container">
    <div class="niche-info-heading"><h2 id="niche-how-title">How it works</h2><p>Three simple steps to explore the marketplace before you list.</p></div>
    <div class="niche-info-grid">
      <article class="niche-step-card"><span class="niche-step-number">01</span><span class="niche-step-icon" aria-hidden="true">⌕</span><h3>Explore niches</h3><p>Search the categories and discover what sellers have already listed on Nileteck.</p></article>
      <article class="niche-step-card"><span class="niche-step-number">02</span><span class="niche-step-icon" aria-hidden="true">▥</span><h3>Compare competition</h3><p>Check live listing and seller counts to see which categories are less crowded.</p></article>
      <article class="niche-step-card"><span class="niche-step-number">03</span><span class="niche-step-icon" aria-hidden="true">◎</span><h3>Start selling</h3><p>Pick a category that fits your product, then create a clear listing with useful photos.</p></article>
    </div>
  </div></section>
  <section class="niche-info-section niche-why-section" aria-labelledby="niche-why-title"><div class="container">
    <div class="niche-info-heading"><h2 id="niche-why-title">Why Niche Finder?</h2><p>A clearer view of where your products could fit on Nileteck.</p></div>
    <div class="niche-info-grid">
      <article class="niche-benefit-card"><span class="niche-benefit-icon" aria-hidden="true">↗</span><h3>Make informed choices</h3><p>See real listing and seller counts instead of guessing how busy a category is.</p></article>
      <article class="niche-benefit-card"><span class="niche-benefit-icon" aria-hidden="true">◇</span><h3>Spot open categories</h3><p>Find categories with few or no current sellers and consider whether your product belongs there.</p></article>
      <article class="niche-benefit-card"><span class="niche-benefit-icon" aria-hidden="true">✓</span><h3>Plan a better listing</h3><p>Open any niche for related categories and practical tips before posting your product.</p></article>
    </div>
  </div></section>
</main>
<dialog class="niche-detail-modal" id="niche-detail-modal" aria-labelledby="niche-modal-title" data-market-base="<?= app_h($siteHrefPrefix) ?>p/marketplace?category=" data-market-country="<?= app_h($country ?: 'all') ?>" data-post-url="<?= app_h($siteHrefPrefix) ?>dashboard?view=marketplace">
  <div class="niche-modal-head"><div><span class="eyebrow">NICHE DETAILS</span><h2 id="niche-modal-title"></h2><p id="niche-modal-group"></p></div><button type="button" class="niche-modal-close" data-niche-close aria-label="Close niche details">×</button></div>
  <div class="niche-modal-body"><div class="niche-modal-stats"><div><strong id="niche-modal-listings"></strong><small>ACTIVE LISTINGS</small><span id="niche-modal-listings-note"></span></div><div><strong id="niche-modal-sellers"></strong><small>ACTIVE SELLERS</small><span>In this category</span></div><div><strong id="niche-modal-group-count"></strong><small>GROUP LISTINGS</small><span id="niche-modal-group-name"></span></div><div><strong id="niche-modal-status"></strong><small>COMPETITION</small><span id="niche-modal-status-note"></span></div></div>
  <p class="niche-modal-data-note">Competition is based on active sellers: low means 1–2 sellers; high means 3 or more. Buyer searches, demand, and trends are not measured yet.</p>
  <section class="niche-modal-related"><h3>Related niches</h3><div id="niche-modal-related-list"></div><button type="button" id="niche-modal-related-more" hidden>Show more related niches</button></section>
  <section class="niche-modal-howto"><h3>✧ &nbsp; How to list in this niche</h3><ol><li><strong>Write a clear title</strong><span>Name the product, brand, model, and condition so buyers can find it.</span></li><li><strong>Describe the details</strong><span>Include size, features, condition, location, and your asking price.</span></li><li><strong>Add useful photos</strong><span>Show the item from several angles in good light.</span></li></ol><div class="niche-modal-photo-tip">▧<span>Add at least 3 clear photos to help buyers understand your listing.</span></div></section>
  <div class="niche-modal-actions"><a id="niche-modal-view" href="#">View current listings →</a><a id="niche-modal-post" href="<?= app_h($siteHrefPrefix) ?>dashboard?view=marketplace">Post a listing ↗</a></div></div>
</dialog>
<script type="application/json" id="niche-data"><?= json_encode($allNiches, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) ?></script>
<script src="<?= app_h($assetHrefPrefix) ?>src/niche-finder.js?v=<?= filemtime(__DIR__ . '/src/niche-finder.js') ?>" defer></script></body></html>
