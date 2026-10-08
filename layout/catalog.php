<?php
declare(strict_types=1);
$catalogServices = ['Website Development','Website Maintenance','Marketplace','Email Marketing','E-voting'];
$catalogSlug = static fn(string $value): string => strtolower(str_replace(' ', '-', $value));
$catalogServiceTargets = [
    'Website Development'=>'website-services#development',
    'Website Maintenance'=>'website-services#maintenance',
    'Marketplace'=>'p/marketplace',
    'Email Marketing'=>'services#email',
    'E-voting'=>app_evoting_url(),
];
