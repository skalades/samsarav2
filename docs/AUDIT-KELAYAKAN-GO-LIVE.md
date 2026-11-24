# Audit & Uji Kelayakan Go-Live — SAMSARA

**Tanggal:** 24 Nov 2026 · **Target:** `samsaracikuray.id` (Nginx + PHP-FPM 8.2 + PostgreSQL + Redis)
**Test suite:** 124 test lulus, 774 assertion (sebelumnya 110 test) · **Gerbang otomatis:** `php artisan samsara:preflight`

## Skor Akhir

| | Sebelum audit | Sesudah perbaikan |
|---|---|---|
| Skor fitur (bobot 40%) | 84 | 84 |
| Skor kelayakan deploy (bobot 60%) | 56 | 79 |
| **Skor total** | **67: belum layak** | **81: layak go-live dengan syarat** |

Syarat go-live: `samsara:preflight` di server harus 0 FAIL, data rekening/QRIS/kontak resmi sudah diisi, dan backup harian (P1 no. 1 di bawah) sudah aktif.

---

## A. Skor Fitur (0–10)

| # | Modul | Skor | Catatan |
|---|---|---|---|
| 1 | Registrasi SIMAKSI publik & kuota harian | 9.0 | Lock kuota, anti-NIK ganda, batas booking belum bayar per WA, auto-expire |
| 2 | Pembayaran (QRIS statis, transfer kode unik, tunai) | 7.0 | Masih verifikasi manual, belum ada payment gateway atau rekonsiliasi otomatis |
| 3 | Gate naik/turun, inspeksi alat, foto regu | 9.0 | Template inspeksi dinamis, watermark, purge 7 hari |
| 4 | Ranger checkpoint Pos 1–3 & puncak | 8.5 | Tektok vs camp sudah ditegakkan |
| 5 | POS, kas, petty cash, inventori & sewa | 8.5 | Ada void, audit, dan cetak thermal |
| 6 | Ojek: penugasan, voucher, settlement | 8.5 | |
| 7 | Laporan keuangan & audit log | 8.5 | |
| 8 | Notifikasi WhatsApp (fonnte/wablas/webhook) | 8.0 | Lewat queue dan bisa retry. Default `log` (tidak mengirim) |
| 9 | Tiket digital, PDF, thermal Bluetooth | 9.0 | |
| 10 | PWA manajemen & offline | 7.5 | `CACHE_VERSION` di `sw.js` masih harus dinaikkan manual |
| 11 | RBAC 4 level (superadmin/kasir/gatekeeper/ranger) | 9.0 | Diuji per rute |
| | **Rata-rata** | **8.4 → 84** | |

## B. Skor Kelayakan Deploy (0–100)

| Aspek | Bobot | Sebelum | Sesudah | Alasan perubahan |
|---|---|---|---|---|
| Keamanan aplikasi | 25% | 58 | 84 | Entropi kode tiket, ekstensi upload, sanitizer, pesan exception tidak bocor lagi |
| Privasi data (UU PDP) | 15% | 70 | 80 | Nomor HP & alamat publik dimasking. Bukti bayar masih di disk publik |
| Konfigurasi & hardcode | 15% | 40 | 90 | Data bayar/kontak contoh dihapus, kredensial pindah ke env |
| Infrastruktur & deploy | 15% | 45 | 85 | SSL aktif, scheduler berjalan, deploy aman bila gagal, ada preflight |
| Pengujian otomatis | 15% | 85 | 88 | Ditambah 8 test regresi go-live |
| Operasional & observability | 15% | 35 | 45 | Belum ada backup otomatis & error monitoring |
| **Total tertimbang** | | **56** | **79** | |

---

## C. Temuan & Status Perbaikan

### Kritis (sudah diperbaiki)

1. **Data pembayaran palsu tampil ke publik.** Di sekitar 20 tempat (Blade, JS, service) ada fallback rekening `810-0982-111`, pemilik `PT SAMSARA ADVENTURE GARUT`, NMID `ID102026110099`, dan WA `6282122334455`. Portal juga membuat **QR QRIS tiruan** lewat `api.qrserver.com`. Di server baru (`DatabaseSeeder` hanya membuat superadmin), pendaki akan melihat rekening dan QRIS palsu.
   Perbaikan: semua fallback sekarang dari `config/samsara.php` (env, default kosong). QR tiruan diganti pesan "QRIS belum tersedia", dan JS menolak menyalin rekening atau membuka WA bila nilainya kosong. `samsara:preflight` gagal bila datanya kosong atau masih nilai demo.
2. **Password superadmin produksi `samsara2026` tertulis di repo & README.** `SuperAdminSeeder` sekarang membaca `SUPERADMIN_EMAIL`/`SUPERADMIN_PASSWORD`. Bila kosong, password dibuat acak 20 karakter. Seeder menolak password bawaan dan tidak menimpa password akun yang sudah ada. `SamsaraManagementSeeder` (demo) menolak berjalan di produksi.
3. **Nginx tidak bisa start.** Ada `listen 443 ssl` tetapi baris sertifikat dikomentari. Sertifikat sekarang aktif, ditambah lokasi ACME HTTP-01 di port 80, HSTS, PHP hanya lewat `index.php`, dan eksekusi PHP di `/storage/` diblokir.
4. **Scheduler tidak pernah berjalan.** Tidak ada cron maupun program supervisor, sehingga `simaksi:expire-unpaid`, purge KTP, dan purge foto regu tidak pernah jalan. Sekarang ada `[program:samsara-scheduler]` dan heartbeat tiap menit yang dicek preflight.
5. **Template `.env` tidak ikut repo.** `.env.example` dan `.env.production.example` ada di `.gitignore`. Sekarang di-track, dan variabel baru sudah ditambahkan.

### Tinggi / Sedang (sudah diperbaiki)

6. **Kode tiket mudah ditebak.** `SMK-YYYYMM-XXXX` hanya punya sekitar 1,7 juta kombinasi per bulan, padahal kode ini satu-satunya kunci untuk lihat tiket, unduh PDF, ganti KTP, dan unggah bukti. Sekarang 8 karakter (sekitar 2,8 triliun kombinasi). Scanner tetap kompatibel.
7. **`SanitizeCurrencyInputs` mengubah semua input.** Password `123.456.789` dan rekening `810.098.211` diubah menjadi angka float. Sekarang field password, token, NIK, phone, account, nmid, code, dan email dikecualikan.
8. **Ekstensi file upload diambil dari nama file kiriman klien.** Sekarang memakai ekstensi hasil deteksi isi file (`extension()`).
9. **Pesan exception bocor ke publik** di upload foto regu. Sekarang pesan generik, dan detailnya dicatat lewat `report()`.
10. **PII publik.** Nomor HP ketua tampil utuh di tiket digital, dan alamat lengkap ikut dalam respons lookup. Keduanya sekarang dimasking (kota saja).
11. **Deploy.** Token bypass maintenance tidak lagi di-hardcode (sekarang acak per deploy). Ada trap supaya aplikasi kembali online bila deploy gagal. Ditambahkan `storage:link`, build aset, `git pull --ff-only`, dan preflight di akhir.
12. **Proxy.** Ditambahkan `config/trustedproxy.php` (`TRUSTED_PROXIES`). Tanpa ini, di belakang Cloudflare semua pendaki terbaca satu IP dan rate-limit pendaftaran memblokir semua orang.
13. **Cache aset.** Nginx mengirim `immutable` 30 hari, tetapi `pwa.js`, `currency-input.js`, dan `escpos-bt.js` tidak diberi versi, sehingga update tidak sampai ke perangkat petugas. Sekarang diberi `?v=filemtime`.
14. **Pencarian tiket lewat nomor WA membocorkan kode tiket.** Siapa pun yang tahu nomor HP pendaki bisa mendapat kode tiket, lalu mengganti KTP atau bukti bayarnya. Sekarang pencarian lewat nomor WA wajib menyertakan 4 digit terakhir NIK ketua. Setelah 5 tebakan salah, nomor itu dikunci 30 menit (berlaku lintas IP). Respons "tidak ditemukan" sama untuk nomor dan NIK yang salah. Contoh kode demo `SMK-202611-001/002` dihapus dari portal, dan query pencarian di-escape (XSS). Pencarian lewat kode tidak berubah.

### Gap tersisa (belum diperbaiki, perlu keputusan)

| Prioritas | Gap | Rekomendasi |
|---|---|---|
| P1 | Belum ada backup otomatis DB & `storage/app` (KTP, bukti bayar) | `spatie/laravel-backup` atau cron `pg_dump` ke off-site, plus uji restore |
| P2 | Bukti bayar disimpan di disk `public` (bisa diakses lewat URL acak) | Pindahkan ke disk `local` dan layani lewat rute bertanda tangan (`URL::temporarySignedRoute`) |
| P2 | Belum ada error monitoring & uptime check | Sentry/Flare, plus monitor `/up` |
| P2 | `uploadProof` langsung men-set `payment_status = paid` sebelum diverifikasi kasir | Tambahkan status `proof_submitted` agar laporan pendapatan tidak terkontaminasi |
| P3 | Belum ada Content-Security-Policy | Mulai dengan `Content-Security-Policy-Report-Only` |
| P3 | Versi cache service worker masih manual | Turunkan versinya dari hash/commit saat deploy |
| P3 | Belum ada pipeline CI | GitHub Actions: `php artisan test` + `pint --test` |

---

## D. Prosedur Uji Kelayakan di Server

```bash
php artisan samsara:preflight            # 0 FAIL = layak
php artisan samsara:preflight --strict   # WARN juga dianggap gagal
```

Preflight memeriksa hal-hal berikut:
- env: production, debug off, https, secure cookie, quick login off, queue, mail, WA
- koneksi DB & Redis, dan migrasi yang belum jalan
- izin tulis folder dan symlink storage
- heartbeat scheduler
- rekening, kontak, dan QRIS resmi (menolak nilai demo)
- superadmin aktif, tidak ada akun ber-password bawaan, dan tidak ada akun demo

Hasil di mesin lokal saat audit: 5 FAIL. Itu memang diharapkan, karena env lokal dan data seeder demo.
