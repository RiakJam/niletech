<?php if (empty($hideSiteChrome)): ?>
<footer class="site-footer"><div class="container footer-main"><div><a class="brand footer-brand" href="<?= app_h($siteHrefPrefix ?? '') ?>"><img class="brand-logo" src="<?= app_h($assetHrefPrefix ?? '') ?>images/Nileteck White Tech Logo.png" alt="Nileteck"></a><p>Website services, Marketplace, and Email Marketing.</p></div><div><h2>Explore</h2><a href="<?= app_h($siteHrefPrefix ?? '') ?>website-services">Website services</a><a href="<?= app_h($siteHrefPrefix ?? '') ?>p/marketplace">Marketplace</a><a href="<?= app_h($siteHrefPrefix ?? '') ?>services#email">Email Marketing</a><a href="<?= app_h(app_evoting_url()) ?>">E-voting</a></div><div><h2>Connect</h2><a href="<?= app_h($siteHrefPrefix ?? '') ?>contact">Contact</a><a href="<?= app_h($siteHrefPrefix ?? '') ?>contact">info@nileteck.com</a><p>Have a question? We are here to help.</p></div></div><div class="container footer-bottom"><span>© <?php echo date('Y'); ?> Nileteck. All rights reserved.</span><span>One home for what comes next.</span></div></footer>
<?php
$contactPhone = trim(app_config('NILETECK_CONTACT_PHONE'));
$contactPhoneReady = (bool)preg_match('/^\+[1-9][0-9]{7,14}$/', $contactPhone);
$contactPhoneDisplay = $contactPhoneReady ? $contactPhone : '+254 000 000 000';
?>
<dialog class="site-contact-dialog" id="site-contact-dialog" aria-labelledby="site-contact-title" aria-describedby="site-contact-intro">
  <div class="site-contact-dialog-inner">
    <button class="site-contact-close" type="button" data-contact-close aria-label="Close contact options">×</button>
    <span class="eyebrow">CONTACT NILETECK</span>
    <h2 id="site-contact-title">Let’s talk about your next step.</h2>
    <p id="site-contact-intro" data-contact-intro>Choose the easiest way to reach our team.</p>
    <div class="site-contact-methods">
      <div class="site-contact-method"><div><span>Email</span><strong>info@nileteck.com</strong></div><button type="button" data-contact-copy="info@nileteck.com" data-contact-label="Email">Copy email</button></div>
      <div class="site-contact-method"><div><span>Phone<?= $contactPhoneReady ? '' : ' · temporary number' ?></span><strong><?= app_h($contactPhoneDisplay) ?></strong></div><button type="button" data-contact-copy="<?= app_h($contactPhoneDisplay) ?>" data-contact-label="Phone number">Copy number</button></div>
    </div>
    <?php if ($contactPhoneReady): ?><a class="site-contact-whatsapp" href="https://wa.me/<?= app_h(substr($contactPhone, 1)) ?>" target="_blank" rel="noopener noreferrer">Continue on WhatsApp <span aria-hidden="true">↗</span></a><?php else: ?><button class="site-contact-whatsapp" type="button" disabled aria-disabled="true" title="WhatsApp will be available when the Nileteck phone number is added">WhatsApp number coming soon</button><?php endif; ?>
    <p class="site-contact-feedback" role="status" aria-live="polite" data-contact-feedback></p>
  </div>
</dialog>
<?php endif; ?>
</body></html>
