
PROYEK AKHIR DESAIN WEB - REGI HEKSA ANANDA

DESKRIPSI PROYEK
Ini adalah website portofolio pribadi statis yang dirancang untuk menampilkan profil, 
keahlian, dan karya digital dari mahasiswa. 
Website ini dibangun dengan fokus pada desain responsif, interaktivitas pengguna, 
dan estetika modern menggunakan palet warna bumi (earth tone).

FITUR UTAMA
1. Navigasi Multi-Halaman:
   - Home: Landing page dengan hero section dan ringkasan layanan.
   - About Me: Profil detail dengan komponen Accordion untuk riwayat pendidikan & skill.
   - Portfolio: Galeri karya dengan fitur pemfilteran kategori interaktif.
   - Contact: Formulir kontak fungsional (simulasi) dan integrasi peta.

2. Galeri & Filter (Portfolio):
   - Menggunakan Vanilla JavaScript (tanpa jQuery) untuk memfilter item berdasarkan kategori:
     [All], [Programming], [Design & Editing], [Photography].
   - Mendukung tampilan Modal (Pop-up) untuk detail setiap item proyek.

3. Formulir Kontak Interaktif:
   - Validasi input HTML5.
   - Simulasi status "Loading" saat pengiriman pesan.
   - Alert notifikasi sukses otomatis setelah pengiriman (via JavaScript).

4. Desain Responsif:
   - Menggunakan Bootstrap 5 Grid System agar kompatibel di Desktop, Tablet, dan Mobile.
   - Komponen Navbar yang kolapsibel (Hamburger menu) pada layar kecil.

TEKNOLOGI YANG DIGUNAKAN
1. Bahasa Utama:
   - HTML5 (Semantik)
   - CSS3 (Custom Styling & Animations)
   - JavaScript (ES6+ Syntax)

2. Framework & Library:
   - Bootstrap v5.0.2 (CSS & JS Bundle)
   - Bootstrap Icons v1.13.1

3. Font & Aset:
   - Google Fonts (via Bootstrap default stack).
   - Ikon vektor SVG untuk separator antar bagian (Wave effect).

STRUKTUR FILE
root/
│
├── index.html          # Halaman Utama
├── about.html          # Halaman Tentang Saya (Profil)
├── portofolio.html     # Halaman Galeri Karya (Filter Logic)
├── contact.html        # Halaman Kontak
├── style.css           # File CSS Utama (Global Styles)
├── script.js           # File JS untuk logika Form Kontak
├── README.txt          # Dokumentasi Proyek
│
└── img/                # Folder Penyimpanan Gambar
    ├── carousel/       # Gambar untuk slide/galeri portfolio
    └── imgregi/        # Foto profil personal

PALET WARNA (DESIGN SYSTEM)
Berdasarkan file style.css:
- Hijau Gelap (Primary)   : #414a37 (Digunakan untuk Navbar, Footer, Button)
- Coklat (Accent)         : #99744a (Digunakan untuk Hover, Elemen Dekoratif)
- Cream (Background/Text) : #dbc2a6 (Digunakan untuk Card, Teks Kontras)

CARA MENJALANKAN (INSTALASI)
1. Pastikan struktur folder sesuai dengan daftar di atas.
2. Koneksi internet diperlukan untuk memuat CDN Bootstrap (CSS/JS) dan Ikon.
3. Buka file `index.html` menggunakan browser modern (Chrome, Edge, Firefox).

PENULIS
Nama  : Regi Heksa Ananda
Prodi : Informatika (Universitas Andalas)
Email : heksa9012@gmail.com

© 2025 Regi Heksa Ananda. All rights reserved.