<?php
// config/db.php - Database connection and initialization

$dbFile = __DIR__ . '/../data/portfolio.db';
$dataDir = dirname($dbFile);

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create admins table
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        name TEXT DEFAULT 'Administrator',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Create reports table
    $pdo->exec("CREATE TABLE IF NOT EXISTS reports (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        sub_title TEXT DEFAULT '',
        slug TEXT UNIQUE NOT NULL,
        category TEXT NOT NULL DEFAULT 'Aplikasi Mobile',
        week_tag TEXT DEFAULT 'Praktikum 1',
        description TEXT DEFAULT '',
        cover_image TEXT DEFAULT '',
        images_json TEXT DEFAULT '[]',
        blocks_json TEXT DEFAULT '[]',
        layout_type TEXT DEFAULT 'vertical',
        social_links_json TEXT DEFAULT '[]',
        status TEXT DEFAULT 'published',
        custom_link TEXT DEFAULT '',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Ensure blocks_json column exists for existing database files
    try {
        $pdo->exec("ALTER TABLE reports ADD COLUMN blocks_json TEXT DEFAULT '[]'");
    } catch (Exception $e) {
        // Column already exists
    }

    // Seed default admin if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM admins");
    $adminCount = $stmt->fetch()['count'];
    if ($adminCount == 0) {
        $defaultUser = 'Regi3051';
        $defaultPass = password_hash('Heksa_3051', PASSWORD_BCRYPT);
        $insertAdmin = $pdo->prepare("INSERT INTO admins (username, password_hash, name) VALUES (?, ?, ?)");
        $insertAdmin->execute([$defaultUser, $defaultPass, 'Regi Heksa Ananda']);
    }

    // Seed default reports if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM reports");
    $reportCount = $stmt->fetch()['count'];
    if ($reportCount == 0) {
        $initialReports = [
            [
                'title' => 'Konfigurasi & Pengenalan Framework Laravel',
                'sub_title' => 'Instalasi, Konfigurasi & Arsitektur MVC Laravel',
                'slug' => 'laporan-6-laravel',
                'category' => 'Praktikum Web',
                'week_tag' => 'Praktikum 6',
                'description' => 'Laravel adalah framework PHP open-source yang populer dengan arsitektur MVC (Model-View-Controller). Praktikum ini membahas instalasi tools, pembuatan proyek baru, hingga menjalankan server lokal menggunakan Laravel.',
                'cover_image' => 'img/imglaprak/laprak_6/04.png',
                'images_json' => json_encode([
                    ['url' => 'img/imglaprak/laprak_6/01.png', 'caption' => 'Composer dan PHP Version'],
                    ['url' => 'img/imglaprak/laprak_6/02.png', 'caption' => 'Instalasi Laravel via Composer'],
                    ['url' => 'img/imglaprak/laprak_6/03.png', 'caption' => 'Struktur Folder Proyek Laravel'],
                    ['url' => 'img/imglaprak/laprak_6/04.png', 'caption' => 'Menjalankan php artisan serve']
                ]),
                'layout_type' => 'vertical',
                'social_links_json' => json_encode([
                    ['platform' => 'github', 'url' => 'https://github.com/Regi3051/', 'label' => 'GitHub Repository']
                ]),
                'status' => 'published',
                'custom_link' => 'laporan_6.html'
            ],
            [
                'title' => 'Laporan Praktikum 1 - Aplikasi Mobile',
                'sub_title' => 'Implementasi Basic Form & Validasi TextFormField Flutter',
                'slug' => 'laprak-appmob-1',
                'category' => 'Praktikum Aplikasi Mobile',
                'week_tag' => 'Praktikum 1',
                'description' => 'Praktikum ini membahas pembuatan basic form TextField dan TextFormField di Flutter beserta implementasi validasi form, format email, dan password matching.',
                'cover_image' => 'img/imglaprak/laprak_AppMob_1/a.1-output.png',
                'images_json' => json_encode([
                    ['url' => 'img/imglaprak/laprak_AppMob_1/a.1-output.png', 'caption' => 'Output Basic Form'],
                    ['url' => 'img/imglaprak/laprak_AppMob_1/b.1-output.png', 'caption' => 'Output Form TextFormField'],
                    ['url' => 'img/imglaprak/laprak_AppMob_1/Latihan.3-output_fail.png', 'caption' => 'Validasi Error'],
                    ['url' => 'img/imglaprak/laprak_AppMob_1/Latihan.3-output_success.png', 'caption' => 'Validasi Berhasil']
                ]),
                'layout_type' => 'vertical',
                'social_links_json' => json_encode([
                    ['platform' => 'github', 'url' => 'https://github.com/Regi3051/', 'label' => 'Source Code GitHub']
                ]),
                'status' => 'published',
                'custom_link' => 'Laprak_AppMob-1.html'
            ],
            [
                'title' => 'Laporan Praktikum 2 - Aplikasi Mobile',
                'sub_title' => 'Widget Dasar Flutter, Layouting & State Management',
                'slug' => 'laprak-appmob-2',
                'category' => 'Praktikum Aplikasi Mobile',
                'week_tag' => 'Praktikum 2',
                'description' => 'Praktikum ini membahas tentang pengenalan widget dasar Flutter seperti Scaffold, Row, Column, State Management dasar, dan Layouting dengan SingleChildScrollView.',
                'cover_image' => 'img/imglaprak/laprak_AppMob_2/widget/6-Widget-OutputShowSaldo.png',
                'images_json' => json_encode([
                    ['url' => 'img/imglaprak/laprak_AppMob_2/widget/6-Widget-OutputShowSaldo.png', 'caption' => 'Output Saldo'],
                    ['url' => 'img/imglaprak/laprak_AppMob_2/widget/9-Widget-OutputLatihan1.png', 'caption' => 'Output Latihan 1']
                ]),
                'layout_type' => 'grid',
                'social_links_json' => json_encode([
                    ['platform' => 'github', 'url' => 'https://github.com/Regi3051/', 'label' => 'Source Code']
                ]),
                'status' => 'published',
                'custom_link' => 'Laprak_AppMob-2.html'
            ],
            [
                'title' => 'Laporan Praktikum 3 - Aplikasi Mobile',
                'sub_title' => 'Form Transaksi, Dropdown & DatePicker Flutter',
                'slug' => 'laprak-appmob-3',
                'category' => 'Praktikum Aplikasi Mobile',
                'week_tag' => 'Praktikum 3',
                'description' => 'Praktikum ini membahas pembuatan UI form transaksi dengan TextFormField dan Dropdown, validasi, penggunaan DatePicker, serta pengaturan State Management untuk menangkap input.',
                'cover_image' => 'img/imglaprak/laprak_AppMob_3/9-OutputPraktikum.png',
                'images_json' => json_encode([
                    ['url' => 'img/imglaprak/laprak_AppMob_3/9-OutputPraktikum.png', 'caption' => 'Output Praktikum 3'],
                    ['url' => 'img/imglaprak/laprak_AppMob_3/13-OutputLatihan1.png', 'caption' => 'Output Latihan']
                ]),
                'layout_type' => 'slider',
                'social_links_json' => json_encode([
                    ['platform' => 'github', 'url' => 'https://github.com/Regi3051/', 'label' => 'Source Code']
                ]),
                'status' => 'published',
                'custom_link' => 'Laprak_AppMob-3.html'
            ],
            [
                'title' => 'Laporan Praktikum 4 - Aplikasi Mobile',
                'sub_title' => 'Navigasi Antarlayar & Passing Data di Flutter',
                'slug' => 'laprak-appmob-4',
                'category' => 'Praktikum Aplikasi Mobile',
                'week_tag' => 'Praktikum 4',
                'description' => 'Praktikum ini membahas tentang navigasi antarlayar (Navigator.push, Navigator.pop), passing data antar halaman, dan integrasi DatePicker pada form input.',
                'cover_image' => 'img/imglaprak/laprak_AppMob_4/OutputPraktikum-1.png',
                'images_json' => json_encode([
                    ['url' => 'img/imglaprak/laprak_AppMob_4/OutputPraktikum-1.png', 'caption' => 'Output Praktikum 4 - Layar 1'],
                    ['url' => 'img/imglaprak/laprak_AppMob_4/OutputPraktikum-2.png', 'caption' => 'Output Praktikum 4 - Layar 2']
                ]),
                'layout_type' => 'vertical',
                'social_links_json' => json_encode([
                    ['platform' => 'github', 'url' => 'https://github.com/Regi3051/', 'label' => 'Source Code']
                ]),
                'status' => 'published',
                'custom_link' => 'Laprak_AppMob-4.html'
            ]
        ];

        $insertReport = $pdo->prepare("INSERT INTO reports 
            (title, sub_title, slug, category, week_tag, description, cover_image, images_json, layout_type, social_links_json, status, custom_link)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($initialReports as $r) {
            $insertReport->execute([
                $r['title'],
                $r['sub_title'],
                $r['slug'],
                $r['category'],
                $r['week_tag'],
                $r['description'],
                $r['cover_image'],
                $r['images_json'],
                $r['layout_type'],
                $r['social_links_json'],
                $r['status'],
                $r['custom_link']
            ]);
        }
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

