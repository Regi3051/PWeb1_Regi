// admin_session.js - Handles global admin session across all portfolio pages
(function() {
    document.addEventListener("DOMContentLoaded", function() {
        fetch('/api/check_session.php')
            .then(res => res.json())
            .then(data => {
                if (data && data.logged_in) {
                    // Validasi: jika website sebelumnya ditutup, hapus sesi
                    if (sessionStorage.getItem('admin_tab_active') !== '1') {
                        fetch('/admin/logout.php').then(() => {
                            // Session di-reset karena website baru dibuka kembali setelah ditutup
                        });
                        return;
                    }
                    initAdminMode(data);
                }
            })
            .catch(() => {
                // If API not reachable (e.g. running statically without server), fail silently
            });
    });

    function initAdminMode(admin) {
        // 1. Inject Admin Top Bar
        const adminBar = document.createElement('div');
        adminBar.id = 'admin-global-topbar';
        adminBar.innerHTML = `
            <div style="background-color: #99744a; color: #2b332b; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 0.85rem; font-weight: 600; padding: 6px 16px; position: fixed; top: 0; left: 0; right: 0; z-index: 1060; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="background-color: #2b332b; color: #dbc2a6; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; text-transform: uppercase;">
                        <i class="bi bi-shield-check me-1"></i> Admin
                    </span>
                    <span class="d-none d-sm-inline">Masuk sebagai: <strong>${admin.name || admin.username}</strong></span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <a href="/admin/report_form.php" style="background-color: #2b332b; color: #dbc2a6; border-radius: 15px; padding: 3px 12px; text-decoration: none; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="bi bi-plus-lg"></i> Tambah Laporan
                    </a>
                    <a href="/admin/reports.php" style="background-color: #2b332b; color: #dbc2a6; border-radius: 15px; padding: 3px 12px; text-decoration: none; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="bi bi-gear-fill"></i> Panel Laporan
                    </a>
                    <a href="/admin/logout.php" onclick="sessionStorage.removeItem('admin_tab_active');" style="background-color: #dc3545; color: #fff; border-radius: 15px; padding: 3px 10px; text-decoration: none; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        `;
        document.body.prepend(adminBar);

        // Adjust fixed-top navbar offset for the admin bar
        const navbar = document.querySelector('.navbar.fixed-top');
        if (navbar) {
            navbar.style.top = '34px';
        }
        document.body.style.paddingTop = '34px';

        // 2. Enhance Report Page if present
        enhanceReportPage();

        // 3. Enhance Detail Laprak Page if present
        enhanceDetailPage();
    }

    function enhanceReportPage() {
        const reportSection = document.querySelector('.report-section .container');
        if (!reportSection) return;

        // Add "+ Tambah Laporan" button above filters if not already added
        if (!document.getElementById('admin-add-report-btn')) {
            const addBtnWrapper = document.createElement('div');
            addBtnWrapper.id = 'admin-add-report-btn';
            addBtnWrapper.className = 'text-center mb-4';
            addBtnWrapper.innerHTML = `
                <a href="/admin/report_form.php" class="btn" style="background-color: #99744a; color: #2b332b; font-weight: bold; border-radius: 25px; padding: 10px 24px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">
                    <i class="bi bi-plus-circle-fill fs-5"></i> Tambah Card Laporan Baru
                </a>
            `;
            const controlFilter = document.querySelector('.report-section .control');
            if (controlFilter) {
                controlFilter.parentNode.insertBefore(addBtnWrapper, controlFilter);
            }
        }

        // Add Edit & Delete buttons to existing report cards
        const cards = document.querySelectorAll('.report-card');
        cards.forEach(card => {
            if (card.querySelector('.admin-card-actions')) return;

            const readMoreBtn = card.querySelector('.btn-report');
            const targetHref = readMoreBtn ? readMoreBtn.getAttribute('href') : '';
            
            // Extract id or slug from href
            let editUrl = '/admin/reports.php';
            if (targetHref.includes('slug=')) {
                const slugMatch = targetHref.match(/slug=([^&]+)/);
                if (slugMatch) editUrl = `/admin/reports.php`;
            } else if (targetHref.includes('Laprak_AppMob-1.html')) {
                editUrl = '/admin/report_form.php?id=2';
            } else if (targetHref.includes('Laprak_AppMob-2.html')) {
                editUrl = '/admin/report_form.php?id=3';
            } else if (targetHref.includes('Laprak_AppMob-3.html')) {
                editUrl = '/admin/report_form.php?id=4';
            } else if (targetHref.includes('Laprak_AppMob-4.html')) {
                editUrl = '/admin/report_form.php?id=5';
            } else if (targetHref.includes('laporan_6.html')) {
                editUrl = '/admin/report_form.php?id=1';
            }

            const badgeRow = card.querySelector('.badge-pertemuan')?.parentElement;
            if (badgeRow && !badgeRow.querySelector('.admin-badge-edit')) {
                const editBadge = document.createElement('a');
                editBadge.className = 'admin-badge-edit';
                editBadge.href = editUrl;
                editBadge.innerHTML = `<i class="bi bi-pencil-square me-1"></i>Edit`;
                editBadge.style.cssText = 'color: #99744a; font-weight: bold; font-size: 0.85rem; text-decoration: none; padding: 2px 8px; border: 1px solid #99744a; border-radius: 12px; transition: 0.2s;';
                editBadge.onmouseover = () => { editBadge.style.backgroundColor = '#99744a'; editBadge.style.color = '#2b332b'; };
                editBadge.onmouseout = () => { editBadge.style.backgroundColor = 'transparent'; editBadge.style.color = '#99744a'; };
                badgeRow.appendChild(editBadge);
            }
        });
    }

    function enhanceDetailPage() {
        const isLaprak = window.location.pathname.includes('Laprak_AppMob') || 
                         window.location.pathname.includes('laporan_6') || 
                         window.location.pathname.includes('report_detail');
        if (!isLaprak) return;

        if (!document.getElementById('floating-admin-edit')) {
            let editLink = '/admin/reports.php';
            const path = window.location.pathname;
            if (path.includes('Laprak_AppMob-1')) editLink = '/admin/report_form.php?id=2';
            else if (path.includes('Laprak_AppMob-2')) editLink = '/admin/report_form.php?id=3';
            else if (path.includes('Laprak_AppMob-3')) editLink = '/admin/report_form.php?id=4';
            else if (path.includes('Laprak_AppMob-4')) editLink = '/admin/report_form.php?id=5';
            else if (path.includes('laporan_6')) editLink = '/admin/report_form.php?id=1';

            const floatBtn = document.createElement('a');
            floatBtn.id = 'floating-admin-edit';
            floatBtn.href = editLink;
            floatBtn.innerHTML = `<i class="bi bi-pencil-square fs-5"></i> <span>Edit Laporan Ini</span>`;
            floatBtn.style.cssText = `
                position: fixed;
                bottom: 25px;
                right: 25px;
                z-index: 1050;
                background-color: #99744a;
                color: #2b332b;
                font-weight: bold;
                padding: 12px 22px;
                border-radius: 30px;
                box-shadow: 0 6px 20px rgba(0,0,0,0.4);
                border: 2px solid #dbc2a6;
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 8px;
                transition: 0.25s;
            `;
            floatBtn.onmouseover = () => { floatBtn.style.transform = 'scale(1.05)'; floatBtn.style.backgroundColor = '#dbc2a6'; };
            floatBtn.onmouseout = () => { floatBtn.style.transform = 'scale(1)'; floatBtn.style.backgroundColor = '#99744a'; };
            document.body.appendChild(floatBtn);
        }
    }
})();

