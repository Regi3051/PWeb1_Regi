<?php
// admin/index.php - Admin entry point
require_once __DIR__ . '/auth.php';

if (!is_admin_logged_in()) {
    require __DIR__ . '/login.php';
    exit;
}

header('Location: /admin/reports.php');
exit;

