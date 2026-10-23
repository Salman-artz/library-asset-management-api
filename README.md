# PUSTAKA SENTRAL — Integrated Library Management REST API (Backend)

Backend REST API untuk sistem informasi perpustakaan terpadu, mengelola koleksi buku, sirkulasi peminjaman, pengembalian, kalkulasi denda otomatis, analitik buku terpopuler, anggota perpustakaan, dan penerbitan surat bebas pustaka resmi. Dibangun menggunakan Laravel 13, PHP 8.3, Eloquent ORM, dan MySQL/MariaDB.

---

## 📸 Antarmuka Sistem & Sirkulasi Pustaka

<p align="center">
  <img src="screenshots/25_dashboard_chart_final.png" alt="Pustaka Sentral Dashboard & Chart Analitik" width="92%" />
  <br/>
  <em>Dashboard Pustaka Sentral — Lengkap dengan Fitur Chart Analitik Buku Paling Sering Dipinjam</em>
</p>

<p align="center">
  <img src="screenshots/07_borrowings_sirkulasi.png" alt="Sirkulasi Peminjaman dan Denda" width="92%" />
  <br/>
  <em>Manajemen Transaksi Sirkulasi Peminjaman Buku, Status Jatuh Tempo, dan Denda Otomatis</em>
</p>

<p align="center">
  <img src="screenshots/12_surat_bebas_pustaka_official.png" alt="Surat Bebas Pustaka Resmi" width="92%" />
  <br/>
  <em>Penerbitan Surat Keterangan Bebas Perpustakaan Resmi dengan Validasi Digital QR & Tanda Tangan</em>
</p>

---

## Fitur Utama Backend

- Autentikasi stateless menggunakan Laravel Sanctum
- Role-based access control (RBAC) untuk Admin, Staff, dan Member
- CRUD kategori koleksi dan katalog buku publik
- Peminjaman dan pengembalian dengan ACID Database Transaction untuk integritas ketersediaan stok
- Kalkulasi denda keterlambatan harian otomatis presisi
- Endpoint analitik buku paling sering dipinjam (`/reports/popular-collections`) dan member paling aktif
- Ekspor multi-format (PDF report stream & CSV data)
- Queue dan scheduler untuk email reminder jatuh tempo
- Dokumentasi kontrak API lengkap melalui `openapi.json`

## Tech Stack

| Area | Teknologi |
| --- | --- |
| Bahasa | PHP 8.3+ |
| Framework | Laravel 13 |
| Database | MySQL atau MariaDB |
| ORM | Eloquent ORM |
| Autentikasi API | Laravel Sanctum |
| Antrian (Queue) | Laravel Queue |
| Penjadwal (Scheduler) | Laravel Scheduler |
| Laporan PDF | barryvdh/laravel-dompdf |
| Testing | PHPUnit 12 |
| Linter & Code Style | Laravel Pint |

## Prasyarat

- PHP 8.3 atau lebih baru
- Composer
- MySQL 8 atau MariaDB 10.4 atau lebih baru
- Node.js dan npm untuk asset frontend
- PHP extensions: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, dan `xml`

## Instalasi

```bash
git clone git@github.com:Salman-artz/library-asset-management-api.git
cd library-asset-management-api
composer install
copy .env.example .env
php artisan key:generate
```

Edit `.env` sesuai konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=library_aset_manajemen_db
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migration dan seeder:

```bash
php artisan migrate --seed
```

Siapkan akses penyimpanan upload:

```bash
php artisan storage:link
```

## Menjalankan Backend

Development server:

```bash
php artisan serve
```

API tersedia di `http://localhost:8000`, dengan base path `/api`.

Untuk development lengkap dengan queue worker dan scheduler:

```bash
php artisan queue:work
php artisan schedule:work
```

Testing:

```bash
php artisan test
```

## Endpoint Utama API

Semua endpoint API menggunakan prefix `/api`.

| Modul | Endpoint | Keterangan |
| --- | --- | --- |
| Auth | `/api/auth/register`, `/api/auth/login`, `/api/auth/logout`, `/api/auth/me` | Autentikasi pengguna |
| Password | `/api/auth/forgot-password`, `/api/auth/reset-password` | Reset kata sandi |
| Categories | `/api/categories`, `/api/categories/{id}` | Kategori buku |
| Collections | `/api/collections`, `/api/collections/{id}` | Katalog koleksi & buku |
| Borrowings | `/api/borrowings`, `/api/borrowings/{id}/return` | Peminjaman & pengembalian |
| Fines | `/api/fines/mine`, `/api/fines`, `/api/fines/{id}/pay` | Kas denda keterlambatan |
| Reports | `/api/reports/popular-collections`, `/api/reports/active-members`, `/api/reports/export` | Analitik & audit laporan |

Header untuk endpoint terproteksi:

```text
Authorization: Bearer <token>
```

## Hak Akses Pengguna

| Role | Akses Utama |
| --- | --- |
| **Admin** | Akses penuh: manajemen koleksi, peminjaman, denda, kategori, dan laporan audit |
| **Staff** | Operasional sirkulasi: peminjaman kilat, verifikasi pengembalian, dan denda |
| **Member** | Akses mandiri: melihat katalog publik, membuat peminjaman, melihat riwayat dan denda sendiri |

## Repository

https://github.com/Salman-artz/library-asset-management-api

## Lisensi

MIT
