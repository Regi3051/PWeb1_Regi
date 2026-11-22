<?php
// api/delete_report.php - Menghapus laporan beserta seluruh file gambar terkait dari website dan database
require_once __DIR__ . '/../admin/auth.php';
require_admin_login();

$id = $_GET['id'] ?? null;
if ($id) {
    // 1. Ambil data laporan terlebih dahulu untuk menemukan seluruh file gambar yang terhubung
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ?");
    $stmt->execute([$id]);
    $report = $stmt->fetch();

    if ($report) {
        $filesToDelete = [];

        // Kumpulkan cover image
        if (!empty($report['cover_image'])) {
            $filesToDelete[] = $report['cover_image'];
        }

        // Kumpulkan gambar dari blocks_json
        if (!empty($report['blocks_json'])) {
            $blocks = json_decode($report['blocks_json'], true) ?: [];
            foreach ($blocks as $b) {
                if (!empty($b['images']) && is_array($b['images'])) {
                    foreach ($b['images'] as $imgUrl) {
                        if (!empty($imgUrl)) {
                            $filesToDelete[] = $imgUrl;
                        }
                    }
                }
                if (!empty($b['image_url'])) {
                    $filesToDelete[] = $b['image_url'];
                }
            }
        }

        // Kumpulkan gambar dari images_json (sinkronisasi legacy)
        if (!empty($report['images_json'])) {
            $imgs = json_decode($report['images_json'], true) ?: [];
            foreach ($imgs as $item) {
                if (!empty($item['url'])) {
                    $filesToDelete[] = $item['url'];
                }
            }
        }

        // Hapus file fisik unik yang berada di dalam folder uploads/
        $filesToDelete = array_unique($filesToDelete);
        $uploadsDir = realpath(__DIR__ . '/../uploads');

        foreach ($filesToDelete as $url) {
            if (is_string($url)) {
                // Hanya hapus file yang diunggah ke uploads/ (hindari menyentuh template statis di img/imglaprak/)
                if (preg_match('#^/?uploads/(?:temp/)?([a-zA-Z0-9_\.-]+)$#', $url, $matches)) {
                    $relativePath = ltrim($url, '/');
                    $fullPath = realpath(__DIR__ . '/../' . $relativePath);

                    // Pastikan file benar-benar berada di dalam folder uploads
                    if ($fullPath && $uploadsDir && strpos($fullPath, $uploadsDir) === 0 && is_file($fullPath)) {
                        @unlink($fullPath);
                    }
                }
            }
        }

        // 2. Hapus data laporan dari database
        $delStmt = $pdo->prepare("DELETE FROM reports WHERE id = ?");
        $delStmt->execute([$id]);
    }
}

header('Location: /admin/reports.php?msg=' . urlencode('Laporan dan seluruh data/gambar terkait berhasil dihapus dari website.'));
exit;
