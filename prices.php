<?php

declare(strict_types=1);
$pageTitle = 'Prices | Nileteck';
$pageDescription = 'Explore how Nileteck prices web development and website maintenance projects.';
require __DIR__ . '/layout/header.php';
?>
<main id="main-content">
  <section class="inner-hero">
    <div class="container"><span class="eyebrow">PRICES</span>
      <h1>Clear scope. A price that fits.</h1>
      <p>Every business needs a different mix of design, development, and support. Tell us what you need and we will provide a tailored quote before work begins.</p><a class="button button-primary" href="contact">Request a quote <span aria-hidden="true">↗</span></a>
    </div>
  </section>
  <section class="section inner-section">
    <div class="container">
      <div class="section-head">
        <div><span class="eyebrow">WHAT WE CAN PRICE</span>
          <h2>Choose a starting point.</h2>
        </div>
        <p>Share your goals, timeline, and any existing website or tools. We will recommend the right scope and explain the cost.</p>
      </div>
      <div class="card-grid">
        <article class="app-card">
          <div class="app-icon large purple">&lt;/&gt;</div><span class="card-kicker">BUILD</span>
          <h3>Web development</h3>
          <p>New websites, redesigns, and custom web experiences.</p><a class="card-foot" href="contact?service=website-development">Get a web quote <span aria-hidden="true">↗</span></a>
        </article>
        <article class="app-card">
          <div class="app-icon large coral">✦</div><span class="card-kicker">CARE</span>
          <h3>Website maintenance</h3>
          <p>Ongoing updates, fixes, and improvements for an existing website.</p><a class="card-foot" href="contact?service=website-maintenance">Get a care quote <span aria-hidden="true">↗</span></a>
        </article>
      </div>
    </div>
  </section>
  <section class="section contact-section">
    <div class="container contact-panel">
      <div><span class="eyebrow">LET'S TALK</span>
        <h2>Know what you need?</h2>
        <p>Send us a short brief and we will take it from there.</p>
      </div><a class="button button-light" href="contact">Email Nileteck <span aria-hidden="true">↗</span></a>
    </div>
  </section>
</main>
<?php require __DIR__ . '/layout/footer.php'; ?>