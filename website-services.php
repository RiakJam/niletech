<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/app.php';
require_once __DIR__ . '/includes/website-projects.php';
$projects=app_db()->query("SELECT id,slug,title,category,summary,cover_image_path,demo_url,live_url FROM website_projects WHERE status='published' ORDER BY sort_order ASC,id DESC LIMIT 24")->fetchAll();
$pageTitle='Website Design, Development & Maintenance | Nileteck';
$pageDescription='Explore Nileteck website design and development work, try our demos, and get dependable website maintenance for your business.';
$websiteServicesAssets=true;
require __DIR__ . '/layout/header.php';
?>
<main id="main-content" class="web-services-page">
  <section class="web-services-hero" aria-labelledby="web-services-title"><div class="container web-services-hero-grid">
    <div class="web-services-hero-copy"><span class="eyebrow">NILETECK WEB SERVICES</span><h1 id="web-services-title">Websites built to look sharp and work hard.</h1><p>From the first idea to ongoing updates, we design, develop, and maintain websites that make it easier for people to find you and take action.</p><div class="web-services-hero-actions"><a class="button button-primary" href="contact?service=website-development">Start a project <span aria-hidden="true">↗</span></a><a class="web-services-text-link" href="#our-work">Explore our work <span aria-hidden="true">↓</span></a></div><div class="web-services-hero-note"><span aria-hidden="true">✦</span> Built for real people, on every screen.</div></div>
    <div class="web-services-hero-photo" role="img" aria-label="People collaborating on a digital project"><span>Design <b>·</b> Development <b>·</b> Care</span></div>
  </div></section>

  <section class="web-services-offer section" aria-labelledby="what-we-build"><div class="container"><div class="web-services-heading"><span class="eyebrow">WHAT WE DO</span><h2 id="what-we-build">A capable website, from launch onward.</h2><p>Get the right support whether you are starting fresh, improving an existing site, or keeping it running smoothly.</p></div><div class="web-services-offer-grid">
    <article class="web-services-offer-card" id="development"><span class="web-services-offer-number">01 / BUILD</span><div><h3>Website design &amp; development</h3><p>Clear structure, responsive layouts, and useful features shaped around your business and audience.</p></div><a href="contact?service=website-development" aria-label="Discuss website design and development">Discuss a new website <span aria-hidden="true">↗</span></a></article>
    <article class="web-services-offer-card" id="maintenance"><span class="web-services-offer-number">02 / CARE</span><div><h3>Website maintenance</h3><p>Keep your site current with updates, improvements, fixes, and practical support as your needs change.</p></div><a href="contact?service=website-maintenance" aria-label="Discuss website maintenance">Discuss maintenance <span aria-hidden="true">↗</span></a></article>
  </div></div></section>

  <section class="web-services-work section" id="our-work" aria-labelledby="our-work-title"><div class="container"><div class="web-services-work-heading"><div><span class="eyebrow">SELECTED WORK</span><h2 id="our-work-title">Explore what we have built.</h2><p>Open a demo and see the experience for yourself.</p></div><a class="web-services-text-link" href="contact?service=website-development">Have a project in mind? <span aria-hidden="true">↗</span></a></div>
  <?php if ($projects): ?><div class="web-services-project-grid">
    <?php foreach ($projects as $project): $image=website_project_image($project['cover_image_path'],$assetHrefPrefix); $demo=website_project_href($project['demo_url'],$siteHrefPrefix); $live=website_project_href($project['live_url'],$siteHrefPrefix); ?>
    <article class="web-services-project-card"><div class="web-services-project-visual"><?php if ($image): ?><img src="<?= app_h($image) ?>" alt="Screenshot of <?= app_h($project['title']) ?>" loading="lazy"><?php else: ?><div class="web-services-project-placeholder" aria-hidden="true"><span>NT</span></div><?php endif; ?><span class="web-services-project-chip"><?= app_h($project['category']) ?></span></div><div class="web-services-project-content"><div><h3><?= app_h($project['title']) ?></h3><p><?= app_h($project['summary']) ?></p></div><div class="web-services-project-links"><?php if ($demo): ?><a href="<?= app_h($demo) ?>" <?= str_starts_with($demo,'https://') ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>Open demo <span aria-hidden="true">↗</span></a><?php endif; ?><?php if ($live): ?><a href="<?= app_h($live) ?>" <?= str_starts_with($live,'https://') ? 'target="_blank" rel="noopener noreferrer"' : '' ?>>Visit website <span aria-hidden="true">↗</span></a><?php endif; ?></div></div></article>
    <?php endforeach; ?>
  </div><?php else: ?><div class="web-services-empty"><p>New projects are being prepared. Check back soon for more work from Nileteck.</p></div><?php endif; ?></div></section>

  <section class="web-services-cta section" aria-labelledby="web-services-cta-title"><div class="container"><div class="web-services-cta-inner"><div><span class="eyebrow">LET’S BUILD</span><h2 id="web-services-cta-title">Ready for a website that moves your business forward?</h2><p>Tell us what you want to build or improve. We will help you find a clear next step.</p></div><a class="button button-primary" href="contact?service=website-development">Talk about your website <span aria-hidden="true">↗</span></a></div></div></section>
</main>
<?php require __DIR__ . '/layout/footer.php'; ?>
