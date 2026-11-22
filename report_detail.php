<?php
// report_detail.php - User View for Lab Reports (Multi-Block Step Cards)
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/admin/auth.php';

$slug = $_GET['slug'] ?? '';
$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE id = ?");
    $stmt->execute([$id]);
} elseif ($slug) {
    $stmt = $pdo->prepare("SELECT * FROM reports WHERE slug = ?");
    $stmt->execute([$slug]);
} else {
    header('Location: /report.php');
    exit;
}

$report = $stmt->fetch();

if (!$report) {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>Laporan Tidak Ditemukan</h1><p><a href='/report.php'>Kembali ke Daftar Laporan</a></p>";
    exit;
}

$isAdmin = is_admin_logged_in();

// Parse blocks & fallback
$blocks = [];
if (!empty($report['blocks_json']) && $report['blocks_json'] !== '[]') {
    $blocks = json_decode($report['blocks_json'], true) ?: [];
} elseif (!empty($report['images_json']) && $report['images_json'] !== '[]') {
    $imgs = json_decode($report['images_json'], true) ?: [];
    foreach ($imgs as $idx => $img) {
        $blocks[] = [
            'sub_title' => $img['caption'] ?? ('Langkah ' . ($idx + 1)),
            'description' => ($idx === 0 && !empty($report['description'])) ? $report['description'] : '',
            'image_url' => $img['url'] ?? ''
        ];
    }
}

$socialLinks = json_decode($report['social_links_json'] ?: '[]', true) ?: [];

function getSocialIconClass($url) {
    $u = strtolower($url);
    if (strpos($u, 'github.com') !== false) return 'bi-github';
    if (strpos($u, 'youtube.com') !== false || strpos($u, 'youtu.be') !== false) return 'bi-youtube';
    if (strpos($u, 'instagram.com') !== false) return 'bi-instagram';
    if (strpos($u, 'linkedin.com') !== false) return 'bi-linkedin';
    if (strpos($u, 'wa.me') !== false || strpos($u, 'whatsapp.com') !== false) return 'bi-whatsapp';
    if (strpos($u, 'twitter.com') !== false || strpos($u, 'x.com') !== false) return 'bi-twitter-x';
    if (strpos($u, 'tiktok.com') !== false) return 'bi-tiktok';
    if (strpos($u, 'facebook.com') !== false) return 'bi-facebook';
    if (strpos($u, 'gitlab.com') !== false) return 'bi-git';
    return 'bi-link-45deg';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($report['title']) ?> - Portfolio Regi</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="/style.css" />

    <style>
        body {
            background-color: #2b332b;
            color: #dbc2a6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .report-hero {
            margin-top: 5rem;
            padding: 4rem 0 2rem 0;
            text-align: center;
        }
        .report-hero h1 {
            font-weight: bold;
            font-size: 2.5rem;
            color: #dbc2a6;
            margin-bottom: 0.5rem;
        }
        .report-hero .badge {
            background-color: #99744a;
            color: #2b332b;
            font-size: 0.9rem;
            padding: 8px 18px;
            border-radius: 20px;
            margin-bottom: 15px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-hero p {
            color: #aeb4a2;
            margin-top: 5px;
            font-size: 0.95rem;
        }
        .content-wrapper {
            max-width: 900px;
            margin: 0 auto;
            padding-bottom: 5rem;
        }
        .btn-laporan {
            background-color: transparent;
            color: #dbc2a6;
            font-weight: bold;
            border: 2px solid #99744a;
            padding: 8px 24px;
            border-radius: 25px;
            transition: 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 5px;
        }
        .btn-laporan:hover {
            background-color: #99744a;
            color: #2b332b;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        /* Step cards (Persis seperti tampilan Laprak sebelumnya) */
        .step-card {
            background-color: #414a37;
            border: 1px solid #99744a;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .step-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(219, 194, 166, 0.2);
            padding-bottom: 15px;
        }
        .step-number {
            background-color: #99744a;
            color: #2b332b;
            width: 35px;
            height: 35px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 1.2rem;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .step-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #dbc2a6;
            margin: 0;
        }
        .step-body {
            color: #e0d5c1;
            line-height: 1.8;
            font-size: 1rem;
        }
        .step-body p {
            margin-bottom: 15px;
        }
        .step-image {
            max-width: 100%;
            height: auto;
            max-height: 550px;
            border-radius: 8px;
            border: 2px solid #2b332b;
            margin: 15px auto;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            object-fit: contain;
            display: block;
        }

        /* Horizontal Gallery - Selalu Posisikan Center dari Blog */
        .horizontal-scroll-center {
            display: flex;
            gap: 16px;
            overflow-x: auto;
            padding-bottom: 12px;
            align-items: center;
            justify-content: center;
            scrollbar-width: thin;
            scrollbar-color: #99744a #2b332b;
            width: 100%;
        }
        .horizontal-scroll-center::before,
        .horizontal-scroll-center::after {
            content: '';
            margin: auto;
        }

        /* Floating Admin Edit Button */
        .floating-edit-btn {
            position: fixed;
            bottom: 25px;
            right: 25px;
            z-index: 1050;
            background-color: #99744a;
            color: #2b332b;
            font-weight: bold;
            padding: 12px 24px;
            border-radius: 30px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.4);
            border: 2px solid #dbc2a6;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }
        .floating-edit-btn:hover {
            background-color: #dbc2a6;
            color: #414a37;
            transform: scale(1.05);
        }
    </style>
</head>
<body>

    <!-- Floating Admin Edit Badge (Jika Admin Sedang Login) -->
    <?php if ($isAdmin): ?>
        <a href="/admin/report_form.php?id=<?= $report['id'] ?>" class="floating-edit-btn" title="Edit Laporan">
            <i class="bi bi-pencil-square fs-5"></i>
            <span>Edit Laporan Ini</span>
        </a>
    <?php endif; ?>

    <!-- Navbar -->
    <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/index.html">Regi Heksa</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link px-lg-4" href="/index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link px-lg-4" href="/about.html">About Me</a></li>
                    <li class="nav-item"><a class="nav-link px-lg-4" href="/portofolio.html">Portfolio</a></li>
                    <li class="nav-item"><a class="nav-link active px-lg-4" aria-current="page" href="/report.php">Report</a></li>
                    <li class="nav-item"><a class="nav-link px-lg-4" href="/contact.html">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- End Navbar -->

    <!-- HERO SECTION: KATEGORI, JUDUL BESAR, PENULIS, DAN TOMBOL MEDIA SOSIAL DI BAWAHNYA -->
    <section class="report-hero container">
        <span class="badge"><?= htmlspecialchars($report['week_tag'] ?: 'Praktikum') ?> &bull; <?= htmlspecialchars($report['category']) ?></span>
        <h1><?= htmlspecialchars($report['title']) ?></h1>
        <p><i class="bi bi-person"></i> Regi Heksa Ananda</p>

        <!-- 5. Tombol Media Sosial (Terletak di bawah Judul Besar seperti pada tampilan Report sebelumnya) -->
        <?php if (!empty($socialLinks)): ?>
            <div class="mt-4 d-flex justify-content-center gap-2 flex-wrap">
                <?php foreach ($socialLinks as $link): ?>
                    <?php 
                        $url = $link['url'] ?? '#';
                        $label = !empty($link['label']) ? $link['label'] : 'Repository GitHub';
                        $icon = getSocialIconClass($url);
                    ?>
                    <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener noreferrer" class="btn-laporan">
                        <i class="bi <?= $icon ?> me-2"></i><?= htmlspecialchars($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- CONTENT SECTION: BLOK-BLOK LANGKAH PRAKTIKUM (SUB JUDUL, DESKRIPSI, DAN GAMBAR PER BLOK) -->
    <section class="container content-wrapper">
        
        <div class="text-center mb-4">
            <h2 style="color: #dbc2a6; font-weight: bold; border-bottom: 2px solid #99744a; padding-bottom: 10px; display: inline-block;">
                A. Langkah Praktikum
            </h2>
        </div>

        <?php if (!empty($blocks)): ?>
            <?php foreach ($blocks as $idx => $block): ?>
                <div class="step-card">
                    <!-- 2. Sub Judul per Blok -->
                    <div class="step-header">
                        <div class="step-number"><?= $idx + 1 ?></div>
                        <h3 class="step-title"><?= htmlspecialchars($block['sub_title'] ?? ('Langkah ' . ($idx + 1))) ?></h3>
                    </div>

                    <div class="step-body">
                        <!-- 3. Deskripsi per Blok -->
                        <?php if (!empty($block['description'])): ?>
                            <p><?= nl2br(htmlspecialchars($block['description'])) ?></p>
                        <?php endif; ?>

                        <!-- 4. Upload Gambar per Blok (Mendukung Multi-Gambar & Tata Letak: Vertikal, Grid, Horizontal) -->
                        <?php 
                        $blockImages = $block['images'] ?? (!empty($block['image_url']) ? [$block['image_url']] : []);
                        $blockLayout = $block['layout'] ?? 'vertical';
                        ?>
                        <?php if (!empty($blockImages)): ?>
                            <?php if ($blockLayout === 'grid' && count($blockImages) > 1): ?>
                                <!-- Layout Grid (2 Kolom): Gambar Utuh Tidak Terpotong & Posisi Center dari Blog -->
                                <div class="row g-4 my-3 justify-content-center align-items-center">
                                    <?php foreach ($blockImages as $imgIdx => $imgUrl): ?>
                                        <div class="col-md-6 d-flex justify-content-center align-items-center">
                                            <div class="w-100 p-2 text-center" style="border-radius:10px; border:2px solid #2b332b; box-shadow:0 4px 12px rgba(0,0,0,0.3); background-color: rgba(43, 51, 43, 0.7); display:flex; justify-content:center; align-items:center;">
                                                <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($block['sub_title'] ?? 'Screenshot') ?> (<?= $imgIdx + 1 ?>)" style="max-width:100%; height:auto; max-height:480px; object-fit:contain; border-radius:6px; display:block; margin:0 auto;" />
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php elseif ($blockLayout === 'horizontal' && count($blockImages) > 1): ?>
                                <!-- Layout Horizontal: Slider Berdampingan dengan Posisi Center dari Blog -->
                                <div class="horizontal-scroll-center my-3 pb-2">
                                    <?php foreach ($blockImages as $imgIdx => $imgUrl): ?>
                                        <div style="flex: 0 0 auto; max-width: 85%; border-radius:10px; overflow:hidden; border:2px solid #2b332b; box-shadow:0 4px 12px rgba(0,0,0,0.3); background-color: rgba(43, 51, 43, 0.7); display:flex; justify-content:center; align-items:center; padding:8px;">
                                            <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($block['sub_title'] ?? 'Screenshot') ?> (<?= $imgIdx + 1 ?>)" style="height:340px; width:auto; max-width:100%; object-fit:contain; border-radius:6px; display:block; margin:0 auto;" />
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <!-- Layout Vertikal (Default: Berjejer ke bawah & Posisi Center) -->
                                <div class="text-center my-2">
                                    <?php foreach ($blockImages as $imgIdx => $imgUrl): ?>
                                        <div class="my-3 p-2 text-center" style="border-radius:10px; border:2px solid #2b332b; box-shadow:0 4px 12px rgba(0,0,0,0.3); background-color: rgba(43, 51, 43, 0.7); display:inline-block; max-width:100%;">
                                            <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($block['sub_title'] ?? 'Screenshot') ?> (<?= $imgIdx + 1 ?>)" style="max-width:100%; height:auto; max-height:550px; object-fit:contain; border-radius:6px; display:block; margin:0 auto;" />
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5" style="color: #aeb4a2;">
                Belum ada blok langkah praktikum yang ditambahkan.
            </div>
        <?php endif; ?>

        <!-- Tombol Kembali ke Daftar Laporan -->
        <div class="text-center mt-5">
            <a href="/report.php" class="btn-laporan" style="display: inline-block; width: auto;">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Laporan
            </a>
        </div>

    </section>

    <!-- Section Footer -->
    <footer class="footer pb-3 text-center" style="background-color: #2b332b; border-top: 1px solid #414a37;">
        <div class="container pt-4">
            <div class="social-icons-footer mb-3">
                <a href="https://www.linkedin.com/in/regi-heksa-462906320/"><i class="bi bi-linkedin" style="color: #dbc2a6;"></i></a>
                <a href="https://github.com/Regi3051/"><i class="bi bi-github" style="color: #dbc2a6;"></i></a>
                <a href="https://wa.me/+6281261265154"><i class="bi bi-whatsapp" style="color: #dbc2a6;"></i></a>
                <a href="https://www.instagram.com/_kur00__/"><i class="bi bi-instagram" style="color: #dbc2a6;"></i></a>
            </div>
            <p style="color: #aeb4a2;"><small>© 2026 Regi Heksa Ananda. All rights reserved.</small></p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/admin_session.js"></script>
</body>
</html>
