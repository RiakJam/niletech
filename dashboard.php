<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/app.php';
require_once __DIR__ . '/includes/marketplace.php';
require_once __DIR__ . '/includes/dashboard-analytics.php';
require_once __DIR__ . '/includes/seller-plans.php';
require_once __DIR__ . '/includes/manager-config.php';
$user = app_require_user();
$defaultCurrency = marketplace_african_countries()[$user['country_code'] ?? ''][1] ?? 'KES';
$db = app_db();
$sellerMembership = seller_plan_membership($db,(int)$user['id']);
$sellerTier = seller_plan_tier($sellerMembership);
$isPaidSeller = $sellerTier !== 'free';
// An account receives a storefront automatically; no address setup is required.
$q = $db->prepare("SELECT p.*,d.slug AS domain_slug FROM creator_pages p LEFT JOIN business_domains d ON d.page_id=p.id WHERE p.user_id=? AND p.section='marketplace' ORDER BY p.id ASC");
$q->execute([$user['id']]); $businesses = $q->fetchAll();
if (!$businesses) {
    $storeTitle = mb_substr($user['name'] . "'s Store", 0, 120);
    $storeDescription = 'Browse products from ' . $user['name'] . '.';
    $storeSlug = 'store-' . (int)$user['id'];
    for ($attempt = 0; $attempt < 3; $attempt++) {
        try {
            $db->prepare("INSERT INTO creator_pages (user_id,section,slug,title,description) VALUES (?,'marketplace',?,?,?)")->execute([$user['id'],$storeSlug,$storeTitle,$storeDescription]);
            break;
        } catch (PDOException $error) {
            if ($error->getCode() !== '23000') throw $error;
            $storeSlug = 'store-' . (int)$user['id'] . '-' . bin2hex(random_bytes(3));
        }
    }
    $q->execute([$user['id']]); $businesses = $q->fetchAll();
    if (!$businesses) throw new RuntimeException('Could not create your storefront.');
}
$selectedId = filter_var($_GET['business'] ?? null, FILTER_VALIDATE_INT) ?: (int)$businesses[0]['id'];
$business = null; foreach ($businesses as $item) if ((int)$item['id'] === $selectedId) $business = $item;
if (!$business) { $business = $businesses[0]; $selectedId = (int)$business['id']; }
$view = in_array($_GET['view'] ?? '', ['overview','products','marketplace','email','analytics','messages','profile','settings'], true) ? (string)$_GET['view'] : 'overview';
if ($view === 'marketplace') $view = 'products';
if ($view === 'profile') $view = 'settings';
$requestedProductsPage = filter_var($_GET['page'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) ?: 1;
$setupRequired = !empty($user['google_sub']) && (empty($user['password_is_set']) || !marketplace_location_valid((string)($user['country_code'] ?? ''), (string)($user['region'] ?? ''), (string)($user['locality'] ?? '')));
if ($setupRequired) $view = 'settings';
if ($setupRequired && $_SERVER['REQUEST_METHOD'] !== 'POST' && ($_GET['view'] ?? '') !== 'settings') { header('Location: dashboard?view=settings'); exit; }
$errors = [];
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    app_verify_csrf();
    $action = (string)($_POST['action'] ?? '');
    if ($setupRequired && !in_array($action, ['settings_location','settings_password'], true)) { header('Location: dashboard?view=settings'); exit; }
    if ($action === 'settings_location') {
        $country = (string)($_POST['country_code'] ?? '');
        $region = trim((string)($_POST['region'] ?? ''));
        $locality = trim((string)($_POST['locality'] ?? ''));
        if (!marketplace_location_valid($country, $region, $locality)) $errors[] = 'Choose an African country and enter your city or region and local area.';
        else {
            $db->prepare('UPDATE users SET country_code=?,region=?,locality=? WHERE id=?')->execute([$country,$region,$locality,$user['id']]);
            setcookie('nileteck_market_country', '', ['expires' => time() - 3600, 'path' => '/', 'samesite' => 'Lax']);
            header('Location: dashboard?view=settings&saved=location'); exit;
        }
    } elseif ($action === 'settings_password') {
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $confirmation = (string)($_POST['password_confirm'] ?? '');
        if (!empty($user['password_is_set'])) {
            $q = $db->prepare('SELECT password FROM users WHERE id=?');
            $q->execute([$user['id']]);
            if (!password_verify($currentPassword, (string)$q->fetchColumn())) $errors[] = 'Enter your current password correctly.';
        }
        if (strlen($password) < 10 || strlen($password) > 4096) $errors[] = 'Use a password of at least 10 characters.';
        if ($password !== $confirmation) $errors[] = 'The new passwords do not match.';
        if (!$errors) {
            $db->prepare('UPDATE users SET password=?,password_is_set=1 WHERE id=?')->execute([password_hash($password, PASSWORD_DEFAULT),$user['id']]);
            session_regenerate_id(true);
            header('Location: dashboard?view=settings&saved=password'); exit;
        }
    } elseif ($action === 'store') {
        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $whatsapp = trim((string)($_POST['whatsapp_phone'] ?? ''));
        $call = trim((string)($_POST['call_phone'] ?? ''));
        if ($title === '' || mb_strlen($title) > 120 || $description === '' || mb_strlen($description) > 500) $errors[] = 'Add a store name and description.';
        if (($whatsapp !== '' && !preg_match('/^\+?[0-9][0-9 ()-]{6,22}$/', $whatsapp)) || ($call !== '' && !preg_match('/^\+?[0-9][0-9 ()-]{6,22}$/', $call))) $errors[] = 'Enter a valid WhatsApp or call number, including the country code.';
        if (!$errors) {
            $db->beginTransaction();
            $db->prepare('UPDATE creator_pages SET title=?,description=? WHERE id=? AND user_id=?')->execute([$title,$description,$selectedId,$user['id']]);
            $db->prepare('INSERT INTO store_contact_settings (page_id,whatsapp_phone,call_phone) VALUES (?,?,?) ON DUPLICATE KEY UPDATE whatsapp_phone=VALUES(whatsapp_phone),call_phone=VALUES(call_phone)')->execute([$selectedId,$whatsapp ?: null,$call ?: null]);
            $db->commit();
            header('Location: dashboard?view=' . $view . '&business=' . $selectedId . '&saved=store'); exit;
        }
    } else {
        $businessId = filter_var($_POST['business_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        $q = $db->prepare("SELECT id FROM creator_pages WHERE id=? AND user_id=? AND section='marketplace'");
        $q->execute([$businessId,$user['id']]);
        if (!$q->fetchColumn()) { http_response_code(403); exit('Business not found.'); }
        if ($action === 'product' || $action === 'edit_product') {
            $editing = $action === 'edit_product';
            $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
            $existing = null;
            if ($editing) {
                $q=$db->prepare('SELECT * FROM page_posts WHERE id=? AND page_id=?'); $q->execute([$productId,$businessId]); $existing=$q->fetch() ?: null;
                if (!$existing) { http_response_code(404); exit('Product not found.'); }
            }
            $title = trim((string)($_POST['title'] ?? ''));
            $body = trim((string)($_POST['body'] ?? ''));
            $slug = $editing ? $existing['slug'] : app_slug($title);
            $price = trim((string)($_POST['price'] ?? ''));
            $country = (string)($user['country_code'] ?? '');
            $currency = marketplace_african_countries()[$country][1] ?? '';
            $region = trim((string)($_POST['region'] ?? ''));
            $locality = trim((string)($_POST['locality'] ?? ''));
            $status = (string)($_POST['status'] ?? 'draft');
            $category = (string)($_POST['category'] ?? 'other');
            if (!isset(marketplace_categories()[$category])) $errors[] = 'Choose a valid category.';
            if ($currency === '') $errors[] = 'Set your account country in Settings before posting a listing.';
            if ($currency !== '' && !marketplace_location_valid($country, $region, $locality)) $errors[] = 'Enter the listing city or region and local area.';
            $image = $_FILES['image'] ?? null;
            $imagePath = $editing ? $existing['image_path'] : null;
            $uploadedPath = null;
            $extraImages = [];
            $extraUpload = $_FILES['extra_images'] ?? null;
            if ($extraUpload && is_array($extraUpload['name'] ?? null)) {
                foreach ($extraUpload['name'] as $index => $unused) {
                    $error = $extraUpload['error'][$index] ?? UPLOAD_ERR_NO_FILE;
                    if ($error === UPLOAD_ERR_NO_FILE) continue;
                    $tmp = $extraUpload['tmp_name'][$index] ?? '';
                    $size = (int)($extraUpload['size'][$index] ?? 0);
                    if ($error !== UPLOAD_ERR_OK || $size < 1 || $size > 5 * 1024 * 1024 || !is_uploaded_file($tmp)) { $errors[] = 'Each additional photo must be under 5 MB.'; break; }
                    $info = @getimagesize($tmp);
                    $mime = $info['mime'] ?? '';
                    if (!isset(['image/jpeg'=>1,'image/png'=>1,'image/webp'=>1][$mime]) || ($info[0] ?? 0) > 6000 || ($info[1] ?? 0) > 6000) { $errors[] = 'Additional photos must be valid JPG, PNG, or WebP images.'; break; }
                    $extraImages[] = ['tmp'=>$tmp, 'mime'=>$mime];
                }
            }
            $existingExtraImages = [];
            $removeExtraIds = array_values(array_filter(array_map('intval', (array)($_POST['remove_extra_images'] ?? [])), static fn($id) => $id > 0));
            if ($editing) {
                $q = $db->prepare('SELECT id,image_path FROM product_images WHERE product_id=? ORDER BY id');
                $q->execute([$productId]);
                $existingExtraImages = $q->fetchAll();
                $removeExtraIds = array_intersect($removeExtraIds, array_column($existingExtraImages, 'id'));
            }
            if (count($extraImages) + count($existingExtraImages) - count($removeExtraIds) > 7) $errors[] = 'A product can have up to 8 photos total.';
            if ($image && ($image['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                if ($image['error'] !== UPLOAD_ERR_OK || $image['size'] > 5 * 1024 * 1024 || $image['size'] < 1 || !is_uploaded_file($image['tmp_name'])) $errors[] = 'Upload a JPG, PNG, or WebP image under 5 MB.';
                else {
                    $info = @getimagesize($image['tmp_name']);
                    $mime = $info['mime'] ?? '';
                    if (!isset(['image/jpeg'=>1,'image/png'=>1,'image/webp'=>1][$mime]) || ($info[0] ?? 0) > 6000 || ($info[1] ?? 0) > 6000) $errors[] = 'Upload a valid JPG, PNG, or WebP image under 6000 pixels wide and high.';
                }
            }
            $priceDecimals = str_contains($price, '.') ? strlen(substr(strrchr($price, '.'), 1)) : 0;
            if (mb_strlen($title) < 3 || mb_strlen($title) > 160 || mb_strlen($body) < 10 || mb_strlen($body) > 30000 || strlen($slug) > 80 || $slug === 'subscribe' || !in_array($status, ['draft','published'], true) || ($price !== '' && (!preg_match('/^\d{1,12}(?:\.\d{1,3})?$/', $price) || $priceDecimals > marketplace_currency_decimals($currency)))) $errors[] = 'Check the product title, details, price, and status.';
            if (!$isPaidSeller && !$editing) {
                $limitQuery=$db->prepare("SELECT COUNT(*) FROM page_posts p JOIN creator_pages c ON c.id=p.page_id WHERE c.user_id=? AND c.section='marketplace' AND p.status<>'rejected'");
                $limitQuery->execute([$user['id']]);
                if ((int)$limitQuery->fetchColumn() >= 5) $errors[] = 'The free plan allows 5 listings. Choose a paid plan to post without a limit.';
            }
            if (!$isPaidSeller && $status === 'published') $status = 'pending';
            if (!$errors) {
                $uploadedPaths = [];
                try {
                    if ($image && $image['error'] === UPLOAD_ERR_OK) {
                        $extension = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime];
                        $imagePath = 'uploads/products/' . bin2hex(random_bytes(16)) . '.' . $extension;
                        if (!move_uploaded_file($image['tmp_name'], __DIR__ . '/' . $imagePath)) throw new RuntimeException('Could not save the image.');
                        $uploadedPath = $imagePath;
                        $uploadedPaths[] = $imagePath;
                        chmod(__DIR__ . '/' . $imagePath, 0644);
                    }
                    if (!$imagePath && $extraImages) {
                        $firstImage = array_shift($extraImages);
                        $extension = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$firstImage['mime']];
                        $imagePath = 'uploads/products/' . bin2hex(random_bytes(16)) . '.' . $extension;
                        if (!move_uploaded_file($firstImage['tmp'], __DIR__ . '/' . $imagePath)) throw new RuntimeException('Could not save the main photo.');
                        chmod(__DIR__ . '/' . $imagePath, 0644);
                        $uploadedPaths[] = $imagePath;
                    }
                    foreach ($extraImages as $extraImage) {
                        $extension = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$extraImage['mime']];
                        $path = 'uploads/products/' . bin2hex(random_bytes(16)) . '.' . $extension;
                        if (!move_uploaded_file($extraImage['tmp'], __DIR__ . '/' . $path)) throw new RuntimeException('Could not save an additional photo.');
                        chmod(__DIR__ . '/' . $path, 0644);
                        $uploadedPaths[] = $path;
                    }
                    $db->beginTransaction();
                    if (!$isPaidSeller) {
                        $lock=$db->prepare('SELECT id FROM users WHERE id=? FOR UPDATE'); $lock->execute([$user['id']]);
                        $freshMembership=seller_plan_membership($db,(int)$user['id']);
                        if (seller_plan_tier($freshMembership) === 'free') {
                            if (!$editing) {
                                $limitQuery=$db->prepare("SELECT COUNT(*) FROM page_posts p JOIN creator_pages c ON c.id=p.page_id WHERE c.user_id=? AND c.section='marketplace' AND p.status<>'rejected'");
                                $limitQuery->execute([$user['id']]);
                                if ((int)$limitQuery->fetchColumn() >= 5) throw new RuntimeException('free_limit');
                            }
                            if ($status === 'published') $status = 'pending';
                        }
                    }
                    if ($editing) {
                        if ($imagePath === $existing['image_path'] && !empty($_POST['remove_image'])) $imagePath = null;
                        $db->prepare('UPDATE page_posts SET title=?,body=?,price=?,currency_code=?,country_code=?,region=?,locality=?,category=?,image_path=?,status=? WHERE id=? AND page_id=?')->execute([$title,$body,$price === '' ? null : $price,$currency,$country,$region,$locality,$category,$imagePath,$status,$productId,$businessId]);
                    } else {
                        $db->prepare('INSERT INTO page_posts (page_id,slug,title,body,price,currency_code,country_code,region,locality,category,image_path,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')->execute([$businessId,$slug,$title,$body,$price === '' ? null : $price,$currency,$country,$region,$locality,$category,$imagePath,$status]);
                        $productId = (int)$db->lastInsertId();
                    }
                    foreach ($uploadedPaths as $path) {
                        if ($path !== $imagePath) $db->prepare('INSERT INTO product_images (product_id,image_path) VALUES (?,?)')->execute([$productId,$path]);
                    }
                    if ($editing && $removeExtraIds) {
                        $placeholders = implode(',', array_fill(0, count($removeExtraIds), '?'));
                        $db->prepare("DELETE FROM product_images WHERE product_id=? AND id IN ($placeholders)")->execute(array_merge([$productId], $removeExtraIds));
                    }
                    $db->commit();
                    if ($editing && $existing['image_path'] !== $imagePath && marketplace_image_url($existing['image_path'] ?? null, '') !== null) @unlink(__DIR__ . '/' . $existing['image_path']);
                    foreach ($existingExtraImages as $oldExtra) if (in_array((int)$oldExtra['id'], $removeExtraIds, true) && marketplace_image_url($oldExtra['image_path'], '') !== null) @unlink(__DIR__ . '/' . $oldExtra['image_path']);
                    header('Location: dashboard?view=products&business=' . $businessId . ($editing ? '&page=' . $requestedProductsPage : '') . '&saved=product'); exit;
                } catch (Throwable $e) { if ($db->inTransaction()) $db->rollBack(); foreach ($uploadedPaths as $path) @unlink(__DIR__ . '/' . $path); if ($e->getMessage() === 'free_limit') $errors[] = 'The free plan allows 5 listings. Choose a paid plan to post without a limit.'; elseif ($e instanceof PDOException && $e->getCode() === '23000') $errors[] = 'A product with that title already exists.'; else $errors[] = 'Could not save the listing. Please try again.'; }
            }
        } elseif ($action === 'delete_product') {
            $productId = (int)($_POST['product_id'] ?? 0);
            $q=$db->prepare('SELECT image_path FROM page_posts WHERE id=? AND page_id=?'); $q->execute([$productId,$businessId]); $oldImage=$q->fetchColumn();
            $q=$db->prepare('SELECT image_path FROM product_images WHERE product_id=?'); $q->execute([$productId]); $oldExtraImages=$q->fetchAll(PDO::FETCH_COLUMN);
            $db->prepare('DELETE FROM page_posts WHERE id=? AND page_id=?')->execute([$productId,$businessId]);
            if ($oldImage && marketplace_image_url((string)$oldImage, '') !== null) @unlink(__DIR__ . '/' . $oldImage);
            foreach ($oldExtraImages as $oldExtra) if (marketplace_image_url($oldExtra, '') !== null) @unlink(__DIR__ . '/' . $oldExtra);
            header('Location: dashboard?view=products&business=' . $businessId . '&page=' . $requestedProductsPage); exit;
        } elseif ($action === 'read_message') {
            $messageId = filter_var($_POST['message_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
            $db->prepare('UPDATE store_messages SET is_read=1 WHERE id=? AND page_id=?')->execute([$messageId,$businessId]);
            header('Location: dashboard?view=messages&business=' . $businessId); exit;
        } elseif ($action === 'contact') {
            $email = strtolower(trim((string)($_POST['email'] ?? '')));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
            elseif (empty($_POST['consent'])) $errors[] = 'Confirm that this contact agreed to receive marketing email.';
            else {
                $token = bin2hex(random_bytes(24));
                $db->prepare('INSERT INTO email_contacts (business_id,email,unsubscribe_token) VALUES (?,?,?) ON DUPLICATE KEY UPDATE active=1')->execute([$businessId,$email,$token]);
                header('Location: dashboard?view=email&business=' . $businessId . '&saved=contact'); exit;
            }
        } elseif ($action === 'campaign') {
            $subject = trim((string)($_POST['subject'] ?? ''));
            $body = trim((string)($_POST['body'] ?? ''));
            if (mb_strlen($subject) < 3 || mb_strlen($subject) > 200 || mb_strlen($body) < 10 || mb_strlen($body) > 30000) $errors[] = 'Enter a subject of 3–200 characters and a message of at least 10 characters.';
            else {
                $db->prepare('INSERT INTO email_campaigns (business_id,subject,body) VALUES (?,?,?)')->execute([$businessId,$subject,$body]);
                header('Location: dashboard?view=email&business=' . $businessId . '&saved=campaign'); exit;
            }
        }
    }
}
$products = $contacts = $campaigns = $messages = [];
$productsTotal = 0;
$productsPerPage = 12;
$productsPage = 1;
$productsPages = 1;
$storeContact = ['whatsapp_phone'=>'','call_phone'=>''];
$unreadMessages = 0;
if ($business) {
    $q=$db->prepare('SELECT COUNT(*) FROM page_posts WHERE page_id=?'); $q->execute([$selectedId]); $productsTotal=(int)$q->fetchColumn();
    $productsPages=max(1,(int)ceil($productsTotal/$productsPerPage));
    $productsPage=min($requestedProductsPage,$productsPages);
    if ($view === 'products') {
        $q=$db->prepare('SELECT * FROM page_posts WHERE page_id=? ORDER BY id DESC LIMIT ? OFFSET ?');
        $q->bindValue(1,$selectedId,PDO::PARAM_INT);
        $q->bindValue(2,$productsPerPage,PDO::PARAM_INT);
        $q->bindValue(3,($productsPage-1)*$productsPerPage,PDO::PARAM_INT);
        $q->execute();
        $products=$q->fetchAll();
    }
    $q=$db->prepare('SELECT email,active,created_at FROM email_contacts WHERE business_id=? ORDER BY id DESC'); $q->execute([$selectedId]); $contacts=$q->fetchAll();
    $q=$db->prepare('SELECT * FROM email_campaigns WHERE business_id=? ORDER BY id DESC'); $q->execute([$selectedId]); $campaigns=$q->fetchAll();
    $q=$db->prepare('SELECT whatsapp_phone,call_phone FROM store_contact_settings WHERE page_id=?'); $q->execute([$selectedId]); $storeContact=$q->fetch() ?: $storeContact;
    $q=$db->prepare('SELECT m.*,p.title AS product_title,p.slug AS product_slug FROM store_messages m LEFT JOIN page_posts p ON p.id=m.product_id WHERE m.page_id=? ORDER BY m.created_at DESC,m.id DESC LIMIT 100'); $q->execute([$selectedId]); $messages=$q->fetchAll();
    $q=$db->prepare('SELECT COUNT(*) FROM store_messages WHERE page_id=? AND is_read=0'); $q->execute([$selectedId]); $unreadMessages=(int)$q->fetchColumn();
}
$analyticsPeriod = in_array($_GET['period'] ?? '', ['days','weeks','months'], true) ? (string)$_GET['period'] : 'days';
$periodData = in_array($view, ['overview','analytics'], true) ? dashboard_period_analytics($db, $selectedId, $view === 'overview' ? 'days' : $analyticsPeriod) : null;
$analyticsBase = 'dashboard?view=analytics&amp;business=' . $selectedId;
$publicUrl = app_page_url('marketplace', $business['slug']);
$postedAction = (string)($_POST['action'] ?? '');
$postedProductId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
$locationComplete = marketplace_location_valid((string)($user['country_code'] ?? ''), (string)($user['region'] ?? ''), (string)($user['locality'] ?? ''));
$settingsAutoModal = $view === 'settings' && $setupRequired ? ($locationComplete ? 'settings-password-modal' : 'settings-location-modal') : '';
if ($errors && $postedAction === 'settings_location') $settingsAutoModal = 'settings-location-modal';
if ($errors && $postedAction === 'settings_password') $settingsAutoModal = 'settings-password-modal';
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Business dashboard | Nileteck</title><link rel="stylesheet" href="src/dashboard.css?v=<?= filemtime(__DIR__ . '/src/dashboard.css') ?>"><link rel="stylesheet" href="src/business.css?v=<?= filemtime(__DIR__ . '/src/business.css') ?>"><script src="src/dashboard.js?v=<?= filemtime(__DIR__ . '/src/dashboard.js') ?>" defer></script></head>
<body class="workspace-body"><a class="skip-link" href="#main-content">Skip to content</a><div class="workspace-shell">
<aside class="workspace-sidebar"><div class="workspace-sidebar-head"><a class="workspace-brand" href="./"><img src="images/Logo.png" alt="" width="46" height="43"><span>Nileteck<span class="brand-subtitle">BUSINESS SUITE</span></span></a><button class="workspace-menu-toggle" type="button" aria-label="Open dashboard menu" aria-controls="workspace-navigation" aria-expanded="false" data-dashboard-menu><span></span><span></span><span></span></button></div><div class="workspace-label">WORKSPACE</div>
<nav class="workspace-nav" id="workspace-navigation" aria-label="Dashboard">
<a href="dashboard" <?= $view==='overview'?'aria-current="page"':'' ?>><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>Overview</a>
<a href="dashboard?view=products<?= count($businesses)>1?'&amp;business='.$selectedId:'' ?>" <?= $view==='products'?'aria-current="page"':'' ?>><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7 12 3l9 4v10l-9 4-9-4z"/><path d="m3 7 9 4 9-4M12 11v10"/></svg>Products</a>
<a href="dashboard?view=email<?= count($businesses)>1?'&amp;business='.$selectedId:'' ?>" <?= $view==='email'?'aria-current="page"':'' ?>><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2.5" y="5" width="19" height="14" rx="2"/><path d="m3 7 9 7 9-7"/></svg>Email Marketing</a>
<a href="dashboard?view=analytics<?= count($businesses)>1?'&amp;business='.$selectedId:'' ?>" <?= $view==='analytics'?'aria-current="page"':'' ?>><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V11m5 9V5m5 15v-7m5 7V9"/></svg>Analytics</a>
<a href="dashboard?view=messages<?= count($businesses)>1?'&amp;business='.$selectedId:'' ?>" <?= $view==='messages'?'aria-current="page"':'' ?>><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v12H9l-5 4z"/><path d="M8 9h8m-8 4h5"/></svg>Messages<?= $unreadMessages ? ' ('.$unreadMessages.')' : '' ?></a>
<a href="dashboard?view=settings" <?= $view==='settings'?'aria-current="page"':'' ?>><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4m0-14.2-1.4 1.4M6.3 17.7l-1.4 1.4"/></svg>Settings</a>
<?php if (manager_admin_email()!=='' && strcasecmp($user['email'],manager_admin_email())===0): ?><a href="manager/"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h7v7H3zm11 0h7v7h-7zM3 14h7v7H3zm11 0h7v7h-7z"/></svg>Manager</a><?php endif; ?>
<form class="workspace-mobile-logout" action="logout" method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><button type="submit">Sign out <span aria-hidden="true">↗</span></button></form>
</nav><div class="workspace-sidebar-bottom"><a href="./" class="workspace-back">← Back to Nileteck</a></div></aside>
<div class="workspace-main-wrap"><header class="workspace-topbar"><div class="workspace-breadcrumb">Workspace <span>/</span> <strong><?= app_h($view==='email'?'Email Marketing':ucfirst($view)) ?></strong></div><div class="workspace-account"><span><?= app_h($user['name']) ?></span><form action="logout" method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><button type="submit">Sign out</button></form></div></header>
<main class="workspace-main" id="main-content" data-setup-modal="<?= app_h($settingsAutoModal) ?>"><div class="workspace-heading"><div><span class="workspace-eyebrow">NILETECK BUSINESS SUITE</span><h1><?= app_h(match($view) {'email'=>'Email Marketing','products'=>'Products','analytics'=>'Analytics','messages'=>'Messages','settings'=>'Settings',default=>'Your dashboard'}) ?></h1><p><?= $view === 'settings' ? 'Manage your location and password.' : 'Manage your storefront, products, and email audience.' ?></p></div></div>
<?php if ($errors): ?><div class="workspace-error" role="alert"><?php foreach ($errors as $error): ?><p><?= app_h($error) ?></p><?php endforeach; ?></div><?php endif; ?><?php if (isset($_GET['saved'])): ?><p class="workspace-success" role="status">Saved successfully.</p><?php endif; ?>
<?php if ($view !== 'settings' && count($businesses)>1): ?><nav class="business-tabs" aria-label="Your storefronts"><?php foreach ($businesses as $item): ?><a href="dashboard?view=<?= app_h($view) ?>&amp;business=<?= (int)$item['id'] ?>" <?= $selectedId===(int)$item['id']?'aria-current="page"':'' ?>><?= app_h($item['title']) ?></a><?php endforeach; ?></nav><?php endif; ?>
<?php if ($view === 'overview'): ?><section class="workspace-editor business-panel business-store-strip" aria-label="Storefront"><div><span class="workspace-eyebrow">YOUR STOREFRONT</span><h2><?= app_h($business['title']) ?></h2><p><?= app_h($business['description']) ?></p></div><div class="business-actions"><a class="business-action" href="<?= app_h($publicUrl) ?>" target="_blank" rel="noopener">Open storefront ↗</a><button class="business-action business-action-secondary" type="button" data-open-modal="store-modal">Edit store</button></div></section><?php endif; ?>
<?php if (empty($user['country_code']) && $view !== 'settings'): ?><p class="workspace-error">Set your account location in Settings so nearby listings and new product forms use the right country. <a href="dashboard?view=settings">Set my location →</a></p><?php endif; ?>
<?php if ($view==='settings'): ?>
<section class="workspace-editor business-panel workspace-settings-intro"><span class="workspace-eyebrow">ACCOUNT SETTINGS</span><h2><?= $setupRequired ? 'Finish setting up your account' : 'Your account settings' ?></h2><p><?= $setupRequired ? 'Set your password and location to start using the dashboard.' : 'Manage your password and the location used for marketplace listings.' ?></p><?php if ($setupRequired): ?><p class="business-note">Complete both sections below to continue.</p><?php endif; ?></section>
<div class="workspace-settings-grid">
<section class="workspace-editor business-panel workspace-setting-card" aria-labelledby="settings-location-card-title"><span class="workspace-eyebrow">LOCATION</span><h2 id="settings-location-card-title">Your location</h2><p><?= app_h(marketplace_location_label($user) ?: 'Not set yet') ?></p><?php if ($locationComplete): ?><p class="business-note">Currency: <?= app_h($defaultCurrency) ?></p><?php else: ?><p class="business-note">Required to show your local marketplace and listing currency.</p><?php endif; ?><button class="workspace-primary" type="button" data-open-modal="settings-location-modal"><?= $locationComplete ? 'Update location' : 'Set location' ?></button></section>
<section class="workspace-editor business-panel workspace-setting-card" aria-labelledby="settings-password-card-title"><span class="workspace-eyebrow">SECURITY</span><h2 id="settings-password-card-title">Password</h2><p><?= empty($user['password_is_set']) ? 'Set a password to sign in with your email address.' : 'Your account has a password.' ?></p><button class="workspace-primary" type="button" data-open-modal="settings-password-modal"><?= empty($user['password_is_set']) ? 'Set password' : 'Change password' ?></button></section>
</div>
<dialog class="workspace-modal workspace-settings-modal" id="settings-location-modal" aria-labelledby="settings-location-title"><div class="workspace-modal-inner"><button class="workspace-modal-close" type="button" data-close-modal aria-label="Close location settings">×</button><h2 id="settings-location-title">Set your location</h2><p>Your country determines which marketplace listings and currency you see.</p><?php if ($postedAction==='settings_location'): foreach ($errors as $error): ?><p class="workspace-error" role="alert"><?= app_h($error) ?></p><?php endforeach; endif; ?><form class="workspace-form" method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="action" value="settings_location"><label>Country<select name="country_code" required><option value="">Choose a country</option><?php foreach (marketplace_african_countries() as $code => $details): ?><option value="<?= app_h($code) ?>" <?= ($postedAction==='settings_location' ? ($_POST['country_code'] ?? '') : ($user['country_code'] ?? ''))===$code?'selected':'' ?>><?= app_h($details[0]) ?></option><?php endforeach; ?></select></label><div class="workspace-form-pair"><label>City or region<input name="region" required maxlength="100" autocomplete="address-level2" placeholder="Nairobi" value="<?= app_h($postedAction==='settings_location' ? (string)($_POST['region'] ?? '') : (string)($user['region'] ?? '')) ?>"></label><label>Area or neighborhood<input name="locality" required maxlength="100" autocomplete="address-level3" placeholder="Kabiria" value="<?= app_h($postedAction==='settings_location' ? (string)($_POST['locality'] ?? '') : (string)($user['locality'] ?? '')) ?>"></label></div><button class="workspace-primary" type="submit">Save location</button></form></div></dialog>
<dialog class="workspace-modal workspace-settings-modal" id="settings-password-modal" aria-labelledby="settings-password-title"><div class="workspace-modal-inner"><button class="workspace-modal-close" type="button" data-close-modal aria-label="Close password settings">×</button><h2 id="settings-password-title"><?= empty($user['password_is_set']) ? 'Set a password' : 'Change your password' ?></h2><p><?= empty($user['password_is_set']) ? 'Create a password so you can also sign in with your email address.' : 'Use a new password to protect your account.' ?></p><?php if ($postedAction==='settings_password'): foreach ($errors as $error): ?><p class="workspace-error" role="alert"><?= app_h($error) ?></p><?php endforeach; endif; ?><form class="workspace-form" method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="action" value="settings_password"><?php if (!empty($user['password_is_set'])): ?><label>Current password<input type="password" name="current_password" required autocomplete="current-password" placeholder="Enter your current password"></label><?php endif; ?><label>New password<input type="password" name="password" required minlength="10" autocomplete="new-password" placeholder="At least 10 characters"></label><label>Confirm new password<input type="password" name="password_confirm" required minlength="10" autocomplete="new-password" placeholder="Repeat your new password"></label><button class="workspace-primary" type="submit"><?= empty($user['password_is_set']) ? 'Set password' : 'Update password' ?></button></form></div></dialog>
<?php endif; ?>
<?php if ($view==='overview'): ?><div class="business-grid business-overview-grid"><section class="workspace-editor business-panel business-card"><span class="business-card-icon" aria-hidden="true">◇</span><h2>Marketplace</h2><p><strong><?= $productsTotal ?></strong> <?= $productsTotal===1?'product':'products' ?> in your store</p><div class="business-card-actions"><a class="business-action" href="dashboard?view=products<?= count($businesses)>1?'&amp;business='.$selectedId:'' ?>">Manage products</a><button class="business-action business-action-secondary" type="button" data-open-modal="product-modal">Post product</button></div></section><section class="workspace-editor business-panel business-card"><span class="business-card-icon" aria-hidden="true">✉</span><h2>Email Marketing</h2><p><strong><?= count(array_filter($contacts,static fn($contact)=>(int)$contact['active']===1)) ?></strong> subscribers · <strong><?= count($campaigns) ?></strong> campaigns</p><div class="business-card-actions"><a class="business-action" href="dashboard?view=email<?= count($businesses)>1?'&amp;business='.$selectedId:'' ?>">Manage email</a><button class="business-action business-action-secondary" type="button" data-open-modal="campaign-modal">New campaign</button></div></section>
<section class="workspace-editor business-panel business-card"><span class="business-card-icon" aria-hidden="true">▥</span><h2>Analytics</h2><p>Last 7 days · <strong><?= number_format($periodData['current']['visitors']) ?></strong> unique visitors · <strong><?= number_format($periodData['current']['views']) ?></strong> product views</p><div class="business-card-actions"><a class="business-action" href="dashboard?view=analytics&amp;business=<?= $selectedId ?>&amp;period=days">See analytics</a></div></section><section class="workspace-editor business-panel business-card"><span class="business-card-icon" aria-hidden="true">✉</span><h2>Messages</h2><p><strong><?= $unreadMessages ?></strong> unread · <strong><?= count($messages) ?></strong> recent messages</p><div class="business-card-actions"><a class="business-action" href="dashboard?view=messages&amp;business=<?= $selectedId ?>">Open inbox</a></div></section></div><?php endif; ?>
<?php if ($view==='products'): ?>
<section class="workspace-editor business-panel business-products-panel" aria-labelledby="products-title">
  <div class="seller-plan-dashboard"><div><strong><?= app_h(seller_plan_label($sellerTier)) ?> seller plan</strong><span><?= $isPaidSeller ? 'Unlimited listings · immediate publishing' : 'Up to 5 listings per account · review before publishing' ?></span></div><a class="business-action business-action-secondary" href="seller-plans">View plans</a></div>
  <div class="business-section-heading"><div><span class="workspace-eyebrow">PRODUCT CATALOG</span><h2 id="products-title">Your listings</h2></div><button class="business-action" type="button" data-open-modal="product-modal">+ Post product</button></div>
  <p class="business-product-summary"><?php if ($productsTotal): ?>Showing <?= ($productsPage-1)*$productsPerPage+1 ?>–<?= min($productsPage*$productsPerPage,$productsTotal) ?> of <?= number_format($productsTotal) ?> listings<?php else: ?>No listings yet<?php endif; ?></p>
  <?php if (!$products): ?><div class="business-empty"><p>No products yet. Post your first product to fill your storefront.</p></div><?php else: ?>
  <div class="business-product-list"><?php foreach ($products as $product): ?>
    <article class="business-product-item"><div class="business-product-main">
      <?php if ($thumb=marketplace_image_url($product['image_path'] ?? null, '')): ?><img class="business-product-thumb" src="<?= app_h($thumb) ?>" alt="" loading="lazy"><?php elseif ($cell=marketplace_demo_cell($product['image_path'] ?? null)): ?><span class="business-product-thumb business-demo-thumb business-demo-cell-<?= $cell ?>" aria-hidden="true"></span><?php else: ?><span class="business-product-thumb business-product-placeholder" aria-hidden="true">◇</span><?php endif; ?>
      <div class="business-product-copy"><span class="business-product-status <?= $product['status']==='published'?'is-published':'is-draft' ?>"><?= app_h(ucfirst($product['status'])) ?></span><h3><?= app_h($product['title']) ?></h3><strong class="business-product-price"><?= $product['price']!==null ? app_h(marketplace_price($product['price'], $product['currency_code'] ?? 'KES')) : 'Price on request' ?></strong><p class="business-product-location"><?= app_h(marketplace_location_label($product) ?: 'Location missing — edit listing') ?></p></div>
    </div><div class="business-product-actions"><button class="business-action business-action-secondary" type="button" data-open-modal="edit-product-<?= (int)$product['id'] ?>">Edit</button><?php if ($product['status']==='published'): ?><a class="business-action business-action-secondary" href="<?= app_h(app_page_url('marketplace',$business['slug'],$product['slug'])) ?>" target="_blank" rel="noopener">View</a><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="business_id" value="<?= (int)$business['id'] ?>"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><button class="business-product-delete" type="submit">Delete</button></form></div></article>
  <?php endforeach; ?></div>
  <?php if ($productsPages > 1): ?><nav class="business-product-pagination" aria-label="Product pages">
    <?php if ($productsPage > 1): ?><a href="dashboard?view=products&amp;business=<?= $selectedId ?>&amp;page=<?= $productsPage-1 ?>" rel="prev">← Previous</a><?php endif; ?>
    <?php for ($pageNumber=max(1,$productsPage-2);$pageNumber<=min($productsPages,$productsPage+2);$pageNumber++): ?><a href="dashboard?view=products&amp;business=<?= $selectedId ?>&amp;page=<?= $pageNumber ?>" <?= $pageNumber===$productsPage?'aria-current="page"':'' ?>><?= $pageNumber ?></a><?php endfor; ?>
    <?php if ($productsPage < $productsPages): ?><a href="dashboard?view=products&amp;business=<?= $selectedId ?>&amp;page=<?= $productsPage+1 ?>" rel="next">Next →</a><?php endif; ?>
    <span>Page <?= $productsPage ?> of <?= $productsPages ?></span>
  </nav><?php endif; ?>
  <?php endif; ?>
</section>
<?php endif; ?>
<?php if ($view==='email'): ?><div class="business-grid"><section class="workspace-editor business-panel"><div class="business-section-heading"><div><span class="workspace-eyebrow">AUDIENCE</span><h2>Email contacts</h2></div><button class="business-action" type="button" data-open-modal="contact-modal">+ Add contact</button></div><p>Only add people who agreed to receive marketing emails. Visitors can also subscribe from your storefront.</p><?php if (!$contacts): ?><div class="business-empty"><p>No contacts yet.</p></div><?php endif; ?><?php foreach ($contacts as $contact): ?><div class="business-row"><strong><?= app_h($contact['email']) ?></strong><span><?= $contact['active']?'Subscribed':'Unsubscribed' ?></span></div><?php endforeach; ?></section><section class="workspace-editor business-panel"><div class="business-section-heading"><div><span class="workspace-eyebrow">CAMPAIGNS</span><h2>Email drafts</h2></div><button class="business-action" type="button" data-open-modal="campaign-modal">+ New campaign</button></div><p>Prepare a message for your audience. Sending requires a configured delivery service.</p><?php if (!$campaigns): ?><div class="business-empty"><p>No campaign drafts yet.</p></div><?php endif; ?><?php foreach ($campaigns as $campaign): ?><article class="business-row"><div><strong><?= app_h($campaign['subject']) ?></strong><span>Draft · <?= app_h($campaign['created_at']) ?></span><p><?= app_h(mb_strimwidth($campaign['body'],0,150,'…')) ?></p></div></article><?php endforeach; ?></section></div><?php endif; ?>
<?php if ($view==='analytics'): $current=$periodData['current']; $previous=$periodData['previous']; $buckets=$periodData['buckets']; ?>
<div class="analytics-toolbar"><div><span class="workspace-eyebrow">PERFORMANCE REPORT</span><h2>Explore your store activity</h2><p><?= app_h($periodData['label']) ?> · <?= app_h($periodData['from']) ?> to <?= app_h($periodData['through']) ?></p></div><div class="analytics-toolbar-controls"><nav class="analytics-pill-group" aria-label="Analytics time period"><?php foreach (['days'=>'Days','weeks'=>'Weeks','months'=>'Months'] as $key=>$label): ?><a href="<?= $analyticsBase ?>&amp;period=<?= $key ?>" <?= $analyticsPeriod===$key?'aria-current="page"':'' ?>><?= $label ?></a><?php endforeach; ?></nav></div></div>
<section class="workspace-editor business-panel business-analytics-overview" aria-labelledby="analytics-overview-title"><div class="business-analytics-heading"><div><span class="workspace-eyebrow">STOREFRONT ACTIVITY</span><h2 id="analytics-overview-title">Your performance at a glance</h2><p>Compare this period with the previous matching time window.</p></div><span class="business-analytics-live"><span aria-hidden="true"></span> <?= app_h($periodData['label']) ?></span></div>
<div class="business-metric-grid"><?php foreach (['visits'=>['Store visits','Visits'],'visitors'=>['Unique visitors','Visitors'],'impressions'=>['Impressions','Impr.'],'clicks'=>['Product clicks','Clicks'],'views'=>['Product views','Views']] as $key=>$labels): ?><article class="business-metric <?= $key==='visits'?'business-metric-featured':'' ?>"><span class="business-metric-label"><span class="business-metric-label-full"><?= $labels[0] ?></span><span class="business-metric-label-short" aria-hidden="true"><?= $labels[1] ?></span></span><strong><?= number_format($current[$key]) ?></strong><small><?= app_h(dashboard_metric_change($current[$key],$previous[$key])) ?></small></article><?php endforeach; ?></div><p class="analytics-data-note">Daily comparisons include activity recorded since daily tracking was introduced.</p></section>
<?php $maxVisits=max(1,...array_column($buckets,'visits')); $maxViews=max(1,...array_column($buckets,'views')); $chartPoints=[]; foreach ($buckets as $index=>$bucket) $chartPoints[]=(20+($index*660/max(1,count($buckets)-1))).','.(170-($bucket['views']/$maxViews*145)); ?>
<div class="analytics-chart-grid"><section class="workspace-editor business-panel analytics-chart-panel" aria-labelledby="visits-chart-title"><span class="workspace-eyebrow">TRAFFIC</span><h2 id="visits-chart-title">Store visits over time</h2><p>See which <?= $analyticsPeriod==='days'?'days':($analyticsPeriod==='weeks'?'weeks':'months') ?> brought the most shoppers.</p><div class="analytics-bar-chart" role="img" aria-label="Store visits by <?= $analyticsPeriod ?>"><?php foreach ($buckets as $bucket): ?><div class="analytics-bar-item" title="<?= app_h($bucket['label']) ?>: <?= number_format($bucket['visits']) ?> visits"><strong><?= number_format($bucket['visits']) ?></strong><span class="analytics-bar-track"><span style="height:<?= max(2,(int)round($bucket['visits']/$maxVisits*100)) ?>%"></span></span><small><?= app_h($bucket['label']) ?></small></div><?php endforeach; ?></div></section><section class="workspace-editor business-panel analytics-chart-panel" aria-labelledby="views-chart-title"><span class="workspace-eyebrow">ENGAGEMENT</span><h2 id="views-chart-title">Product views over time</h2><p>Compare interest in your listings throughout the period.</p><div class="analytics-line-chart" role="img" aria-label="Product views: <?php foreach ($buckets as $bucket) echo app_h($bucket['label']).' '.number_format($bucket['views']).'; '; ?>"><svg viewBox="0 0 700 190" preserveAspectRatio="none" aria-hidden="true"><path d="M20 170 H680 M20 97 H680 M20 25 H680" class="analytics-grid-lines"/><polyline points="<?= implode(' ',$chartPoints) ?>"/><?php foreach ($buckets as $index=>$bucket): $cx=20+($index*660/max(1,count($buckets)-1)); $cy=170-($bucket['views']/$maxViews*145); ?><circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="4"/><?php endforeach; ?></svg><div class="analytics-line-labels"><?php foreach ($buckets as $bucket): ?><span><?= app_h($bucket['label']) ?></span><?php endforeach; ?></div></div></section></div>
<section class="workspace-editor business-panel business-product-performance" aria-labelledby="product-performance-title"><div class="business-analytics-heading"><div><span class="workspace-eyebrow">PRODUCT PERFORMANCE</span><h2 id="product-performance-title">Activity by product</h2><p>Compare listing activity in the selected period.</p></div><span class="business-product-count"><?= count($periodData['products']) ?> <?= count($periodData['products'])===1?'product':'products' ?></span></div>
<?php if (!$periodData['products']): ?><div class="business-empty"><p>Product activity will appear here after you publish products.</p></div><?php else: $maxProductViews=max(1,...array_map(static fn($item)=>(int)$item['views'],$periodData['products'])); ?><div class="business-performance-list"><?php foreach ($periodData['products'] as $stat): $viewShare=round(100*(int)$stat['views']/$maxProductViews); ?><article class="business-performance-row"><div class="business-performance-top"><h3><?= app_h($stat['title']) ?></h3><strong><?= number_format((int)$stat['views']) ?> <span>views</span></strong></div><div class="business-performance-track" role="img" aria-label="<?= app_h($stat['title']) ?>: <?= number_format((int)$stat['views']) ?> product views"><span style="width:<?= $viewShare ?>%"></span></div><div class="business-performance-meta"><span><?= number_format((int)$stat['impressions']) ?> impressions</span><span><?= number_format((int)$stat['clicks']) ?> clicks</span><span><?= number_format((int)$stat['views']) ?> views</span></div></article><?php endforeach; ?></div><?php endif; ?></section>
<?php endif; ?>
<?php if ($view==='messages'): ?><section class="workspace-editor business-panel"><span class="workspace-eyebrow">CUSTOMER ORDERS & QUESTIONS</span><h2>Messages <?= $unreadMessages ? '· ' . $unreadMessages . ' unread' : '' ?></h2><?php if (!$messages): ?><div class="business-empty"><p>No messages yet. Customers can contact you from your store and product pages.</p></div><?php endif; ?><?php foreach ($messages as $message): ?><article class="business-message <?= $message['is_read'] ? '' : 'is-unread' ?>"><div class="business-message-head"><div><strong><?= app_h($message['sender_name']) ?></strong><span><?= app_h($message['sender_email']) ?> · <?= app_h($message['created_at']) ?></span><?php if ($message['product_title']): ?><span>About <?= app_h($message['product_title']) ?></span><?php endif; ?></div><?php if (!$message['is_read']): ?><span class="business-unread">New</span><?php endif; ?></div><p><?= nl2br(app_h($message['body'])) ?></p><div class="business-message-actions"><a class="business-action business-action-secondary" href="mailto:<?= app_h($message['sender_email']) ?>?subject=<?= rawurlencode('Re: your message to ' . $business['title']) ?>">Reply by email</a><?php if (!$message['is_read']): ?><form method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="action" value="read_message"><input type="hidden" name="business_id" value="<?= $selectedId ?>"><input type="hidden" name="message_id" value="<?= (int)$message['id'] ?>"><button class="business-action business-action-secondary" type="submit">Mark read</button></form><?php endif; ?></div></article><?php endforeach; ?></section><?php endif; ?>
<dialog class="workspace-modal" id="store-modal" aria-labelledby="store-modal-title"><div class="workspace-modal-inner"><button class="workspace-modal-close" type="button" data-close-modal aria-label="Close dialog">×</button><div class="workspace-editor"><h2 id="store-modal-title">Edit storefront</h2><?php if ($postedAction==='store'): foreach ($errors as $error): ?><p class="workspace-error" role="alert"><?= app_h($error) ?></p><?php endforeach; endif; ?><form class="workspace-form" method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="action" value="store"><label>Store name<input name="title" maxlength="120" required placeholder="Your store name" value="<?= app_h($postedAction==='store' ? (string)($_POST['title'] ?? '') : $business['title']) ?>"></label><label>Description<textarea name="description" maxlength="500" required placeholder="Tell buyers what your store offers"><?= app_h($postedAction==='store' ? (string)($_POST['description'] ?? '') : $business['description']) ?></textarea></label><div class="workspace-form-pair"><label>WhatsApp number<input name="whatsapp_phone" type="tel" maxlength="24" placeholder="+254712345678" value="<?= app_h($postedAction==='store' ? (string)($_POST['whatsapp_phone'] ?? '') : (string)$storeContact['whatsapp_phone']) ?>"></label><label>Call number<input name="call_phone" type="tel" maxlength="24" placeholder="+254712345678" value="<?= app_h($postedAction==='store' ? (string)($_POST['call_phone'] ?? '') : (string)$storeContact['call_phone']) ?>"></label></div><button class="workspace-primary" type="submit">Save details</button></form></div></div></dialog>
<dialog class="workspace-modal" id="product-modal" aria-labelledby="product-modal-title"><div class="workspace-modal-inner"><button class="workspace-modal-close" type="button" data-close-modal aria-label="Close dialog">×</button><div class="workspace-editor"><h2 id="product-modal-title">Post a listing</h2><?php if ($postedAction==='product'): foreach ($errors as $error): ?><p class="workspace-error" role="alert"><?= app_h($error) ?></p><?php endforeach; endif; ?><form class="workspace-form" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="action" value="product"><input type="hidden" name="business_id" value="<?= (int)$business['id'] ?>"><label>Listing title<input name="title" maxlength="160" required placeholder="e.g. Samsung Galaxy A55, 128 GB" value="<?= app_h($postedAction==='product' ? (string)($_POST['title'] ?? '') : '') ?>"></label><label>Details<textarea name="body" rows="5" maxlength="30000" required placeholder="Describe the condition, features, and what is included"><?= app_h($postedAction==='product' ? (string)($_POST['body'] ?? '') : '') ?></textarea></label><div class="workspace-form-pair"><label>Price (optional)<input name="price" inputmode="decimal" placeholder="0.00" value="<?= app_h($postedAction==='product' ? (string)($_POST['price'] ?? '') : '') ?>"></label><p class="business-note">Currency: <strong><?= app_h($defaultCurrency) ?></strong> · based on your account country</p><label>Category<select name="category" required><option value="" disabled <?= $postedAction !== 'product' || empty($_POST['category']) ? 'selected' : '' ?>>Choose a category</option><?php foreach (marketplace_category_groups() as $key => $details): ?><optgroup label="<?= app_h($details[0]) ?>"><option value="<?= app_h($key) ?>" <?= $postedAction==='product' && ($_POST['category'] ?? '')===$key?'selected':'' ?>>All <?= app_h($details[0]) ?></option><?php foreach ($details[2] as $childKey => $childLabel): ?><option value="<?= app_h($childKey) ?>" <?= $postedAction==='product' && ($_POST['category'] ?? '')===$childKey?'selected':'' ?>><?= app_h($childLabel) ?></option><?php endforeach; ?></optgroup><?php endforeach; ?></select></label></div><div class="workspace-listing-location"><h3>Product location</h3><p class="business-note">Buyers can find this listing by country, city, and area.</p><p class="business-note">Country: <strong><?= app_h(marketplace_country_name($user['country_code'] ?? null) ?: 'Not set') ?></strong><?php if (empty($user['country_code'])): ?> · <a href="dashboard?view=settings">Set my location</a><?php endif; ?></p><div class="workspace-form-pair"><label>City or region<input name="region" required maxlength="100" placeholder="Nairobi" value="<?= app_h($postedAction==='product' ? (string)($_POST['region'] ?? '') : (string)($user['region'] ?? '')) ?>"></label><label>Area or neighborhood<input name="locality" required maxlength="100" placeholder="Kabiria" value="<?= app_h($postedAction==='product' ? (string)($_POST['locality'] ?? '') : (string)($user['locality'] ?? '')) ?>"></label></div></div><p class="business-note">Prices use the currency of your account country. Nileteck does not convert exchange rates.</p><label>Cover photo (optional, JPG, PNG, or WebP, up to 5 MB)<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label><label>Product photos (select several at once, up to 8 total)<input type="file" name="extra_images[]" accept="image/jpeg,image/png,image/webp" multiple></label><p class="business-note">Select several photos together. If no cover photo is chosen, the first selected photo becomes the cover.</p><label>Status<select name="status"><option value="published"><?= $isPaidSeller ? 'Publish now' : 'Submit for review' ?></option><option value="draft" <?= $postedAction==='product' && ($_POST['status'] ?? '')==='draft'?'selected':'' ?>>Draft</option></select></label><button class="workspace-primary" type="submit">Save listing</button></form></div></div></dialog>
<?php foreach ($products as $product): $editingThis = $postedAction==='edit_product' && $postedProductId===(int)$product['id']; ?>
<dialog class="workspace-modal" id="edit-product-<?= (int)$product['id'] ?>" aria-labelledby="edit-product-title-<?= (int)$product['id'] ?>"><div class="workspace-modal-inner"><button class="workspace-modal-close" type="button" data-close-modal aria-label="Close dialog">×</button><div class="workspace-editor"><h2 id="edit-product-title-<?= (int)$product['id'] ?>">Edit listing</h2><?php if ($editingThis): foreach ($errors as $error): ?><p class="workspace-error" role="alert"><?= app_h($error) ?></p><?php endforeach; endif; ?><form class="workspace-form" method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="action" value="edit_product"><input type="hidden" name="business_id" value="<?= (int)$business['id'] ?>"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><label>Listing title<input name="title" maxlength="160" required placeholder="e.g. Samsung Galaxy A55, 128 GB" value="<?= app_h($editingThis ? (string)($_POST['title'] ?? '') : $product['title']) ?>"></label><label>Details<textarea name="body" rows="6" maxlength="30000" required placeholder="Describe the condition, features, and what is included"><?= app_h($editingThis ? (string)($_POST['body'] ?? '') : $product['body']) ?></textarea></label><div class="workspace-form-pair"><label>Price (optional)<input name="price" inputmode="decimal" placeholder="0.00" value="<?= app_h($editingThis ? (string)($_POST['price'] ?? '') : (string)($product['price'] ?? '')) ?>"></label><p class="business-note">Currency: <strong><?= app_h($defaultCurrency) ?></strong> · based on your account country</p><?php if (($product['currency_code'] ?? $defaultCurrency) !== $defaultCurrency): ?><p class="business-note">This listing is currently priced in <?= app_h($product['currency_code']) ?>. Review the amount before saving it in <?= app_h($defaultCurrency) ?>.</p><?php endif; ?><label>Category<select name="category" required><?php foreach (marketplace_category_groups() as $key => $details): ?><optgroup label="<?= app_h($details[0]) ?>"><option value="<?= app_h($key) ?>" <?= ($editingThis ? ($_POST['category'] ?? '') : $product['category'])===$key?'selected':'' ?>>All <?= app_h($details[0]) ?></option><?php foreach ($details[2] as $childKey => $childLabel): ?><option value="<?= app_h($childKey) ?>" <?= ($editingThis ? ($_POST['category'] ?? '') : $product['category'])===$childKey?'selected':'' ?>><?= app_h($childLabel) ?></option><?php endforeach; ?></optgroup><?php endforeach; ?></select></label></div><div class="workspace-listing-location"><h3>Product location</h3><p class="business-note">Keep the listing location accurate for nearby buyers.</p><p class="business-note">Country: <strong><?= app_h(marketplace_country_name($user['country_code'] ?? null) ?: 'Not set') ?></strong> · <a href="dashboard?view=settings">Change account location</a></p><div class="workspace-form-pair"><label>City or region<input name="region" required maxlength="100" placeholder="Nairobi" value="<?= app_h($editingThis ? (string)($_POST['region'] ?? '') : (string)($product['region'] ?: ($user['region'] ?? ''))) ?>"></label><label>Area or neighborhood<input name="locality" required maxlength="100" placeholder="Kabiria" value="<?= app_h($editingThis ? (string)($_POST['locality'] ?? '') : (string)($product['locality'] ?: ($user['locality'] ?? ''))) ?>"></label></div></div><p class="business-note">Prices use the currency of your account country. Nileteck does not convert exchange rates.</p><label>Replace cover photo (optional)<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label><label>Add product photos (select several at once, up to 8 total)<input type="file" name="extra_images[]" accept="image/jpeg,image/png,image/webp" multiple></label><p class="business-note">Select several photos together. Existing photos stay unless you remove them below.</p><?php $q=$db->prepare("SELECT id,image_path FROM product_images WHERE product_id=? ORDER BY id"); $q->execute([$product["id"]]); foreach ($q->fetchAll() as $extra): ?><label class="business-consent"><input type="checkbox" name="remove_extra_images[]" value="<?= (int)$extra["id"] ?>"> Remove photo <img class="business-thumb" src="<?= app_h(marketplace_image_url($extra["image_path"], "")) ?>" alt="" loading="lazy"></label><?php endforeach; ?><?php if ($product['image_path']): ?><label class="business-consent"><input type="checkbox" name="remove_image" value="1" <?= $editingThis && !empty($_POST['remove_image'])?'checked':'' ?>> Remove current image</label><?php endif; ?><label>Status<select name="status"><option value="published" <?= ($editingThis ? ($_POST['status'] ?? '') : $product['status'])!=='draft'?'selected':'' ?>><?= $isPaidSeller ? 'Publish now' : 'Submit for review' ?></option><option value="draft" <?= ($editingThis ? ($_POST['status'] ?? '') : $product['status'])==='draft'?'selected':'' ?>>Draft</option></select></label><?php if (marketplace_demo_cell($product['image_path'] ?? null)): ?><p class="business-note">This is a sample listing. Replace the sample image and details when it becomes a real product available for orders.</p><?php endif; ?><p class="business-note">The product link stays the same when you change its name.</p><button class="workspace-primary" type="submit">Save changes</button></form></div></div></dialog>
<?php endforeach; ?>
<dialog class="workspace-modal" id="contact-modal" aria-labelledby="contact-modal-title"><div class="workspace-modal-inner"><button class="workspace-modal-close" type="button" data-close-modal aria-label="Close dialog">×</button><div class="workspace-editor"><h2 id="contact-modal-title">Add email contact</h2><?php if ($postedAction==='contact'): foreach ($errors as $error): ?><p class="workspace-error" role="alert"><?= app_h($error) ?></p><?php endforeach; endif; ?><p>Only add someone who has agreed to marketing emails from your store.</p><form class="workspace-form" method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="action" value="contact"><input type="hidden" name="business_id" value="<?= (int)$business['id'] ?>"><label>Email address<input type="email" name="email" required placeholder="name@example.com" autocomplete="email" value="<?= app_h($postedAction==='contact' ? (string)($_POST['email'] ?? '') : '') ?>"></label><label class="business-consent"><input type="checkbox" name="consent" value="1" required <?= $postedAction==='contact' && !empty($_POST['consent'])?'checked':'' ?>> This person agreed to marketing emails</label><button class="workspace-primary" type="submit">Add contact</button></form></div></div></dialog>
<dialog class="workspace-modal" id="campaign-modal" aria-labelledby="campaign-modal-title"><div class="workspace-modal-inner"><button class="workspace-modal-close" type="button" data-close-modal aria-label="Close dialog">×</button><div class="workspace-editor"><h2 id="campaign-modal-title">Create email campaign</h2><?php if ($postedAction==='campaign'): foreach ($errors as $error): ?><p class="workspace-error" role="alert"><?= app_h($error) ?></p><?php endforeach; endif; ?><p>Save a campaign draft. Sending requires a configured delivery service.</p><form class="workspace-form" method="post"><input type="hidden" name="csrf" value="<?= app_h(app_csrf()) ?>"><input type="hidden" name="action" value="campaign"><input type="hidden" name="business_id" value="<?= (int)$business['id'] ?>"><label>Subject<input name="subject" maxlength="200" required placeholder="Your campaign subject" value="<?= app_h($postedAction==='campaign' ? (string)($_POST['subject'] ?? '') : '') ?>"></label><label>Message<textarea name="body" rows="8" maxlength="30000" required placeholder="Write your message to subscribed contacts"><?= app_h($postedAction==='campaign' ? (string)($_POST['body'] ?? '') : '') ?></textarea></label><button class="workspace-primary" type="submit">Save draft</button></form></div></div></dialog>
</main></div></div></body></html>
