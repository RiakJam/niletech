<?php
declare(strict_types=1);

// Existing Contact URLs now open the shared modal on the home page.
$service = (string)($_GET['service'] ?? '');
$query = in_array($service, ['website-development', 'website-maintenance'], true)
    ? '?contact=' . rawurlencode($service)
    : '';
header('Location: ./' . $query . '#site-contact-dialog', true, 302);
exit;
