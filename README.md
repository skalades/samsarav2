# SAMSARA EXPEDITION — Sistem Manajemen Operasional (Laravel 12)

Aplikasi portal resmi dan sistem manajemen operasional pendakian Gunung Cikuray (2.821 MDPL) via Jalur Samsara Garut, dibangun berbasis **Laravel 12** sesuai spesifikasi dokumen perencanaan kebutuhan produk ([PRD-Portal-Pendakian-SAMSARA.md](file:///c:/Users/skala/OneDrive/Documents/SAMSARA/PRD-Portal-Pendakian-SAMSARA.md)).

---

## ⚡ Cara Menjalankan Aplikasi

Jalankan perintah berikut di terminal (PowerShell):

```bash
# 1. Masuk ke direktori SAMSARA
cd c:\Users\skala\OneDrive\Documents\SAMSARA

# 2. Jalankan server lokal Laravel
php artisan serve
```

Aplikasi dapat langsung diakses melalui peramban:
- **Portal Publik (Mobile-First Landing Page)**: [http://localhost:8000/](http://localhost:8000/)
- **Sistem Manajemen Petugas (Backoffice)**: [http://localhost:8000/management](http://localhost:8000/management)

---

## 🔑 Akun & Kredensial Pengujian (Merged Kasir Level)

Kredensial default untuk seluruh akun:
- **Kata Sandi (Semua Akun)**: `samsara2026`
- Fitur **One-Click Quick Login** di halaman login memungkinkan login langsung sebagai **Kasir Operasional Loket (All-in-One)**.

| Peran (Role) | Akun Email | Kewenangan & Modul Utama (Merged di Level Kasir) |
|---|---|---|
| **Kasir Operasional Loket (All-in-One)** | `kasir@samsara.id` | **Penggabungan 4 Peran Lapangan:**<br>1. **Verifikasi SIMAKSI**: Review antrean, verifikasi data/manifest, konfirmasi bayar langsung (*Set PAID*).<br>2. **Kasir POS**: Penjualan tiket, ojek trail, sewa tenda/alat, cetak struk termal 80mm.<br>3. **Petugas Gate**: Pemindai tiket, checklist kehadiran manifest, kontrol deposit sampah Rp 50.000 & check-out.<br>4. **Petugas Inventori**: Cek stok tenda/alat, serah-terima sewa, denda kerusakan saat alat kembali. |
| **Superadmin** | `admin@samsara.id` | Akses penuh pengaturan kuota, telemetri, konfigurasi integrasi, dan laporan lintas fungsi. |
| **Auditor** | `auditor@samsara.id` | Akses read-only audit trail tak terhapuskan untuk seluruh aksi operasional petugas. |

---

## 📌 Alur Status SIMAKSI (Disederhanakan: Pending & Paid)

Status permohonan SIMAKSI kini disederhanakan agar tidak membingungkan kasir/petugas loket:
1. **`PENDING`** (Kuning/Amber): Permohonan telah dikirim, menunggu verifikasi berkas atau pembayaran di kasir/transfer.
2. **`PAID`** (Hijau/Emerald): Pembayaran telah lunas dikonfirmasi kasir/sistem (izin resmi terbit & kuota terkunci otomatis). Siap untuk *Gate Check-in*.
3. **`CHECKED-IN`** (Alpine Orange): Rombongan telah hadir di gerbang basecamp, diverifikasi manifest-nya, kantong sampah diserahkan, dan sedang mendaki di jalur.
4. **`SELESAI`** (Abu-abu): Rombongan selesai turun gunung, verifikasi sampah selesai, deposit dikembalikan.

---

## 🏗️ Arsitektur Aplikasi (Scalable & Modular)

Arsitektur aplikasi menerapkan pemisahan tanggung jawab yang ketat (*Separation of Concerns*) sesuai aturan `harus scalable`, `modular`, `mudah di-maintenance`, dan `konsisten ui`:

```text
SAMSARA/
├── app/
│   ├── Http/Controllers/Management/
│   │   ├── AuthController.php         # Autentikasi staf, quick-login role switcher, & session guard
│   │   ├── DashboardController.php    # Agregasi metrik KPI harian, telemetri, kuota, & antrean
│   │   ├── SimaksiController.php      # Antrean permohonan, detail manifest anggota, aksi putusan izin
│   │   ├── QuotaController.php        # Kalender kuota 150 pendaki/hari, penutupan darurat, telemetri
│   │   ├── PosController.php          # Kasir POS terpadu, struk termal, & pembatalan (VOID)
│   │   ├── GateCheckInController.php  # Verifikasi fisik manifest gerbang, kantong sampah, & check-out
│   │   ├── InventoryController.php    # Manajemen stok alat, serah terima sewa, denda kerusakan
│   │   └── AuditLogController.php     # Immutable audit trail log viewer
│   ├── Models/
│   │   ├── User.php                   # Model user dengan RBAC method
│   │   ├── SimaksiRegistration.php    # Model permohonan SIMAKSI
│   │   ├── ClimberMember.php          # Model manifest anggota rombongan
│   │   ├── DailyQuota.php             # Model kuota harian & emergency closure
│   │   ├── TrailCondition.php         # Model telemetri lapangan (air, suhu, tanah)
│   │   ├── InventoryItem.php          # Model katalog alat & logistik
│   │   ├── PosOrder.php               # Model transaksi kasir
│   │   ├── PosOrderItem.php           # Model rincian barang kasir
│   │   ├── RentalRecord.php           # Model jejak peminjaman alat sewa
│   │   └── AuditLog.php               # Model catatan audit log tak terhapuskan
│   └── Services/
│       ├── AuditService.php           # Logger audit terpusat & otomatis
│       ├── CheckInService.php         # Verifikasi manifest rombongan & deposit sampah
│       ├── GroupPhotoService.php      # Kompresi & SAR watermarking foto regu
│       ├── NotificationService.php    # WhatsApp click-to-chat & gateway dispatcher
│       ├── OjekService.php            # Manajemen dispatch armada & bagi hasil ojek trail
│       ├── PosService.php             # Business logic checkout transaksi POS & stock decrement
│       ├── QuotaService.php           # Generator grid kalender kuota bulanan & closure
│       ├── ReportService.php          # Laporan keuangan terpadu & analitik laba bersih
│       ├── SimaksiService.php         # Business logic persetujuan/penolakan SIMAKSI & kuota locking
│       ├── TariffCalculatorService.php # SSOT kalkulator tarif terpusat (SIMAKSI, ojek, parkir)
│       └── TicketPdfService.php       # Generator e-tiket resmi format PDF
├── database/
│   ├── migrations/                    # 22 migrasi skema tabel terstruktur & indeks komposit
│   └── seeders/
│       └── SamsaraManagementSeeder.php # Seeder awal user, kuota 30 hari, barang POS, registrasi demo
├── resources/views/
│   ├── portal/                        # Portal publik mobile landing page (Blade View)
│   ├── tickets/                       # E-Tiket digital responsif & template cetak PDF
│   └── management/                    # Antarmuka manajemen bertema Bold Adventure Brand
│       ├── layout.blade.php           # Layout admin terpadu dengan navigasi, breadcrumbs, & toast alert
│       ├── dashboard.blade.php        # Dashboard KPI HUD
│       ├── auth/login.blade.php       # Login page dengan form kredensial aman
│       ├── simaksi/                   # Antrean dan detail verifikasi SIMAKSI
│       ├── quota/                     # Kalender kapasitas & telemetri jalur
│       ├── pos/                       # Kasir POS, struk termal 80mm, & riwayat VOID
│       ├── gate/                      # Gate Check-in manifest & tracking sampah
│       ├── ojek/                      # Manajemen pengemudi, dispatch, & bagi hasil
│       ├── inventory/                 # Monitoring alat sewa & penerimaan kembali
│       ├── reports/                   # Laporan keuangan & jurnal kas terpadu
│       ├── settings/                  # Konfigurasi tarif & profil ekspedisi
│       └── audit/                     # Audit trail viewer
└── public/
    └── assets/                        # Asset CSS & JS
```

---

## 🛡️ Kepatuhan terhadap PRD (Section 6 & 11)

1. **Prinsip Kuota Maksimal (PRD 6.1)**: Kuota terkunci otomatis saat SIMAKSI disetujui petugas verifikator (`SimaksiService::approve`). Batas default 150 pendaki/hari dengan perlindungan overbooking.
2. **Audit Trail Tak Terhapuskan (PRD 6.3)**: Seluruh tindakan sensitif (Persetujuan/Penolakan SIMAKSI dengan alasan wajib, Pembatalan VOID kasir POS, Penutupan jalur darurat) tercatat otomatis di tabel `audit_logs` bersama identitas petugas, IP address, waktu, dan payload.
3. **Pengelolaan Sampah Terpantau (PRD 6.1)**: Gerbang check-in wajib mencatat nomor seri kantong sampah (`trash_bag_code`) dan status deposit Rp 50.000. Saat rombongan turun, petugas memeriksa sampah untuk mengembalikan deposit atau menyatakan hangus bila sampah ditinggalkan di gunung.
4. **Desain Visual Konsisten (Bold Adventure Brand)**:
   - Palet Matte Graphite (`#0A0B0D`, `#121418`) dan aksen Alpine Rescue Orange (`#FF5722`).
   - Tipografi teknikal: *Space Grotesk* (Display Headers) + *JetBrains Mono* (Data/Telemetri/Mata Uang/Status).

---

## 🚀 Konfigurasi Redis & Live Server Deployment

Untuk mendukung performa tinggi, skalabilitas ribuan pendaki, dan isolasi proses di server produksi (VPS / Dedicated Server / Cloud), SAMSARA telah terkonfigurasi dengan arsitektur **Redis In-Memory Data Store**:

### 1. Redis Database Index Isolation
Redis diisolasi per indeks database agar flushing cache tidak merusak antrean atau sesi login staf:
- **DB 0 (`REDIS_DB=0`)**: Default framework & sistem umum.
- **DB 1 (`REDIS_CACHE_DB=1`)**: Fast in-memory cache & rate limiter kuota (aman di-flush sewaktu-waktu).
- **DB 2 (`REDIS_SESSION_DB=2`)**: Dedicated session store (mencegah user/petugas ter-logout saat cache dibersihkan).
- **DB 3 (`REDIS_QUEUE_DB=3`)**: Queue background jobs (antrean email tiket PDF, notifikasi WhatsApp, sinkronisasi stok).

### 2. Client Redis & Kompatibilitas
Aplikasi mendukung 2 mode client:
- **`predis`** (`REDIS_CLIENT=predis`): Paket PHP murni (`predis/predis`), terpasang secara default untuk kompatibilitas universal di berbagai lingkungan server tanpa ketergantungan ekstensi C.
- **`phpredis`** (`REDIS_CLIENT=phpredis`): Ekstensi C PHP native berkecepatan tinggi untuk throughput maksimal pada server berbeban berat.

### 3. Template Konfigurasi Produksi
Telah disediakan berkas template siap pakai di root proyek:
- [`.env.production.example`](file:///c:/Users/skala/OneDrive/Documents/SAMSARA/.env.production.example): Contoh konfigurasi environment produksi lengkap dengan Redis session, cache, dan queue.
- [`deployment/supervisor/samsara-worker.conf`](file:///c:/Users/skala/OneDrive/Documents/SAMSARA/deployment/supervisor/samsara-worker.conf): Konfigurasi Supervisor process manager untuk menjaga queue worker Redis tetap aktif 24/7.
- [`deployment/nginx/samsara.conf`](file:///c:/Users/skala/OneDrive/Documents/SAMSARA/deployment/nginx/samsara.conf): Konfigurasi Nginx produksi dengan security headers, denial aturan file sensitif (`.env`, `.git`), dan optimasi asset caching.
- [`deployment/deploy.sh`](file:///c:/Users/skala/OneDrive/Documents/SAMSARA/deployment/deploy.sh): Skrip otomasi deployment zero-downtime (git pull, composer install, migration, caching, queue restart).
