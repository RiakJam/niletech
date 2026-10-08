<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/app.php';
$headerUser = empty($hideSiteChrome) ? app_user() : null;
$sitePath = rtrim(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/')), '/.');
$siteHrefPrefix = $siteHrefPrefix ?? ($sitePath === '' ? '/' : $sitePath . '/');
$assetHrefPrefix = $assetHrefPrefix ?? $siteHrefPrefix;
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: DENY');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'");
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?php echo htmlspecialchars($pageDescription ?? 'Nileteck builds and maintains websites and offers Marketplace and Email Marketing.', ENT_QUOTES, 'UTF-8'); ?>">
  <meta name="theme-color" content="#0044b9">
  <title><?php echo htmlspecialchars($pageTitle ?? 'Nileteck | Digital services for your business', ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="icon" href="<?= app_h($assetHrefPrefix) ?>images/Nileteck%20White%20Tech%20Logo.png" type="image/png">
  <link rel="preload" href="<?= app_h($assetHrefPrefix) ?>images/Logo.png?v=<?php echo filemtime(__DIR__ . '/../images/Logo.png'); ?>" as="image" fetchpriority="high">
  <link rel="stylesheet" href="<?= app_h($assetHrefPrefix) ?>dist/output.css?v=<?php echo filemtime(__DIR__ . '/../dist/output.css'); ?>">
  <link rel="stylesheet" href="<?= app_h($assetHrefPrefix) ?>src/site.css?v=<?php echo filemtime(__DIR__ . '/../src/site.css'); ?>">
  <?php if (!empty($hideSiteChrome)): ?><script src="<?= app_h($assetHrefPrefix) ?>src/auth.js?v=<?= filemtime(__DIR__ . '/../src/auth.js') ?>" defer></script><?php endif; ?>
  <?php if (empty($skipSiteScript)): ?><script src="<?= app_h($assetHrefPrefix) ?>src/site.js?v=<?php echo filemtime(__DIR__ . '/../src/site.js'); ?>" defer></script><?php endif; ?>
  <?php if (!empty($marketplaceAssets)): ?><link rel="stylesheet" href="<?= app_h($assetHrefPrefix) ?>src/marketplace.css?v=<?= filemtime(__DIR__ . '/../src/marketplace.css') ?>"><?php endif; ?>
  <?php if (!empty($websiteServicesAssets)): ?><link rel="stylesheet" href="<?= app_h($assetHrefPrefix) ?>src/website-services.css?v=<?= filemtime(__DIR__ . '/../src/website-services.css') ?>"><?php endif; ?>
  <?php if (!empty($forumAssets)): ?><link rel="stylesheet" href="<?= app_h($assetHrefPrefix) ?>src/forum.css?v=<?= filemtime(__DIR__ . '/../src/forum.css') ?>"><script src="<?= app_h($assetHrefPrefix) ?>src/forum.js?v=<?= filemtime(__DIR__ . '/../src/forum.js') ?>" defer></script><?php endif; ?>
</head>
<body<?php if (!empty($hideSiteChrome)): ?> class="auth-layout"<?php endif; ?>>
<a class="skip-link" href="#main-content">Skip to content</a>
<?php if (empty($hideSiteChrome)): ?>
<header class="site-header"><div class="container nav-inner">
  <a class="brand" href="<?= app_h($siteHrefPrefix) ?>" aria-label="Nileteck home"><img class="brand-logo" src="<?= app_h($assetHrefPrefix) ?>images/Logo.png?v=<?php echo filemtime(__DIR__ . '/../images/Logo.png'); ?>" width="76" height="70" alt="Nileteck"></a>
  <button class="menu-toggle" type="button" aria-label="Open menu" aria-controls="site-navigation" aria-expanded="false"><span></span><span></span><span></span></button>
  <nav class="site-nav" id="site-navigation" aria-label="Main navigation">
    <a href="<?= app_h($siteHrefPrefix) ?>">Home</a>
    <a href="<?= app_h($siteHrefPrefix) ?>website-services">Web Services</a>
    <a href="<?= app_h($siteHrefPrefix) ?>p/marketplace">Marketplace</a>
    <a href="<?= app_h(app_evoting_url()) ?>">E-voting</a>
    <a href="<?= app_h($siteHrefPrefix) ?>contact">Contact</a><?php if ($headerUser): ?><a class="nav-contact" href="<?= app_h($siteHrefPrefix) ?>dashboard">My Account <span aria-hidden="true">↗</span></a><?php else: ?><a href="<?= app_h($siteHrefPrefix) ?>signin">Sign in</a><a class="nav-contact" href="<?= app_h($siteHrefPrefix) ?>signup">Sign up <span aria-hidden="true">↗</span></a><?php endif; ?>
  </nav>
</div></header>
<?php endif; ?>
