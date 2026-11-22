<?php
// admin/logout.php - Admin logout handler
require_once __DIR__ . '/auth.php';

logout_admin();
header('Location: /');
exit;

