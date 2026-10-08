<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/app.php';
require_once __DIR__ . '/includes/marketplace.php';
require_once __DIR__ . '/includes/seller-plans.php';
$sitePath = rtrim(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/')), '/.');
$assetHrefPrefix = $siteHrefPrefix = $sitePath === '' ? '/' : $sitePath . '/';
$mainSite = rtrim(app_config('NILETECK_MAIN_URL', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $sitePath), '/');
$categories = marketplace_categories();
$marketplaceAssets = true;
$landingProducts = [];
try {
    $landingViewer = app_user();
    $order = 'p.created_at DESC, p.id DESC';
    $parameters = [];
    if (!empty($landingViewer['country_code'])) {
        $order = 'CASE WHEN p.country_code=? AND p.region=? THEN 0 WHEN p.country_code=? THEN 1 ELSE 2 END, ' . $order;
        $parameters = [$landingViewer['country_code'], $landingViewer['region'] ?? '', $landingViewer['country_code']];
    }
    $query = app_db()->prepare("SELECT p.*, c.slug AS business_slug, c.title AS business_title, d.slug AS domain_slug,CASE WHEN m.current_period_end>UTC_TIMESTAMP() THEN m.tier ELSE 'free' END AS seller_tier FROM page_posts p JOIN creator_pages c ON c.id=p.page_id LEFT JOIN business_domains d ON d.page_id=c.id LEFT JOIN seller_memberships m ON m.user_id=c.user_id WHERE p.status='published' AND c.section='marketplace' ORDER BY $order LIMIT 6");
    $query->execute($parameters);
    $landingProducts = $query->fetchAll();
} catch (PDOException | RuntimeException $error) {
    error_log('Landing marketplace products unavailable: ' . $error->getMessage());
}
$pageTitle = 'Nileteck | Web Design, Development, Maintenance & Marketing';
$pageDescription = 'Nileteck offers web design and development, website maintenance, Email Marketing, and an online Marketplace for growing businesses.';
require __DIR__ . '/layout/header.php';
?>
<main id="main-content" class="landing-page">
  <section class="hero platform-hero" id="home">
    <div class="container hero-content">
      <span class="eyebrow"><span class="status-dot"></span> NILETECK WEB &amp; DIGITAL SERVICES</span>
      <h1>Web design and development<br><span class="highlight">for growing businesses.</span></h1>
      <p>We design, build, and maintain websites. Sell through Nileteck Marketplace and stay connected with your customers using Email Marketing.</p>
      <div class="actions">
        <a class="button button-primary" href="website-services">Explore website services <span aria-hidden="true">↗</span></a>
        <a class="button button-quiet" href="contact">Talk to our team <span aria-hidden="true">→</span></a>
      </div>
      <div class="hero-prompt"><span></span> BUILD. MAINTAIN. SELL. CONNECT. <span></span></div>
    </div>
    <div class="hero-catalog"><div class="container">
      <p class="catalog-intro">Explore what we can build and grow together</p>
      <div class="catalog-grid" aria-label="Nileteck services and tools">
        <a class="catalog-item" href="website-services#development"><span class="catalog-icon icon-web" aria-hidden="true">&lt;/&gt;</span><span>Web design &amp; development</span></a>
        <a class="catalog-item" href="website-services#maintenance"><span class="catalog-icon icon-care" aria-hidden="true">✦</span><span>Web maintenance</span></a>
        <a class="catalog-item" href="services#email"><span class="catalog-icon icon-email" aria-hidden="true">✉</span><span>Email Marketing</span></a>
        <a class="catalog-item" href="p/marketplace"><span class="catalog-icon icon-social" aria-hidden="true">◇</span><span>Marketplace</span></a>
      </div>
    </div></div>
  </section>

  <section class="section landing-marketplace" id="market-products"><div class="container">
    <div class="section-head"><div><span class="eyebrow">NILETECK MARKETPLACE</span><h2>Fresh from the marketplace.</h2></div><a class="text-link" href="<?= app_h($siteHrefPrefix) ?>p/marketplace">Browse all products <span aria-hidden="true">↗</span></a></div>
    <?php if ($landingProducts): ?><div class="marketplace-product-grid landing-product-grid"><?php foreach ($landingProducts as $product): require __DIR__ . '/includes/marketplace-card.php'; endforeach; ?></div>
    <?php else: ?><p class="landing-marketplace-empty">New listings are on the way. <a href="<?= app_h($siteHrefPrefix) ?>p/marketplace">Explore the marketplace →</a></p><?php endif; ?>
  </div></section>

  <section class="section product-section"><div class="container"><div class="section-head"><div><span class="eyebrow">USE NILETECK YOUR WAY</span><h2>Sell products. Reach your audience.</h2></div><p>Marketplace and Email Marketing are available from your dashboard. Your storefront is ready automatically when you open your dashboard.</p></div><div class="card-grid"><article class="app-card product-card-marketplace"><div class="card-top"><span class="card-kicker">SELL</span><span class="badge">Marketplace</span></div><h3>Showcase your products</h3><p>Publish listings and share your storefront.</p><a class="card-foot" href="p/marketplace">Explore Marketplace <span aria-hidden="true">↗</span></a></article><article class="app-card product-card-email"><div class="card-top"><span class="card-kicker">CONNECT</span><span class="badge">Email Marketing</span></div><h3>Stay in touch</h3><p>Grow a list of subscribed contacts and prepare campaigns for your audience.</p><a class="card-foot" href="services#email">Explore Email Marketing <span aria-hidden="true">↗</span></a></article><article class="app-card product-card-workspace"><div class="card-top"><span class="card-kicker">MANAGE</span><span class="badge">Workspace</span></div><h3>Your business dashboard</h3><p>Keep products and email marketing together in one workspace.</p><a class="card-foot" href="dashboard">Open dashboard <span aria-hidden="true">↗</span></a></article></div></div></section>

  <section class="section services-section" id="services"><div class="container services-editorial"><div class="services-copy">
    <span class="eyebrow">WHAT WE DO</span><h2>Website design, development, and maintenance.</h2><p>We design and build websites that make a strong first impression, then keep them working as your business grows.</p>
    <nav class="services-links" aria-label="Website services"><a id="web-development" href="website-services#development"><span><strong>Web design &amp; development</strong><small>Thoughtful websites built around your goals.</small></span><span aria-hidden="true">↗</span></a><a id="web-maintenance" href="website-services#maintenance"><span><strong>Website maintenance</strong><small>Ongoing updates, improvements, and care.</small></span><span aria-hidden="true">↗</span></a></nav>
  </div></div></section>

  <section class="section apps-section" id="apps"><div class="container">
    <div class="apps-photo-banner"><div class="apps-photo-copy"><span class="eyebrow">MORE SERVICES</span><h2>Useful tools, all under one Nileteck roof.</h2><p>Sell through Nileteck Marketplace and stay connected with customers using Email Marketing.</p></div></div>
    <div class="apps-action-links" aria-label="Nileteck tools"><a id="email" href="dashboard?view=email"><span>Email Marketing</span><span aria-hidden="true">↗</span></a><a id="marketplace" href="p/marketplace"><span>Marketplace</span><span aria-hidden="true">↗</span></a></div>
  </div></section>

  <section class="section approach-section" id="approach"><div class="container approach-grid"><div><span class="eyebrow">ONE HOME FOR DIGITAL GROWTH</span><h2>Start with what you need today.</h2><p>Work with us on your website and digital presence, then use the tools that help your business grow.</p><a class="text-link" href="contact">Tell us what you need <span aria-hidden="true">↗</span></a></div><div class="steps"><div class="step"><span>01</span><div><h3>Build your foundation</h3><p>Launch a website that gives your business a strong online home.</p></div></div><div class="step"><span>02</span><div><h3>Keep it moving</h3><p>Maintain and improve your website as your business grows.</p></div></div><div class="step"><span>03</span><div><h3>Grow with the tools</h3><p>Sell in the marketplace and connect with customers by email.</p></div></div></div></div></section>
  <section class="section contact-section" id="contact"><div class="container contact-panel"><div><span class="eyebrow">LET'S CONNECT</span><h2>What are you building next?</h2><p>Tell us what your business needs. We would love to help you take the next step.</p></div><a class="button button-light" href="contact">Email Nileteck <span aria-hidden="true">↗</span></a></div></section>
</main>
<?php require __DIR__ . '/layout/footer.php'; ?>
