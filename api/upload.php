<?php
// api/upload.php - Temporary image upload handler with auto-resize support
require_once __DIR__ . '/../admin/auth.php';
require_admin_login();

header('Content-Type: application/json; charset=utf-8');

// Folder uploads dan uploads/temp
$uploadDir = __DIR__ . '/../uploads/';
$tempDir = __DIR__ . '/../uploads/temp/';

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}
if (!is_dir($tempDir)) {
    @mkdir($tempDir, 0777, true);
}

// Garbage collection otomatis: bersihkan file di uploads/temp yang lebih dari 2 jam
$oldFiles = glob($tempDir . '*');
if ($oldFiles) {
    $now = time();
    foreach ($oldFiles as $f) {
        if (is_file($f) && ($now - filemtime($f) > 7200)) {
            @unlink($f);
        }
    }
}

// 1. Upload via multipart/form-data
if (!empty($_FILES['image'])) {
    $file = $_FILES['image'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Upload error code: ' . $file['error']]);
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau WebP.']);
        exit;
    }

    $filename = 'img_temp_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
    $targetPath = $tempDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode([
            'success' => true,
            'url' => '/uploads/temp/' . $filename,
            'filename' => $filename,
            'is_temp' => true
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memindahkan file ke folder temporary.']);
        exit;
    }
}

// 2. Upload via base64 (dari HTML5 Canvas auto-resize)
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!empty($data['base64'])) {
    $base64 = $data['base64'];
    if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
        $type = strtolower($matches[1]);
        $base64Data = substr($base64, strpos($base64, ',') + 1);
        $decoded = base64_decode($base64Data);

        if ($decoded === false) {
            echo json_encode(['success' => false, 'message' => 'Gagal mendecode data base64 gambar.']);
            exit;
        }

        $ext = ($type === 'jpeg') ? 'jpg' : $type;
        $filename = 'img_temp_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $tempDir . $filename;

        if (file_put_contents($targetPath, $decoded)) {
            echo json_encode([
                'success' => true,
                'url' => '/uploads/temp/' . $filename,
                'filename' => $filename,
                'is_temp' => true
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan file hasil resize ke folder temporary.']);
            exit;
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Tidak ada file gambar yang dikirim.']);
exit;
