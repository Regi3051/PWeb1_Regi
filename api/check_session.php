<?php
// api/check_session.php - Returns current admin session status
require_once __DIR__ . '/../admin/auth.php';

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'logged_in' => is_admin_logged_in(),
    'username' => $_SESSION['admin_username'] ?? null,
    'name' => $_SESSION['admin_name'] ?? null
]);

