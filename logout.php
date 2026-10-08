<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/app.php';
app_session();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard'); exit; }
app_verify_csrf();
$_SESSION = [];
session_regenerate_id(true);
session_destroy();
header('Location: signin');
