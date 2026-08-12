# Product Requirements Document (PRD)
# Library & Asset Management API (PHP / Laravel)

**Versi:** 1.0
**Penulis:** Putra
**Tanggal:** Juli 2026
**Status:** Draft — untuk proyek portofolio

---

## 1. Latar Belakang & Tujuan

### 1.1 Latar Belakang
Perpustakaan, baik sekolah, kampus, maupun instansi, masih banyak yang mengelola peminjaman koleksi secara manual atau dengan sistem usang. Proyek ini membangun backend API untuk sistem manajemen perpustakaan/aset yang mencakup pengelolaan koleksi, alur peminjaman-pengembalian, serta perhitungan denda otomatis. Proyek ini dibangun menggunakan PHP (Laravel) untuk menunjukkan kemampuan bekerja lintas stack, sekaligus memanfaatkan fitur khas Laravel seperti Eloquent ORM, Queue, dan Task Scheduling.

### 1.2 Tujuan Proyek
- Menunjukkan kemampuan membangun REST API menggunakan PHP/Laravel dengan best practice.
- Mendemonstrasikan penanganan business logic non-trivial (denda, batas pinjam, reminder otomatis).
- Menunjukkan penggunaan fitur Laravel: Sanctum (auth), Queue, Scheduler, Policy/Gate.
- Menambah variasi portofolio backend di luar Node.js.

### 1.3 Target Pengguna
- Admin/staff perpustakaan yang mengelola koleksi dan transaksi peminjaman
- Member/peminjam yang meminjam dan mengembalikan koleksi
- Digunakan sebagai studi kasus portofolio, didesain agar realistis dipakai institusi kecil-menengah (sekolah, kampus, kantor)

---

## 2. Ruang Lingkup (Scope)

### 2.1 In-Scope (MVP)
- Manajemen koleksi/aset (buku atau barang inventaris)
- Manajemen kategori & kode unik koleksi
- Alur peminjaman & pengembalian dengan validasi stok
- Perhitungan denda otomatis untuk keterlambatan
- Reminder otomatis (email) sebelum & saat jatuh tempo
- Laporan/statistik peminjaman
- Role: admin, staff, member
- Export laporan ke PDF/Excel

### 2.2 Out of Scope (untuk versi awal)
- Aplikasi frontend/mobile
- Sistem pembayaran denda online (payment gateway) — pembayaran dicatat manual dulu
- Multi-cabang perpustakaan (multi-branch)
- Sistem reservasi/antrian buku yang sedang dipinjam (nice-to-have, opsional)

---

## 3. Peran & Hak Akses (Roles & Permissions)

| Role | Deskripsi | Hak Akses Utama |
|---|---|---|
| **Admin** | Pengelola sistem | Full access: kelola staff, koleksi, kategori, lihat semua laporan |
| **Staff** | Petugas perpustakaan | Kelola koleksi, proses peminjaman/pengembalian, catat pembayaran denda |
| **Member** | Peminjam | Lihat katalog, lihat riwayat & status peminjaman sendiri |

---

## 4. Functional Requirements

### 4.1 Autentikasi & User Management
- Register (khusus member, staff/admin dibuat oleh admin), login, logout
- Autentikasi API menggunakan Laravel Sanctum
- Update profil member
- Reset password via email

### 4.2 Manajemen Koleksi (Admin/Staff)
- CRUD koleksi: judul/nama, kode unik, kategori, jumlah stok, kondisi
- Manajemen kategori koleksi
- Upload gambar sampul/foto koleksi
- Status koleksi: `available`, `borrowed`, `maintenance`, `lost`

### 4.3 Katalog Koleksi (Member)
- List koleksi dengan pagination
- Search berdasarkan judul/nama
- Filter berdasarkan kategori & status ketersediaan
- Detail koleksi (termasuk jumlah stok tersedia)

### 4.4 Peminjaman (Borrowing)
- Member mengajukan peminjaman, atau staff mencatatkan peminjaman untuk member
- Validasi:
  - Stok koleksi tersedia
  - Member tidak melebihi batas maksimal pinjam (misal maksimal 3 koleksi aktif)
  - Member tidak memiliki denda yang belum dibayar (opsional, bisa jadi aturan bisnis)
- Tentukan tanggal jatuh tempo otomatis (misal 7/14 hari dari tanggal pinjam)
- **Stock deduction menggunakan database transaction** agar tidak terjadi overbooking saat peminjaman bersamaan

### 4.5 Pengembalian (Return)
- Staff memproses pengembalian koleksi
- Sistem otomatis menghitung keterlambatan (jika ada) berdasarkan selisih tanggal jatuh tempo dan tanggal kembali
- **Perhitungan denda otomatis**: `denda = jumlah_hari_telat × tarif_denda_per_hari`
- Status koleksi dikembalikan menjadi `available`

### 4.6 Denda & Pembayaran
- Riwayat denda per member
- Staff mencatat pembayaran denda (manual, cash/transfer — tanpa payment gateway di MVP)
- Status denda: `unpaid`, `paid`

### 4.7 Reminder Otomatis
- Job terjadwal (Laravel Scheduler) yang berjalan harian untuk:
  - Kirim email reminder H-1 sebelum jatuh tempo
  - Kirim email notifikasi saat peminjaman sudah melewati jatuh tempo
- Menggunakan Laravel Queue agar pengiriman email tidak memblokir request utama

### 4.8 Laporan & Statistik (Admin/Staff)
- Koleksi paling sering dipinjam
- Member paling aktif meminjam
- Total denda terkumpul per periode
- Export laporan peminjaman ke PDF/Excel

---

## 5. Non-Functional Requirements

| Aspek | Requirement |
|---|---|
| **Security** | Password hashing (bcrypt via Laravel default), autentikasi token via Sanctum, otorisasi berbasis Policy/Gate per role |
| **Data Integrity** | Transaksi peminjaman & stok menggunakan DB transaction untuk mencegah race condition |
| **Performance** | Endpoint katalog dioptimasi dengan pagination & index pada kolom pencarian |
| **Reliability** | Job reminder & perhitungan denda berjalan konsisten via scheduler terjadwal |
| **Maintainability** | Struktur kode mengikuti konvensi Laravel (Controller-Service-Repository pattern) |
| **Dokumentasi** | API terdokumentasi via Swagger (L5-Swagger) atau Postman collection |

---

## 6. Data Model (Ringkasan Entitas)

```
User (id, name, email, password, role, created_at)
Category (id, name)
Collection (id, code, title, category_id, total_stock, available_stock, condition, status)
Borrowing (id, user_id, collection_id, borrowed_at, due_date, returned_at, status)
Fine (id, borrowing_id, amount, days_late, status, paid_at)
```

**Relasi kunci:**
- 1 Category → banyak Collection
- 1 User → banyak Borrowing
- 1 Collection → banyak Borrowing (riwayat pinjam)
- 1 Borrowing → 0/1 Fine (denda muncul hanya jika telat)

---

## 7. Contoh Endpoint API (Ringkas)

```
POST   /auth/register
POST   /auth/login
POST   /auth/logout

GET    /collections?category=&search=&status=&page=
GET    /collections/:id
POST   /collections            (admin/staff)
PATCH  /collections/:id        (admin/staff)
DELETE /collections/:id        (admin/staff)

POST   /borrowings              -> ajukan/catat peminjaman
GET    /borrowings              -> riwayat peminjaman (member: milik sendiri, staff: semua)
PATCH  /borrowings/:id/return   -> proses pengembalian (staff)

GET    /fines                   -> riwayat denda
PATCH  /fines/:id/pay           -> catat pembayaran denda (staff)

GET    /reports/popular-collections   (admin)
GET    /reports/active-members        (admin)
GET    /reports/export?format=pdf     (admin)
```

---

## 8. Tech Stack yang Disarankan

| Layer | Teknologi |
|---|---|
| Framework | PHP + Laravel |
| Database | MySQL |
| ORM | Eloquent (built-in Laravel) |
| Auth | Laravel Sanctum |
| Background Job | Laravel Queue (database/redis driver) |
| Scheduler | Laravel Task Scheduling (cron) |
| Dokumentasi API | L5-Swagger (OpenAPI) |
| Export laporan | barryvdh/laravel-dompdf (PDF), Maatwebsite/Laravel-Excel |
| Deployment | Railway / Render / shared hosting dengan cPanel |
| Testing | PHPUnit / Pest |

---

## 9. Success Metrics (untuk Portofolio)

- API terdeploy live dan bisa diuji end-to-end (register → pinjam koleksi → telat kembalikan → denda terhitung otomatis)
- Job reminder terbukti berjalan sesuai jadwal (bisa didemokan via log/screenshot)
- Dokumentasi lengkap (README + ERD + Swagger/Postman)
- Video/gif demo alur peminjaman-pengembalian-denda untuk ditampilkan di portofolio

---

## 10. Milestone & Estimasi Waktu

| Fase | Deliverable | Estimasi |
|---|---|---|
| 1 | Setup project Laravel, auth (Sanctum), database schema | 2–3 hari |
| 2 | CRUD koleksi & kategori | 2–3 hari |
| 3 | Alur peminjaman & pengembalian + transaction-safe stock | 4–5 hari |
| 4 | Perhitungan denda otomatis | 2 hari |
| 5 | Reminder otomatis (Queue + Scheduler) | 2–3 hari |
| 6 | Laporan, export PDF/Excel | 2 hari |
| 7 | Testing, dokumentasi, deployment | 3 hari |
| **Total** | | **~2.5–3 minggu** |
