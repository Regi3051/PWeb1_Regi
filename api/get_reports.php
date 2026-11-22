<?php
// api/get_reports.php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $stmt = $pdo->query("SELECT id, title, sub_title, slug, category, week_tag, description, cover_image, custom_link, created_at FROM reports WHERE status = 'published' ORDER BY id DESC");
    $reports = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $reports]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

