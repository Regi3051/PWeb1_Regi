<?php
// router.php - Router for PHP built-in server (php -S localhost:8000 router.php)

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve existing static files directly
$filePath = __DIR__ . $uri;
if ($uri !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false;
}

// Admin routes
if ($uri === '/admin' || $uri === '/admin/') {
    require __DIR__ . '/admin/index.php';
    exit;
}

if ($uri === '/admin/login' || $uri === '/admin/login.php') {
    require __DIR__ . '/admin/login.php';
    exit;
}

if ($uri === '/admin/reports' || $uri === '/admin/reports.php') {
    require __DIR__ . '/admin/reports.php';
    exit;
}

if ($uri === '/admin/reports/create') {
    require __DIR__ . '/admin/report_form.php';
    exit;
}

if (preg_match('#^/admin/reports/edit/(\d+)$#', $uri, $matches)) {
    $_GET['id'] = $matches[1];
    require __DIR__ . '/admin/report_form.php';
    exit;
}

if ($uri === '/admin/logout' || $uri === '/admin/logout.php') {
    require __DIR__ . '/admin/logout.php';
    exit;
}

// Report routes
if ($uri === '/report' || $uri === '/reports') {
    require __DIR__ . '/report.php';
    exit;
}

if (preg_match('#^/report/([a-zA-Z0-9_-]+)$#', $uri, $matches)) {
    $_GET['slug'] = $matches[1];
    require __DIR__ . '/report_detail.php';
    exit;
}

// Default root - Arahkan ke dashboard utama
if ($uri === '/' || $uri === '' || $uri === '/index.php') {
    header('Location: /index.html');
    exit;
}

return false;

