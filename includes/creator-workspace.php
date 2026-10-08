<?php
declare(strict_types=1);
$db = app_db();
$sections = app_sections();
$returnView = in_array($_GET['view'] ?? '', ['articles', 'events'], true) ? (string)$_GET['view'] : 'pages';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    app_verify_csrf();
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'page') {
        $section = (string)($_POST['section'] ?? '');
        $title = trim((string)($_POST['title'] ?? ''));
        $slug = trim(substr(app_slug($title), 0, 60), '-');
        if (strlen($slug) < 3) $slug = 'page' . ($slug !== '' ? '-' . $slug : '');
        $description = trim((string)($_POST['description'] ?? ''));
        if (!isset($sections[$section])) $errors[] = 'Choose a valid section.';
        if ($title === '' || mb_strlen($title) > 120 || $description === '' || mb_strlen($description) > 500) $errors[] = 'Add a page title and a short description.';
        if (!$errors) {
            $baseSlug = $slug;
            $suffix = 2;
            $checkSlug = $db->prepare('SELECT 1 FROM creator_pages WHERE section=? AND slug=? LIMIT 1');
            while (true) {
                $checkSlug->execute([$section, $slug]);
                if (!$checkSlug->fetchColumn()) break;
                $ending = '-' . $suffix++;
                $slug = rtrim(substr($baseSlug, 0, 60 - strlen($ending)), '-') . $ending;
            }
            try {
                $query = $db->prepare('INSERT INTO creator_pages (user_id,section,slug,title,description) VALUES (?,?,?,?,?)');
                $query->execute([$user['id'],$section,$slug,$title,$description]);
                header('Location: dashboard?view=' . $returnView . ($returnView === 'pages' ? '&page=' . $db->lastInsertId() . '#page-preview' : '#entry-modal')); exit;
            } catch (PDOException $error) {
                if ($error->getCode() !== '23000') throw $error;
                $errors[] = 'That page address is already taken in this section.';
            }
        }
    } elseif ($action === 'post') {
        $pageId = filter_var($_POST['page_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        $query = $db->prepare('SELECT * FROM creator_pages WHERE id=? AND user_id=?');
        $query->execute([$pageId,$user['id']]);
        $ownedPage = $query->fetch(PDO::FETCH_ASSOC);
        if (!$ownedPage || ($returnView !== 'pages' && $ownedPage['section'] !== ($returnView === 'events' ? 'events' : 'blog'))) { http_response_code(403); exit('Page not found.'); }
        $postId = filter_var($_POST['post_id'] ?? null, FILTER_VALIDATE_INT) ?: null;
        $title = trim((string)($_POST['title'] ?? ''));
        $slug = app_slug((string)(trim((string)($_POST['slug'] ?? '')) ?: $title));
        $body = trim((string)($_POST['body'] ?? ''));
        $price = trim((string)($_POST['price'] ?? ''));
        $date = trim((string)($_POST['event_date'] ?? ''));
        $venue = trim((string)($_POST['venue'] ?? ''));
        $status = (string)($_POST['status'] ?? 'draft');
        if ($title === '' || mb_strlen($title) > 160 || $body === '' || mb_strlen($body) > 30000 || !preg_match('/^[a-z0-9](?:[a-z0-9-]{1,78}[a-z0-9])$/', $slug)) $errors[] = 'Add a title, content, and a valid address of 3–80 characters.';
        if (!in_array($status, ['draft','published'], true)) $errors[] = 'Choose a valid status.';
        if ($ownedPage['section'] === 'events' && ($date === '' || !DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $date) || $venue === '' || mb_strlen($venue) > 200)) $errors[] = 'Events need a date, time, and venue.';
        if ($price !== '' && (!is_numeric($price) || (float)$price < 0 || mb_strlen($price) > 20)) $errors[] = 'Enter a valid nonnegative price.';
        if (!$errors) {
            try {
                if ($postId) {
                    $query = $db->prepare('UPDATE page_posts SET slug=?,title=?,body=?,price=?,event_date=?,venue=?,status=? WHERE id=? AND page_id=?');
                    $query->execute([$slug,$title,$body,$price ?: null,$date ?: null,$venue ?: null,$status,$postId,$pageId]);
                } else {
                    $query = $db->prepare('INSERT INTO page_posts (page_id,slug,title,body,price,event_date,venue,status) VALUES (?,?,?,?,?,?,?,?)');
                    $query->execute([$pageId,$slug,$title,$body,$price ?: null,$date ?: null,$venue ?: null,$status]);
                }
                header('Location: dashboard?view=' . $returnView . ($returnView === 'pages' ? '&page=' . $pageId . '#page-preview' : '&created=1')); exit;
            } catch (PDOException $error) {
                if ($error->getCode() !== '23000') throw $error;
                $errors[] = 'That entry address is already used on this page.';
            }
        }
    } elseif ($action === 'delete') {
        $pageId = filter_var($_POST['page_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        $query = $db->prepare('DELETE FROM page_posts WHERE id=? AND page_id IN (SELECT id FROM creator_pages WHERE id=? AND user_id=?)');
        $query->execute([(int)($_POST['post_id'] ?? 0),$pageId,$user['id']]);
        header('Location: dashboard?view=pages&page=' . $pageId); exit;
    }
}
$query = $db->prepare('SELECT * FROM creator_pages WHERE user_id=? ORDER BY created_at DESC,id DESC');
$query->execute([$user['id']]);
$pages = $query->fetchAll(PDO::FETCH_ASSOC);
$pageId = filter_var($_GET['page'] ?? null, FILTER_VALIDATE_INT) ?: 0;
$page = null;
foreach ($pages as $candidate) if ((int)$candidate['id'] === $pageId) $page = $candidate;
$posts = [];
$editing = null;
if ($page) {
    $query = $db->prepare('SELECT * FROM page_posts WHERE page_id=? ORDER BY created_at DESC,id DESC');
    $query->execute([$pageId]); $posts = $query->fetchAll(PDO::FETCH_ASSOC);
    $editId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT) ?: 0;
    foreach ($posts as $post) if ((int)$post['id'] === $editId) $editing = $post;
}

if ($page && $errors && ($_POST['action'] ?? '') === 'post') {
    $editing = array_merge($editing ?? [], array_intersect_key($_POST, array_flip(['title','slug','body','price','event_date','venue','status'])));
    $editing['id'] = (int)($_POST['post_id'] ?? 0);
}
