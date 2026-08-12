# Product Requirements Document (PRD)
# Library & Asset Management API — Berdasarkan Implementasi Aktual

**Versi:** 1.0 (Berdasarkan Codebase)
**Penulis:** Technical Writer (Analisis Kode)
**Tanggal:** Juli 2026
**Status:** Final — Dokumentasi implementasi aktual

---

## 1. Latar Belakang & Tujuan

### 1.1 Latar Belakang

Berdasarkan analisis terhadap codebase, aplikasi ini adalah **REST API backend** untuk sistem manajemen perpustakaan dan aset institusi. API ini menangani siklus hidup koleksi/aset mulai dari katalogisasi, peminjaman, pengembalian, hingga perhitungan denda otomatis.

Aplikasi dibangun menggunakan **PHP Laravel 13** dengan **Sanctum** untuk autentikasi API, **Eloquent ORM** untuk database, serta **Laravel Queue & Scheduler** untuk pengiriman email reminder terjadwal.

### 1.2 Tujuan Aplikasi (Berdasarkan Kode)

- Menyediakan API RESTful untuk mengelola koleksi perpustakaan/aset institusi.
- Mengelola alur peminjaman dan pengembalian dengan validasi stok menggunakan database transaction.
- Menghitung denda keterlambatan secara otomatis saat pengembalian.
- Mengirimkan email reminder H-1 sebelum jatuh tempo dan notifikasi keterlambatan via job scheduler.
- Menyediakan laporan statistik peminjaman (koleksi populer, member aktif, export data).

### 1.3 Target Pengguna (Berdasarkan Role)

| Role | Teridentifikasi Dari |
|---|---|
| **Admin** | Enum `'admin'` di migration users, middleware role di route |
| **Staff** | Enum `'staff'` di migration users, middleware role di route |
| **Member** | Enum `'member'` di migration users, default role saat register |

---

## 2. Ruang Lingkup (Scope)

### 2.1 In-Scope (Fitur yang Sudah Diimplementasikan)

| Modul | Fitur | Status Implementasi |
|---|---|---|
| **Autentikasi** | Register member baru | ✅ Controller + Service + Request |
| | Login (email + password) | ✅ Controller + Service + Request |
| | Logout (hapus token Sanctum) | ✅ Controller + Service |
| | Lihat profil sendiri | ✅ Controller + Resource |
| | Update profil | ✅ Controller + Request + Service |
| | Forgot password (kirim link reset) | ✅ Controller + Request |
| | Reset password | ✅ Controller + Request + Service |
| **Manajemen Kategori** | List kategori (publik) | ✅ Controller + pagination + search |
| | Detail kategori (publik) | ✅ Controller |
| | Tambah kategori (admin/staff) | ✅ Controller + Request validasi |
| | Update kategori (admin/staff) | ✅ Controller + Request validasi |
| | Hapus kategori (admin/staff) | ✅ Controller + proteksi relasi |
| **Manajemen Koleksi** | List koleksi/katalog (publik) | ✅ Controller + search, filter, pagination |
| | Detail koleksi (publik) | ✅ Controller |
| | Tambah koleksi (admin/staff) | ✅ Controller + upload gambar |
| | Update koleksi (admin/staff) | ✅ Controller + upload/hapus gambar |
| | Hapus koleksi (admin/staff) | ✅ Controller + proteksi sedang dipinjam |
| **Peminjaman** | Catat peminjaman baru | ✅ Service + DB transaction + lockForUpdate |
| | List riwayat peminjaman | ✅ Service + filter (member: sendiri, staff: semua) |
| | Detail peminjaman | ✅ Controller + proteksi akses member |
| | Proses pengembalian | ✅ Service + fine calculation otomatis |
| **Denda** | List denda (admin/staff) | ✅ Controller + filter status & user |
| | Detail denda (admin/staff) | ✅ Controller |
| | Catat pembayaran denda (admin/staff) | ✅ Controller + update status |
| **Laporan** | Koleksi paling sering dipinjam | ✅ Controller + query withCount |
| | Member paling aktif | ✅ Controller + query withCount |
| | Export laporan peminjaman (JSON) | ✅ Controller + filter tanggal |
| **Reminder Otomatis** | Email reminder H-1 sebelum jatuh tempo | ✅ Job + Mail + Scheduler |
| | Email notifikasi keterlambatan | ✅ Job + Mail + Scheduler |
| | Mark overdue otomatis | ✅ Job (update status di DB) |

### 2.2 Out of Scope / Belum Diimplementasikan

| Fitur | Keterangan |
|---|---|
| **Email verification** | Kolom `email_verified_at` ada di schema, tapi tidak ada endpoint verifikasi email |
| **Export PDF/Excel** | Endpoint export ada, tapi output JSON saja. Kode memiliki komentar: *"In production, PDF/Excel export would use dompdf/laravel-excel"* |
| **Frontend/mobile app** | Tidak ada kode frontend |
| **Payment gateway** | Pembayaran denda dicatat manual (staff update status) |
| **Multi-cabang** | Tidak ada entitas branch/cabang |
| **Reservasi/antrian koleksi** | Tidak ada endpoint atau logika reservasi |
| **Rate limiting** | Tidak ada konfigurasi throttle/rate limit di routes |
| **Unit & Feature Tests** | Hanya ada file contoh (`ExampleTest.php`), tidak ada test spesifik untuk endpoints |

---

## 3. Peran & Hak Akses

### 3.1 Definisi Role (Dari Kode)

Terdapat **3 role** yang didefinisikan di `app/Models/User.php`:

```php
public const ROLE_ADMIN  = 'admin';
public const ROLE_STAFF  = 'staff';
public const ROLE_MEMBER = 'member';
```

Role disimpan sebagai **enum string** di kolom `role` tabel `users` (migration: `$table->enum('role', ['admin', 'staff', 'member'])`).

### 3.2 Matriks Hak Akses

| Endpoint | Member | Staff | Admin | Mekanisme Proteksi |
|---|---|---|---|---|
| **Auth** (register, login, forgot/reset password) | ✅ | ✅ | ✅ | Publik (tanpa middleware) |
| **Auth** (logout, me, update profile) | ✅ | ✅ | ✅ | `auth:sanctum` |
| **Categories** (index, show) | ✅ | ✅ | ✅ | Publik (tanpa middleware) |
| **Categories** (store, update, destroy) | ❌ | ✅ | ✅ | `auth:sanctum` + `role:admin,staff` |
| **Collections** (index, show) | ✅ | ✅ | ✅ | Publik (tanpa middleware) |
| **Collections** (store, update, destroy) | ❌ | ✅ | ✅ | `auth:sanctum` + `role:admin,staff` |
| **Borrowings** (index, store, show) | ✅ **(terbatas)** | ✅ | ✅ | `auth:sanctum` + logic di controller (member hanya lihat milik sendiri) |
| **Borrowings** (return) | ❌ | ✅ | ✅ | `auth:sanctum` + `role:admin,staff` |
| **Fines** (index, show, pay) | ❌ | ✅ | ✅ | `auth:sanctum` + `role:admin,staff` |
| **Reports** (popular, active, export) | ❌ | ✅ | ✅ | `auth:sanctum` + `role:admin,staff` |

**Catatan:** Member **tidak bisa** melihat daftar denda atau status denda mereka sendiri melalui API — endpoint fines sepenuhnya untuk staff/admin.

---

## 4. Functional Requirements

### 4.1 Modul Autentikasi & Manajemen User

**Endpoint:** `POST /auth/register`
- **Akses:** Publik
- **Validasi:** `name` (required, string, max:255), `email` (required, email, unique), `password` (required, confirmed, min:8, mixedCase, numbers)
- **Logic:** Membuat user baru dengan role `member` (default). Mengembalikan token Sanctum + data user.
- **File kunci:** `AuthService::register()`, `RegisterRequest`, `UserRepository::create()`

**Endpoint:** `POST /auth/login`
- **Akses:** Publik
- **Validasi:** `email` (required, email), `password` (required)
- **Logic:** Verifikasi email + password menggunakan `Hash::check()`. Jika gagal, throw `ValidationException`. Mengembalikan token Sanctum.
- **File kunci:** `AuthService::login()`, `LoginRequest`

**Endpoint:** `POST /auth/logout`
- **Akses:** Authenticated (semua role)
- **Logic:** Menghapus token akses saat ini (`currentAccessToken()->delete()`).
- **File kunci:** `AuthService::logout()`

**Endpoint:** `GET /auth/me`
- **Akses:** Authenticated (semua role)
- **Logic:** Mengembalikan data user yang sedang login via `UserResource`.
- **File kunci:** `AuthController::me()`

**Endpoint:** `PATCH /auth/profile`
- **Akses:** Authenticated (semua role)
- **Validasi:** `name` (sometimes, required, string, max:255), `email` (sometimes, required, email, unique ignore current user)
- **Logic:** Update name dan/atau email user.
- **File kunci:** `ProfileController::update()`, `UpdateProfileRequest`, `AuthService::updateProfile()`

**Endpoint:** `POST /auth/forgot-password`
- **Akses:** Publik
- **Validasi:** `email` (required, email, exists:users)
- **Logic:** Mengirimkan link reset password ke email menggunakan Laravel Password Broker.
- **File kunci:** `PasswordResetController::sendResetLinkEmail()`, `AuthService::sendPasswordResetLink()`

**Endpoint:** `POST /auth/reset-password`
- **Akses:** Publik
- **Validasi:** `token` (required), `email` (required, email, exists), `password` (required, confirmed, min:8, mixedCase, numbers)
- **Logic:** Mereset password menggunakan token + email. Memicu event `PasswordReset`.
- **File kunci:** `PasswordResetController::resetPassword()`, `AuthService::resetPassword()`

### 4.2 Modul Manajemen Kategori

**Endpoint:** `GET /categories`
- **Akses:** Publik
- **Fitur:** Pagination, search by name (`?search=`), `?per_page=`
- **Logic:** Mengembalikan list kategori dengan `collections_count` (count relasi).
- **File kunci:** `CategoryController::index()`, `CategoryResource`

**Endpoint:** `GET /categories/{id}`
- **Akses:** Publik
- **Logic:** Detail kategori dengan `collections_count`.
- **File kunci:** `CategoryController::show()`

**Endpoint:** `POST /categories`
- **Akses:** Admin/Staff
- **Validasi:** `name` (required, unique)
- **Logic:** Membuat kategori baru.
- **File kunci:** `CategoryController::store()`, `StoreCategoryRequest`

**Endpoint:** `PATCH /categories/{id}`
- **Akses:** Admin/Staff
- **Validasi:** `name` (required, unique ignore self)
- **Logic:** Update nama kategori.
- **File kunci:** `CategoryController::update()`, `UpdateCategoryRequest`

**Endpoint:** `DELETE /categories/{id}`
- **Akses:** Admin/Staff
- **Business Rule:** Tidak bisa dihapus jika masih memiliki koleksi terkait (return 409 Conflict).
- **Logic:** Hapus kategori.
- **File kunci:** `CategoryController::destroy()`

### 4.3 Modul Manajemen Koleksi

**Endpoint:** `GET /collections`
- **Akses:** Publik
- **Fitur:** Pagination (default 12), search by title/code, filter by `category_id`, `status`, `condition`
- **Logic:** Mengembalikan list koleksi dengan relasi `category`.
- **File kunci:** `CollectionController::index()`, `CollectionResource`

**Endpoint:** `GET /collections/{id}`
- **Akses:** Publik
- **Logic:** Detail koleksi dengan relasi `category`. URL gambar cover (`cover_image_url`) dibentuk dari `asset('storage/' . $path)`.
- **File kunci:** `CollectionController::show()`

**Endpoint:** `POST /collections`
- **Akses:** Admin/Staff
- **Validasi:** `code` (required, unique), `title` (required), `category_id` (required, exists), `cover_image` (nullable, image, max 2MB), `total_stock` (required, min:0), `condition` (required, enum: good/fair/damaged), `status` (required, enum: available/borrowed/maintenance/lost)
- **Logic:** Upload gambar ke `storage/app/public/collections/covers/`. `available_stock` di-set = `total_stock`.
- **File kunci:** `CollectionController::store()`, `StoreCollectionRequest`

**Endpoint:** `PATCH /collections/{id}`
- **Akses:** Admin/Staff
- **Validasi:** Sama seperti store, tapi semua field `sometimes`. `code` unique ignore self.
- **Logic:** Update data koleksi. Jika upload gambar baru, gambar lama dihapus.
- **File kunci:** `CollectionController::update()`, `UpdateCollectionRequest`

**Endpoint:** `DELETE /collections/{id}`
- **Akses:** Admin/Staff
- **Business Rule:** Tidak bisa dihapus jika status `borrowed` atau `overdue` (return 409 Conflict).
- **Logic:** Hapus gambar cover jika ada, lalu hapus koleksi.
- **File kunci:** `CollectionController::destroy()`

### 4.4 Modul Peminjaman

**Endpoint:** `POST /borrowings`
- **Akses:** Authenticated (semua role)
- **Validasi:** `collection_id` (required, exists), `user_id` (required, exists) — `user_id` hanya dipakai jika role staff/admin, member dipaksa pakai ID sendiri.
- **Business Rules (BorrowingService):**
  1. **Lock row** `collections` dengan `lockForUpdate()` (pessimistic locking) untuk mencegah race condition.
  2. **Cek stok:** `available_stock` harus >= 1.
  3. **Cek batas maksimal:** Member tidak boleh memiliki > 3 peminjaman aktif (status `borrowed` atau `overdue`).
  4. **Cek denda:** Member tidak boleh memiliki denda `unpaid` (berdasarkan semua borrowing user).
  5. **Buat record:** `borrowed_at` = now, `due_date` = now + 7 hari, `status` = `borrowed`.
  6. **Decrement** `available_stock` koleksi.
  7. Jika stok habis, update status koleksi menjadi `borrowed`.
- **File kunci:** `BorrowingController::store()`, `BorrowingService::borrow()`, `StoreBorrowingRequest`

**Endpoint:** `GET /borrowings`
- **Akses:** Authenticated (semua role)
- **Fitur:** Filter `?status=`, `?user_id=` (staff/admin only), `?per_page=`
- **Logic:** Member hanya melihat peminjaman milik sendiri. Staff/admin bisa melihat semua dengan filter user.
- **File kunci:** `BorrowingController::index()`, `BorrowingService::getBorrowings()`

**Endpoint:** `GET /borrowings/{id}`
- **Akses:** Authenticated (semua role)
- **Business Rule:** Member hanya bisa melihat detail peminjaman milik sendiri (return 403 jika bukan miliknya).
- **File kunci:** `BorrowingController::show()`

**Endpoint:** `PATCH /borrowings/{id}/return`
- **Akses:** Admin/Staff
- **Business Rules (BorrowingService):**
  1. Validasi status: hanya `borrowed` atau `overdue` yang bisa dikembalikan.
  2. Hitung `daysLate` = selisih hari antara `returned_at` (now) dan `due_date`.
  3. **Jika telat (`daysLate > 0`):** status borrowing = `overdue`, buat record `Fine` dengan `amount = daysLate * 3000`, `days_late`, `daily_rate = 3000`, `status = unpaid`.
  4. **Jika tepat waktu:** status borrowing = `returned`.
  5. **Increment** `available_stock` koleksi.
  6. Jika stok > 0 dan status koleksi `borrowed`, kembalikan ke `available`.
- **File kunci:** `BorrowingController::returnCollection()`, `BorrowingService::returnCollection()`

### 4.5 Modul Denda

**Endpoint:** `GET /fines`
- **Akses:** Admin/Staff
- **Fitur:** Filter `?status=` (unpaid/paid), `?user_id=`, `?per_page=`
- **Logic:** List semua denda dengan relasi borrowing + user + collection.
- **File kunci:** `FineController::index()`, `FineResource`

**Endpoint:** `GET /fines/{id}`
- **Akses:** Admin/Staff
- **Logic:** Detail denda.
- **File kunci:** `FineController::show()`

**Endpoint:** `PATCH /fines/{id}/pay`
- **Akses:** Admin/Staff
- **Business Rule:** Tidak bisa membayar denda yang sudah `paid` (return 422).
- **Logic:** Update `status` = `paid`, `paid_at` = now.
- **File kunci:** `FineController::pay()`

**Catatan:** Tidak ada endpoint untuk member melihat denda mereka sendiri. Member hanya bisa melihat denda saat melihat detail borrowing yang memiliki relasi fine.

### 4.6 Modul Laporan

**Endpoint:** `GET /reports/popular-collections`
- **Akses:** Admin/Staff
- **Parameter:** `?limit=` (default 10)
- **Logic:** Koleksi diurutkan berdasarkan `borrowings_count` (withCount) descending.
- **File kunci:** `ReportController::popularCollections()`

**Endpoint:** `GET /reports/active-members`
- **Akses:** Admin/Staff
- **Parameter:** `?limit=` (default 10)
- **Logic:** User dengan role `member` diurutkan berdasarkan `borrowings_count` descending.
- **File kunci:** `ReportController::activeMembers()`

**Endpoint:** `GET /reports/export`
- **Akses:** Admin/Staff
- **Parameter:** `?start_date=`, `?end_date=`
- **Logic:** Mengembalikan data borrowing dalam format JSON array (bukan PDF/Excel). Setiap record berisi: id, member name, collection title, borrowed_at, due_date, returned_at, status, fine_amount, fine_status.
- **File kunci:** `ReportController::export()`

### 4.7 Modul Reminder Otomatis (Background Job)

**Jadwal:** Setiap hari pukul 08:00 (didefinisikan di `routes/console.php`):
```php
Schedule::job(new SendBorrowingReminders)->dailyAt('08:00');
```

**Job:** `SendBorrowingReminders`
- **Tugas 1 - H-1 Reminder:** Mencari borrowing dengan `status = borrowed` dan `due_date = tomorrow`. Mengirim `BorrowingReminderMail` ke email user.
- **Tugas 2 - Overdue Notification:** Mencari borrowing dengan `status = borrowed` dan `due_date < today`. Update status jadi `overdue`. Mengirim `OverdueNotificationMail` ke email user.
- **Mail Driver:** Menggunakan konfigurasi `config/mail.php` (default `log`). Email dikirim via Queue (`ShouldQueue` interface).

**File kunci:**
- `routes/console.php` — scheduling
- `app/Jobs/SendBorrowingReminders.php` — job logic
- `app/Mail/BorrowingReminderMail.php` — template email H-1
- `app/Mail/OverdueNotificationMail.php` — template email overdue

---

## 5. Non-Functional Requirements

### 5.1 Security

| Aspek | Implementasi |
|---|---|
| **Password hashing** | Menggunakan bcrypt via Laravel default (`Hash::make()`). Password di-cast sebagai `hashed` di model User. |
| **Autentikasi API** | Laravel Sanctum (token-based). Token dikirim via header `Authorization: Bearer {token}`. |
| **Otorisasi** | Middleware `EnsureRole` memeriksa `$user->role` terhadap daftar role yang diizinkan. |
| **Input validation** | Menggunakan FormRequest dengan rules eksplisit (required, max, unique, exists, enum, dll.). |
| **SQL injection** | Terproteksi oleh Eloquent ORM dan parameter binding. |
| **CORS** | Tidak ada konfigurasi CORS eksplisit (menggunakan default Laravel 11/13). |
| **Rate limiting** | **Tidak ada** — tidak ada middleware `throttle` di routes. |

### 5.2 Data Integrity

| Aspek | Implementasi |
|---|---|
| **Database transaction** | Digunakan di `BorrowingService::borrow()` dan `BorrowingService::returnCollection()` untuk memastikan atomicity. |
| **Pessimistic locking** | `lockForUpdate()` digunakan saat membaca stok koleksi untuk mencegah race condition. |
| **Foreign key constraints** | Semua migrasi menggunakan `foreignId()->constrained()` dengan `restrictOnDelete` / `cascadeOnUpdate`. |
| **Unique constraints** | `code` di collections, `name` di categories, `email` di users, `borrowing_id` di fines. |

### 5.3 Performance

| Aspek | Implementasi |
|---|---|
| **Pagination** | Semua endpoint list menggunakan `paginate()` (default: 12-20 per halaman). |
| **Database indexing** | Index pada kolom: `email` (unique), `role`, `code` (unique), `title`, `condition`, `status`, `category_id`, `borrowed_at`, `due_date`, `returned_at`, kombinasi `(category_id, status)`, `(user_id, status)`, `(collection_id, status)`. |
| **Eager loading** | Menggunakan `with()` dan `load()` untuk menghindari N+1 query. |
| **Queue** | Email reminder dikirim via Queue (database driver) agar tidak memblokir request. |

### 5.4 Maintainability

| Aspek | Implementasi |
|---|---|
| **Struktur kode** | Mengikuti konvensi Laravel: Controller → Service (untuk business logic kompleks) → Repository (untuk User). |
| **API Response wrapper** | `ApiResponse` helper class untuk format respons JSON konsisten: `{success, message, data}`. |
| **Resource/Transformer** | Menggunakan `JsonResource` untuk transformasi data model ke response. |
| **Type hints** | PHP 8.3 type hints digunakan di seluruh parameter dan return. |

---

## 6. Data Model

### 6.1 Entity Relationship Diagram (Relasi)

```
User (1) ──────── (N) Borrowing (N) ──────── (1) Collection
                          │
                          │ (0/1)
                          │
                         Fine

Category (1) ────── (N) Collection
```

### 6.2 Tabel: `users`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id | bigint (PK) | auto-increment | |
| name | string(255) | required | |
| email | string(255) | unique, required | |
| role | enum('admin','staff','member') | default 'member' | Indexed |
| email_verified_at | timestamp | nullable | Tidak digunakan (belum ada fitur verifikasi) |
| password | string(255) | required | Di-hash dengan bcrypt |
| remember_token | string(100) | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

**Relasi:** `HasMany` ke Borrowing (`user_id`).

### 6.3 Tabel: `categories`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id | bigint (PK) | auto-increment | |
| name | string(255) | unique, required | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

**Relasi:** `HasMany` ke Collection (`category_id`).

### 6.4 Tabel: `collections`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id | bigint (PK) | auto-increment | |
| code | string(255) | unique, required | Kode unik koleksi |
| title | string(255) | required, indexed | Judul/nama koleksi |
| category_id | bigint (FK) | FK → categories.id, restrictOnDelete | |
| cover_image_path | string(255) | nullable | Path ke file gambar cover |
| total_stock | unsigned int | default 0 | Total stok keseluruhan |
| available_stock | unsigned int | default 0 | Stok tersedia untuk dipinjam |
| condition | enum('good','fair','damaged') | default 'good', indexed | |
| status | enum('available','borrowed','maintenance','lost') | default 'available', indexed | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

**Index:** `(category_id, status)` composite index.
**Relasi:** `BelongsTo` ke Category. `HasMany` ke Borrowing (`collection_id`).

### 6.5 Tabel: `borrowings`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id | bigint (PK) | auto-increment | |
| user_id | bigint (FK) | FK → users.id, restrictOnDelete | |
| collection_id | bigint (FK) | FK → collections.id, restrictOnDelete | |
| borrowed_at | datetime | required, indexed | Tanggal pinjam |
| due_date | date | required, indexed | Tanggal jatuh tempo |
| returned_at | datetime | nullable, indexed | Tanggal kembali (null jika belum) |
| status | enum('borrowed','returned','overdue','cancelled') | default 'borrowed', indexed | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

**Index:** `(user_id, status)` composite, `(collection_id, status)` composite.
**Relasi:** `BelongsTo` ke User. `BelongsTo` ke Collection. `HasOne` ke Fine (`borrowing_id`).

### 6.6 Tabel: `fines`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id | bigint (PK) | auto-increment | |
| borrowing_id | bigint (FK) | FK → borrowings.id, restrictOnDelete, **unique** | Satu denda per peminjaman |
| amount | decimal(12,2) | required | Total denda = days_late x daily_rate |
| days_late | unsigned int | required | Jumlah hari keterlambatan |
| daily_rate | decimal(12,2) | required | Tarif per hari (Rp 3.000) |
| status | enum('unpaid','paid') | default 'unpaid', indexed | |
| paid_at | timestamp | nullable | |
| created_at | timestamp | | |
| updated_at | timestamp | | |

**Relasi:** `BelongsTo` ke Borrowing.

---

## 7. Daftar Endpoint API

### 7.1 Autentikasi (7 endpoint)

| Method | Path | Deskripsi | Akses |
|---|---|---|---|
| POST | `/api/auth/register` | Registrasi member baru | Publik |
| POST | `/api/auth/login` | Login | Publik |
| POST | `/api/auth/logout` | Logout (hapus token) | Member/Staff/Admin |
| GET | `/api/auth/me` | Lihat profil sendiri | Member/Staff/Admin |
| PATCH | `/api/auth/profile` | Update profil | Member/Staff/Admin |
| POST | `/api/auth/forgot-password` | Kirim link reset password | Publik |
| POST | `/api/auth/reset-password` | Reset password dengan token | Publik |

### 7.2 Kategori (5 endpoint)

| Method | Path | Deskripsi | Akses |
|---|---|---|---|
| GET | `/api/categories` | List kategori (search, pagination) | Publik |
| POST | `/api/categories` | Tambah kategori | Admin/Staff |
| GET | `/api/categories/{id}` | Detail kategori | Publik |
| PATCH | `/api/categories/{id}` | Update kategori | Admin/Staff |
| DELETE | `/api/categories/{id}` | Hapus kategori | Admin/Staff |

### 7.3 Koleksi (5 endpoint)

| Method | Path | Deskripsi | Akses |
|---|---|---|---|
| GET | `/api/collections` | List koleksi (search, filter, pagination) | Publik |
| POST | `/api/collections` | Tambah koleksi + upload gambar | Admin/Staff |
| GET | `/api/collections/{id}` | Detail koleksi | Publik |
| PATCH | `/api/collections/{id}` | Update koleksi | Admin/Staff |
| DELETE | `/api/collections/{id}` | Hapus koleksi | Admin/Staff |

### 7.4 Peminjaman (4 endpoint)

| Method | Path | Deskripsi | Akses |
|---|---|---|---|
| GET | `/api/borrowings` | List riwayat peminjaman | Member/Staff/Admin* |
| POST | `/api/borrowings` | Catat peminjaman baru | Member/Staff/Admin |
| GET | `/api/borrowings/{id}` | Detail peminjaman | Member/Staff/Admin* |
| PATCH | `/api/borrowings/{id}/return` | Proses pengembalian | Admin/Staff |

*\*Member hanya melihat data milik sendiri.*

### 7.5 Denda (3 endpoint)

| Method | Path | Deskripsi | Akses |
|---|---|---|---|
| GET | `/api/fines` | List denda (filter status, user) | Admin/Staff |
| GET | `/api/fines/{id}` | Detail denda | Admin/Staff |
| PATCH | `/api/fines/{id}/pay` | Catat pembayaran denda | Admin/Staff |

### 7.6 Laporan (3 endpoint)

| Method | Path | Deskripsi | Akses |
|---|---|---|---|
| GET | `/api/reports/popular-collections` | Koleksi paling sering dipinjam | Admin/Staff |
| GET | `/api/reports/active-members` | Member paling aktif | Admin/Staff |
| GET | `/api/reports/export` | Export laporan peminjaman (JSON) | Admin/Staff |

**Total: 27 endpoint**

---

## 8. Tech Stack

### 8.1 Stack Aktual (Berdasarkan Codebase)

| Layer | Teknologi | Versi/Keterangan |
|---|---|---|
| **Bahasa** | PHP | ^8.3 |
| **Framework** | Laravel | ^13.8 |
| **Database** | MySQL (via XAMPP) | Konfigurasi di `.env` |
| **ORM** | Eloquent ORM | Built-in Laravel |
| **Autentikasi API** | Laravel Sanctum | ^4.3 |
| **Queue Driver** | Database | `QUEUE_CONNECTION=database` |
| **Scheduler** | Laravel Task Scheduling | `Schedule::job()->dailyAt('08:00')` |
| **Mail Default** | Log | `MAIL_MAILER=log` (untuk development) |
| **Testing** | PHPUnit | ^12.5.12 |
| **Frontend Build** | Vite + Laravel Vite plugin | |

### 8.2 Library Penting (Dari composer.json)

- `laravel/framework` ^13.8 — Framework utama
- `laravel/sanctum` ^4.3 — API token authentication
- `laravel/tinker` ^3.0 — REPL interaktif
- `fakerphp/faker` ^1.23 — Data dummy untuk factory
- `laravel/pail` ^1.2.5 — Log viewer CLI
- `nunomaduro/collision` ^8.6 — Error handler untuk CLI

### 8.3 Library yang Tidak Ada (Tapi Direferensikan di Komentar)

- `barryvdh/laravel-dompdf` — Tidak terinstall, disebut di komentar untuk export PDF
- `maatwebsite/laravel-excel` — Tidak terinstall, disebut di komentar untuk export Excel

---

## 9. Gap & Rekomendasi Pengembangan

### 9.1 Gap yang Ditemukan

| # | Gap | Severity | Detail |
|---|---|---|---|
| 1 | **Tidak ada endpoint untuk member melihat denda sendiri** | Medium | Member hanya bisa tahu denda dari detail borrowing. Tidak ada endpoint khusus untuk member mengecek total denda yang belum dibayar. |
| 2 | **Export hanya JSON, belum PDF/Excel** | Medium | Endpoint `/reports/export` ada, tapi output JSON. Library dompdf dan laravel-excel tidak terinstall. |
| 3 | **`middleware()` di constructor controller redundan** | Low | CategoryController dan CollectionController memanggil `$this->middleware()` di constructor, tapi role-based middleware sudah diatur di routes. Ini tidak error (Laravel akan merge) tapi membingungkan. |
| 4 | **Tidak ada email verification** | Medium | Kolom `email_verified_at` dan `MustVerifyEmail` interface tidak digunakan. User bisa login tanpa verifikasi email. |
| 5 | **Tidak ada rate limiting** | Medium | Endpoint login dan register tidak dilindungi throttle, berpotensi brute force attack. |
| 6 | **Tidak ada unit/feature test spesifik** | High | Hanya ada `ExampleTest.php` bawaan Laravel. Tidak ada test untuk endpoint auth, kategori, koleksi, peminjaman, denda, laporan, atau job. |
| 7 | **Member tidak bisa filter denda sendiri** | Low | Staff/admin bisa filter fines by `user_id`, tapi member tidak punya akses ke endpoint fines sama sekali. |
| 8 | **Tidak ada CORS configuration** | Low | Jika frontend di domain berbeda, akan terkena CORS policy. Laravel 11/13 menyediakan konfigurasi CORS via `config/cors.php`. |
| 9 | **Mail driver default ke `log`** | Low | Untuk production, perlu diubah ke SMTP/ sesuaikan dengan mail provider. |

### 9.2 Rekomendasi untuk Versi Berikutnya

| Prioritas | Rekomendasi | Estimasi |
|---|---|---|
| **High** | Buat unit/feature test untuk seluruh endpoint menggunakan PHPUnit/Pest | 2 hari |
| **High** | Tambahkan rate limiting (`throttle:60,1`) pada endpoint login & register | 0.5 hari |
| **Medium** | Install `barryvdh/laravel-dompdf` atau `maatwebsite/laravel-excel` untuk export PDF/Excel | 1 hari |
| **Medium** | Buat endpoint khusus member untuk melihat denda sendiri (`GET /fines/mine`) | 0.5 hari |
| **Medium** | Implementasi email verification flow | 1 hari |
| **Low** | Hapus `$this->middleware()` redundan dari CategoryController dan CollectionController | 0.25 hari |
| **Low** | Konfigurasi CORS jika ada frontend terpisah | 0.25 hari |
| **Low** | Setup mail driver (SMTP/Mailgun/SES) untuk production | 0.5 hari |

---

## Lampiran A: Response Format API

Semua response API menggunakan format standar melalui `App\Support\ApiResponse`:

**Success Response:**
```json
{
    "success": true,
    "message": "Pesan sukses",
    "data": { ... }
}
```

**Error Response:**
```json
{
    "success": false,
    "message": "Pesan error",
    "errors": { "field": ["Error detail"] }  // Opsional, hanya untuk validation error
}
```

**HTTP Status Codes yang Digunakan:**
- `200` — Success / OK
- `201` — Created (store success)
- `400` — Bad Request (error umum)
- `401` — Unauthenticated (token invalid/missing)
- `403` — Forbidden (role tidak sesuai)
- `404` — Not Found
- `409` — Conflict (data memiliki relasi)
- `422` — Validation Error
- `500` — Internal Server Error

---

## Lampiran B: Direktori Proyek

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php
│   │   └── Api/
│   │       ├── AuthController.php
│   │       ├── BorrowingController.php
│   │       ├── CategoryController.php
│   │       ├── CollectionController.php
│   │       ├── FineController.php
│   │       ├── PasswordResetController.php
│   │       ├── ProfileController.php
│   │       └── ReportController.php
│   ├── Middleware/
│   │   └── EnsureRole.php
│   ├── Requests/
│   │   ├── Auth/
│   │   │   ├── ForgotPasswordRequest.php
│   │   │   ├── LoginRequest.php
│   │   │   ├── RegisterRequest.php
│   │   │   ├── ResetPasswordRequest.php
│   │   │   └── UpdateProfileRequest.php
│   │   ├── Borrowing/
│   │   │   └── StoreBorrowingRequest.php
│   │   ├── Category/
│   │   │   ├── StoreCategoryRequest.php
│   │   │   └── UpdateCategoryRequest.php
│   │   └── Collection/
│   │       ├── StoreCollectionRequest.php
│   │       └── UpdateCollectionRequest.php
│   └── Resources/
│       ├── BorrowingResource.php
│       ├── CategoryResource.php
│       ├── CollectionResource.php
│       ├── FineResource.php
│       └── UserResource.php
├── Jobs/
│   └── SendBorrowingReminders.php
├── Mail/
│   ├── BorrowingReminderMail.php
│   └── OverdueNotificationMail.php
├── Models/
│   ├── Borrowing.php
│   ├── Category.php
│   ├── Fine.php
│   ├── LibraryCollection.php
│   └── User.php
├── Providers/
│   └── AppServiceProvider.php
├── Repositories/
│   └── UserRepository.php
├── Services/
│   ├── AuthService.php
│   └── BorrowingService.php
└── Support/
    └── ApiResponse.php

database/
├── factories/
│   ├── BorrowingFactory.php
│   ├── CategoryFactory.php
│   ├── FineFactory.php
│   ├── LibraryCollectionFactory.php
│   └── UserFactory.php
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000001_create_cache_table.php
│   ├── 0001_01_01_000002_create_jobs_table.php
│   ├── 2026_07_27_000001_create_categories_table.php
│   ├── 2026_07_27_000002_create_collections_table.php
│   ├── 2026_07_27_000003_create_borrowings_table.php
│   └── 2026_07_27_000004_create_fines_table.php
└── seeders/
    └── DatabaseSeeder.php

routes/
├── api.php
├── console.php
└── web.php
```

---

*Dokumen ini disusun berdasarkan analisis menyeluruh terhadap codebase pada tanggal 28 Juli 2026. Konten hanya mencerminkan apa yang benar-benar ada di kode, bukan asumsi atau rencana.*
