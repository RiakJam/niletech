<?php

declare(strict_types=1);
require_once __DIR__ . '/includes/app.php';
require_once __DIR__ . '/includes/marketplace.php';
require_once __DIR__ . '/includes/seller-plans.php';
require_once __DIR__ . '/includes/marketplace-analytics.php';
$host = strtolower(preg_replace('/:\d+$/', '', (string)($_SERVER['HTTP_HOST'] ?? '')) ?? '');
$reserved = ['www', 'mail', 'email', 'admin', 'api', 'blog', 'forum', 'marketplace', 'events', 'dashboard', 'support', 'help', 'account', 'signin', 'signup'];
$businessHost = preg_match('/^([a-z][a-z0-9-]{2,59})\.nileteck\.com$/', $host, $hostMatch) && !in_array($hostMatch[1], $reserved, true);
$slug = $businessHost ? $hostMatch[1] : (string)($_GET['page'] ?? '');
$postSlug = (string)($_GET['post'] ?? '');
if (!preg_match('/^[a-z0-9-]{3,60}$/', $slug) || ($postSlug !== '' && !preg_match('/^[a-z0-9-]{3,80}$/', $postSlug))) {
    http_response_code(404);
    exit('Page not found.');
}
if (!$businessHost && $slug === 'store-2') {
    header('Location: ' . app_page_url('marketplace', 'nileteck-demo', $postSlug ?: null), true, 301);
    exit;
}
$db = app_db();
$query = $db->prepare("SELECT c.*,u.name AS creator,u.email AS creator_email,d.slug AS domain_slug,CASE WHEN m.current_period_end>UTC_TIMESTAMP() THEN m.tier ELSE 'free' END AS seller_tier FROM creator_pages c JOIN users u ON u.id=c.user_id LEFT JOIN business_domains d ON d.page_id=c.id LEFT JOIN seller_memberships m ON m.user_id=c.user_id WHERE c.section='marketplace' AND " . ($businessHost ? 'd.slug=?' : 'c.slug=?'));
$query->execute([$slug]);
$page = $query->fetch() ?: null;
if (!$page) http_response_code(404);
$post = null;
$posts = [];
$relatedProducts = [];
$productImages = [];
if ($page) {
    if ($postSlug !== '') {
        $query = $db->prepare("SELECT * FROM page_posts WHERE page_id=? AND slug=? AND status='published'");
        $query->execute([$page['id'], $postSlug]);
        $post = $query->fetch() ?: null;
        if (!$post) http_response_code(404);
        if ($post) {
            $query = $db->prepare('SELECT image_path FROM product_images WHERE product_id=? ORDER BY id');
            $query->execute([$post['id']]);
            $productImages = $query->fetchAll(PDO::FETCH_COLUMN);
            $query = $db->prepare("SELECT * FROM page_posts WHERE page_id=? AND id<>? AND status='published' ORDER BY CASE WHEN category=? THEN 0 ELSE 1 END, CASE WHEN country_code=? AND region=? THEN 0 WHEN country_code=? THEN 1 ELSE 2 END, created_at DESC, id DESC LIMIT 6");
            $query->execute([$page['id'], $post['id'], $post['category'], $post['country_code'], $post['region'], $post['country_code']]);
            $relatedProducts = $query->fetchAll();
        }
    } else {
        $query = $db->prepare("SELECT * FROM page_posts WHERE page_id=? AND status='published' ORDER BY created_at DESC,id DESC");
        $query->execute([$page['id']]);
        $posts = $query->fetchAll();
    }
}
if ($page && ($postSlug === '' || $post) && !marketplace_analytics_automated_request() && (int)(app_user()['id'] ?? 0) !== (int)$page['user_id']) marketplace_record_visit($db, (int)$page['id'], $post ? (int)$post['id'] : null);
if ($page) app_csrf();
$contactSettings = ['whatsapp_phone' => null, 'call_phone' => null];
if ($page) {
    $contactQuery = $db->prepare('SELECT whatsapp_phone,call_phone FROM store_contact_settings WHERE page_id=?');
    $contactQuery->execute([$page['id']]);
    $contactSettings = $contactQuery->fetch() ?: $contactSettings;
}
$isDemoListing = $post && marketplace_demo_cell($post['image_path'] ?? null) !== null;
$whatsappNumber = preg_replace('/\D+/', '', (string)$contactSettings['whatsapp_phone']);
$callNumber = (string)$contactSettings['call_phone'];
$orderText = $isDemoListing ? 'Hello, I am interested in ' . $post['title'] . '. Is it available?' : 'Hello, I would like to order ' . ($post['title'] ?? 'from ' . ($page['title'] ?? 'your store')) . '.';
$whatsappUrl = $whatsappNumber ? 'https://wa.me/' . $whatsappNumber . '?text=' . rawurlencode($orderText) : null;
$localPath = rtrim(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/')), '/.');
$assetHrefPrefix = ($localPath === '' ? '/' : $localPath . '/');
$mainSite = rtrim(app_config('NILETECK_MAIN_URL', in_array($host, ['localhost', '127.0.0.1'], true) ? ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $localPath) : 'https://nileteck.com'), '/');
$siteHrefPrefix = $mainSite . '/';
$publicPageUrl = $businessHost ? 'https://' . $host . '/' : $mainSite . '/p/marketplace/' . rawurlencode($slug);
$publicPostUrl = static fn(string $itemSlug): string => $businessHost ? 'https://' . $host . '/' . rawurlencode($itemSlug) : $mainSite . '/p/marketplace/' . rawurlencode($slug) . '/' . rawurlencode($itemSlug);
$pageTitle = ($post['title'] ?? $page['title'] ?? 'Business not found') . ' | Nileteck Marketplace';
$pageDescription = mb_strimwidth(preg_replace('/\s+/', ' ', (string)($post['body'] ?? $page['description'] ?? '')) ?? '', 0, 155, '…');
if (!$page || ($postSlug !== '' && !$post)) header('X-Robots-Tag: noindex');
require __DIR__ . '/layout/marketplace-document-start.php';
$categories = marketplace_categories();
?>
<main id="main-content" class="marketplace-page marketplace-storefront">
    <div class="container marketplace-wrap">
        <div class="marketplace-breadcrumb"><a href="<?= app_h($mainSite) ?>/p/marketplace">Marketplace</a><span aria-hidden="true">›</span><?php if ($post): ?><a href="<?= app_h($publicPageUrl) ?>"><?= app_h($page['title']) ?></a><span aria-hidden="true">›</span><strong><?= app_h($post['title']) ?></strong><?php else: ?><strong><?= app_h($page['title'] ?? 'Business not found') ?></strong><?php endif; ?></div>
        <?php if (($_GET['message'] ?? '') === 'sent' && $page): ?><p class="marketplace-message-success" role="status">Your message was sent to the store.</p><?php endif; ?>
        <?php if (($_GET['report'] ?? '') === 'sent' && $post): ?><p class="marketplace-message-success" role="status">Thank you. Your report has been submitted for review.</p><?php endif; ?>
        <?php if (!$page || ($postSlug !== '' && !$post)): ?><section class="marketplace-empty">
                <h1>Page not found</h1>
                <p>This address is unavailable.</p><a href="<?= app_h($mainSite) ?>/p/marketplace">Browse Marketplace →</a>
            </section>
        <?php elseif ($post): ?>
            <div class="storefront-listing-heading"><span class="eyebrow"><?= app_h($categories[$post['category'] ?? 'other'][0] ?? 'Other') ?></span><h1><?= app_h($post['title']) ?></h1><?= seller_plan_badge((string)$page['seller_tier']) ?><?php $productLocation = marketplace_location_label($post); ?><p class="storefront-listing-location">⌖ <?= app_h($productLocation ?: 'Location not provided') ?></p></div>
            <div class="storefront-product-detail">
                <div class="storefront-detail-main">
                    <?php $galleryPaths = array_merge(marketplace_image_url($post['image_path'] ?? null, $assetHrefPrefix) ? [$post['image_path']] : [], $productImages);
                    $galleryUrls = array_values(array_filter(array_map(static fn(string $path): ?string => marketplace_image_url($path, $assetHrefPrefix), $galleryPaths))); ?>
                    <div class="storefront-photo-gallery" data-gallery-images="<?= app_h(json_encode($galleryUrls, JSON_UNESCAPED_SLASHES)) ?>">
                    <div class="storefront-detail-image"><?php $image = marketplace_image_url($post['image_path'] ?? null, $assetHrefPrefix);
                                                            $demoCell = marketplace_demo_cell($post['image_path'] ?? null);
                                                            if (!$image && $productImages) $image = marketplace_image_url($productImages[0], $assetHrefPrefix);
                                                            if ($image): ?><img id="product-gallery-main" src="<?= app_h($image) ?>" alt="<?= app_h($post['title']) ?>" data-product-title="<?= app_h($post['title']) ?>"><?php elseif ($demoCell): ?><span class="demo-product-photo demo-cell-<?= $demoCell ?>" role="img" aria-label="<?= app_h($post['title']) ?>"></span><?php else: ?><span aria-hidden="true">◇</span><?php endif; ?>
                        <?php if ($image || $demoCell): ?><span class="storefront-gallery-seller"><?= app_h($page['title']) ?></span><?php endif; ?>
                        <?php if ($image || $demoCell): ?><span class="marketplace-photo-watermark storefront-photo-watermark" aria-hidden="true">NILETECK MARKETPLACE</span><?php endif; ?>
                        <?php if (count($galleryUrls) > 1): ?><button type="button" class="storefront-gallery-arrow is-previous" data-gallery-step="-1" aria-label="Previous product photo">‹</button><button type="button" class="storefront-gallery-arrow is-next" data-gallery-step="1" aria-label="Next product photo">›</button><?php endif; ?>
                        <?php if ($image || $demoCell): ?><span class="storefront-gallery-count"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M8 4.5 6.5 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2.5L16 4.5H8ZM12 9a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9Zm0 2a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5Z"/></svg><span data-gallery-count aria-live="polite">1 / <?= max(1, count($galleryUrls)) ?></span></span><?php endif; ?>
                    </div>
                    <?php if (count($galleryUrls) > 1): ?><div class="storefront-gallery-thumbs" aria-label="Product photos"><?php foreach (array_slice($galleryUrls, 0, 5) as $index => $thumbUrl): ?><button type="button" class="storefront-gallery-thumb<?= $index === 0 ? ' is-active' : '' ?>" data-gallery-index="<?= $index ?>" aria-label="View photo <?= $index + 1 ?> of <?= count($galleryUrls) ?>" aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>"><img src="<?= app_h($thumbUrl) ?>" alt="" loading="lazy"><span class="marketplace-photo-watermark" aria-hidden="true">NILETECK MARKETPLACE</span><?php if ($index === 4 && count($galleryUrls) > 5): ?><span class="storefront-gallery-more">+<?= count($galleryUrls) - 5 ?><small>images</small></span><?php endif; ?></button><?php endforeach; ?></div><?php endif; ?>
                    </div>
                    <section class="storefront-detail-section" aria-labelledby="product-features-title"><h2 id="product-features-title">Features</h2><dl class="storefront-feature-list"><div><dt>Category</dt><dd><?= app_h($categories[$post['category'] ?? 'other'][0] ?? 'Other') ?></dd></div><div><dt>Location</dt><dd><?= app_h($productLocation ?: 'Location not provided') ?></dd></div></dl></section>
                    <section class="storefront-detail-section" aria-labelledby="product-details-title"><h2 id="product-details-title">Product details</h2><?php if ($demoCell): ?><p class="storefront-demo-note">Demo listing · This product is for illustration only.</p><?php endif; ?><div class="storefront-product-description"><?= nl2br(app_h($post['body'])) ?></div></section>
                </div>
                <aside class="storefront-detail-sidebar" aria-label="Seller and price">
                    <section class="storefront-seller-card"><div class="storefront-seller-identity"><span class="storefront-seller-avatar" aria-hidden="true"><?= app_h(mb_strtoupper(mb_substr(trim((string)$page['creator']), 0, 1))) ?></span><div><span class="storefront-seller-label">Seller</span><strong><?= app_h($page['creator']) ?></strong><?= seller_plan_badge((string)$page['seller_tier']) ?><a href="<?= app_h($publicPageUrl) ?>"><?= app_h($page['title']) ?> →</a></div></div><div class="storefront-sidebar-price"><span>Price</span><strong><?= app_h(marketplace_price($post['price'], $post['currency_code'] ?? 'KES')) ?></strong></div><?php if ($callNumber || $whatsappNumber): ?><button class="marketplace-contact-button marketplace-contact-primary storefront-show-contact" type="button" data-show-contact aria-expanded="false" aria-controls="storefront-contact-details">Show contact</button><div class="storefront-contact-details" id="storefront-contact-details" hidden><?php if ($callNumber): ?><div><span>Call</span><a href="tel:<?= app_h($callNumber) ?>"><?= app_h($callNumber) ?></a><button type="button" data-copy-contact="<?= app_h($callNumber) ?>">Copy</button></div><?php endif; ?><?php if ($whatsappNumber): ?><div><span>WhatsApp</span><a href="https://wa.me/<?= app_h($whatsappNumber) ?>" target="_blank" rel="noopener"><?= app_h((string)$contactSettings['whatsapp_phone']) ?></a><button type="button" data-copy-contact="<?= app_h((string)$contactSettings['whatsapp_phone']) ?>">Copy</button></div><?php endif; ?></div><?php endif; ?><?php require __DIR__ . '/includes/store-order-actions.php'; ?></section>
                    <section class="storefront-safety" aria-labelledby="safety-tips-title"><h2 id="safety-tips-title">Safety tips</h2><ul><li>Avoid sending any prepayments.</li><li>Meet with the seller at a safe public place.</li><li>Inspect what you're going to buy to make sure it's what you need.</li><li>Check all the documents and only pay if you're satisfied.</li></ul><button type="button" class="marketplace-contact-button" data-open-report>Report this product</button></section>
                </aside>
            </div>
            <?php if ($relatedProducts): ?><section class="marketplace-section storefront-related-products" aria-labelledby="related-products-title">
                <div class="marketplace-section-head"><div><span class="marketplace-section-icon" aria-hidden="true">◇</span><h2 id="related-products-title">Related products</h2></div><a href="<?= app_h($publicPageUrl) ?>">View all products →</a></div>
                <div class="marketplace-product-grid"><?php foreach ($relatedProducts as $product):
                    $product['business_slug'] = $page['slug'];
                    $product['business_title'] = $page['title'];
                    $product['domain_slug'] = $page['domain_slug'];
                    require __DIR__ . '/includes/marketplace-card.php';
                endforeach; ?></div>
            </section><?php endif; ?>
        <?php else: ?><section class="storefront-hero">
                <div><span class="eyebrow">NILETECK BUSINESS</span>
                    <h1><?= app_h($page['title']) ?></h1><?= seller_plan_badge((string)$page['seller_tier']) ?>
                    <p><?= app_h($page['description']) ?></p><span>By <?= app_h($page['creator']) ?></span>
                </div>
                <div class="storefront-hero-actions"><button class="button button-primary" type="button" data-open-follow>Follow</button><?php require __DIR__ . '/includes/store-order-actions.php'; ?></div>
            </section>
            <section class="marketplace-section">
                <div class="marketplace-section-head">
                    <div><span class="marketplace-section-icon" aria-hidden="true">◇</span>
                        <h2>Products</h2>
                    </div><span><?= count($posts) ?> <?= count($posts) === 1 ? 'listing' : 'listings' ?></span>
                </div><?php if ($posts): ?><div class="marketplace-product-grid"><?php foreach ($posts as $product): $product['business_slug'] = $page['slug'];
                                                                                        $product['business_title'] = $page['title'];
                                                                                        $product['domain_slug'] = $page['domain_slug'];
                                                                                        require __DIR__ . '/includes/marketplace-card.php';
                                                                                    endforeach; ?></div><?php else: ?><div class="marketplace-empty">
                        <h3>No products yet</h3>
                        <p>Check back soon for new listings.</p>
                    </div><?php endif; ?>
            </section><?php endif; ?>
    </div>
</main>
<?php if ($post): ?><dialog class="marketplace-message-modal" id="report-modal" aria-labelledby="report-modal-title"><button type="button" class="marketplace-message-close" data-close-report aria-label="Close">×</button><h2 id="report-modal-title">Report this product</h2><p>Tell us why this listing may not be real. Your report will be reviewed.</p><form method="post" action="<?= app_h($assetHrefPrefix) ?>product-report"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="product_id" value="<?= (int)$post['id'] ?>"><label class="marketplace-honeypot">Website<input name="website" tabindex="-1" autocomplete="off"></label><label>Why are you reporting this product?<textarea name="reason" rows="4" minlength="10" maxlength="2000" required></textarea></label><button class="marketplace-contact-button marketplace-contact-primary" type="submit">Submit report</button></form></dialog><?php endif; ?>
<?php if ($page && !$post): ?><dialog class="marketplace-message-modal" id="follow-modal" aria-labelledby="follow-modal-title"><button type="button" class="marketplace-message-close" data-close-follow aria-label="Close">×</button>
    <h2 id="follow-modal-title">Follow <?= app_h($page['title']) ?></h2>
    <p>Get email updates from this store.</p>
    <form method="post" action="<?= app_h($assetHrefPrefix) ?>store-follow" data-follow-form><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="page_id" value="<?= (int)$page['id'] ?>"><label>Email address<input type="email" name="email" autocomplete="email" required></label><label class="follow-consent"><input type="checkbox" name="consent" value="1" required><span>I agree to receive marketing emails from <?= app_h($page['title']) ?>.</span></label><p class="follow-feedback" data-follow-feedback role="status" hidden></p><button class="marketplace-contact-button marketplace-contact-primary" type="submit">Follow</button></form>
</dialog><?php endif; ?>
<?php if ($page && ($postSlug === '' || $post)): ?><dialog class="marketplace-message-modal" id="order-message-modal" aria-labelledby="order-message-title"><button type="button" class="marketplace-message-close" data-close-order-message aria-label="Close">×</button>
        <h2 id="order-message-title">Message <?= app_h($page['title']) ?></h2>
        <p>Send an order request or ask about <?= app_h($post['title'] ?? 'this store') ?>.</p>
        <form method="post" action="<?= app_h($assetHrefPrefix) ?>store-message"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="page_id" value="<?= (int)$page['id'] ?>"><?php if ($post): ?><input type="hidden" name="product_id" value="<?= (int)$post['id'] ?>"><?php endif; ?><label class="marketplace-honeypot">Website<input name="website" tabindex="-1" autocomplete="off"></label><label>Your name<input name="sender_name" maxlength="120" required></label><label>Email address<input type="email" name="sender_email" maxlength="254" required></label><label>Message<textarea name="body" rows="5" minlength="10" maxlength="5000" required><?= app_h($post ? $orderText . ' ' : '') ?></textarea></label><button class="marketplace-contact-button marketplace-contact-primary" type="submit">Send message</button></form>
    </dialog><?php endif; ?>
</body>

</html>
