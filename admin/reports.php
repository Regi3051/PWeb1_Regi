<?php
// admin/reports.php - Manage Reports List
require_once __DIR__ . '/auth.php';
require_admin_login();

// Fetch all reports
$stmt = $pdo->query("SELECT * FROM reports ORDER BY id DESC");
$reports = $stmt->fetchAll();
$totalReports = count($reports);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Daftar Laporan | Admin Panel</title>
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
        .btn-pill-logout {
            border-color: #e0d5c1;
            color: #e0d5c1;
        }
        .btn-pill-logout:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: #fff;
        }
        .page-title {
            font-size: 2.8rem;
            font-weight: 800;
            color: #dbc2a6;
            margin-top: 1rem;
            margin-bottom: 0.2rem;
            letter-spacing: -0.5px;
        }
        .section-subtitle {
            font-size: 1.6rem;
            font-weight: 700;
            color: #dbc2a6;
            margin-bottom: 0.2rem;
        }
        .report-count-text {
            color: #aeb4a2;
            font-size: 1rem;
            margin-bottom: 1.8rem;
        }
        .btn-add-report {
            background-color: transparent;
            color: #dbc2a6;
            border: 2px solid #99744a;
            border-radius: 30px;
            padding: 12px 24px;
            font-size: 1.05rem;
            font-weight: 600;
            width: 100%;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: 0.3s;
            margin-bottom: 2.5rem;
        }
        .btn-add-report:hover {
            background-color: #99744a;
            color: #2b332b;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.3);
        }
        .reports-container {
            background-color: #414a37;
            border: 2px solid #99744a;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
        }
        .reports-header-bar {
            background-color: #99744a;
            color: #2b332b;
            padding: 14px 20px;
            font-weight: 700;
            font-size: 1rem;
            display: flex;
            justify-content: space-between;
        }
        .report-item-row {
            padding: 18px 20px;
            border-bottom: 1px solid rgba(153, 116, 74, 0.35);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            transition: 0.2s;
        }
        .report-item-row:last-child {
            border-bottom: none;
        }
        .report-item-row:hover {
            background-color: rgba(43, 51, 43, 0.45);
        }
        .report-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
            min-width: 250px;
        }
        .report-thumb {
            width: 75px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            border: 1.5px solid #99744a;
            background-color: #2b332b;
            flex-shrink: 0;
        }
        .report-text h5 {
            color: #dbc2a6;
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .report-text h5 a {
            color: #dbc2a6;
            text-decoration: none;
            transition: 0.2s;
        }
        .report-text h5 a:hover {
            color: #99744a;
        }
        .report-meta {
            color: #aeb4a2;
            font-size: 0.85rem;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .badge-status {
            display: inline-block;
            padding: 5px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1.5px solid #99744a;
            text-transform: capitalize;
        }
        .badge-published {
            background-color: transparent;
            color: #dbc2a6;
        }
        .badge-draft {
            background-color: rgba(43, 51, 43, 0.7);
            color: #aeb4a2;
            border-color: #777;
        }
        .report-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-action {
            background-color: transparent;
            color: #dbc2a6;
            border: 1.5px solid #99744a;
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: 0.2s;
            cursor: pointer;
        }
        .btn-action:hover {
            background-color: #99744a;
            color: #2b332b;
        }
        .btn-action-delete {
            border-color: rgba(220, 53, 69, 0.7);
            color: #e0d5c1;
        }
        .btn-action-delete:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: #fff;
        }
        .modal-content-custom {
            background-color: #414a37;
            color: #dbc2a6;
            border: 2px solid #99744a;
            border-radius: 14px;
        }
        .modal-header-custom {
            border-bottom: 1px solid rgba(153, 116, 74, 0.3);
        }
        .modal-footer-custom {
            border-top: 1px solid rgba(153, 116, 74, 0.3);
        }
    </style>
</head>
<body>

    <div class="container py-4" style="max-width: 900px;">
        
        <!-- Top Navigation -->
        <div class="admin-topbar">
            <a href="/" class="btn-pill">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="/admin/logout.php" onclick="sessionStorage.removeItem('admin_tab_active');" class="btn-pill btn-pill-logout">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>

        <!-- Header Section -->
        <h1 class="page-title">Reports</h1>
        <div class="section-subtitle">Daftar Laporan</div>
        <div class="report-count-text"><?= $totalReports ?> laporan</div>

        <!-- Add Report Button -->
        <a href="/admin/report_form.php" class="btn-add-report">
            <i class="bi bi-plus-lg"></i> Tambah Laporan
        </a>

        <!-- Alert Notification -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 mb-4" role="alert" style="background-color: #99744a; color: #2b332b; font-weight: 600; border-radius: 10px;">
                <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($_GET['msg']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Reports List Box -->
        <div class="reports-container">
            <div class="reports-header-bar">
                <span>Status</span>
                <span>Aksi</span>
            </div>

            <?php if (empty($reports)): ?>
                <div class="text-center py-5" style="color: #aeb4a2;">
                    <i class="bi bi-journal-x fs-1 mb-2 d-block"></i>
                    Belum ada laporan. Klik tombol <strong>+ Tambah Laporan</strong> untuk membuat baru.
                </div>
            <?php else: ?>
                <?php foreach ($reports as $r): ?>
                    <?php 
                        $viewUrl = !empty($r['custom_link']) ? '/' . $r['custom_link'] : '/report_detail.php?slug=' . urlencode($r['slug']);
                        $cover = !empty($r['cover_image']) ? '/' . ltrim($r['cover_image'], '/') : '/img/carousel/coding1.jpg';
                    ?>
                    <div class="report-item-row">
                        <!-- Info -->
                        <div class="report-info">
                            <img src="<?= htmlspecialchars($cover) ?>" alt="Cover" class="report-thumb" onerror="this.src='/img/carousel/coding1.jpg'" />
                            <div class="report-text">
                                <h5>
                                    <a href="/admin/report_form.php?id=<?= $r['id'] ?>">
                                        <?= htmlspecialchars($r['title']) ?>
                                    </a>
                                </h5>
                                <div class="report-meta">
                                    <span><i class="bi bi-tag me-1"></i><?= htmlspecialchars($r['category']) ?></span>
                                    <span>&bull;</span>
                                    <span><i class="bi bi-bookmark me-1"></i><?= htmlspecialchars($r['week_tag']) ?></span>
                                    <span>&bull;</span>
                                    <span><i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($r['created_at'])) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Status & Actions -->
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge-status <?= $r['status'] === 'published' ? 'badge-published' : 'badge-draft' ?>">
                                <?= htmlspecialchars($r['status']) ?>
                            </span>

                            <div class="report-actions">
                                <a href="<?= htmlspecialchars($viewUrl) ?>" target="_blank" class="btn-action" title="Lihat Tampilan Pengguna">
                                    <i class="bi bi-eye"></i> Lihat
                                </a>
                                <a href="/admin/report_form.php?id=<?= $r['id'] ?>" class="btn-action" title="Edit Laporan">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <button type="button" class="btn-action btn-action-delete" onclick="confirmDelete(<?= $r['id'] ?>, '<?= htmlspecialchars(addslashes($r['title'])) ?>')" title="Hapus Laporan">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4">
            <a href="/report.php" target="_blank" class="text-decoration-none" style="color: #99744a; font-weight: 500;">
                <i class="bi bi-box-arrow-up-right me-1"></i> Buka Halaman Publik "Report"
            </a>
        </div>

    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" style="color: #dbc2a6;">Konfirmasi Hapus Laporan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="color: #e0d5c1;">
                    Apakah Anda yakin ingin menghapus laporan <strong id="deleteReportTitle" style="color: #dbc2a6;"></strong>? Tindakan ini tidak dapat dibatalkan.
                </div>
                <div class="modal-footer modal-footer-custom">
                    <button type="button" class="btn-pill" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="confirmDeleteBtn" class="btn-pill btn-pill-logout" style="border-color: #dc3545; background-color: #dc3545; color: #fff;">
                        <i class="bi bi-trash me-1"></i> Ya, Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(id, title) {
            document.getElementById('deleteReportTitle').innerText = '"' + title + '"';
            document.getElementById('confirmDeleteBtn').href = '/api/delete_report.php?id=' + id;
            var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
    </script>
</body>
</html>

