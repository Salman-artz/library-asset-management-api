# 🏛️ Library & Asset Management API

**Backend REST API** untuk sistem manajemen perpustakaan dan aset institusi yang komprehensif. Dibangun dengan **PHP Laravel 13** menyediakan fitur lengkap dari katalogisasi koleksi, alur peminjaman-pengembalian, perhitungan denda otomatis, email reminder terjadwal, hingga laporan statistik dengan role-based access control.

Aplikasi ini ideal untuk **perpustakaan, institusi pendidikan, museum, atau organisasi yang perlu mengelola inventori dengan peminjaman-pengembalian terstruktur**.

---

## ✨ Fitur Unggulan

### 🔐 Autentikasi & Otorisasi
- **Laravel Sanctum** — Token-based API authentication
- **Role-Based Access Control** — Admin, Staff, Member dengan middleware permission
- **Reset Password** — Email verification & token-based reset
- **Profile Management** — Update profil & password user

### 📚 Manajemen Koleksi
- **CRUD Koleksi & Kategori** — Kelola koleksi dengan upload gambar
- **Katalog Publik** — Search, filter by kategori/status, pagination
- **Status Tracking** — Available, Borrowed, Maintenance, Lost
- **Stock Management** — Validasi stok dengan database transaction
- **Upload Gambar** — Gambar koleksi dengan preview

### 📖 Peminjaman & Pengembalian
- **Alur Peminjaman** — Validasi ketersediaan, durasi peminjaman fleksibel
- **Pengembalian Tracking** — Proses pengembalian dengan kondisi barang
- **Denda Otomatis** — Perhitungan denda keterlambatan (Rp 3.000/hari)
- **Transaction Safety** — Database transaction untuk consistency stok
- **Batasan Peminjaman** — Max 3 peminjaman aktif per member, no-borrow jika ada denda

### 📧 Notifikasi & Reminder
- **Email Reminder H-1** — Notifikasi sebelum jatuh tempo via Laravel Queue
- **Overdue Notification** — Alert keterlambatan pengembalian
- **Scheduler Jobs** — Automated background tasks dengan Laravel Scheduler
- **Configurable Queue** — Gunakan Redis, Database, atau SyncQueue

### 📊 Laporan & Analytics
- **Statistik Peminjaman** — Total peminjaman, koleksi populer, member aktif, trend
- **Export Data** — JSON export untuk laporan bulanan/tahunan
- **Member Activity Log** — Tracking riwayat peminjaman per member dengan detail
- **Fine Report** — Laporan denda & pembayaran

### 🗄️ Data Integrity
- **Database Transaction** — Prevent overselling & double-booking
- **Soft Delete** — Archive data tanpa menghapus (untuk audit trail)
- **Audit Trail** — Logging setiap perubahan data penting
- **Constraint Validation** — Foreign key & business logic validation

---

## 📋 Tech Stack

| Layer | Teknologi |
|-------|-----------|
| **Language** | PHP ^8.3 |
| **Framework** | Laravel 13.8 |
| **Database** | MySQL / MariaDB |
| **API Auth** | Laravel Sanctum 4.0 |
| **ORM** | Eloquent |
| **Queue** | Laravel Queue (Redis/Database driver) |
| **Scheduler** | Laravel Scheduler |
| **Validation** | Laravel Validation Rules |
| **Frontend Assets** | Vite + Tailwind CSS v4 |
| **Testing** | PHPUnit 12.5 |
| **Linting** | Pint 1.27 |

---

## 🎯 Prerequisites

Sebelum memulai, pastikan sudah terinstall:

- **PHP ^8.3** — Download dari [php.net](https://www.php.net/)
  - Extensions: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`

- **Composer** — Download dari [getcomposer.org](https://getcomposer.org/)

- **MySQL 8.0+** atau **MariaDB 10.4+** — Sudah running & accessible

- **Node.js 18+** — Untuk build frontend assets (Vite)

### Verifikasi Instalasi

```bash
# Check PHP version
php --version

# Check Composer
composer --version

# Check MySQL
mysql --version

# Check Node.js
node --version
```

---

## ⚙️ Instalasi (Step-by-Step)

### 1️⃣ Clone Repository

```bash
# Gunakan SSH
git clone git@github.com:yourusername/library-asset-management-api.git
cd library-asset-management-api

# Atau HTTPS
git clone https://github.com/yourusername/library-asset-management-api.git
cd library-asset-management-api
```

### 2️⃣ Install PHP Dependencies

```bash
composer install
```

Ini akan install semua package Laravel dan dependencies lainnya.

### 3️⃣ Setup Environment

```bash
# Copy .env.example menjadi .env
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4️⃣ Konfigurasi Database

Edit file `.env` dan sesuaikan dengan MySQL Anda:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=library_asset_db
DB_USERNAME=root
DB_PASSWORD=
```

Atau gunakan `.env` default jika MySQL running di localhost dengan no password.

### 5️⃣ Install API Authentication (Sanctum)

```bash
php artisan install:api
```

### 6️⃣ Setup Database & Migration

```bash
# Jalankan migration + seeder
php artisan migrate:fresh --seed
```

Output yang diharapkan:
```
✓ Database migrations completed
✓ Database seeders completed
✓ Seeded: CategorySeeder
✓ Seeded: UserSeeder
```

### 7️⃣ Create Storage Link

```bash
php artisan storage:link
```

Ini membuat symbolic link ke folder `storage/app/public` agar file uploaded bisa diakses via HTTP.

### 8️⃣ Install Frontend Assets (Opsional)

```bash
npm install
npm run dev
```

---

## 🚀 Menjalankan Aplikasi

### Development Mode

```bash
# Terminal 1: Start Laravel server
php artisan serve
```

Output:
```
Laravel development server started: http://127.0.0.1:8000
```

Akses API di: **http://localhost:8000/api/...**

### Jalankan Background Jobs & Scheduler

Untuk email reminder otomatis, jalankan di terminal terpisah:

```bash
# Terminal 2: Queue worker (untuk email jobs)
php artisan queue:work

# Terminal 3: Scheduler (untuk reminder H-1)
php artisan schedule:work
```

### Production Mode

```bash
# Build optimized
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Start server
php artisan serve --env=production
```

### Testing

```bash
php artisan test
```

---

## 🔑 Akun Demo

Setelah menjalankan seeder, akun yang tersedia:

| Role | Email | Password | Keterangan |
|---|---|---|---|
| **Admin** | admin@library.test | password | Full access |
| **Staff** | staff1@library.test | password | Manage collections & borrowings |
| **Member** | member1@library.test | password | Borrow & view collections |

---

## 📚 Akses Aplikasi

- **API Server**: http://localhost:8000
- **API Documentation**: http://localhost:8000/api/docs (jika Swagger disetup)
- **Laravel Tinker**: `php artisan tinker`

---

## 📡 API Endpoints (Summary)

### Autentikasi

| Method | Endpoint | Deskripsi | Akses |
|---|---|---|---|
| POST | `/api/auth/register` | Registrasi member baru | Public |
| POST | `/api/auth/login` | Login pengguna | Public |
| POST | `/api/auth/logout` | Logout (hapus token) | Member/Staff/Admin |
| GET | `/api/auth/me` | Lihat profil sendiri | Member/Staff/Admin |
| PATCH | `/api/auth/profile` | Update profil | Member/Staff/Admin |
| POST | `/api/auth/forgot-password` | Kirim link reset password | Public |
| POST | `/api/auth/reset-password` | Reset password | Public |

### Kategori

| Method | Endpoint | Deskripsi | Akses |
|---|---|---|---|
| GET | `/api/categories` | Daftar kategori + jumlah koleksi | Public |
| GET | `/api/categories/{id}` | Detail kategori | Public |
| POST | `/api/categories` | Tambah kategori | Admin/Staff |
| PATCH | `/api/categories/{id}` | Update kategori | Admin/Staff |
| DELETE | `/api/categories/{id}` | Hapus kategori | Admin/Staff |

### Koleksi

| Method | Endpoint | Deskripsi | Akses |
|---|---|---|---|
| GET | `/api/collections` | Daftar koleksi (search, filter, pagination) | Public |
| GET | `/api/collections/{id}` | Detail koleksi | Public |
| POST | `/api/collections` | Tambah koleksi (dengan upload gambar) | Admin/Staff |
| PATCH | `/api/collections/{id}` | Update koleksi | Admin/Staff |
| DELETE | `/api/collections/{id}` | Hapus koleksi | Admin/Staff |

**Parameter GET `/api/collections`:**
- `search` - Cari berdasarkan judul/kode
- `category_id` - Filter kategori
- `status` - Filter status (available, borrowed, maintenance, lost)
- `condition` - Filter kondisi (good, fair, damaged)
- `per_page` - Jumlah per halaman (default: 12)

### Peminjaman

| Method | Endpoint | Deskripsi | Akses |
|---|---|---|---|
| POST | `/api/borrowings` | Catat peminjaman baru | Member/Staff/Admin |
| GET | `/api/borrowings` | Daftar riwayat peminjaman | Member (milik sendiri), Staff/Admin (semua) |
| GET | `/api/borrowings/{id}` | Detail peminjaman | Member (milik sendiri), Staff/Admin |
| PATCH | `/api/borrowings/{id}/return` | Proses pengembalian koleksi | Staff/Admin |

**Aturan Peminjaman:**
- Maksimal 3 peminjaman aktif per member
- Stok otomatis berkurang saat peminjaman dan bertambah saat pengembalian
- Member dengan denda belum dibayar tidak bisa meminjam
- Jatuh tempo otomatis: 7 hari

### Denda

| Method | Endpoint | Deskripsi | Akses |
|---|---|---|---|
| GET | `/api/fines` | Daftar semua denda | Staff/Admin |
| GET | `/api/fines/{id}` | Detail denda | Staff/Admin |
| PATCH | `/api/fines/{id}/pay` | Catat pembayaran denda | Staff/Admin |

**Perhitungan Denda:** `jumlah_hari_telat × Rp 3.000/hari`

### Laporan

| Method | Endpoint | Deskripsi | Akses |
|---|---|---|---|
| GET | `/api/reports/popular-collections` | Koleksi paling sering dipinjam | Admin |
| GET | `/api/reports/active-members` | Member paling aktif | Admin |
| GET | `/api/reports/export` | Export laporan peminjaman (JSON) | Admin |

---

## 💻 Struktur Folder Utama

```
app/
  ├── Http/
  │   ├── Controllers/
  │   │   ├── AuthController.php
  │   │   ├── CategoryController.php
  │   │   ├── CollectionController.php
  │   │   ├── BorrowingController.php
  │   │   └── ReportController.php
  │   └── Requests/         # Form validation
  ├── Models/
  │   ├── User.php
  │   ├── Category.php
  │   ├── Collection.php
  │   ├── Borrowing.php
  │   └── Fine.php
  ├── Services/            # Business logic
  └── Jobs/                # Queue jobs
routes/
  └── api.php             # API routes
database/
  ├── migrations/
  ├── seeders/
  └── factories/
config/                   # Konfigurasi
tests/                   # Unit & feature tests
```

---

## 🧪 Testing

```bash
# Jalankan semua test
php artisan test

# Run specific test
php artisan test tests/Feature/AuthTest.php

# Dengan coverage report
php artisan test --coverage
```

---

## 📊 Workflow Utama

### 1. Alur Peminjaman

```
Member browse koleksi
  ↓
Member request borrow
  ↓
System check: stok & no-denda? → Yes ✓
  ↓
Create borrowing record, kurangi stok
  ↓
Send email confirmation
  ↓
Schedule reminder H-1 sebelum jatuh tempo
```

### 2. Alur Pengembalian

```
Member return koleksi
  ↓
Staff input kondisi koleksi
  ↓
System check: tepat waktu? → Yes: Selesai, No: Hitung denda
  ↓
Update borrowing status, tambah stok
  ↓
Create fine record (jika ada keterlambatan)
  ↓
Send receipt email
```

### 3. Email Reminder Flow

```
Daily Scheduler runs at 00:00
  ↓
Check borrowings jatuh tempo dalam H-1
  ↓
Create RemindBorrowingJob untuk each borrowing
  ↓
Queue worker processes job
  ↓
Send reminder email to member
  ↓
If overdue: Send overdue notification to staff
```

---

## 🔒 Security Best Practices

✅ **Implemented:**
- Password hashing dengan bcrypt
- CSRF protection via Laravel
- Rate limiting untuk API endpoints
- SQL injection prevention via Eloquent ORM & prepared statements
- XSS prevention via Blade templating
- Token-based authentication dengan Sanctum
- Role-based authorization middleware

⚠️ **Recommended untuk Production:**
- Setup HTTPS/SSL certificate
- Configure CORS properly
- Use environment-specific `.env` files
- Enable query logging untuk audit trail
- Backup database regularly
- Setup monitoring & logging service
- Use Redis untuk production queue & cache

---

## 🐛 Troubleshooting

| Error | Solusi |
|-------|--------|
| **"SQLSTATE[HY000]: General error: 1030 Got error 28"** | Disk space penuh, bersihkan temporary files |
| **"No application encryption key has been specified"** | Jalankan `php artisan key:generate` |
| **"Class 'PDO' not found"** | Install PHP extension `php-pdo` & `php-pdo_mysql` |
| **"SQLSTATE[HY000] [2002] Connection refused"** | MySQL service tidak running atau kredensial `.env` salah |
| **"Illuminate\Contracts\Container\BindingResolutionException"** | Jalankan `composer install` dan `composer dump-autoload` |
| **"File permission denied storage"** | Jalankan `chmod -R 775 storage` atau `php artisan storage:link` |
| **"Queue job not processing"** | Pastikan `php artisan queue:work` running di terminal terpisah |
| **"Email not sending"** | Check MAIL_* config di `.env`, gunakan Mailtrap atau Mailgun untuk testing |
| **"CRLF will be replaced by LF"** | Git warning (normal), dapat dihapus dengan `git config core.safecrlf false` |

---

## 📚 Dokumentasi Lengkap

- [Database Schema](./docs/SCHEMA.md) — ER diagram & field descriptions
- [API Documentation](./docs/API.md) — Detailed endpoint docs dengan examples
- [Setup Guide](./docs/SETUP.md) — Step-by-step setup untuk development & production
- [Contributing](./CONTRIBUTING.md) — Panduan untuk contributor
- [Changelog](./CHANGELOG.md) — Riwayat perubahan version

---

## 🤝 Contributing

Contributions sangat welcome! Silakan:

1. Fork repository
2. Buat branch feature (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

Silakan lihat [CONTRIBUTING.md](./CONTRIBUTING.md) untuk detail lebih lanjut.

---

## 📄 License

Project ini dilisensikan di bawah MIT License — lihat file [LICENSE](./LICENSE) untuk detail.

---

## 📞 Support & Contact

Untuk pertanyaan, bug reports, atau feature requests:

- **Issues**: Buka GitHub issue
- **Email**: support@library-api.com
- **Docs**: Lihat wiki & dokumentasi lengkap

---

## 🎯 Roadmap

---

## 🏗️ Struktur Proyek

```
app/
├── Http/
│   ├── Controllers/Api/    # Controller API
│   │   ├── AuthController.php
│   │   ├── BorrowingController.php
│   │   ├── CategoryController.php
│   │   ├── CollectionController.php
│   │   ├── FineController.php
│   │   ├── PasswordResetController.php
│   │   ├── ProfileController.php
│   │   └── ReportController.php
│   ├── Middleware/          # Custom middleware
│   │   └── EnsureRole.php
│   ├── Requests/           # Form request validasi
│   └── Resources/          # API Resources (JSON transformation)
├── Jobs/                   # Queue jobs
│   └── SendBorrowingReminders.php
├── Mail/                   # Mail classes
│   ├── BorrowingReminderMail.php
│   └── OverdueNotificationMail.php
├── Models/                 # Eloquent models
├── Services/               # Business logic layer
│   ├── AuthService.php
│   └── BorrowingService.php
└── Support/                # Helpers
    └── ApiResponse.php
routes/
├── api.php                 # API routes
└── console.php             # Scheduler definitions
```

---

## 🗄️ Entity Relationship Diagram

```
User (id, name, email, role, password)
  │
  ├──< Borrowing (id, user_id, collection_id, borrowed_at, due_date, returned_at, status)
  │     │
  │     └──< Fine (id, borrowing_id, amount, days_late, daily_rate, status, paid_at)
  │
Category (id, name)
  │
  └──< Collection (id, code, title, category_id, cover_image_path, total_stock, available_stock, condition, status)
        │
        └──< Borrowing (id, user_id, collection_id, ...)
```

---

## 📦 Tech Stack

| Layer | Teknologi |
|---|---|
| Framework | Laravel 13 |
| Database | MySQL |
| ORM | Eloquent |
| Auth | Laravel Sanctum |
| Queue | Database Driver |
| Scheduler | Laravel Task Scheduling |
| Storage | Local (public disk) |

---

## 📝 Lisensi

Proyek ini dikembangkan untuk tujuan portofolio. MIT License.
