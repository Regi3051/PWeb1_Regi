<?php
// api/save_report.php - Save or update report
require_once __DIR__ . '/../admin/auth.php';
require_admin_login();

header('Content-Type: application/json; charset=utf-8');

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!$data) {
    $data = $_POST;
}

$id = !empty($data['id']) ? intval($data['id']) : null;
$title = trim($data['title'] ?? '');
$subTitle = trim($data['sub_title'] ?? '');
$slug = trim($data['slug'] ?? '');
$category = trim($data['category'] ?? 'Praktikum Aplikasi Mobile');
$weekTag = trim($data['week_tag'] ?? 'Praktikum');
$description = trim($data['description'] ?? '');
$coverImage = trim($data['cover_image'] ?? '');
$layoutType = in_array($data['layout_type'] ?? '', ['vertical', 'grid', 'slider']) ? $data['layout_type'] : 'vertical';
$status = in_array($data['status'] ?? '', ['published', 'draft']) ? $data['status'] : 'published';

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Judul laporan tidak boleh kosong.']);
    exit;
}

// Generate slug if empty
if (empty($slug)) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
    if (empty($slug)) {
        $slug = 'report-' . time();
    }
} else {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slug), '-'));
}

// Ensure unique slug
$checkSlugStmt = $pdo->prepare("SELECT id FROM reports WHERE slug = ? AND id != ?");
$checkSlugStmt->execute([$slug, $id ?? 0]);
if ($checkSlugStmt->fetch()) {
    $slug .= '-' . time();
}

// Helper function to promote temporary uploaded image to permanent storage
function promoteTempImage($url) {
    if (empty($url) || !is_string($url) || strpos($url, '/uploads/temp/') === false) {
        return $url;
    }
    $filename = basename($url);
    $cleanFilename = str_replace('img_temp_', 'img_', $filename);
    $tempPath = __DIR__ . '/../uploads/temp/' . $filename;
    $permPath = __DIR__ . '/../uploads/' . $cleanFilename;
    if (file_exists($tempPath) && is_file($tempPath)) {
        if (!is_dir(dirname($permPath))) {
            @mkdir(dirname($permPath), 0777, true);
        }
        rename($tempPath, $permPath);
        return '/uploads/' . $cleanFilename;
    }
    return $url;
}

// Promote cover_image if it is in temp
$coverImage = promoteTempImage($coverImage);

// Process blocks_json
$blocks = $data['blocks'] ?? [];
if (is_string($blocks)) {
    $blocksArray = json_decode($blocks, true) ?: [];
} else {
    $blocksArray = $blocks;
}

// Promote all temporary images inside blocks to permanent storage
if (!empty($blocksArray) && is_array($blocksArray)) {
    foreach ($blocksArray as &$b) {
        if (!empty($b['images']) && is_array($b['images'])) {
            foreach ($b['images'] as &$imgUrl) {
                $imgUrl = promoteTempImage($imgUrl);
            }
            unset($imgUrl);
        }
        if (!empty($b['image_url'])) {
            $b['image_url'] = promoteTempImage($b['image_url']);
        }
    }
    unset($b);
}
$blocksJson = json_encode($blocksArray);

// Process images_json (sync from blocks or direct)
$images = $data['images'] ?? [];
if (empty($images) && !empty($blocksArray)) {
    $images = [];
    foreach ($blocksArray as $b) {
        if (!empty($b['images']) && is_array($b['images'])) {
            foreach ($b['images'] as $imgUrl) {
                if (!empty($imgUrl)) {
                    $images[] = [
                        'url' => $imgUrl,
                        'caption' => $b['sub_title'] ?? ''
                    ];
                }
            }
        } elseif (!empty($b['image_url'])) {
            $images[] = [
                'url' => $b['image_url'],
                'caption' => $b['sub_title'] ?? ''
            ];
        }
    }
}
$imagesJson = is_string($images) ? $images : json_encode($images);

// Process social_links_json
$socialLinks = $data['social_links'] ?? [];
if (is_string($socialLinks)) {
    $socialLinksJson = $socialLinks;
} else {
    $socialLinksJson = json_encode($socialLinks);
}

try {
    if ($id) {
        // Update existing report
        $updateStmt = $pdo->prepare("UPDATE reports SET 
            title = ?,
            sub_title = ?,
            slug = ?,
            category = ?,
            week_tag = ?,
            description = ?,
            cover_image = ?,
            images_json = ?,
            blocks_json = ?,
            layout_type = ?,
            social_links_json = ?,
            status = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ?");
        
        $updateStmt->execute([
            $title,
            $subTitle,
            $slug,
            $category,
            $weekTag,
            $description,
            $coverImage,
            $imagesJson,
            $blocksJson,
            $layoutType,
            $socialLinksJson,
            $status,
            $id
        ]);
        $reportId = $id;
    } else {
        // Insert new report
        $insertStmt = $pdo->prepare("INSERT INTO reports 
            (title, sub_title, slug, category, week_tag, description, cover_image, images_json, blocks_json, layout_type, social_links_json, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $insertStmt->execute([
            $title,
            $subTitle,
            $slug,
            $category,
            $weekTag,
            $description,
            $coverImage,
            $imagesJson,
            $blocksJson,
            $layoutType,
            $socialLinksJson,
            $status
        ]);
        $reportId = $pdo->lastInsertId();
    }

    $redirectUrl = '/report_detail.php?slug=' . urlencode($slug);

    echo json_encode([
        'success' => true,
        'message' => 'Laporan berhasil disimpan.',
        'id' => $reportId,
        'slug' => $slug,
        'redirect_url' => $redirectUrl
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . $e->getMessage()]);
}

