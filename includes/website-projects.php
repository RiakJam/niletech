<?php
declare(strict_types=1);
require_once __DIR__ . '/app.php';

function website_project_href(?string $raw,string $sitePrefix): ?string {
    $raw=trim((string)$raw);
    if ($raw==='') return null;
    if ($raw==='@evoting') return app_evoting_url();
    if (preg_match('~^https://[a-z0-9.-]+(?::[0-9]+)?(?:/[^\s]*)?$~i',$raw) && filter_var($raw,FILTER_VALIDATE_URL)) return $raw;
    if (preg_match('~^/[a-z0-9][a-z0-9/_-]*$~i',$raw)) return $raw;
    if (preg_match('~^[a-z0-9][a-z0-9/_-]*$~i',$raw)) return $sitePrefix . $raw;
    return null;
}
function website_project_image(?string $raw,string $assetPrefix): ?string {
    $raw=trim((string)$raw);
    if (!preg_match('~^images/portfolio/[a-z0-9_-]+\.(?:png|jpe?g|webp)$~i',$raw)) return null;
    if (!is_file(__DIR__ . '/../' . $raw)) return null;
    return $assetPrefix . $raw;
}
