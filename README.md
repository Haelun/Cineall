# Cineall-Project
Web project for Web Programming 

CineAll is an online streaming platform that allows users to watch movies and TV shows easily in one place. The website is designed with a user-friendly interface, providing fast access to entertainment content across various genres. CineAll aims to deliver a convenient, accessible, and enjoyable streaming experience for users anytime and anywhere.

## Group's Member:
1. LALU AHMAD FAIZ HAQIQI (F1D02410117)     -- Content Manager and UI/UX Developer
2. HAIDAR WAHYU YASARI (F1D02410114)        -- Project Manajer and Backend Developer
3. NYOMAN ADHI DHIRA PURNOMO (F1D02410132)  -- Database Developer and Frontend Developer

## Features
- Browse and search movies across multiple streaming services
- Filter by genre, platform, year, and ratings
- View detailed movie information with critic and audience scores
- Personal watchlist management
- Platform subscription tracking
- Compare streaming service coverage

## SiteMap
```
CINEALL SYSTEM SITEMAP
│
├── 1. PUBLIC PORTAL (Single Page Application - SPA)
│   │   [Diatur secara dinamis oleh js/app.js & js/pages.js]
│   │
│   ├── HOME (Discover)
│   │   ├── Stats Strip (Total catalogued, services, new titles, leaving soon)
│   │   ├── Hero Banner (Featured Film of the week) ──> MOVIE DETAIL
│   │   ├── Curated Editorial Rows (Home rows data) ──> MOVIE DETAIL
│   │   └── "My Services" Filter Toggle (Menyaring agar hanya menampilkan film dari platform langganan user)
│   │
│   ├── ⌕ SEARCH & BROWSE
│   │   ├── Live Search Suggestions (Hasil instan yang muncul saat mengetik) ──> MOVIE DETAIL
│   │   ├── Search Results Grid (Halaman hasil pencarian penuh) ──> MOVIE DETAIL
│   │   ├── Sidebar Filters
│   │   │   ├── Genre selection (Checkboxes)
│   │   │   └── Streaming platform selection (Checkboxes)
│   │   └── Sorting Options (Relevance, Rating, Newest, A–Z)
│   │
│   ├── GENRES
│   │   ├── Genre & Mood Selector (Tombol filter dinamis antar genre)
│   │   └── Genre Movie Grid ──> MOVIE DETAIL
│   │
│   ├── COMPARE SERVICES (Compare)
│   │   ├── Service Statistics (Membandingkan jumlah konten berlangganan, sewa, dan beli)
│   │   ├── Coverage Progress Bar (Visualisasi perbandingan volume katalog antar platform)
│   │   └── Sample Movies (Link poster cepat) ──> MOVIE DETAIL
│   │
│   ├── RECOMMENDATIONS (For You)
│   │   └── Personal Recommendations (Rekomendasi terpersonalisasi berdasarkan Watchlist & Subscriptions) ──> MOVIE DETAIL
│   │
│   ├── WATCHLIST
│   │   ├── List of Saved Movies (Menampilkan judul, durasi, skor kritikus/audiens, & platform) ──> MOVIE DETAIL
│   │   └── Quick Remove from Watchlist
│   │
│   ├── ACCOUNT / AUTH MODAL
│   │   ├── Toggle Platform Subscriptions (Memilih platform streaming yang dilanggan  user)
│   │   ├── Sign In Form (Modal login cepat)
│   │   └── Sign Out trigger
│   │
│   └── MOVIE DETAIL
│       ├── Score Split Display (Visualisasi rating kritikus vs audiens)
│       ├── Technical Metadata Table (Sutradara, Pemeran, Genre, Durasi)
│       ├── "Where to Watch" Widget (Daftar platform streaming, mengarahkan ke link eksternal platform asli)
│       ├── Add to / Remove from Watchlist
│       ├── Play Movie Trailer (Modal popup)
│       └── Related Movies Grid (Rekomendasi film sejenis) ──> MOVIE DETAIL
│
├── 2. AUTH PORTAL (auth/)
│   ├── Login / Sign In (`auth/index.php` / Auth Modal)
│   ├── Register / Sign Up (`auth/signup.php`)
│   ├── Forgot Password Reset (`auth/forgot-password.php`)
│   ├── Verify 2FA (`auth/verify-2fa.php` - Autentikasi dua faktor)
│   └── Sign Out / Logout (`auth/logout.php`)
│
├── 3. CURATOR PORTAL (curator/) [Akses untuk Curator & Admin]
│   ├── Dashboard / Overview (`curator/index.php` - KPI ringkasan data, status homepage, log aktivitas terbaru)
│   ├── Analytics (`curator/pages/analytics.php` - Grafik tren pengunjung dan log aktivitas kurator)
│   ├── Films (`curator/pages/films.php` - Melihat data katalog film secara read-only)
│   ├── Availability (`curator/pages/availability.php` - Pemantauan link streaming aktif/rusak)
│   ├── Homepage Editorial (`curator/pages/editorial.php` - Mengatur baris film yang muncul di halaman depan)
│   └── Reviews Moderation (`curator/pages/reviews.php` - Menyetujui atau menolak review film dari pengguna)
│
└── 4. ADMIN PORTAL (admin/) [Akses Khusus Admin]
    ├── Dashboard (`admin/pages/dashboard.php` - KPI kunjungan, jumlah pencarian, rasio klik ke platform luar, & log audit sistem)
    ├── Films Catalog (`admin/pages/films.php` - Manajemen penuh database film)
    ├── Film Editor (`admin/pages/film-editor.php` - Form tambah film baru atau edit film yang sudah ada)
    └── Users Management (`admin/pages/users.php` - Manajemen akun pengguna, ganti role, blokir user)
````

## Project Structure
```
cineall/
├── index.php                  ← Entry point (halaman publik utama)
├── .htaccess                  ← URL routing Apache
│
├── config/                    ← Konfigurasi global
│   ├── config.php             ← Konstanta app (DB, URL, sesi, dll)
│   └── database.php           ← Koneksi database (singleton PDO)
│
├── includes/                  ← Template & fungsi bersama
│   ├── header.php             ← HTML head + navbar
│   ├── footer.php             ← Script JS + penutup </body>
│   └── functions.php          ← Fungsi auth, user, validasi, response
│
├── css/                       ← Stylesheet publik
│   ├── variables.css          ← CSS variables (warna, font, spacing)
│   ├── base.css               ← Reset + style dasar
│   ├── components.css         ← Komponen UI (navbar, kartu, modal, dll)
│   └── pages.css              ← Layout per halaman (hero, search, detail)
│
├── js/                        ← JavaScript publik
│   ├── api.js                 ← Semua request ke backend PHP
│   ├── components.js          ← Fungsi render komponen UI
│   ├── pages.js               ← Fungsi render tiap halaman
│   └── app.js                 ← Inisialisasi & routing utama
│
├── api/                       ← Backend PHP (endpoint JSON)
│   ├── movies.php             ← API film (list, detail, search, home rows)
│   ├── genres.php             ← API genre
│   ├── platforms.php          ← API platform streaming
│   └── user.php               ← API user (watchlist, subscription)
│
├── auth/                      ← Sistem autentikasi
│   ├── index.php              ← Halaman login
│   ├── signup.php             ← Halaman registrasi
│   ├── logout.php             ← Proses logout
│   ├── forgot-password.php    ← Lupa password
│   ├── verify-2fa.php         ← Verifikasi 2FA
│   ├── api/                   ← Backend auth (login, signup, dll)
│   └── components/
│       ├── auth-header.php    ← HTML head halaman auth
│       ├── auth-footer.php    ← Script + penutup halaman auth
│       └── poster-wall.php    ← Dekorasi poster film di sisi kiri
│
├── admin/                     ← Panel administrator
│   ├── index.php              ← Entry admin
│   ├── pages/
│   │   ├── dashboard.php      ← Statistik & ringkasan
│   │   ├── films.php          ← Daftar film
│   │   ├── film-editor.php    ← Form tambah/edit film
│   │   └── users.php          ← Manajemen pengguna
│   ├── css/                   ← CSS khusus admin
│   └── js/                    ← JS khusus admin
│
├── curator/                   ← Panel kurator (editor konten)
│   ├── index.php              ← Entry curator
│   ├── pages/
│   │   ├── editorial.php      ← Editor konten editorial
│   │   ├── films.php          ← Daftar film kurator
│   │   ├── analytics.php      ← Statistik kurator
│   │   ├── availability.php   ← Manajemen ketersediaan
│   │   └── reviews.php        ← Ulasan film
│   ├── css/                   ← CSS khusus curator
│   └── js/                    ← JS khusus curator
│
├── database/                  ← File SQL
│   ├── schema.sql             ← Struktur tabel database
│   ├── seed.sql               ← Data awal
│   ├── seed_data.sql          ← Data tambahan
│   └── install.sql            ← Script instalasi lengkap
│
└── assets/                    ← Aset statis (gambar, dll)
    ├── css/                   ← Symlink/copy dari /css
    ├── js/                    ← Symlink/copy dari /js
    └── images/                ← Gambar poster film
```

## TechStack
- **Frontend**: HTML5, CSS3 (CSS Variables), Vanilla JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Architecture**: MVC-inspired, RESTful API
