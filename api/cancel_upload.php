<?php
// api/cancel_upload.php - Menghapus file gambar temporary saat laporan dibatalkan atau ditutup
require_once __DIR__ . '/../admin/auth.php';

header('Content-Type: application/json; charset=utf-8');

// Ambil input dari JSON payload atau fetch/sendBeacon
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data && !empty($_POST['temp_images'])) {
    $data = ['temp_images' => json_decode($_POST['temp_images'], true) ?: []];
}

$tempImages = $data['temp_images'] ?? [];
$deleted = [];

if (!empty($tempImages) && is_array($tempImages)) {
    $tempDir = realpath(__DIR__ . '/../uploads/temp');
    
    foreach ($tempImages as $url) {
        if (is_string($url) && strpos($url, '/uploads/temp/') !== false) {
            $filename = basename($url);
            $targetPath = __DIR__ . '/../uploads/temp/' . $filename;
            
            // Keamanan path traversal: pastikan file berada di dalam folder uploads/temp
            $realPath = realpath($targetPath);
            if ($realPath && $tempDir && strpos($realPath, $tempDir) === 0 && is_file($realPath)) {
                @unlink($realPath);
                $deleted[] = $filename;
            }
        }
    }
}

echo json_encode([
    'success' => true,
    'message' => 'File temporary berhasil dibersihkan.',
    'deleted' => $deleted
]);
exit;
