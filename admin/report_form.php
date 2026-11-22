<?php
// admin/report_form.php - Tambah & Edit Laporan Praktikum (Multi-Blok Langkah)
require_once __DIR__ . '/auth.php';
require_admin_login();

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$isEdit = false;
$report = [
    'id' => '',
    'title' => '',
    'sub_title' => '',
    'slug' => '',
    'category' => 'Praktikum Aplikasi Mobile',
    'week_tag' => 'Praktikum 1',
    'description' => '',
    'cover_image' => '',
    'blocks_json' => '[]',
    'images_json' => '[]',
    'social_links_json' => '[]',
    'status' => 'published'
];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if ($existing) {
        $report = $existing;
        $isEdit = true;
    }
}

// Prepare initial blocks data
$initialBlocks = [];
if (!empty($report['blocks_json']) && $report['blocks_json'] !== '[]') {
    $initialBlocks = json_decode($report['blocks_json'], true) ?: [];
} elseif (!empty($report['images_json']) && $report['images_json'] !== '[]') {
    // Fallback migration from images_json if blocks_json empty
    $imgs = json_decode($report['images_json'], true) ?: [];
    foreach ($imgs as $idx => $img) {
        $initialBlocks[] = [
            'sub_title' => $img['caption'] ?? ('Langkah ' . ($idx + 1)),
            'description' => ($idx === 0 && !empty($report['description'])) ? $report['description'] : '',
            'images' => !empty($img['url']) ? [$img['url']] : [],
            'layout' => 'vertical'
        ];
    }
}

if (empty($initialBlocks)) {
    $initialBlocks = [
        [
            'sub_title' => '',
            'description' => '',
            'images' => [],
            'layout' => 'vertical'
        ]
    ];
}

// Ensure each block has 'images' (array) and 'layout'
foreach ($initialBlocks as &$b) {
    if (!isset($b['images'])) {
        $b['images'] = !empty($b['image_url']) ? [$b['image_url']] : [];
    }
    if (!isset($b['layout'])) {
        $b['layout'] = 'vertical';
    }
}
unset($b);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit Laporan' : 'Tambah Laporan' ?> | Admin Panel</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="/style.css" />
    <style>
        body {
            background-color: #2b332b;
            color: #dbc2a6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding-bottom: 5rem;
        }
        .admin-topbar {
            padding: 1.5rem 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-pill {
            background-color: transparent;
            color: #dbc2a6;
            border: 1.5px solid #99744a;
            border-radius: 25px;
            padding: 7px 18px;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: 0.25s;
        }
        .btn-pill:hover {
            background-color: #99744a;
            color: #2b332b;
        }
        .breadcrumb-admin {
            font-size: 0.85rem;
            color: #aeb4a2;
            letter-spacing: 1.5px;
            font-weight: 700;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }
        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: #dbc2a6;
            margin-bottom: 0.3rem;
        }
        .page-subtitle {
            color: #aeb4a2;
            font-size: 1rem;
            margin-bottom: 2rem;
        }
        .form-card {
            background-color: #414a37;
            border: 2px solid #99744a;
            border-radius: 16px;
            padding: 2.2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin-bottom: 2rem;
        }
        .card-inner-header h4 {
            color: #dbc2a6;
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 4px;
        }
        .card-inner-header p {
            color: #aeb4a2;
            font-size: 0.9rem;
            margin-bottom: 1.8rem;
        }
        .form-label {
            color: #dbc2a6;
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 6px;
        }
        .form-control, .form-select {
            background-color: #2b332b;
            border: 1.5px solid #99744a;
            color: #dbc2a6;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.95rem;
        }
        .form-control:focus, .form-select:focus {
            background-color: #2b332b;
            color: #fff;
            border-color: #dbc2a6;
            box-shadow: 0 0 10px rgba(153, 116, 74, 0.4);
        }
        .form-help-text {
            color: #aeb4a2;
            font-size: 0.8rem;
            margin-top: 4px;
        }

        /* Step Card / Blok Styling in Admin Form */
        .step-block-card {
            background-color: #2b332b;
            border: 2px solid #99744a;
            border-radius: 14px;
            padding: 22px;
            margin-bottom: 22px;
            position: relative;
            box-shadow: 0 6px 16px rgba(0,0,0,0.25);
            transition: 0.3s;
        }
        .step-block-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(153, 116, 74, 0.35);
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .step-number-badge {
            background-color: #99744a;
            color: #2b332b;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .step-block-title {
            color: #dbc2a6;
            font-weight: 700;
            font-size: 1.15rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Layout Selector Buttons */
        .btn-layout-active {
            background-color: #99744a !important;
            color: #2b332b !important;
            font-weight: 700 !important;
            border: 1.5px solid #dbc2a6 !important;
        }
        .btn-layout-inactive {
            background-color: #2b332b;
            color: #dbc2a6;
            border: 1.5px solid #99744a;
            font-weight: 500;
        }
        .btn-layout-inactive:hover {
            background-color: rgba(153, 116, 74, 0.35);
            color: #fff;
        }
        .block-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 14px;
            margin-top: 10px;
            margin-bottom: 12px;
            justify-content: center;
        }
        .block-images-grid.layout-preview-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
            justify-content: center;
        }
        .block-images-grid.layout-preview-horizontal {
            display: flex;
            gap: 14px;
            overflow-x: auto;
            justify-content: center;
            align-items: center;
            padding-bottom: 10px;
            scrollbar-width: thin;
            scrollbar-color: #99744a #2b332b;
        }
        .block-images-grid.layout-preview-horizontal::before,
        .block-images-grid.layout-preview-horizontal::after {
            content: '';
            margin: auto;
        }
        .block-image-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            border: 1.5px solid #99744a;
            background-color: #2b332b;
            box-shadow: 0 4px 10px rgba(0,0,0,0.25);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 6px;
        }
        .block-image-item img {
            width: auto;
            max-width: 100%;
            height: auto;
            max-height: 180px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
            border-radius: 4px;
        }
        .block-image-item .btn-remove-thumb {
            position: absolute;
            top: 4px;
            right: 4px;
            background-color: rgba(220, 53, 69, 0.9);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            cursor: pointer;
            transition: 0.2s;
        }
        .block-image-item .btn-remove-thumb:hover {
            background-color: #dc3545;
            transform: scale(1.1);
        }

        /* Upload Box per Blok */
        .block-upload-box {
            border: 2px dashed #99744a;
            border-radius: 12px;
            padding: 22px 16px;
            text-align: center;
            background-color: rgba(65, 74, 55, 0.25);
            cursor: pointer;
            transition: all 0.25s ease-in-out;
            position: relative;
        }
        .block-upload-box:hover {
            border-color: #dbc2a6;
            background-color: rgba(65, 74, 55, 0.55);
            transform: translateY(-2px);
        }
        .block-upload-box.dragover {
            border-color: #dbc2a6 !important;
            background-color: rgba(153, 116, 74, 0.45) !important;
            border-style: solid !important;
            box-shadow: 0 0 20px rgba(219, 194, 166, 0.4);
            transform: scale(1.02);
        }
        .block-preview-img {
            max-height: 220px;
            width: 100%;
            object-fit: cover;
            border-radius: 8px;
            border: 1.5px solid #99744a;
            margin-top: 10px;
            display: none;
        }

        /* Social Link Items */
        .social-link-item {
            background-color: #2b332b;
            border: 1.5px solid #99744a;
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }
        .social-icon-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: #414a37;
            border: 1.5px solid #99744a;
            color: #dbc2a6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        /* Action Buttons */
        .btn-add-block {
            background-color: transparent;
            color: #dbc2a6;
            border: 2px dashed #99744a;
            border-radius: 12px;
            padding: 14px;
            font-weight: 700;
            font-size: 1rem;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: 0.25s;
            margin-top: 10px;
            cursor: pointer;
        }
        .btn-add-block:hover {
            background-color: #99744a;
            color: #2b332b;
        }
        .btn-submit-main {
            background-color: transparent;
            color: #dbc2a6;
            border: 2px solid #99744a;
            border-radius: 30px;
            padding: 12px;
            font-weight: 700;
            font-size: 1rem;
            width: 100%;
            transition: 0.3s;
            margin-bottom: 12px;
        }
        .btn-submit-main:hover {
            background-color: #99744a;
            color: #2b332b;
        }
        .btn-cancel-main {
            background-color: transparent;
            color: #aeb4a2;
            border: 2px solid rgba(174, 180, 162, 0.4);
            border-radius: 30px;
            padding: 12px;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            transition: 0.3s;
        }
        .btn-cancel-main:hover {
            background-color: rgba(174, 180, 162, 0.2);
            color: #dbc2a6;
        }

        /* Modal Custom */
        .modal-content-custom {
            background-color: #414a37;
            color: #dbc2a6;
            border: 2px solid #99744a;
            border-radius: 16px;
        }
        .modal-header-custom {
            border-bottom: 1px solid rgba(153, 116, 74, 0.35);
        }
        .modal-footer-custom {
            border-top: 1px solid rgba(153, 116, 74, 0.35);
        }
    </style>
</head>
<body>

    <div class="container py-4" style="max-width: 860px;">
        
        <!-- Topbar Navigation -->
        <div class="admin-topbar">
            <a href="javascript:void(0)" onclick="openCancelConfirmModal()" class="btn-pill">
                <i class="bi bi-arrow-left"></i> Back to Reports
            </a>
            <a href="/admin/logout.php" onclick="sessionStorage.removeItem('admin_tab_active');" class="btn-pill" style="border-color: #e0d5c1; color: #e0d5c1;">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>

        <!-- Breadcrumb & Header -->
        <div class="breadcrumb-admin">ADMIN / REPORTS / <?= $isEdit ? 'EDIT' : 'TAMBAH' ?></div>
        <h1 class="page-title"><?= $isEdit ? 'Edit Laporan' : 'Tambah Laporan' ?></h1>
        <p class="page-subtitle"><?= $isEdit ? 'Perbarui informasi laporan dan blok-blok langkah praktikum.' : 'Buat laporan baru dengan melengkapi informasi utama, tautan media sosial, dan blok-blok langkah praktikum.' ?></p>

        <!-- Form Card Container -->
        <div class="form-card">
            
            <form id="reportForm" autocomplete="off">
                <input type="hidden" id="reportId" value="<?= htmlspecialchars($report['id']) ?>" />

                <!-- BAGIAN 1: INFORMASI UTAMA & JUDUL BESAR (HANYA 1 KALI) -->
                <div class="card-inner-header">
                    <h4>1. Informasi Utama Laporan</h4>
                    <p>Judul utama laporan praktikum dibuat 1 kali untuk keseluruhan dokumen laporan.</p>
                </div>

                <!-- Input Judul Besar -->
                <div class="mb-3">
                    <label class="form-label" for="title">Judul Laporan (Judul Besar) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" placeholder="Contoh: Laporan Praktikum 3 Aplikasi Mobile" value="<?= htmlspecialchars($report['title']) ?>" required />
                </div>

                <div class="row">
                    <!-- Kategori -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="category">Kategori</label>
                        <input type="text" class="form-control" id="category" placeholder="Contoh: Praktikum Aplikasi Mobile" value="<?= htmlspecialchars($report['category']) ?>" />
                    </div>

                    <!-- Week / Pekan -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="week_tag">Week / Pekan</label>
                        <input type="text" class="form-control" id="week_tag" placeholder="Contoh: Praktikum 3 / Pekan 3" value="<?= htmlspecialchars($report['week_tag']) ?>" />
                    </div>
                </div>

                <!-- Slug URL -->
                <div class="mb-3">
                    <label class="form-label" for="slug">Slug URL</label>
                    <input type="text" class="form-control" id="slug" placeholder="praktikum-3-aplikasi-mobile" value="<?= htmlspecialchars($report['slug']) ?>" />
                    <div class="form-help-text">Kosongkan jika ingin dibuat otomatis dari Judul Besar.</div>
                </div>

                <!-- Status Publikasi -->
                <div class="mb-4">
                    <label class="form-label" for="status">Status Publikasi</label>
                    <select class="form-select" id="status">
                        <option value="published" <?= $report['status'] === 'published' ? 'selected' : '' ?>>Published (Ditampilkan ke Publik)</option>
                        <option value="draft" <?= $report['status'] === 'draft' ? 'selected' : '' ?>>Draft (Hanya Admin)</option>
                    </select>
                </div>

                <!-- Cover Image (Opsional untuk Card Thumbnail) -->
                <div class="mb-4">
                    <label class="form-label">Cover Image (Thumbnail Kartu Laporan)</label>
                    <div class="block-upload-box" 
                         id="coverUploadBox" 
                         onclick="document.getElementById('coverInput').click()"
                         ondragover="handleDragOver(event)"
                         ondragenter="handleDragOver(event)"
                         ondragleave="handleDragLeave(event)"
                         ondrop="handleCoverDrop(event)">
                        <i class="bi bi-cloud-arrow-up fs-2 mb-1 d-block" style="color: #99744a;"></i>
                        <span id="coverUploadText" style="color: #dbc2a6; font-weight: 600;">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Drag &amp; Drop gambar cover di sini, atau klik untuk memilih file
                        </span>
                        <div class="form-help-text">JPG, PNG, atau WEBP. Sistem otomatis meresize gambar.</div>
                    </div>
                    <input type="file" id="coverInput" accept="image/*" style="display: none;" onchange="handleCoverUpload(event)" />
                    <input type="hidden" id="cover_image" value="<?= htmlspecialchars($report['cover_image']) ?>" />
                    
                    <img id="coverPreview" class="block-preview-img" src="<?= !empty($report['cover_image']) ? '/' . ltrim($report['cover_image'], '/') : '' ?>" style="<?= !empty($report['cover_image']) ? 'display: block;' : '' ?>" alt="Cover Preview" />
                </div>

                <hr style="border-color: rgba(153, 116, 74, 0.4); margin: 2rem 0;" />

                <!-- BAGIAN 2: TOMBOL MEDIA SOSIAL (TERLETAK DI BAWAH JUDUL BESAR DI REPORT) -->
                <div class="card-inner-header">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h4 class="mb-0">2. Tombol Tautan Eksternal / Media Sosial</h4>
                        <button type="button" class="btn btn-sm btn-pill" onclick="addSocialLink()">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Link
                        </button>
                    </div>
                    <p>Tautan ini akan tampil sebagai tombol pill tepat <strong>di bawah Judul Besar</strong> (seperti tombol Repository GitHub pada laporan sebelumnya).</p>
                </div>

                <div id="socialLinksContainer" class="mb-4">
                    <!-- Populated by JS -->
                </div>

                <hr style="border-color: rgba(153, 116, 74, 0.4); margin: 2rem 0;" />

                <!-- BAGIAN 3: BLOK-BLOK LANGKAH LAPORAN (SUB JUDUL, DESKRIPSI, DAN GAMBAR PER BLOK) -->
                <div class="card-inner-header">
                    <h4>3. Blok-Blok Langkah Praktikum (Per Blok)</h4>
                    <p>Tambahkan setiap langkah praktikum dalam satu blok terpadu yang memuat <strong>Sub Judul</strong>, <strong>Deskripsi</strong>, dan <strong>Upload Gambar Screenshot</strong> (persis seperti tampilan step-card report sebelumnya).</p>
                </div>

                <div id="blocksContainer">
                    <!-- Populated by JS: Each block has Sub Judul, Deskripsi, and Gambar -->
                </div>

                <!-- Tombol Tambah Blok Baru -->
                <button type="button" class="btn-add-block" onclick="addNewBlock()">
                    <i class="bi bi-plus-circle-fill fs-5"></i> + Tambah Blok / Langkah Praktikum Baru
                </button>

                <!-- BAGIAN 4: TOMBOL AKSI SIMPAN EDIT & CANCEL EDIT -->
                <div class="mt-5 pt-3">
                    <button type="button" class="btn-submit-main" onclick="openSaveConfirmModal()">
                        <i class="bi bi-check-lg me-1"></i> <?= $isEdit ? 'Simpan Edit' : 'Buat Laporan' ?>
                    </button>
                    <button type="button" class="btn-cancel-main" onclick="openCancelConfirmModal()">
                        <i class="bi bi-x-lg me-1"></i> Cancel Edit
                    </button>
                </div>

            </form>
        </div>

    </div>

    <!-- MODAL 1: Konfirmasi SIMPAN -->
    <div class="modal fade" id="saveConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" style="color: #dbc2a6;">
                        <i class="bi bi-question-circle me-2" style="color: #99744a;"></i> Konfirmasi Simpan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="color: #e0d5c1;">
                    Apakah Anda ingin menyimpan seluruh blok data laporan ini sekarang? Data akan disimpan ke database dan Anda akan langsung diarahkan ke tampilan akhir laporan pengguna (*User View*).
                </div>
                <div class="modal-footer modal-footer-custom">
                    <button type="button" class="btn-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn-pill" id="btnExecuteSave" onclick="executeSaveReport()" style="background-color: #99744a; color: #2b332b; font-weight: bold;">
                        <i class="bi bi-check-circle me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Konfirmasi BATAL / CANCEL EDIT -->
    <div class="modal fade" id="cancelConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" style="color: #dbc2a6;">
                        <i class="bi bi-exclamation-triangle me-2" style="color: #e0d5c1;"></i> Konfirmasi Batal
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="color: #e0d5c1;">
                    Tutup formulir pengeditan? Pilihan <strong>"Batal"</strong> akan tetap mempertahankan seluruh ketikan data blok Anda di form ini tanpa menghilangkannya.
                </div>
                <div class="modal-footer modal-footer-custom">
                    <button type="button" class="btn-pill" data-bs-dismiss="modal">
                        Batal (Kembali ke Form)
                    </button>
                    <button type="button" onclick="cancelAndExit()" class="btn-pill" style="border-color: #dc3545; background-color: #dc3545; color: #fff;">
                        Keluar Tanpa Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // State variables
        let reportBlocks = <?= json_encode($initialBlocks) ?>;
        let socialLinks = <?= !empty($report['social_links_json']) ? $report['social_links_json'] : '[]' ?>;
        let newlyUploadedTempImages = [];
        let isReportSavedSuccessfully = false;

        // Auto Slug generation from Title
        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');
        let isSlugManuallyEdited = <?= !empty($report['slug']) ? 'true' : 'false' ?>;

        titleInput.addEventListener('input', () => {
            if (!isSlugManuallyEdited) {
                slugInput.value = titleInput.value
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)+/g, '');
            }
        });

        slugInput.addEventListener('input', () => {
            isSlugManuallyEdited = slugInput.value.trim().length > 0;
        });

        // Auto-Resize helper using HTML5 Canvas (Target Max 1280x850, quality 0.85)
        function resizeImage(file, maxWidth = 1280, maxHeight = 850, quality = 0.85) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = (e) => {
                    const img = new Image();
                    img.src = e.target.result;
                    img.onload = () => {
                        let width = img.width;
                        let height = img.height;

                        if (width > maxWidth || height > maxHeight) {
                            if (width / height > maxWidth / maxHeight) {
                                height = Math.round((height * maxWidth) / width);
                                width = maxWidth;
                            } else {
                                width = Math.round((width * maxHeight) / height);
                                height = maxHeight;
                            }
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        const dataUrl = canvas.toDataURL('image/jpeg', quality);
                        resolve(dataUrl);
                    };
                    img.onerror = reject;
                };
                reader.onerror = reject;
            });
        }

        // Drag and Drop global event handlers
        function handleDragOver(event) {
            event.preventDefault();
            event.stopPropagation();
            event.currentTarget.classList.add('dragover');
        }

        function handleDragLeave(event) {
            event.preventDefault();
            event.stopPropagation();
            event.currentTarget.classList.remove('dragover');
        }

        // Handle Cover Image Upload (Click & Drag-and-Drop)
        async function processCoverFile(file) {
            if (!file) return;
            const coverText = document.getElementById('coverUploadText');
            if (coverText) coverText.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengunggah cover...';

            try {
                const resizedBase64 = await resizeImage(file, 1200, 600, 0.88);
                const res = await fetch('/api/upload.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ base64: resizedBase64 })
                });
                const data = await res.json();
                if (data.success) {
                    document.getElementById('cover_image').value = data.url;
                    newlyUploadedTempImages.push(data.url);
                    const preview = document.getElementById('coverPreview');
                    preview.src = data.url;
                    preview.style.display = 'block';
                    if (coverText) coverText.innerHTML = '<i class="bi bi-check-circle me-1 text-success"></i> Cover berhasil diunggah (Klik / Drag untuk ganti)';
                } else {
                    alert('Upload cover gagal: ' + data.message);
                    if (coverText) coverText.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Drag &amp; Drop gambar cover di sini, atau klik untuk memilih file';
                }
            } catch (err) {
                console.error(err);
                alert('Gagal memproses gambar cover.');
                if (coverText) coverText.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Drag &amp; Drop gambar cover di sini, atau klik untuk memilih file';
            }
        }

        function handleCoverUpload(event) {
            if (event.target.files && event.target.files.length > 0) {
                processCoverFile(event.target.files[0]);
            }
        }

        function handleCoverDrop(event) {
            event.preventDefault();
            event.stopPropagation();
            event.currentTarget.classList.remove('dragover');
            if (event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files.length > 0) {
                const file = event.dataTransfer.files[0];
                if (file.type.startsWith('image/')) {
                    processCoverFile(file);
                } else {
                    alert('File yang diunggah harus berupa gambar (JPG, PNG, atau WEBP).');
                }
            }
        }

        // ----------------------------------------------------
        // MANAJEMEN BLOK LANGKAH PRAKTIKUM ("PER BLOK")
        // ----------------------------------------------------
        function addNewBlock() {
            reportBlocks.push({
                sub_title: '',
                description: '',
                images: [],
                layout: 'vertical'
            });
            renderBlocks();
            // Scroll to the newly added block
            const blockCards = document.querySelectorAll('.step-block-card');
            if (blockCards.length > 0) {
                blockCards[blockCards.length - 1].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        function removeBlock(index) {
            if (reportBlocks.length <= 1) {
                alert('Laporan harus memiliki minimal 1 blok langkah praktikum.');
                return;
            }
            if (confirm(`Hapus Blok Langkah #${index + 1}?`)) {
                reportBlocks.splice(index, 1);
                renderBlocks();
            }
        }

        function updateBlockField(index, field, value) {
            if (reportBlocks[index]) {
                reportBlocks[index][field] = value;
            }
        }

        function updateBlockLayout(index, layout) {
            if (reportBlocks[index]) {
                reportBlocks[index].layout = layout;
                renderBlocks();
            }
        }

        function removeBlockImage(blockIndex, imgIndex) {
            if (reportBlocks[blockIndex] && reportBlocks[blockIndex].images) {
                const removedUrl = reportBlocks[blockIndex].images[imgIndex];
                reportBlocks[blockIndex].images.splice(imgIndex, 1);
                if (reportBlocks[blockIndex].images.length <= 1) {
                    reportBlocks[blockIndex].layout = 'vertical';
                }
                renderBlocks();

                // Hapus langsung dari server jika file ini berstatus temporary
                if (removedUrl && removedUrl.includes('/uploads/temp/')) {
                    fetch('/api/cancel_upload.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ temp_images: [removedUrl] })
                    });
                    newlyUploadedTempImages = newlyUploadedTempImages.filter(u => u !== removedUrl);
                }
            }
        }

        // Upload Gambar per Blok (Mendukung Multi-Image Upload, Drag-and-Drop, dan Auto-Resize)
        async function processBlockFiles(index, fileList) {
            const files = Array.from(fileList).filter(f => f.type.startsWith('image/'));
            if (!files.length) {
                alert('Silakan pilih atau jatuhkan file gambar yang valid.');
                return;
            }

            const uploadStatus = document.getElementById(`upload-status-${index}`);
            if (uploadStatus) uploadStatus.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengunggah ' + files.length + ' gambar...';

            try {
                if (!reportBlocks[index].images) {
                    reportBlocks[index].images = [];
                }

                for (const file of files) {
                    // Auto-resize gambar langkah (1280x850 proporsional)
                    const resizedBase64 = await resizeImage(file, 1280, 850, 0.85);

                    const res = await fetch('/api/upload.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ base64: resizedBase64 })
                    });
                    const data = await res.json();

                    if (data.success) {
                        reportBlocks[index].images.push(data.url);
                        newlyUploadedTempImages.push(data.url);
                    } else {
                        alert('Gagal mengunggah gambar: ' + (data.message || 'Error'));
                    }
                }

                if (!reportBlocks[index].layout) {
                    reportBlocks[index].layout = 'vertical';
                }

                renderBlocks();
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat memproses gambar.');
                if (uploadStatus) uploadStatus.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Pilih Gambar Screenshot';
            }
        }

        function handleBlockImageUpload(index, event) {
            if (event.target.files && event.target.files.length > 0) {
                processBlockFiles(index, event.target.files);
                event.target.value = '';
            }
        }

        function handleBlockDrop(index, event) {
            event.preventDefault();
            event.stopPropagation();
            event.currentTarget.classList.remove('dragover');
            if (event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files.length > 0) {
                processBlockFiles(index, event.dataTransfer.files);
            }
        }

        function renderBlocks() {
            const container = document.getElementById('blocksContainer');
            container.innerHTML = '';

            reportBlocks.forEach((block, index) => {
                // Pastikan format data memiliki array images dan layout
                if (!block.images) {
                    block.images = block.image_url ? [block.image_url] : [];
                    delete block.image_url;
                }
                const images = block.images || [];
                const currentLayout = block.layout || 'vertical';
                const hasMultipleImages = images.length > 1;

                // Thumbnail Grid Gambar (Mengikuti tata letak terpilih, tidak memotong gambar dan posisi center)
                let thumbsHtml = '';
                if (images.length > 0) {
                    let layoutClass = '';
                    if (hasMultipleImages) {
                        if (currentLayout === 'grid') layoutClass = 'layout-preview-grid';
                        else if (currentLayout === 'horizontal') layoutClass = 'layout-preview-horizontal';
                    }

                    thumbsHtml = `<div class="block-images-grid ${layoutClass}">` +
                        images.map((url, imgIdx) => `
                            <div class="block-image-item">
                                <img src="${url}" alt="Screenshot ${imgIdx + 1}" />
                                <button type="button" class="btn-remove-thumb" onclick="removeBlockImage(${index}, ${imgIdx})" title="Hapus gambar ini">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        `).join('') +
                    `</div>`;
                }

                // Opsi Tata Letak Gambar (Hanya muncul jika gambar > 1)
                let layoutSelectorHtml = '';
                if (hasMultipleImages) {
                    layoutSelectorHtml = `
                        <div class="mb-3 p-3 rounded" style="background: rgba(0,0,0,0.2); border: 1.5px solid rgba(153, 116, 74, 0.5);">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                <span class="fw-bold" style="color: #dbc2a6; font-size: 0.9rem;">
                                    <i class="bi bi-grid-3x2-gap me-1"></i> Opsi Tata Letak Gambar (${images.length} Gambar):
                                </span>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn ${currentLayout === 'vertical' ? 'btn-layout-active' : 'btn-layout-inactive'}" onclick="updateBlockLayout(${index}, 'vertical')">
                                        <i class="bi bi-distribute-vertical me-1"></i> Vertikal
                                    </button>
                                    <button type="button" class="btn ${currentLayout === 'grid' ? 'btn-layout-active' : 'btn-layout-inactive'}" onclick="updateBlockLayout(${index}, 'grid')">
                                        <i class="bi bi-grid-fill me-1"></i> Grid
                                    </button>
                                    <button type="button" class="btn ${currentLayout === 'horizontal' ? 'btn-layout-active' : 'btn-layout-inactive'}" onclick="updateBlockLayout(${index}, 'horizontal')">
                                        <i class="bi bi-distribute-horizontal me-1"></i> Horizontal
                                    </button>
                                </div>
                            </div>
                            <div class="form-help-text" style="font-size: 0.8rem; margin: 0;">
                                <i class="bi bi-info-circle me-1"></i>
                                ${currentLayout === 'vertical' ? 'Mode Vertikal: Gambar ditampilkan berjajar ke bawah bersusun penuh.' : (currentLayout === 'grid' ? 'Mode Grid: Gambar disusun rapi 2 kolom berdampingan.' : 'Mode Horizontal: Gambar disusun menyamping (slider horizontal yang bisa di-scroll).')}
                            </div>
                        </div>
                    `;
                }

                const blockDiv = document.createElement('div');
                blockDiv.className = 'step-block-card';
                blockDiv.innerHTML = `
                    <div class="step-block-header">
                        <div class="step-block-title">
                            <span class="step-number-badge">${index + 1}</span>
                            <span>Langkah / Blok #${index + 1}</span>
                        </div>
                        ${reportBlocks.length > 1 ? `
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeBlock(${index})" title="Hapus Blok Ini">
                                <i class="bi bi-trash me-1"></i> Hapus Blok
                            </button>
                        ` : ''}
                    </div>

                    <!-- 1. Input Sub Judul Blok -->
                    <div class="mb-3">
                        <label class="form-label">Sub Judul Langkah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" placeholder="Contoh: Mengimpor Material Design & Class Stateful / main.dart" value="${escapeHtml(block.sub_title || '')}" oninput="updateBlockField(${index}, 'sub_title', this.value)" required />
                        <div class="form-help-text">Judul dari langkah atau modul pada blok ini.</div>
                    </div>

                    <!-- 2. Input Deskripsi Blok -->
                    <div class="mb-3">
                        <label class="form-label">Deskripsi / Penjelasan Langkah</label>
                        <textarea class="form-control" rows="4" placeholder="Tuliskan penjelasan detail langkah, kode implementasi, atau fungsi yang dibuat..." oninput="updateBlockField(${index}, 'description', this.value)">${escapeHtml(block.description || '')}</textarea>
                        <div class="form-help-text">Uraian pembahasan praktikum untuk langkah ini.</div>
                    </div>

                    <!-- 3. Upload Gambar Screenshot per Blok (Multi-Upload & Layout) -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0">Upload Gambar Screenshot Kode / Output</label>
                            <span class="badge" style="background:#2b332b; color:#dbc2a6; border:1px solid #99744a; padding: 5px 10px; border-radius: 12px; font-size: 0.78rem;">
                                ${images.length} Gambar Terpilih
                            </span>
                        </div>

                        ${thumbsHtml}

                        ${layoutSelectorHtml}

                        <div class="block-upload-box mt-2" 
                             id="blockUploadBox-${index}"
                             onclick="document.getElementById('blockInput-${index}').click()"
                             ondragover="handleDragOver(event)"
                             ondragenter="handleDragOver(event)"
                             ondragleave="handleDragLeave(event)"
                             ondrop="handleBlockDrop(${index}, event)">
                            <i class="bi bi-cloud-arrow-up fs-2 mb-1 d-block" style="color: #99744a;"></i>
                            <span id="upload-status-${index}" style="color: #dbc2a6; font-weight: 600;">
                                <i class="bi bi-cloud-arrow-up me-1"></i> ${images.length > 0 ? '+ Drag &amp; Drop atau Klik untuk Tambah Gambar' : 'Drag &amp; Drop Gambar Screenshot di sini, atau Klik untuk Memilih'}
                            </span>
                            <div class="form-help-text">Mendukung Drag &amp; Drop satu atau banyak gambar sekaligus. Sistem otomatis menerapkan auto-resize agar seragam dan rapi.</div>
                        </div>

                        <input type="file" id="blockInput-${index}" accept="image/*" multiple style="display: none;" onchange="handleBlockImageUpload(${index}, event)" />
                    </div>
                `;
                container.appendChild(blockDiv);
            });
        }

        // ----------------------------------------------------
        // MANAJEMEN TOMBOL MEDIA SOSIAL / TAUTAN EKSTERNAL
        // ----------------------------------------------------
        function detectSocialIcon(url) {
            const u = url.toLowerCase();
            if (u.includes('github.com')) return 'bi bi-github';
            if (u.includes('youtube.com') || u.includes('youtu.be')) return 'bi bi-youtube';
            if (u.includes('instagram.com')) return 'bi bi-instagram';
            if (u.includes('linkedin.com')) return 'bi bi-linkedin';
            if (u.includes('wa.me') || u.includes('whatsapp.com')) return 'bi bi-whatsapp';
            if (u.includes('twitter.com') || u.includes('x.com')) return 'bi bi-twitter-x';
            if (u.includes('tiktok.com')) return 'bi bi-tiktok';
            if (u.includes('facebook.com')) return 'bi bi-facebook';
            if (u.includes('gitlab.com')) return 'bi bi-git';
            return 'bi bi-link-45deg';
        }

        function addSocialLink() {
            socialLinks.push({ url: '', label: 'Repository GitHub' });
            renderSocialLinks();
        }

        function updateSocialLink(index, field, val) {
            if (socialLinks[index]) {
                socialLinks[index][field] = val;
                if (field === 'url') {
                    const iconEl = document.getElementById('social-icon-' + index);
                    if (iconEl) iconEl.className = detectSocialIcon(val);
                }
            }
        }

        function removeSocialLink(index) {
            socialLinks.splice(index, 1);
            renderSocialLinks();
        }

        function renderSocialLinks() {
            const container = document.getElementById('socialLinksContainer');
            container.innerHTML = '';

            if (socialLinks.length === 0) {
                container.innerHTML = `
                    <div class="text-muted small py-2">
                        <i class="bi bi-link-45deg me-1"></i> Belum ada tombol media sosial. Klik <strong>"+ Tambah Link"</strong> untuk menambahkan tombol (misal: link GitHub Repository).
                    </div>
                `;
                return;
            }

            socialLinks.forEach((item, index) => {
                const iconClass = detectSocialIcon(item.url || '');
                const div = document.createElement('div');
                div.className = 'social-link-item';
                div.innerHTML = `
                    <div class="social-icon-circle">
                        <i id="social-icon-${index}" class="${iconClass}"></i>
                    </div>
                    <div class="flex-grow-1 row g-2">
                        <div class="col-md-7">
                            <input type="url" class="form-control form-control-sm" placeholder="https://github.com/..." value="${escapeHtml(item.url || '')}" oninput="updateSocialLink(${index}, 'url', this.value)" />
                        </div>
                        <div class="col-md-5">
                            <input type="text" class="form-control form-control-sm" placeholder="Label (misal: Repository GitHub)" value="${escapeHtml(item.label || '')}" oninput="updateSocialLink(${index}, 'label', this.value)" />
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm text-danger" onclick="removeSocialLink(${index})" title="Hapus link">
                        <i class="bi bi-trash fs-5"></i>
                    </button>
                `;
                container.appendChild(div);
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.innerText = text;
            return div.innerHTML;
        }

        // ----------------------------------------------------
        // ALUR KONFIRMASI SIMPAN & BATAL
        // ----------------------------------------------------
        let saveModalInstance = null;
        let cancelModalInstance = null;

        function openSaveConfirmModal() {
            const title = document.getElementById('title').value.trim();
            if (!title) {
                alert('Silakan masukkan Judul Besar Laporan terlebih dahulu.');
                document.getElementById('title').focus();
                return;
            }

            // Check if at least 1 block has sub_title
            const hasSubTitle = reportBlocks.some(b => b.sub_title && b.sub_title.trim().length > 0);
            if (!hasSubTitle) {
                alert('Silakan isi Sub Judul pada minimal satu Blok Langkah Praktikum.');
                return;
            }

            if (!saveModalInstance) {
                saveModalInstance = new bootstrap.Modal(document.getElementById('saveConfirmModal'));
            }
            saveModalInstance.show();
        }

        function openCancelConfirmModal() {
            if (!cancelModalInstance) {
                cancelModalInstance = new bootstrap.Modal(document.getElementById('cancelConfirmModal'));
            }
            cancelModalInstance.show();
        }

        // Eksekusi Simpan via AJAX
        async function executeSaveReport() {
            const saveBtn = document.getElementById('btnExecuteSave');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

            // First block description can also act as general description fallback
            const mainDescription = reportBlocks.length > 0 ? (reportBlocks[0].description || '') : '';

            const payload = {
                id: document.getElementById('reportId').value || null,
                title: document.getElementById('title').value.trim(),
                sub_title: reportBlocks.length > 0 ? (reportBlocks[0].sub_title || '') : '',
                slug: document.getElementById('slug').value.trim(),
                category: document.getElementById('category').value.trim(),
                week_tag: document.getElementById('week_tag').value.trim(),
                description: mainDescription,
                cover_image: document.getElementById('cover_image').value.trim(),
                status: document.getElementById('status').value || 'published',
                blocks: reportBlocks,
                social_links: socialLinks
            };

            try {
                const res = await fetch('/api/save_report.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();

                if (result.success) {
                    isReportSavedSuccessfully = true;
                    newlyUploadedTempImages = [];
                    saveModalInstance.hide();
                    // Redirect langsung ke tampilan akhir laporan pengguna
                    window.location.href = result.redirect_url;
                } else {
                    alert('Gagal menyimpan: ' + result.message);
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Simpan';
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan koneksi saat menyimpan laporan.');
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Simpan';
            }
        }

        // Batalkan & Hapus seluruh gambar temporary saat keluar tanpa simpan
        async function cancelAndExit() {
            if (newlyUploadedTempImages.length > 0) {
                try {
                    await fetch('/api/cancel_upload.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ temp_images: newlyUploadedTempImages })
                    });
                } catch (e) {
                    console.error(e);
                }
                newlyUploadedTempImages = [];
            }
            window.location.href = '/admin/reports.php';
        }

        // Listener jika tab atau halaman ditutup/di-close sebelum disimpan
        window.addEventListener('pagehide', function() {
            if (!isReportSavedSuccessfully && newlyUploadedTempImages.length > 0) {
                const payload = JSON.stringify({ temp_images: newlyUploadedTempImages });
                const blob = new Blob([payload], { type: 'application/json' });
                navigator.sendBeacon('/api/cancel_upload.php', blob);
            }
        });

        // Init page
        document.addEventListener('DOMContentLoaded', () => {
            renderBlocks();
            renderSocialLinks();
        });
    </script>
</body>
</html>
