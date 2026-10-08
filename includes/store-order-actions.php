<div class="marketplace-order-actions" aria-label="Order options">
<?php if ($whatsappUrl): ?><a class="marketplace-contact-button marketplace-contact-whatsapp" href="<?= app_h($whatsappUrl) ?>" target="_blank" rel="noopener">WhatsApp</a><?php else: ?><span class="marketplace-contact-button is-disabled" title="WhatsApp number not added by this store">WhatsApp</span><?php endif; ?>
<?php if ($callNumber): ?><a class="marketplace-contact-button" href="tel:<?= app_h($callNumber) ?>">Call</a><?php else: ?><span class="marketplace-contact-button is-disabled" title="Call number not added by this store">Call</span><?php endif; ?>
<button class="marketplace-contact-button marketplace-contact-primary" type="button" data-open-order-message>Message</button>
</div>
