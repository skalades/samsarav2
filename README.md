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

## 🔑 4 Level Akses & Akun Pengujian

Kata sandi semua akun demo: `samsara2026`. Superadmin dapat menambah, mengubah level, dan menonaktifkan akun di **Pengaturan → Petugas & Akses**. Peran lama otomatis dipetakan (admin/auditor → superadmin, verifikator/inventori → kasir, gate_officer/petugas_lapangan → gatekeeper).

| Level | Akun demo | Beranda & modul |
|---|---|---|
| **Superadmin** | `admin@samsara.id` | Ringkasan operasional, laporan, audit, kuota, tarif, checklist Gate, WhatsApp Gateway, petugas & akses — plus seluruh modul level lain. |
| **Kasir Basecamp** | `kasir@samsara.id` | Meja kasir: verifikasi bayar (konfirmasi LUNAS → WhatsApp otomatis), antrean kiriman Gate, cetak surat jalan / voucher ojek / izin parkir, POS, kas keluar, sewa alat, ojek. |
| **Gatekeeper** | `gate@samsara.id` | Layar Gate mobile: scan tiket, pemeriksaan naik (anggota, foto tim, alat wajib, hitung logistik), pemeriksaan turun (hitung ulang logistik), laporan radio HT. |
| **Ranger Pos** | `ranger@samsara.id` | Scanner Pos 1–3 & puncak: catat naik / turun / buka tenda, daftar rombongan di jalur. |

Tombol **Mode Mobile / Mode Desktop** di header mengubah tampilan seketika; di ponsel tampilan otomatis mobile.

---

## 📌 Alur Status Rombongan (Gate ↔ Kasir)

1. **Menunggu bayar** — permohonan masuk, menunggu verifikasi pembayaran.
2. **Lunas** (`paid`) — kasir mengonfirmasi lunas; info pendakian lengkap + tautan e-SIMAKSI dikirim otomatis ke WhatsApp ketua.
3. **Lolos Gate** (`gate_cleared`) — gatekeeper memeriksa anggota, foto tim, alat wajib (centang) dan menghitung logistik berpotensi sampah (jumlah).
4. **Sedang mendaki** (`checked_in`) — kasir mencetak Surat Jalan & Voucher Ojek lalu memberangkatkan rombongan; ranger mencatat di tiap pos.
5. **Sudah turun** (`returned`) — gatekeeper menghitung ulang logistik & alat; selisih tercatat sebagai catatan untuk kasir.
6. **Selesai** (`completed`) — kasir menyelesaikan, mencetak Izin Keluar Parkir; KTP ketua otomatis dimusnahkan (UU PDP).

Deposit sampah dinonaktifkan sementara (bisa diaktifkan lagi di Tarif & Kebijakan).

### WhatsApp Gateway

Atur di `.env`: `WA_GATEWAY_DRIVER` (`log` | `fonnte` | `wablas` | `webhook`), `WA_GATEWAY_TOKEN`, `WA_GATEWAY_ENDPOINT`, `WA_GATEWAY_SECRET`, `WA_AUTO_SEND_ON_PAID`. Selama driver `log`, pesan hanya dicatat. Pesan dikirim lewat antrean (`php artisan queue:work`), tercatat di **Pengaturan → WhatsApp Gateway**, dan bisa dikirim ulang.

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
