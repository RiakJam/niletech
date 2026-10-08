<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/app.php';
require_once __DIR__ . '/layout/catalog.php';
$pageTitle = 'Services | Nileteck';
$pageDescription = 'Explore Nileteck website development, maintenance, E-voting, Marketplace, and Email Marketing.';
require __DIR__ . '/layout/header.php';
?>
<main id="main-content">
  <section class="inner-hero"><div class="container"><span class="eyebrow">OUR SERVICES</span><h1>Find the right digital support.</h1><p>From your first website to campaigns and business tools, explore what Nileteck can help you build.</p><a class="button button-primary" href="contact">Talk to our team <span aria-hidden="true">↗</span></a></div></section>
  <section class="section inner-section" id="apps"><div class="container"><div class="section-head"><div><span class="eyebrow">EXPLORE</span><h2>Services for your next step.</h2></div><p>Website development and maintenance are managed services. Marketplace and Email Marketing are self-service tools. E-voting runs on its own platform.</p></div><div class="directory-grid">
  <?php foreach ($catalogServices as $item): ?><article class="directory-service" id="catalog-<?= app_h($catalogSlug($item)) ?>"><h3><?= app_h($item) ?></h3><a class="text-link" href="<?= app_h($catalogServiceTargets[$item]) ?>"><?= in_array($item, ['Website Development','Website Maintenance'], true) ? 'See website work and quotes' : ($item === 'E-voting' ? 'Explore E-voting' : 'Explore service') ?> <span aria-hidden="true">↗</span></a></article><?php endforeach; ?>
  </div></div></section>
</main>
<?php require __DIR__ . '/layout/footer.php'; ?>
