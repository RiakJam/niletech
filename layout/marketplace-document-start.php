<?php
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; connect-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'");
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="description" content="<?= app_h($pageDescription) ?>"><title><?= app_h($pageTitle) ?></title><link rel="icon" href="<?= app_h($assetHrefPrefix) ?>images/Nileteck%20White%20Tech%20Logo.png" type="image/png"><link rel="stylesheet" href="<?= app_h($assetHrefPrefix) ?>src/site.css?v=<?= filemtime(__DIR__ . '/../src/site.css') ?>"><link rel="stylesheet" href="<?= app_h($assetHrefPrefix) ?>src/marketplace.css?v=<?= filemtime(__DIR__ . '/../src/marketplace.css') ?>"><script src="<?= app_h($assetHrefPrefix) ?>src/marketplace.js?v=<?= filemtime(__DIR__ . '/../src/marketplace.js') ?>" defer></script></head><body class="marketplace-standalone" data-analytics-endpoint="<?= app_h($assetHrefPrefix) ?>analytics-track"><a class="skip-link" href="#main-content">Skip to content</a>
