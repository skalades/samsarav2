# Product Requirements Document — Portal Pendakian SAMSARA

**Produk:** Portal pendakian Jalur SAMSARA, Gunung Cikuray  
**Versi:** 0.3 — Diperluas untuk ojek, POS, dan inventori  
**Status:** Konsep/MVP; belum terhubung ke SIMAKSI resmi  
**Tanggal:** 21 November 2026  
**Pemilik produk:** Belum ditetapkan  

> Dokumen ini menerjemahkan mockup interaktif SAMSARA menjadi kebutuhan produk yang dapat ditinjau bersama pengelola jalur dan tim implementasi. Tarif, kuota, regulasi, jalur layanan, aturan pendakian, dan integrasi sistem belum dikonfirmasi. Semua nilai tersebut wajib divalidasi sebelum peluncuran.

## 1. Ringkasan produk

Portal SAMSARA adalah layanan digital untuk membantu calon pendaki memperoleh informasi resmi tentang Jalur SAMSARA di Gunung Cikuray dan mengajukan izin pendakian (SIMAKSI) secara daring. Portal menyediakan pengalaman publik bagi pendaki serta area operasional bagi petugas untuk meninjau permohonan. Jika pengelola telah memiliki sistem SIMAKSI, portal diharapkan berintegrasi dengannya melalui mekanisme resmi yang disepakati.

Tujuan MVP adalah membawa proses dari “mencari informasi dan menghubungi petugas secara terpisah” menjadi alur yang mudah dipahami: pendaki membaca informasi, mengirim permohonan rombongan, memantau keputusan dan instruksi berikutnya, lalu menunjukkan bukti izin saat check-in. Petugas dapat mengelola permohonan secara tertib dengan jejak audit. Portal juga mencatat layanan lokal yang tersedia bagi pendaki—ojek lokal, sewa alat, dan penjualan produk/makanan—melalui sistem kasir agar transaksi, stok, dan tanggung jawab operasional dapat direkonsiliasi.

MVP produksi tidak boleh mengesankan bahwa pengajuan sudah menjadi izin. Permohonan hanya sah setelah tahapan resmi yang ditetapkan pengelola selesai dan status penerbitan izin dapat diverifikasi. Layanan ojek, penyewaan dan penjualan adalah transaksi operasional terpisah dari izin pendakian.

## 2. Latar belakang dan masalah

Informasi jalur, persyaratan, kapasitas, jadwal, pembayaran, dan prosedur izin dapat tersebar di beberapa kanal. Pendaki berisiko menerima informasi yang tidak seragam atau datang tanpa persiapan. Petugas berisiko menangani data rombongan dan transaksi layanan secara manual, sulit melacak perubahan status, stok atau pembayaran, serta harus menjawab pertanyaan berulang.

Portal perlu menjadi sumber informasi yang dikelola pengelola jalur, menyediakan pengajuan SIMAKSI terstruktur, serta mendukung operasi layanan ojek lokal, penyewaan alat, penjualan produk/makanan, dan pencatatan kasir. Kebutuhan ini harus disesuaikan dengan proses resmi yang berlaku; produk tidak menetapkan kebijakan kuota, tarif, keselamatan, pangan, atau persyaratan atas nama pengelola.

## 3. Visi, sasaran, dan indikator

**Visi:** Satu pintu digital yang jelas, aman, dan dapat dipercaya untuk merencanakan pendakian melalui Jalur SAMSARA dan mengelola layanan pendukungnya.

**Sasaran MVP:**

- Memastikan pendaki mudah menemukan panduan dan persyaratan yang telah disahkan pengelola.
- Mengurangi permohonan tidak lengkap melalui formulir dan validasi yang jelas.
- Memungkinkan petugas meninjau permohonan dan memperbarui status dengan alasan serta waktu perubahan.
- Menyediakan status, konfirmasi, dan instruksi yang dapat dipahami pendaki.
- Mencegah penerbitan ganda atau pemesanan melebihi kapasitas yang disahkan.
- Mencatat layanan ojek dan transaksi sewa/penjualan di POS, dengan stok serta kas yang dapat direkonsiliasi.
- Membatasi akses sistem berdasarkan tanggung jawab masing-masing peran.

**Indikator keberhasilan yang perlu ditetapkan baseline dan targetnya bersama pengelola:** tingkat penyelesaian formulir, persentase permohonan lengkap pada pengajuan pertama, waktu median hingga keputusan petugas, tingkat kegagalan transaksi, jumlah konflik kuota, transaksi ojek yang cocok dengan penugasan dan rekonsiliasi, selisih kas, akurasi stok, tingkat pengembalian alat tepat waktu, jumlah pertanyaan dukungan, dan insiden privasi/keamanan. Target numerik belum ditentukan.

## 4. Pengguna dan kebutuhan

**Calon pendaki / ketua rombongan:** ingin memahami kondisi dan persyaratan jalur, memilih tanggal yang tersedia, mendaftarkan seluruh anggota, membayar jika diwajibkan, serta menerima bukti izin dan instruksi check-in. Jika tersedia, pendaki juga dapat meminta layanan ojek lokal resmi, menyewa alat, dan membeli produk atau makanan/minuman.

**Anggota rombongan:** perlu mengetahui bahwa datanya dimasukkan oleh ketua, memahami persyaratan yang berlaku, dan dapat meminta koreksi atau penghapusan data sesuai kebijakan.

**Petugas verifikasi:** ingin melihat permohonan dan dokumen yang diperlukan, membandingkan data dengan aturan yang berlaku, meminta perbaikan, menyetujui atau menolak dengan alasan, serta mencatat proses secara akuntabel.

**Kasir:** ingin mencatat dan merekonsiliasi transaksi SIMAKSI bila berlaku, layanan ojek, penyewaan, dan penjualan produk/makanan melalui satu alur kasir; dapat menangani pembayaran, pengembalian dana atau pembatalan sesuai kewenangan tanpa mengubah catatan audit.

**Pengemudi ojek / operator layanan:** ingin menerima permintaan yang disetujui, mengetahui titik jemput/tujuan dan status tugas yang diperlukan, serta mencatat penerimaan atau penyelesaian layanan. Hanya informasi minimum yang diperlukan untuk tugas yang boleh ditampilkan.

**Petugas penyewaan / inventori:** ingin melihat ketersediaan alat, mencatat serah-terima, pengembalian, kondisi barang dan stok produk.

**Admin/pengelola jalur:** ingin mengatur informasi, persyaratan, kalender/kuota, tarif, produk, stok, layanan, peran pengguna, dan laporan operasional tanpa mengubah kode aplikasi.

**Petugas check-in:** ingin memverifikasi izin dan anggota rombongan dengan cepat, termasuk saat jaringan terbatas jika kebijakan operasional mengizinkannya.

## 5. Prinsip dan batasan produk

- Informasi resmi harus memiliki pemilik, waktu pembaruan, dan kanal koreksi.
- Status permohonan ditulis jelas; status “diajukan” tidak sama dengan “izin terbit”.
- Integrasi hanya dilakukan setelah otorisasi tertulis dan spesifikasi API dari pemilik sistem SIMAKSI.
- Hindari mengumpulkan data yang tidak dibutuhkan. Data identitas sensitif hanya diminta jika benar-benar disyaratkan kebijakan resmi dan mempunyai dasar pemrosesan yang sah.
- Setiap keputusan petugas, transaksi kasir, perubahan harga/stok, pembatalan dan refund harus dapat diaudit.
- Antarmuka harus mudah digunakan di telepon seluler dan pada koneksi lambat.
- Ojek dalam dokumen ini berarti layanan ojek lokal yang diakomodasi jalur; tidak serta-merta berarti integrasi platform transportasi daring pihak ketiga.
- Mockup saat ini merupakan simulasi antarmuka: data disimpan di memori browser, formulir tidak dikirim ke backend, dan tindakan dashboard bukan keputusan resmi.

## 6. Ruang lingkup

### 6.1 Termasuk dalam MVP produksi

- Halaman publik berisi profil jalur, panduan, persyaratan, aturan kawasan, pengumuman kondisi/penutupan, FAQ, dan kontak resmi.
- Kalender jadwal dan kapasitas berdasarkan data yang disahkan pengelola.
- Akun atau mekanisme verifikasi kontak ketua rombongan yang dipilih tim keamanan.
- Formulir permohonan SIMAKSI untuk ketua dan anggota, persetujuan/pemberitahuan privasi, validasi, nomor permohonan, dan penyimpanan aman.
- Alur review petugas: antrean, rincian permohonan, permintaan koreksi, persetujuan/penolakan dengan alasan, dan riwayat tindakan.
- Notifikasi status dan instruksi melalui kanal yang disepakati (misalnya email atau SMS/WhatsApp resmi, jika tersedia dan diizinkan).
- Pembayaran SIMAKSI hanya bila proses resmi mensyaratkan; integrasi melalui penyedia dan rekening resmi, dengan rekonsiliasi status transaksi.
- Penerbitan bukti izin/QR hanya sesudah seluruh syarat resmi terpenuhi.
- Check-in berbasis bukti izin dan prosedur verifikasi petugas.
- Katalog layanan lokal yang disetujui: permintaan jasa ojek yang mengakomodasi pendaki, reservasi/penyewaan alat, serta penjualan produk dan makanan/minuman.
- Sistem kasir/POS untuk mencatat setiap transaksi layanan ojek, sewa alat, produk/makanan, dan transaksi lain yang disetujui; struk dan jejak transaksi.
- Pengelolaan ketersediaan operator/pengemudi, inventori barang sewa, stok produk, pencatatan serah-terima/pengembalian, dan laporan transaksi.
- Multi-level user berbasis peran dan cakupan akses: admin/pemilik, manajer operasional, petugas SIMAKSI, kasir, petugas inventori/penyewaan, pengemudi/operator ojek, petugas check-in, dan auditor/pelihat laporan.
- Dashboard operasional dan ekspor terbatas sesuai peran, dengan log audit.
- Analitik agregat tanpa mengekspos data identitas.

### 6.2 Tidak termasuk tanpa keputusan eksplisit

- Menetapkan tarif, komisi, kuota, jam operasional, syarat usia/kesehatan, dokumen wajib, kebijakan pembatalan, tarif ojek, biaya sewa, deposit, atau harga produk.
- Menjamin kondisi cuaca, keselamatan jalur, waktu tempuh, kualitas/keamanan makanan, atau ketersediaan layanan penyelamatan.
- Peta navigasi offline, pelacakan GPS, SOS, asuransi, marketplace pemandu/peralatan, dan pengelolaan basecamp penuh.
- Menggunakan atau menyalin sistem SIMAKSI pihak lain tanpa izin dan kontrak integrasi.
- Menganggap data atau status yang ada di mockup sebagai data pendaki/izin sungguhan.
- Integrasi aplikasi transportasi pihak ketiga. Jika nantinya diperlukan, tentukan mitra dan cakupannya sebagai inisiatif terpisah.

### 6.3 Multi-level user dan batas kewenangan

Akses menggunakan RBAC (role-based access control) dan prinsip least privilege. Peran awal berikut perlu dipetakan ke struktur organisasi nyata:

| Peran | Kewenangan yang disarankan | Batasan utama |
|---|---|---|
| Pemilik sistem / superadmin | Pengaturan global, penetapan peran, konfigurasi integrasi, audit dan laporan lintas fungsi | Akun sangat terbatas; MFA wajib; tindakan sensitif tercatat dan ditinjau. |
| Admin/pengelola | Konten, katalog, harga, jadwal, kuota, stok, laporan operasional, pengelolaan akun sesuai delegasi | Tidak dapat menghapus audit log atau mengubah transaksi tertutup tanpa prosedur koreksi. |
| Manajer operasional | Melihat antrean dan ringkasan operasional, menetapkan penugasan, menyetujui pengecualian sesuai delegasi | Tidak mengubah konfigurasi sistem atau menetapkan harga kecuali diberikan kewenangan eksplisit. |
| Petugas SIMAKSI/verifikator | Melihat data yang diperlukan untuk review; minta perbaikan, setujui/tolak | Tidak mengakses kasir, data pembayaran penuh, atau mengubah daftar harga. |
| Kasir | Membuat transaksi POS, menerima pembayaran, mencetak/mengirim struk, mengajukan void/refund sesuai batas | Tidak mengubah harga/konfigurasi produk dan tidak menghapus transaksi; refund/void tertentu perlu persetujuan manajer. |
| Petugas inventori/penyewaan | Mengelola stok fisik, ketersediaan alat, serah-terima, pengembalian dan kondisi | Tidak melihat data SIMAKSI lengkap atau mengubah harga tanpa wewenang. |
| Operator/pengemudi ojek | Melihat tugas yang ditugaskan, status penugasan, titik temu dan informasi minimum pelanggan | Tidak melihat seluruh antrean/rombongan, NIK, informasi pembayaran, atau laporan kasir. |
| Petugas check-in | Memeriksa validitas izin dan status kehadiran serta mencatat check-in | Tidak mengubah pembayaran, harga, atau data permohonan di luar prosedur koreksi. |
| Auditor/pelihat laporan | Membaca laporan dan jejak audit sesuai cakupan | Read-only; ekspor data pribadi dibatasi dan dicatat. |
| Pendaki | Mengelola permohonan/layanan miliknya, melihat tagihan, status, struk, dan instruksi | Tidak dapat melihat permohonan atau transaksi pengguna lain. |

Semua akun staf bersifat individual; akun bersama dilarang untuk transaksi dan keputusan. Perubahan peran, penonaktifan akun, reset kredensial, dan akses darurat harus tercatat. Tindakan refund, void, perubahan harga, koreksi stok, dan penutupan kas mengikuti pemisahan tugas/otorisasi berjenjang yang ditetapkan pengelola.

### 6.4 Prinsip kasir dan inventori

Kasir adalah catatan transaksi operasional, bukan sekadar tombol pembayaran. Setiap transaksi memiliki nomor unik, waktu, petugas, kanal pembayaran, item/layanan, jumlah, harga saat transaksi, diskon atau biaya bila disahkan, status pembayaran, referensi pendaki/permohonan jika relevan, dan jejak koreksi. Pembatalan tidak menghapus transaksi asal; sistem membuat transaksi pembalik/koreksi yang beralasan dan dapat diaudit.

Untuk jasa ojek, sistem mencatat permintaan, asal/tujuan atau titik temu yang ditetapkan, jumlah penumpang/barang bila relevan, pengemudi/operator yang menerima, status layanan, tarif yang berlaku pada saat transaksi, pembayaran, dan penyelesaian. Pengelola perlu menetapkan apakah pendaki membayar ke kasir, langsung ke pengemudi melalui kanal resmi, atau melalui pembayaran digital terpusat; pencatatan kasir tetap diwajibkan dan rekonsiliasi harus mengikuti model tersebut. Jangan mengasumsikan integrasi aplikasi transportasi.

Untuk sewa, sistem mengelola SKU/identitas alat, jumlah tersedia, periode, tarif, status reservasi, kondisi sebelum dan sesudah, serah-terima, pengembalian, keterlambatan/kerusakan, dan deposit/denda bila kebijakan resmi menentukan. Barang yang belum kembali atau sedang diperiksa tidak boleh dianggap tersedia.

Untuk produk dan makanan/minuman, katalog dapat memuat nama, SKU, kategori, harga efektif, unit, stok, status aktif, dan informasi yang secara resmi diwajibkan. Penjualan mengurangi stok sesuai aturan transaksi; retur, produk rusak/kedaluwarsa, koreksi stok, dan pembatalan harus dicatat sebagai peristiwa tersendiri. Pengelola bertanggung jawab menetapkan kepatuhan perizinan, keamanan pangan, label/alergen, masa simpan, dan prosedur penanganan.

## 7. Alur utama

### 7.1 Pengajuan pendaki

1. Pendaki membuka portal dan membaca informasi terbaru, persyaratan, kuota, serta kebijakan pembatalan.
2. Pendaki memilih tanggal mulai dan jumlah anggota; sistem menampilkan ketersediaan berdasarkan sumber resmi.
3. Ketua mengisi identitas dan kontak minimum yang diwajibkan. Data anggota dikumpulkan hanya sesuai kebutuhan proses resmi.
4. Sistem memvalidasi format, kelengkapan, usia/persyaratan bila aturan resmi telah ditentukan, dan persetujuan privasi.
5. Sistem membuat nomor permohonan, menahan kapasitas sementara hanya jika kebijakan dan desain kuota mengizinkan, serta memberi tahu langkah berikutnya.
6. Petugas meninjau. Jika perlu, petugas mengembalikan permohonan untuk koreksi; pendaki menerima alasan dan batas waktu koreksi.
7. Setelah disetujui, pendaki menyelesaikan pembayaran jika diwajibkan. Sistem menunggu konfirmasi pembayaran terverifikasi dari penyedia, bukan dari input manual pengguna.
8. Setelah semua prasyarat dipenuhi, sistem menerbitkan bukti izin unik yang dapat diverifikasi.
9. Saat check-in, petugas memindai atau memeriksa bukti, mencocokkan rombongan sesuai prosedur, dan merekam kedatangan.
10. Setelah perjalanan, sistem menyelesaikan status dan menerapkan retensi/penghapusan data sesuai kebijakan.

### 7.2 Keputusan petugas

Petugas masuk melalui autentikasi kuat, membuka fungsi sesuai perannya, meninjau data sesuai kewenangan, lalu melakukan tindakan yang diizinkan. Tindakan sensitif memerlukan alasan dan, bila ditetapkan kebijakan, persetujuan tingkat kedua. Perubahan dicatat beserta aktor, waktu, status sebelum/sesudah, dan catatan. Keputusan izin, refund, void, perubahan harga, koreksi stok, dan penutupan kas tidak boleh dilakukan melalui tautan publik atau tanpa autentikasi. Pencabutan peran atau akun harus segera mencabut sesi/token aktif sesuai desain keamanan.

### 7.3 Layanan ojek lokal

1. Pendaki memilih layanan ojek lokal yang tersedia setelah atau bersamaan dengan pengajuan SIMAKSI, memilih titik/tujuan yang ditawarkan, jumlah penumpang, dan waktu penjemputan sesuai kapasitas serta aturan pengelola.
2. Sistem menampilkan tarif resmi dan ketentuan yang berlaku sebelum pendaki mengonfirmasi. Bila layanan perlu dikonfirmasi operator, permintaan berstatus menunggu penugasan dan belum dianggap dipesan.
3. Operator yang berwenang menerima/menolak penugasan. Pendaki menerima status, titik temu, instruksi dan identitas minimum pengemudi yang telah disetujui untuk dibagikan.
4. Transaksi jasa masuk ke POS dengan referensi unik dan terkait ke permohonan SIMAKSI hanya jika relevan. Catat kanal pembayaran dan penerima dana; bila pembayaran langsung ke pengemudi, kasir tetap merekam transaksi dan proses rekonsiliasi sesuai prosedur.
5. Setelah layanan selesai, operator atau petugas mengonfirmasi penyelesaian. Pembatalan, no-show, perubahan tarif yang disetujui, dan sengketa dicatat tanpa menghapus riwayat.

### 7.4 Penyewaan alat dan penjualan produk/makanan

1. Pendaki melihat ketersediaan alat sewa dan produk yang dijual; untuk sewa, sistem mengunci stok hanya dalam jangka waktu yang ditetapkan pengelola.
2. Kasir membuat pesanan, mengonfirmasi jumlah/harga yang berlaku, menerima pembayaran, lalu mengeluarkan struk. Produk yang stoknya tidak mencukupi tidak dapat dijual kecuali manajer mencatat pengecualian.
3. Untuk barang sewa, petugas mencatat kondisi dan jumlah saat serah-terima. Saat pengembalian, petugas mencatat waktu, kondisi dan selisih; deposit, denda atau biaya kerusakan hanya diproses menurut kebijakan yang disahkan.
4. Penjualan makanan/minuman mencatat SKU, jumlah, harga efektif dan perubahan stok. Retur, barang rusak/kedaluwarsa, dan koreksi stok menggunakan alasan serta akun petugas.
5. Tutup kas mencocokkan total POS dengan uang tunai dan laporan penyedia pembayaran; selisih harus diberi catatan dan dapat ditinjau manajer.

### 7.5 Pembatalan dan perubahan

Aturan untuk pembatalan pendakian, perubahan tanggal/anggota, pembatalan ojek, no-show, pengembalian dana, pengembalian sewa, keterlambatan, kerusakan alat, retur produk, dan penutupan jalur harus ditetapkan pengelola. Sistem harus menampilkan konsekuensi sebelum pendaki mengonfirmasi dan menyimpan riwayat perubahan. Jangan mengimplementasikan otomatisasi kebijakan sebelum aturan disepakati.

## 8. Persyaratan fungsional

| ID | Persyaratan | Prioritas | Kriteria penerimaan ringkas |
|---|---|---|---|
| FR-01 | Menampilkan konten jalur yang dikelola admin | P0 | Pengunjung melihat persyaratan, aturan, kontak, waktu pembaruan dan status pengumuman; konten kedaluwarsa dapat ditandai. |
| FR-02 | Menampilkan kalender ketersediaan resmi | P0 | Tanggal penuh/tertutup tidak dapat dipilih; kapasitas berasal dari sumber yang menjadi sumber kebenaran. |
| FR-03 | Membuat permohonan SIMAKSI | P0 | Validasi sisi klien dan server; data minimum; nomor unik; konfirmasi hanya setelah penyimpanan backend berhasil. |
| FR-04 | Memvalidasi rombongan dan kuota | P0 | Permintaan bersamaan tidak dapat melampaui kapasitas; transaksi atomik atau mekanisme setara mencegah overbooking. |
| FR-05 | Mengelola autentikasi dan otorisasi petugas | P0 | Hanya petugas berwenang dapat melihat/mengubah data; akses admin dan petugas dapat dibedakan. |
| FR-06 | Meninjau permohonan | P0 | Petugas dapat menyetujui, menolak, atau meminta koreksi dengan alasan; tindakan tercatat di audit log. |
| FR-07 | Menampilkan status kepada pendaki | P0 | Pendaki dapat melihat status terkini melalui sesi aman atau tautan ber-token yang kedaluwarsa; tidak membocorkan data melalui nomor permohonan saja. |
| FR-08 | Mengirim notifikasi status | P1 | Notifikasi dikirim setelah perubahan status, kegagalan dicatat dan dapat dicoba ulang tanpa menggandakan keputusan. |
| FR-09 | Pembayaran resmi SIMAKSI | P1* | Jika diwajibkan: status berasal dari callback terverifikasi/rekonsiliasi penyedia; idempotensi dan pengembalian dana dicatat. |
| FR-10 | Menerbitkan bukti izin dan QR | P1* | Hanya terbit jika syarat yang ditetapkan dipenuhi; memiliki identitas unik, masa berlaku, dan mekanisme validasi/revokasi. |
| FR-11 | Check-in petugas | P1 | Petugas memverifikasi bukti, mencegah scan/check-in ganda tanpa konfirmasi, dan mencatat waktu serta hasil. |
| FR-12 | Administrasi konten dan konfigurasi | P1 | Admin berwenang mengubah konten, tanggal tutup, kuota dan persyaratan; perubahan penting terekam. |
| FR-13 | Ekspor dan pelaporan | P2 | Ekspor dibatasi peran, dicatat, meminimalkan PII, dan menyertakan filter periode/status. |
| FR-14 | Pembatalan/perubahan jadwal pendakian | P1* | Implementasi mengikuti aturan resmi yang telah disahkan dan mengomunikasikan dampak sebelum konfirmasi. |
| FR-15 | Multi-level user dan RBAC | P0 | Peran staf memiliki akses least-privilege; akses ditolak secara default; perubahan akun/peran tercatat; tiap peran lulus pengujian akses. |
| FR-16 | Pencatatan jasa ojek lokal | P1 | Permintaan, tarif efektif, operator, status layanan, pembayaran dan pembatalan memiliki ID transaksi serta tercatat di POS. |
| FR-17 | Manajemen penugasan ojek | P1 | Operator hanya melihat tugas yang ditugaskan dan data minimum; kapasitas/waktu mengikuti konfigurasi disetujui; penyelesaian tercatat. |
| FR-18 | POS/kasir multi-layanan | P1 | Kasir dapat mencatat item produk dan layanan; struk rinci; metode bayar/status tercatat; void tidak menghapus transaksi asal. |
| FR-19 | Katalog dan stok produk/makanan | P1 | Admin mengatur SKU, harga efektif, kategori, stok dan status; penjualan mengurangi stok; retur/rusak/kedaluwarsa tercatat. |
| FR-20 | Reservasi dan penyewaan alat | P1 | Ketersediaan dihitung berdasar stok/periode; serah-terima/pengembalian mencatat kondisi dan waktu. |
| FR-21 | Tutup kas dan rekonsiliasi | P1 | Kasir merekonsiliasi tunai dan kanal pembayaran per shift; selisih dicatat dan ditinjau peran berwenang. |
| FR-22 | Refund, void, dan koreksi berjenjang | P1 | Tindakan mengikuti batas/persetujuan peran; alasan wajib; transaksi asal tetap dapat ditelusuri. |
| FR-23 | Laporan layanan dan inventori | P2 | Laporan dapat difilter per tanggal, kategori, SKU, operator dan status; ekspor dibatasi peran. |

P0 = wajib untuk peluncuran awal. P1* bergantung pada kebijakan dan integrasi yang disepakati; jangan merilis proses yang secara resmi mensyaratkan pembayaran/izin tanpa fungsi tersebut.

## 9. Status permohonan dan layanan

Status SIMAKSI awal untuk divalidasi: **Draft → Diajukan → Menunggu verifikasi → Perlu perbaikan → Menunggu pembayaran (jika berlaku) → Pembayaran terkonfirmasi (jika berlaku) → Disetujui/izin terbit → Check-in → Selesai**. Status terminal alternatif: **Ditolak**, **Dibatalkan**, **Kedaluwarsa**, **Jalur ditutup**.

Status ojek awal: **Diminta → Menunggu penugasan → Ditugaskan → Dalam layanan → Selesai**, dengan alternatif **Ditolak**, **Dibatalkan**, **No-show**, dan **Perlu bantuan**. Status sewa meliputi **Dipesan → Dibayar/terkonfirmasi → Diserahkan → Dikembalikan → Diperiksa → Ditutup**, dengan jalur **Terlambat** atau **Perlu penanganan**. Status transaksi kasir: **Dibuat → Menunggu pembayaran → Dibayar**, atau **Dibatalkan**, **Refund sebagian/penuh**, **Gagal**. Transisi harus eksplisit, tervalidasi server-side, mempunyai aktor pemicu, serta tercatat. Jangan menyamakan status pesanan layanan dengan status izin pendakian.

## 10. Model transaksi dan operasional kasir

Semua transaksi yang dikenai pembayaran—SIMAKSI bila berlaku, ojek, sewa, produk, serta makanan/minuman—harus tercatat di satu ledger POS atau ledger terintegrasi dengan ID unik, waktu, pelaku, rincian item/layanan, kuantitas, harga efektif saat penjualan, diskon/biaya disahkan, pajak bila berlaku, total, kanal pembayaran, status, dan referensi terkait. Nilai transaksi historis tidak berubah ketika harga katalog diperbarui.

Kasir memilih layanan/SKU, mengaitkan transaksi dengan nomor permohonan atau pelanggan bila diperlukan, mengonfirmasi total, merekam pembayaran, dan menerbitkan struk. Pembayaran digital dikonfirmasi backend dari penyedia; pembayaran tunai dicatat kasir. Satu transaksi dapat memuat beberapa kategori jika pengelola mengizinkan. Kebijakan pembagian pendapatan antara pengelola dan pengemudi harus ditetapkan dan direkonsiliasi.

Setiap shift kas memiliki pembukaan, transaksi, void/refund yang disetujui, setoran/serah-terima dan penutupan. Laporan mencocokkan nilai POS dengan uang fisik dan settlement penyedia; perbedaan dicatat sebagai selisih untuk ditinjau. Dilarang menghapus transaksi yang telah dibayar atau menimpa harga transaksi historis.

### Kriteria penerimaan kasir dan layanan

1. Hanya peran berwenang dapat membuka POS; kasir tidak dapat mengubah master harga atau menghapus transaksi yang telah disimpan.
2. Setiap layanan ojek yang dipesan atau diterima di lokasi memiliki satu referensi tugas dan transaksi kasir, status layanan, penerima pembayaran, serta cara rekonsiliasi yang konsisten.
3. Operator hanya melihat tugasnya dan data minimum; perubahan status tugas tercatat.
4. Reservasi alat tidak melebihi ketersediaan; alat belum kembali atau sedang diperiksa tidak dapat disewakan kembali.
5. Serah-terima dan pengembalian mencatat petugas, waktu, jumlah dan kondisi; biaya kerusakan/deposit mengikuti aturan yang disahkan.
6. Penjualan mengurangi stok sekali saja; pengulangan callback/permintaan tidak menggandakan transaksi atau pengurangan stok.
7. Void, refund, retur, koreksi stok memiliki alasan, aktor, waktu, persetujuan sesuai kewenangan; transaksi asal tetap dapat ditelusuri.
8. Penutupan kas menampilkan total per metode bayar dan selisih terhadap hitung aktual.
9. Setiap peran lulus pengujian akses positif dan negatif.
10. Struk tidak menampilkan NIK atau data identitas pendaki yang tidak diperlukan.

## 11. Persyaratan data

Skema minimum konseptual:

- **Application:** ID acak, nomor referensi publik nonsekuensial, tanggal pendakian, jalur, jumlah anggota, status, waktu pembuatan/perubahan, versi kebijakan yang disetujui.
- **Applicant / group member:** nama dan data kontak/identitas hanya sesuai dasar kebutuhan; hubungan dengan permohonan; status verifikasi. Data anggota tidak boleh dapat diakses pemohon lain.
- **Emergency contact:** nama dan kontak bila disahkan sebagai kebutuhan; informasi kepada pihak kontak ditangani transparan.
- **Payment transaction:** ID penyedia, nilai/mata uang dari konfigurasi resmi, status, waktu dan referensi rekonsiliasi; tidak menyimpan data kartu.
- **Permit:** ID, masa berlaku, status, referensi permohonan dan token/QR yang dapat dicabut.
- **POS transaction / line item:** ID transaksi, shift kasir, waktu, akun kasir, kategori, SKU/layanan, kuantitas, harga saat transaksi, diskon/biaya/pajak bila berlaku, kanal bayar, status dan referensi terkait.
- **Transport service / assignment:** titik layanan, waktu, jumlah penumpang bila relevan, operator yang ditugaskan, status dan bukti penyelesaian; data pribadi pendaki diminimalkan.
- **Rental asset / movement:** identitas aset/SKU, status ketersediaan, reservasi/periode, kondisi keluar/kembali, petugas serah-terima, deposit/refund bila berlaku, catatan insiden.
- **Product / stock movement:** SKU, kategori, unit, harga efektif, stok, batch/tanggal kedaluwarsa bila diperlukan, serta peristiwa penjualan, retur, kerusakan atau koreksi.
- **Cash shift / reconciliation:** petugas pembuka/penutup, saldo awal, total per metode bayar, setoran, selisih, catatan dan persetujuan bila diwajibkan.
- **Staff user / role assignment:** ID pengguna individual, peran, cakupan akses, status akun, waktu pemberian/pencabutan peran dan aktor pemberi.
- **Audit log:** akses dan perubahan sensitif, retensi serta akses khusus; log tidak menyalin PII secara berlebihan.

Masa retensi, dasar pemrosesan, hak akses, prosedur koreksi/penghapusan, dan penanganan permintaan subjek data harus ditentukan bersama pemilik data dan penasihat yang sesuai sebelum produksi. Jangan mencatat NIK atau data identitas lengkap di log aplikasi.

## 12. Integrasi dan arsitektur konseptual

**Prinsip sumber kebenaran:** tentukan apakah portal menjadi sistem utama, lapisan frontend di atas SIMAKSI yang ada, atau sistem pengajuan yang menyinkronkan status dengan SIMAKSI resmi. Hindari dua basis data yang sama-sama dianggap kanonis.

**Komponen konseptual:** frontend publik responsif; backend API; autentikasi/otorisasi; basis data transaksional; penyimpanan dokumen privat jika diperlukan; antrean notifikasi; adaptor SIMAKSI; adaptor pembayaran; modul layanan ojek/penugasan; modul POS dan shift kas; katalog serta inventori/penyewaan; panel petugas; audit log dan monitoring.

**Kontrak integrasi SIMAKSI:** klarifikasi pemilik/otoritas API, autentikasi, endpoint/model data, sandbox, pembuatan/pembaruan/pembatalan, nomor referensi kanonis, kuota/penguncian slot, idempotensi, webhook/signature, retry, rate limits, timeout, SLA, rekonsiliasi, perubahan skema dan dukungan gangguan. Jangan menebak endpoint atau mengirim data sebelum otorisasi.

**Pembayaran:** gunakan penyedia resmi yang disetujui, hosted checkout bila sesuai, verifikasi signature callback di backend, idempotency key, rekonsiliasi berkala dan prosedur refund. Status dari browser pengguna bukan bukti pembayaran.

**Kegagalan layanan:** jika SIMAKSI atau pembayaran tidak tersedia, tampilkan status tertunda yang akurat, pertahankan idempotensi, dan sediakan prosedur dukungan. Jangan tampilkan izin sebagai terbit jika sistem otoritatif belum mengonfirmasi. Untuk POS/inventori, tentukan prosedur fallback manual dan rekonsiliasi setelah sistem pulih; hindari penjualan ganda.

## 13. Persyaratan nonfungsional

- **Keamanan:** HTTPS; enkripsi data tersimpan dan transit; pengelolaan rahasia aman; MFA untuk staf; RBAC/least privilege; akun individual; batas/otorisasi ganda untuk refund, void dan perubahan harga; perlindungan CSRF/XSS/SQL injection; rate limiting; validasi server; pemindaian dependensi; backup terenkripsi; respons insiden.
- **Privasi:** pengumpulan minimum, pemberitahuan jelas, persetujuan bila diperlukan, pembatasan akses, jadwal penghapusan, pengelolaan subprosesor, dan pemenuhan hak data. Tinjau peraturan perlindungan data Indonesia bersama pihak kompeten.
- **Ketersediaan:** target layanan dan maintenance window ditetapkan dengan pengelola; pantau API, database, antrean, pembayaran, POS, sinkronisasi inventori dan notifikasi; tetapkan RPO/RTO.
- **Kinerja:** target disepakati setelah baseline; halaman tetap ringan di jaringan seluler; daftar petugas dipaginasi dan mendukung beban puncak.
- **Aksesibilitas:** sasaran WCAG 2.2 AA; keyboard, label form, pesan kesalahan terkait field, kontras cukup, fokus terlihat, dan bahasa Indonesia yang jelas.
- **Kompatibilitas:** mobile-first; dukungan browser modern ditentukan; formulir dapat pulih dari validasi gagal.
- **Keandalan data:** permohonan, kuota, pembayaran, izin, transaksi dan stok konsisten; operasi ulang aman/idempoten; audit trail tidak dapat diubah petugas biasa.
- **Lokalisasi:** bahasa Indonesia; format tanggal, zona waktu dan kontak sesuai ketentuan lokal; mata uang dan pajak hanya tampil sesuai konfigurasi yang disahkan.

## 14. Keamanan, privasi, dan keselamatan operasi

Data identitas dan kontak pendaki dianggap sensitif secara operasional. Hindari data sungguhan di staging, screenshot, analitik dan tiket dukungan. Tetapkan peran pengendali/pemroses data, pihak yang dapat mengakses, tujuan, dasar pemrosesan, masa simpan, retensi backup, penghapusan serta prosedur insiden sebelum onboarding.

QR izin sebaiknya memuat token acak yang tidak mengungkap nama/NIK; validasi server-side dan token dapat dicabut. Hindari QR yang dapat ditebak. Untuk check-in offline, lakukan threat modeling dan sepakati batas sinkronisasi serta risiko penggunaan ulang.

Informasi ketinggian, estimasi waktu, kondisi medan, kontak darurat, prosedur tutup jalur, serta batas operasional harus ditinjau dan diberi tanggal oleh pengelola. Portal bukan pengganti arahan petugas atau penilaian kondisi lapangan. Pembagian data pendaki kepada operator ojek dibatasi hanya untuk menjalankan layanan dan sesuai pemberitahuan privasi.

## 15. UX dan konten

Antarmuka mengikuti identitas SAMSARA yang tenang dan terinspirasi vegetasi pegunungan, namun informasi keselamatan, status izin dan harga layanan harus jelas. Aksi menggunakan label yang menyebut hasilnya, seperti “Ajukan permohonan” dan “Konfirmasi sewa”. Pesan sukses harus menjelaskan apakah pengajuan/transaksi benar-benar tercatat dan langkah berikutnya.

Konten awal yang harus disediakan pengelola: deskripsi resmi jalur; titik awal dan koordinat bila boleh dipublikasikan; estimasi yang disahkan; tingkat kesulitan yang didefinisikan; daftar perlengkapan; persyaratan dan aturan; prosedur check-in; tarif, kuota dan cara pembayaran; kebijakan pembatalan; kondisi/penutupan; kontak resmi; katalog ojek/sewa/produk; ketersediaan dan jam operasional; kebijakan retur; informasi produk/makanan yang diwajibkan; serta tanggal pembaruan.

## 16. Metrik, analitik, dan observabilitas

Kumpulkan funnel agregat: kunjungan info → cek tanggal → mulai formulir → pengajuan → verifikasi → pembayaran (bila ada) → izin terbit → check-in. Metrik layanan: permintaan/penyelesaian ojek, waktu penugasan, transaksi/rekonsiliasi kas, selisih kas, akurasi stok, utilisasi dan keterlambatan sewa, retur/kerusakan produk. Hindari nama, email, telepon, nomor identitas dan isi formulir pada event analytics.

Pantau waktu respons API, error rate, sinkronisasi SIMAKSI, callback pembayaran, konflik kuota, permohonan melewati SLA, kegagalan notifikasi, transaksi POS tertunda, selisih rekonsiliasi, kegagalan pengurangan stok, reservasi sewa tumpang tindih, stok rendah/kedaluwarsa, dan aktivitas administratif berisiko. Sediakan alarm dan runbook sesuai kapasitas pengelola.

## 17. Kriteria penerimaan MVP

1. Pengunjung dapat membaca persyaratan resmi, melihat waktu pembaruan, serta menemukan kontak dan pengumuman penutupan.
2. Pengajuan tidak lengkap ditolak dengan pesan spesifik; permohonan valid menghasilkan satu nomor referensi.
3. Dua pengajuan bersamaan untuk slot terakhir tidak dapat sama-sama mengonsumsi kapasitas.
4. Pendaki hanya dapat melihat permohonan miliknya melalui mekanisme akses aman.
5. Petugas tanpa izin tidak dapat mengakses data; keputusan dan perubahan status tercatat di audit log.
6. Permohonan yang perlu perbaikan menampilkan alasan; penolakan memiliki alasan yang dapat ditinjau.
7. Jika pembayaran wajib, izin tidak diterbitkan sebelum pembayaran diverifikasi backend dan syarat lain terpenuhi.
8. QR/bukti izin dapat diverifikasi, tidak membocorkan PII, serta dapat dicabut.
9. Kegagalan integrasi tidak menggandakan permohonan atau pembayaran dan tidak menampilkan status berhasil palsu.
10. Formulir dapat diselesaikan dengan keyboard dan pada ukuran layar ponsel.
11. Untuk layanan ojek, transaksi terhubung dengan penugasan/operator dan tercatat di kasir; model pembayaran dan rekonsiliasi mengikuti kebijakan pengelola.
12. Operator hanya melihat tugas miliknya dan data minimum; perubahan status ojek tercatat.
13. Reservasi serta pengembalian alat memperbarui ketersediaan dan mencatat kondisi barang.
14. Penjualan produk/makanan mencatat item dan stok; retur/koreksi memiliki jejak audit.
15. Tindakan kasir sensitif mengikuti otorisasi berjenjang; penutupan kas menampilkan rekonsiliasi.
16. Uji akses peran, transaksi ganda, overselling inventori, aksesibilitas, keamanan, backup-restore dan prosedur gangguan selesai.
17. Konten operasional yang belum disahkan tidak dipublikasikan sebagai fakta resmi.

## 18. Pengujian dan rilis

Rencana uji mencakup unit test validasi/status; integration test SIMAKSI dan pembayaran pada sandbox; end-to-end pendaki/petugas/kasir; konkurensi kuota; kontrol akses dan OWASP; aksesibilitas; jaringan lambat/mobile; notifikasi; pemulihan backup; dan simulasi insiden/penutupan jalur. Tambahkan uji penugasan dan rekonsiliasi ojek, transaksi campuran, void/refund, tutup shift, inventori sewa berdasarkan periode, keterlambatan/kerusakan, retur produk, pengurangan stok idempoten dan pelaporan.

Tahapan rilis: penemuan proses dan persetujuan kebijakan; prototipe/uji kegunaan; implementasi sandbox; UAT bersama petugas; pilot terbatas; evaluasi metrik dan insiden; lalu peluncuran bertahap. Go-live memerlukan persetujuan tertulis pemilik SIMAKSI, pengelola jalur, penanggung jawab privasi/operasi, serta pemilik proses kasir, ojek, inventori dan penjualan makanan.

## 19. Risiko dan mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| API SIMAKSI tidak tersedia/diizinkan | Integrasi tidak dapat dilakukan; status berbeda | Pastikan pemilik dan API sebelum komitmen; mode manual hanya jika disetujui, dengan rekonsiliasi dan label jelas. |
| Aturan/tarif/kuota belum ditetapkan | Informasi salah atau konflik kapasitas | Jangan mengarang nilai; tetapkan pemilik konten dan persetujuan sebelum publikasi. |
| Data pribadi berlebihan atau bocor | Risiko bagi pendaki dan pengelola | Minimisasi, kontrol akses, enkripsi, retensi, audit dan prosedur insiden. |
| Pembayaran terkonfirmasi ganda/palsu | Kerugian dan izin salah terbit | Callback bertanda tangan, idempotensi, rekonsiliasi; jangan mempercayai status browser. |
| Kecurangan kasir/transaksi tidak cocok | Kehilangan pendapatan dan laporan tak akurat | Akun individual, pemisahan tugas, otorisasi void/refund, tutup shift, rekonsiliasi dan audit log. |
| Stok alat/produk tidak akurat | Overbooking atau penjualan stok kosong | Pergerakan stok atomik, serah-terima, stock opname dan penandaan barang rusak/terlambat. |
| Ojek salah ditugaskan atau pembayaran tidak jelas | Layanan gagal, sengketa dan risiko keselamatan | Tetapkan SOP, titik layanan, aturan pembayaran/komisi, bukti penyelesaian, komplain dan batas data. |
| Pengelolaan makanan tidak sesuai aturan | Risiko kesehatan, hukum dan reputasi | Validasi izin, keamanan, label/alergen, masa simpan dan prosedur penarikan sebelum katalog aktif. |
| Lonjakan permohonan | Situs lambat, kuota tidak akurat | Uji beban, antrean, penguncian kuota atomik, monitoring dan rencana kapasitas. |
| Kondisi lapangan berubah | Informasi usang dan risiko keselamatan | Pengumuman bercap waktu, pemilik konten piket, mekanisme tutup tanggal/jalur cepat. |
| Koneksi internet lemah di basecamp | Check-in/POS terhambat | Prosedur fallback yang disetujui; rekonsiliasi setelah sistem pulih; mode offline perlu threat modeling. |

## 20. Dependensi dan keputusan terbuka

Sebelum estimasi dan implementasi produksi, tetapkan pemilik/operator SIMAKSI dan akses API; proses pengajuan/status; kalender, kuota dan reservasi; data wajib serta dasar pengumpulan; tarif, penyedia pembayaran, refund, pajak dan rekonsiliasi; pembatalan/no-show; penerbitan izin/check-in; notifikasi; privasi/retensi/insiden; SLA dukungan; sumber informasi kondisi jalur; peran dan cakupan akses; volume puncak; serta pemilik biaya operasi.

Keputusan tambahan untuk layanan lokal dan POS: apakah ojek adalah layanan pengemudi lokal saja atau perlu platform eksternal; operator yang bertanggung jawab dan verifikasi pengemudi/kendaraan; titik jemput/tujuan, jam, kapasitas, harga, komisi/pembagian pendapatan dan model pembayaran; SOP keselamatan, cuaca buruk, pembatalan, no-show dan komplain; daftar alat sewa, kondisi, periode, deposit/denda dan aturan kehilangan/kerusakan; katalog SKU, satuan, harga, pajak dan retur; makanan/minuman yang boleh dijual, pemilik tanggung jawab keamanan pangan, label/alergen, masa simpan, perizinan dan prosedur penarikan; metode pembayaran dan pemilik settlement; batas refund/void dan persetujuan kedua; pembukaan/penutupan shift, setoran kas dan rekonsiliasi; role nyata, cakupan akses, audit stok; dan apakah MVP mencakup POS langsung atau pencatatan yang direkonsiliasi manual.

## 21. Rekomendasi prioritas berikutnya

Mulai dengan lokakarya pengelola dan petugas untuk memetakan proses SIMAKSI, ojek, kasir, penyewaan, inventori dan penjualan makanan; validasi kebijakan serta tanggung jawabnya. Secara paralel, minta dokumentasi resmi SIMAKSI dan sandbox integrasi. Setelah sumber kebenaran, data minimum, kuota, pembayaran, model layanan lokal, kewenangan peran dan SOP kasir disepakati, ubah dokumen ini menjadi backlog dengan pemilik, estimasi, dan target terukur. Mockup saat ini berguna untuk menguji navigasi/formulir, bukan bukti kesiapan integrasi atau validasi proses resmi.


**Produk:** Portal pendakian Jalur SAMSARA, Gunung Cikuray  
**Versi:** 0.3 — Diperluas untuk ojek, POS, dan inventori  
**Status:** Konsep/MVP; belum terhubung ke SIMAKSI resmi  
**Tanggal:** 21 November 2026  
**Pemilik produk:** Belum ditetapkan  

> Dokumen ini menerjemahkan mockup interaktif SAMSARA menjadi kebutuhan produk yang dapat ditinjau bersama pengelola jalur dan tim implementasi. Tarif, kuota, regulasi, jalur layanan, aturan pendakian, dan integrasi sistem belum dikonfirmasi. Semua nilai tersebut wajib divalidasi sebelum peluncuran.

## 1. Ringkasan produk

Portal SAMSARA adalah layanan digital untuk membantu calon pendaki memperoleh informasi resmi tentang Jalur SAMSARA di Gunung Cikuray dan mengajukan izin pendakian (SIMAKSI) secara daring. Portal menyediakan pengalaman publik bagi pendaki serta area operasional bagi petugas untuk meninjau permohonan. Jika pengelola telah memiliki sistem SIMAKSI, portal diharapkan berintegrasi dengannya melalui mekanisme resmi yang disepakati.

Tujuan MVP adalah membawa proses dari “mencari informasi dan menghubungi petugas secara terpisah” menjadi alur yang mudah dipahami: pendaki membaca informasi, mengirim permohonan rombongan, memantau keputusan dan instruksi berikutnya, lalu menunjukkan bukti izin saat check-in. Petugas dapat mengelola permohonan secara tertib dengan jejak audit.

MVP produksi tidak boleh mengesankan bahwa pengajuan sudah menjadi izin. Permohonan hanya sah setelah tahapan resmi yang ditetapkan pengelola selesai dan status penerbitan izin dapat diverifikasi.

## 2. Latar belakang dan masalah

Informasi jalur, persyaratan, kapasitas, jadwal, pembayaran, dan prosedur izin dapat tersebar di beberapa kanal. Pendaki berisiko menerima informasi yang tidak seragam atau datang tanpa persiapan. Petugas berisiko menangani data rombongan secara manual, sulit melacak perubahan status, dan harus menjawab pertanyaan berulang.

Portal perlu menjadi sumber informasi yang dikelola pengelola jalur, menyediakan pengajuan SIMAKSI terstruktur, dan memberi petugas kendali terhadap validasi. Kebutuhan ini harus disesuaikan dengan proses resmi yang berlaku; produk tidak menetapkan kebijakan kuota, tarif, keselamatan, atau persyaratan atas nama pengelola.

## 3. Visi, sasaran, dan indikator

**Visi:** Satu pintu digital yang jelas, aman, dan dapat dipercaya untuk merencanakan pendakian melalui Jalur SAMSARA.

**Sasaran MVP:**

- Memastikan pendaki mudah menemukan panduan dan persyaratan yang telah disahkan pengelola.
- Mengurangi permohonan tidak lengkap melalui formulir dan validasi yang jelas.
- Memungkinkan petugas meninjau permohonan dan memperbarui status dengan alasan serta waktu perubahan.
- Menyediakan status, konfirmasi, dan instruksi yang dapat dipahami pendaki.
- Mencegah penerbitan ganda atau pemesanan melebihi kapasitas yang disahkan.

**Indikator keberhasilan yang perlu ditetapkan baseline dan targetnya bersama pengelola:** tingkat penyelesaian formulir, persentase permohonan lengkap pada pengajuan pertama, waktu median hingga keputusan petugas, tingkat kegagalan transaksi, jumlah konflik kuota, jumlah pertanyaan dukungan terkait status, dan insiden privasi/keamanan. Target numerik belum ditentukan.

## 4. Pengguna dan kebutuhan

**Calon pendaki / ketua rombongan:** ingin memahami kondisi dan persyaratan jalur, memilih tanggal yang tersedia, mendaftarkan seluruh anggota, membayar jika diwajibkan, serta menerima bukti izin dan instruksi check-in. Jika tersedia, pendaki juga dapat meminta layanan ojek lokal resmi untuk perjalanan yang ditentukan pengelola, menyewa alat, dan membeli produk atau makanan.

**Anggota rombongan:** perlu mengetahui bahwa datanya dimasukkan oleh ketua, memahami persyaratan yang berlaku, dan dapat meminta koreksi atau penghapusan data sesuai kebijakan.

**Petugas verifikasi:** ingin melihat permohonan dan dokumen yang diperlukan, membandingkan data dengan aturan yang berlaku, meminta perbaikan, menyetujui atau menolak dengan alasan, serta mencatat proses secara akuntabel.

**Kasir:** ingin mencatat dan merekonsiliasi transaksi SIMAKSI bila berlaku, layanan ojek, penyewaan, dan penjualan produk/makanan melalui satu alur kasir; dapat menangani pembayaran, pengembalian dana atau pembatalan sesuai kewenangan tanpa mengubah catatan audit.

**Pengemudi ojek / operator layanan:** ingin menerima permintaan yang disetujui, mengetahui titik jemput/tujuan dan status tugas yang diperlukan, serta mencatat penerimaan atau penyelesaian layanan. Hanya informasi minimum yang diperlukan untuk tugas yang boleh ditampilkan.

**Petugas penyewaan / inventori:** ingin melihat ketersediaan alat, mencatat serah-terima, pengembalian, kondisi barang dan deposit bila berlaku.

**Admin/pengelola jalur:** ingin mengatur informasi, persyaratan, kalender/kuota, tarif, produk, stok, layanan, peran pengguna, dan laporan operasional tanpa mengubah kode aplikasi.

**Petugas check-in:** ingin memverifikasi izin dan anggota rombongan dengan cepat, termasuk saat jaringan terbatas jika kebijakan operasional mengizinkannya.

## 5. Prinsip dan batasan produk

- Informasi resmi harus memiliki pemilik, waktu pembaruan, dan kanal koreksi.
- Status permohonan ditulis jelas; status “diajukan” tidak sama dengan “izin terbit”.
- Integrasi hanya dilakukan setelah otorisasi tertulis dan spesifikasi API dari pemilik sistem SIMAKSI.
- Hindari mengumpulkan data yang tidak dibutuhkan. Data identitas sensitif hanya diminta jika benar-benar disyaratkan kebijakan resmi dan mempunyai dasar pemrosesan yang sah.
- Setiap keputusan petugas dan perubahan status harus dapat diaudit.
- Antarmuka harus mudah digunakan di telepon seluler dan pada koneksi lambat.
- Mockup saat ini merupakan simulasi antarmuka: data disimpan di memori browser, formulir tidak dikirim ke backend, dan tindakan dashboard bukan keputusan resmi.

## 6. Ruang lingkup

### 6.1 Termasuk dalam MVP produksi

- Halaman publik berisi profil jalur, panduan, persyaratan, aturan kawasan, pengumuman kondisi/penutupan, FAQ, dan kontak resmi.
- Kalender jadwal dan kapasitas berdasarkan data yang disahkan pengelola.
- Akun atau mekanisme verifikasi kontak ketua rombongan yang dipilih tim keamanan.
- Formulir permohonan SIMAKSI untuk ketua dan anggota, persetujuan/pemberitahuan privasi, validasi, nomor permohonan, dan penyimpanan aman.
- Alur review petugas: antrean, rincian permohonan, permintaan koreksi, persetujuan/penolakan dengan alasan, dan riwayat tindakan.
- Notifikasi status dan instruksi melalui kanal yang disepakati (misalnya email atau SMS/WhatsApp resmi, jika tersedia dan diizinkan).
- Pembayaran SIMAKSI hanya bila proses resmi mensyaratkan; integrasi melalui penyedia dan rekening resmi, dengan rekonsiliasi status transaksi.
- Penerbitan bukti izin/QR hanya sesudah seluruh syarat resmi terpenuhi.
- Check-in berbasis bukti izin dan prosedur verifikasi petugas.
- Katalog layanan lokal yang disetujui: permintaan jasa ojek yang mengakomodasi pendaki, reservasi/penyewaan alat, serta penjualan produk dan makanan/minuman.
- Sistem kasir/POS untuk mencatat setiap transaksi layanan ojek, sewa alat, produk/makanan, dan transaksi lain yang disetujui; satu struk dapat memuat beberapa item/layanan jika proses operasi mengizinkan.
- Pengelolaan ketersediaan pengemudi/layanan, inventori barang sewa, stok produk, pencatatan serah-terima/pengembalian, dan laporan transaksi.
- Multi-level user berbasis peran dan cakupan akses: admin/pemilik, manajer operasional, petugas SIMAKSI, kasir, petugas inventori/penyewaan, pengemudi/operator ojek, petugas check-in, dan auditor/pelihat laporan.
- Dashboard operasional dan ekspor terbatas sesuai peran, dengan log audit.
- Analitik agregat tanpa mengekspos data identitas.

### 6.2 Tidak termasuk tanpa keputusan eksplisit

- Menetapkan tarif, komisi, kuota, jam operasional, syarat usia/kesehatan, dokumen wajib, kebijakan pembatalan, tarif ojek, biaya sewa, deposit, atau harga produk.
- Menjamin kondisi cuaca, keselamatan jalur, waktu tempuh, kualitas/keamanan makanan, atau ketersediaan layanan penyelamatan.
- Peta navigasi offline, pelacakan GPS, SOS, asuransi, marketplace pemandu/peralatan, dan pengelolaan basecamp penuh.
- Menggunakan atau menyalin sistem SIMAKSI pihak lain tanpa izin dan kontrak integrasi.
- Menganggap data atau status yang ada di mockup sebagai data pendaki/izin sungguhan.
- Menganggap “ojek online” sebagai integrasi aplikasi transportasi pihak ketiga. Istilah di PRD ini berarti layanan ojek lokal yang tersedia/diakomodasi jalur, kecuali integrasi platform tertentu disetujui terpisah.

### 6.3 Multi-level user dan batas kewenangan

Akses menggunakan RBAC (role-based access control) dan prinsip least privilege. Peran awal berikut perlu dipetakan ke struktur organisasi nyata:

| Peran | Kewenangan yang disarankan | Batasan utama |
|---|---|---|
| Pemilik sistem / superadmin | Pengaturan global, penetapan peran, konfigurasi integrasi, audit dan laporan lintas fungsi | Akun sangat terbatas; MFA wajib; tindakan sensitif tercatat dan ditinjau. |
| Admin/pengelola | Konten, katalog, harga, jadwal, kuota, stok, laporan operasional, pengelolaan akun sesuai delegasi | Tidak dapat menghapus audit log atau mengubah transaksi tertutup tanpa prosedur koreksi. |
| Manajer operasional | Melihat antrean dan ringkasan operasional, menetapkan penugasan, menyetujui pengecualian sesuai delegasi | Tidak mengubah konfigurasi sistem atau menetapkan harga kecuali diberikan kewenangan eksplisit. |
| Petugas SIMAKSI/verifikator | Melihat data yang diperlukan untuk review; minta perbaikan, setujui/tolak | Tidak mengakses kasir, data pembayaran penuh, atau mengubah daftar harga. |
| Kasir | Membuat transaksi POS, menerima pembayaran, mencetak/mengirim struk, mengajukan void/refund sesuai batas | Tidak mengubah harga/konfigurasi produk dan tidak menghapus transaksi; refund/void bernilai atau kondisi tertentu perlu persetujuan manajer. |
| Petugas inventori/penyewaan | Mengelola stok fisik, ketersediaan alat, serah-terima, pengembalian dan kondisi | Tidak melihat data SIMAKSI lengkap atau mengubah harga tanpa wewenang. |
| Operator/pengemudi ojek | Melihat tugas yang ditugaskan, status penugasan, titik temu dan informasi minimum pelanggan | Tidak melihat seluruh antrean/rombongan, NIK, informasi pembayaran, atau laporan kasir. |
| Petugas check-in | Memeriksa validitas izin dan status kehadiran serta mencatat check-in | Tidak mengubah pembayaran, harga, atau data permohonan di luar prosedur koreksi. |
| Auditor/pelihat laporan | Membaca laporan dan jejak audit sesuai cakupan | Read-only; ekspor data pribadi dibatasi dan dicatat. |
| Pendaki | Mengelola permohonan/layanan miliknya, melihat tagihan, status, struk, dan instruksi | Tidak dapat melihat permohonan atau transaksi pengguna lain. |

Semua akun staf bersifat individual; akun bersama dilarang untuk transaksi dan keputusan. Perubahan peran, penonaktifan akun, reset kredensial, dan akses darurat harus tercatat. Tindakan refund, void, perubahan harga, koreksi stok, dan penutupan kas mengikuti pemisahan tugas/otorisasi berjenjang yang ditetapkan pengelola.

### 6.4 Prinsip kasir dan inventori

Kasir adalah catatan transaksi operasional, bukan sekadar tombol pembayaran. Setiap transaksi memiliki nomor unik, waktu, petugas, kanal pembayaran, item/layanan, jumlah, harga saat transaksi, diskon atau biaya bila disahkan, status pembayaran, referensi pendaki/permohonan jika relevan, dan jejak koreksi. Pembatalan tidak menghapus transaksi asal; sistem membuat transaksi pembalik/koreksi yang beralasan dan dapat diaudit.

Untuk jasa ojek, sistem mencatat permintaan, asal/tujuan atau titik temu yang ditetapkan, jumlah penumpang/barang bila relevan, pengemudi/operator yang menerima, status layanan, tarif yang berlaku pada saat transaksi, pembayaran, dan penyelesaian. Pengelola perlu menetapkan apakah pendaki membayar ke kasir, langsung ke pengemudi melalui kanal resmi, atau melalui pembayaran digital terpusat; pencatatan kasir tetap diwajibkan dan rekonsiliasi harus mengikuti model tersebut. Jangan mengasumsikan integrasi aplikasi transportasi.

Untuk sewa, sistem mengelola SKU/identitas alat, jumlah tersedia, periode, tarif, status reservasi, kondisi sebelum dan sesudah, serah-terima, pengembalian, keterlambatan/kerusakan, dan deposit/denda bila kebijakan resmi menentukan. Barang yang belum kembali atau sedang diperiksa tidak boleh dianggap tersedia.

Untuk produk dan makanan/minuman, katalog dapat memuat nama, SKU, kategori, harga efektif, unit, stok, status aktif, dan informasi yang secara resmi diwajibkan. Penjualan mengurangi stok sesuai aturan transaksi; retur, produk rusak/kedaluwarsa, koreksi stok, dan pembatalan harus dicatat sebagai peristiwa tersendiri. Pengelola bertanggung jawab menetapkan kepatuhan perizinan, keamanan pangan, label/alergen, masa simpan, dan prosedur penanganan.


## 7. Alur utama

### 7.1 Pengajuan pendaki

1. Pendaki membuka portal dan membaca informasi terbaru, persyaratan, kuota, serta kebijakan pembatalan.
2. Pendaki memilih tanggal mulai dan jumlah anggota; sistem menampilkan ketersediaan berdasarkan sumber resmi.
3. Ketua mengisi identitas dan kontak minimum yang diwajibkan. Data anggota dikumpulkan hanya sesuai kebutuhan proses resmi.
4. Sistem memvalidasi format, kelengkapan, usia/persyaratan bila aturan resmi telah ditentukan, dan persetujuan privasi.
5. Sistem membuat nomor permohonan, menahan kapasitas sementara hanya jika kebijakan dan desain kuota mengizinkan, serta memberi tahu langkah berikutnya.
6. Petugas meninjau. Jika perlu, petugas mengembalikan permohonan untuk koreksi; pendaki menerima alasan dan batas waktu koreksi.
7. Setelah disetujui, pendaki menyelesaikan pembayaran jika diwajibkan. Sistem menunggu konfirmasi pembayaran terverifikasi dari penyedia, bukan dari input manual pengguna.
8. Setelah semua prasyarat dipenuhi, sistem menerbitkan bukti izin unik yang dapat diverifikasi.
9. Saat check-in, petugas memindai atau memeriksa bukti, mencocokkan rombongan sesuai prosedur, dan merekam kedatangan.
10. Setelah perjalanan, sistem menyelesaikan status dan menerapkan retensi/penghapusan data sesuai kebijakan.

### 7.2 Keputusan petugas

Petugas masuk melalui autentikasi kuat, membuka antrean berdasarkan status/tanggal, meninjau detail sesuai kewenangan, lalu menyetujui, menolak, atau meminta koreksi dengan alasan wajib. Perubahan dicatat beserta aktor, waktu, status sebelumnya, status baru, dan catatan. Keputusan yang memengaruhi izin atau pembayaran tidak boleh dibuat melalui tautan publik atau tanpa autentikasi.

### 7.3 Layanan ojek lokal

1. Pendaki memilih layanan ojek lokal yang tersedia setelah atau bersamaan dengan pengajuan SIMAKSI, memilih titik/tujuan yang ditawarkan, jumlah penumpang, dan waktu penjemputan sesuai kapasitas serta aturan pengelola.
2. Sistem menampilkan tarif resmi dan ketentuan yang berlaku sebelum pendaki mengonfirmasi. Bila layanan perlu dikonfirmasi operator, permintaan berstatus menunggu penugasan dan belum dianggap dipesan.
3. Operator yang berwenang menerima/menolak penugasan. Pendaki menerima status, titik temu, instruksi dan identitas minimum pengemudi yang telah disetujui untuk dibagikan.
4. Transaksi jasa masuk ke POS dengan referensi unik dan terkait ke permohonan SIMAKSI hanya jika relevan. Catat kanal pembayaran dan penerima dana; bila pembayaran langsung ke pengemudi, kasir tetap merekam transaksi dan proses rekonsiliasi sesuai prosedur.
5. Setelah layanan selesai, operator atau petugas mengonfirmasi penyelesaian. Pembatalan, no-show, perubahan tarif yang disetujui, dan sengketa dicatat tanpa menghapus riwayat.

### 7.4 Penyewaan alat dan penjualan produk/makanan

1. Pendaki melihat ketersediaan alat sewa dan produk yang dijual; untuk sewa, sistem mengunci stok hanya dalam jangka waktu yang ditetapkan pengelola.
2. Kasir membuat pesanan, mengonfirmasi jumlah/harga yang berlaku, menerima pembayaran, lalu mengeluarkan struk. Produk yang stoknya tidak mencukupi tidak dapat dijual kecuali manajer mencatat pengecualian.
3. Untuk barang sewa, petugas mencatat kondisi dan jumlah saat serah-terima. Saat pengembalian, petugas mencatat waktu, kondisi dan selisih; deposit, denda atau biaya kerusakan hanya diproses menurut kebijakan yang disahkan.
4. Penjualan makanan/minuman mencatat SKU, jumlah, harga efektif dan perubahan stok. Retur, barang rusak/kedaluwarsa, dan koreksi stok menggunakan alasan serta akun petugas.
5. Tutup kas mencocokkan total POS dengan uang tunai dan laporan penyedia pembayaran; selisih harus diberi catatan dan dapat ditinjau manajer.

### 7.5 Keputusan petugas dan hak akses

Petugas masuk melalui autentikasi kuat, membuka fungsi sesuai perannya, meninjau data sesuai kewenangan, lalu melakukan tindakan yang diizinkan. Tindakan sensitif memerlukan alasan dan, bila ditetapkan kebijakan, persetujuan tingkat kedua. Perubahan dicatat beserta aktor, waktu, status sebelum/sesudah, dan catatan. Keputusan izin, refund, void, perubahan harga, koreksi stok, dan penutupan kas tidak boleh dilakukan melalui tautan publik atau tanpa autentikasi. Pencabutan peran atau akun harus segera mencabut sesi/token aktif sesuai desain keamanan.

### 7.6 Pembatalan dan perubahan

Aturan untuk pembatalan pendakian, perubahan tanggal/anggota, pembatalan ojek, no-show, pengembalian dana, pengembalian sewa, keterlambatan, kerusakan alat, retur produk, dan penutupan jalur harus ditetapkan pengelola. Sistem harus menampilkan konsekuensi sebelum pendaki mengonfirmasi dan menyimpan riwayat perubahan. Jangan mengimplementasikan otomatisasi kebijakan sebelum aturan disepakati.

## 8. Persyaratan fungsional

| ID | Persyaratan | Prioritas | Kriteria penerimaan ringkas |
|---|---|---|---|
| FR-01 | Menampilkan konten jalur yang dikelola admin | P0 | Pengunjung melihat persyaratan, aturan, kontak, waktu pembaruan dan status pengumuman; konten kedaluwarsa dapat ditandai. |
| FR-02 | Menampilkan kalender ketersediaan resmi | P0 | Tanggal penuh/tertutup tidak dapat dipilih; kapasitas berasal dari sumber yang menjadi sumber kebenaran. |
| FR-03 | Membuat permohonan SIMAKSI | P0 | Validasi sisi klien dan server; data minimum; nomor unik; konfirmasi hanya setelah penyimpanan backend berhasil. |
| FR-04 | Memvalidasi rombongan dan kuota | P0 | Permintaan bersamaan tidak dapat melampaui kapasitas; transaksi atomik atau mekanisme setara mencegah overbooking. |
| FR-05 | Mengelola autentikasi dan otorisasi petugas | P0 | Hanya petugas berwenang dapat melihat/mengubah data; akses admin dan petugas dapat dibedakan. |
| FR-06 | Meninjau permohonan | P0 | Petugas dapat menyetujui, menolak, atau meminta koreksi dengan alasan; tindakan tercatat di audit log. |
| FR-07 | Menampilkan status kepada pendaki | P0 | Pendaki dapat melihat status terkini melalui sesi aman atau tautan ber-token yang kedaluwarsa; tidak membocorkan data melalui nomor permohonan saja. |
| FR-08 | Mengirim notifikasi status | P1 | Notifikasi dikirim setelah perubahan status, kegagalan dicatat dan dapat dicoba ulang tanpa menggandakan keputusan. |
| FR-09 | Pembayaran resmi | P1* | Jika diwajibkan: status berasal dari callback terverifikasi/rekonsiliasi penyedia; idempotensi dan pengembalian dana dicatat. |
| FR-10 | Menerbitkan bukti izin dan QR | P1* | Hanya terbit jika syarat yang ditetapkan dipenuhi; memiliki identitas unik, masa berlaku, dan mekanisme validasi/revokasi. |
| FR-11 | Check-in petugas | P1 | Petugas memverifikasi bukti, mencegah scan/check-in ganda tanpa konfirmasi, dan mencatat waktu serta hasil. |
| FR-12 | Administrasi konten dan konfigurasi | P1 | Admin berwenang mengubah konten, tanggal tutup, kuota dan persyaratan; perubahan penting terekam. |
| FR-13 | Ekspor dan pelaporan | P2 | Ekspor dibatasi peran, dicatat, meminimalkan PII, dan menyertakan filter periode/status. |
| FR-14 | Pembatalan/perubahan jadwal pendakian | P1* | Implementasi mengikuti aturan resmi yang telah disahkan dan mengomunikasikan dampak sebelum konfirmasi. |
| FR-15 | Multi-level user dan RBAC | P0 | Peran staf memiliki akses least-privilege; akses ditolak secara default; perubahan akun/peran tercatat; pengujian memastikan tiap peran hanya dapat mengakses aksi yang diizinkan. |
| FR-16 | Pencatatan jasa ojek lokal | P1 | Permintaan, tarif efektif, operator, status penugasan/layanan, pembayaran, dan pembatalan memiliki ID transaksi dan tercatat di POS; tidak memerlukan aplikasi ojek pihak ketiga. |
| FR-17 | Manajemen penugasan ojek | P1 | Operator hanya melihat tugas yang ditugaskan dan data minimum; kapasitas/waktu layanan mengikuti konfigurasi yang disetujui; status selesai atau gagal tercatat. |
| FR-18 | POS/kasir multi-layanan | P1 | Kasir dapat mencatat item produk dan layanan pada transaksi bernomor unik; struk menampilkan rincian; metode bayar dan status tercatat; pembatalan/void tidak menghapus transaksi asal. |
| FR-19 | Katalog dan stok produk/makanan | P1 | Admin berwenang mengatur SKU, harga efektif, kategori, stok dan status; penjualan mengurangi stok; koreksi, retur, rusak/kedaluwarsa tercatat dengan alasan. |
| FR-20 | Reservasi dan penyewaan alat | P1 | Ketersediaan dihitung berdasarkan stok dan periode; serah-terima/pengembalian mencatat kondisi dan waktu; barang belum kembali tidak tersedia untuk reservasi baru. |
| FR-21 | Tutup kas dan rekonsiliasi | P1 | Kasir merekonsiliasi tunai dan kanal pembayaran per shift; selisih dicatat; laporan hanya dapat dilihat/ditinjau oleh peran yang berwenang. |
| FR-22 | Refund, void, dan koreksi berjenjang | P1 | Tindakan mengikuti batas dan persetujuan peran; alasan wajib; transaksi asal tetap ada; seluruh tindakan masuk audit log. |
| FR-23 | Laporan layanan dan inventori | P2 | Laporan dapat difilter per tanggal, jenis layanan, SKU, operator dan status; ekspor dibatasi peran dan tidak memuat PII berlebihan. |

P0 = wajib untuk peluncuran awal. P1* bergantung pada kebijakan dan integrasi yang disepakati; jangan merilis proses yang secara resmi mensyaratkan pembayaran/izin tanpa fungsi tersebut.

## 9. Status permohonan yang disarankan

Model status awal untuk divalidasi dengan pengelola: **Draft → Diajukan → Menunggu verifikasi → Perlu perbaikan → Menunggu pembayaran (jika berlaku) → Pembayaran terkonfirmasi (jika berlaku) → Disetujui/izin terbit → Check-in → Selesai**. Status terminal alternatif: **Ditolak**, **Dibatalkan**, **Kedaluwarsa**, **Jalur ditutup**. Transisi harus eksplisit, tervalidasi di server, mempunyai aktor pemicu, dan tercatat. Status “Disetujui” dan “izin terbit” sebaiknya satu transisi hanya jika persyaratan izin memang sudah terpenuhi; jika tidak, pisahkan.

## 10. Model transaksi dan operasional kasir

Semua transaksi yang dikenai pembayaran—SIMAKSI bila berlaku, ojek, sewa, produk, serta makanan/minuman—harus tercatat di satu ledger POS atau ledger terintegrasi dengan ID unik, waktu, pelaku, rincian item/layanan, kuantitas, harga efektif saat penjualan, diskon/biaya bila disahkan, pajak bila berlaku, total, kanal pembayaran, status, dan referensi terkait. Sistem harus membedakan status **dibuat**, **menunggu pembayaran**, **dibayar**, **dibatalkan**, **refund sebagian/penuh**, dan **gagal**. Nilai transaksi historis tidak berubah ketika harga katalog diperbarui.

Kasir memilih layanan/SKU, mengaitkan transaksi dengan nomor permohonan atau pelanggan bila diperlukan, mengonfirmasi total, merekam pembayaran, dan menerbitkan struk. Pembayaran digital dikonfirmasi oleh backend dari penyedia; pembayaran tunai dicatat oleh kasir. Satu transaksi dapat memuat beberapa kategori item bila pengelola mengizinkan. Kebijakan pembagian pendapatan antara kasir/pengelola dan pengemudi harus ditetapkan dan direkonsiliasi; sistem menyimpan penerima/akun penyelesaian sesuai model bisnis tanpa membocorkan data ke pendaki.

Setiap shift kas memiliki pembukaan, transaksi, void/refund yang disetujui, setoran/serah-terima dan penutupan. Laporan mencocokkan nilai POS dengan uang fisik dan settlement penyedia; perbedaan tidak dihapus tetapi dicatat sebagai selisih untuk ditinjau. Dilarang menghapus transaksi yang telah dibayar atau menimpa harga transaksi historis.

## 11. Persyaratan data

Skema minimum konseptual:

- **Application:** ID acak, nomor referensi publik nonsekuensial, tanggal pendakian, jalur, jumlah anggota, status, waktu pembuatan/perubahan, versi kebijakan yang disetujui.
- **Applicant / group member:** nama dan data kontak/identitas hanya sesuai dasar kebutuhan; hubungan dengan permohonan; status verifikasi. Data anggota tidak boleh dapat diakses oleh pemohon lain.
- **Emergency contact:** nama dan kontak bila disahkan sebagai kebutuhan; informasi kepada pihak kontak harus ditangani secara transparan.
- **Review event:** aktor petugas, tindakan, alasan, waktu, dan status sebelum/sesudah.
- **Payment transaction:** ID penyedia, nilai dan mata uang dari konfigurasi resmi, status, waktu, referensi rekonsiliasi; tidak menyimpan data kartu.
- **Permit:** ID, masa berlaku, status, referensi permohonan dan token/QR yang dapat dicabut.
- **POS transaction / line item:** ID transaksi, shift kasir, waktu, akun kasir, kategori (SIMAKSI/ojek/sewa/produk/makanan atau kategori lain yang disahkan), SKU/layanan, kuantitas, harga saat transaksi, diskon/biaya/pajak bila berlaku, kanal bayar, status, referensi pembayaran dan permohonan terkait.
- **Transport service / assignment:** titik layanan, waktu, jumlah penumpang bila relevan, operator/pengemudi yang ditugaskan, status dan bukti penyelesaian; data pribadi pendaki diminimalkan.
- **Rental asset / movement:** identitas aset/SKU, status ketersediaan, reservasi dan periode, kondisi saat keluar/kembali, petugas serah-terima, deposit/refund bila berlaku, serta catatan insiden.
- **Product / stock movement:** SKU, kategori, unit, harga efektif, stok, batch/tanggal kedaluwarsa bila sesuai kebutuhan makanan, dan peristiwa penjualan, retur, kerusakan atau koreksi.
- **Cash shift / reconciliation:** petugas pembuka/penutup, saldo awal, total per metode bayar, setoran, selisih, catatan, dan persetujuan manajer bila diwajibkan.
- **Staff user / role assignment:** ID pengguna individual, peran, cakupan akses, status akun, waktu pemberian/pencabutan peran dan aktor pemberi.
- **Review event:** aktor petugas, tindakan, alasan, waktu, dan status sebelum/sesudah.
- **Audit log:** akses dan perubahan sensitif, retensi serta akses khusus; log tidak menyalin isi PII secara berlebihan.

Masa retensi, dasar pemrosesan, hak akses, prosedur koreksi/penghapusan, dan penanganan permintaan subjek data harus ditentukan bersama pemilik data dan penasihat yang sesuai sebelum produksi. Jangan mencatat NIK atau data identitas lengkap di log aplikasi.

## 12. Integrasi dan arsitektur konseptual

**Prinsip sumber kebenaran:** tentukan apakah portal menjadi sistem utama, lapisan frontend di atas SIMAKSI yang ada, atau sistem pengajuan yang menyinkronkan status dengan SIMAKSI resmi. Hindari dua basis data yang sama-sama dianggap kanonis.

**Komponen konseptual:** frontend publik responsif; backend API; layanan autentikasi/otorisasi; basis data transaksional; penyimpanan dokumen privat (hanya jika dokumen benar-benar dibutuhkan); antrean notifikasi; adaptor SIMAKSI; adaptor penyedia pembayaran; modul layanan ojek/penugasan; modul POS dan shift kas; katalog serta inventori/penyewaan; panel petugas; log audit dan monitoring.

**Kontrak integrasi SIMAKSI yang harus diklarifikasi:** pemilik/otoritas API; autentikasi dan rotasi kredensial; endpoint dan model data; lingkungan uji; pembuatan/pembaruan/pembatalan permohonan; nomor referensi kanonis; kuota dan penguncian slot; aturan idempotensi; webhook dan validasi signature; retry dan dead-letter; rate limits; timeout; SLA; sinkronisasi/reconciliation; penanganan perubahan skema; serta dukungan ketika sistem pihak lain tidak tersedia. Jangan menebak endpoint atau mengirim data sebelum otorisasi.

**Pembayaran:** gunakan penyedia resmi yang disetujui, redirect/hosted checkout bila sesuai, verifikasi signature callback di backend, idempotency key, rekonsiliasi berkala, dan prosedur refund. Status dari browser pengguna tidak dapat dianggap bukti pembayaran.

**Kegagalan layanan:** jika SIMAKSI atau pembayaran tidak tersedia, tampilkan status tertunda yang akurat, pertahankan idempotensi, dan sediakan prosedur dukungan. Jangan tampilkan izin sebagai terbit jika sistem otoritatif belum mengonfirmasi.

## 13. Persyaratan nonfungsional

- **Keamanan:** HTTPS; enkripsi data tersimpan dan transit; pengelolaan rahasia di secret manager; autentikasi kuat untuk petugas, idealnya MFA; RBAC dan prinsip least privilege; akun staf individual tanpa akun kasir bersama; batas nominal/otorisasi ganda untuk refund, void dan perubahan harga; perlindungan CSRF/XSS/SQL injection; rate limiting; validasi server; pemindaian dependensi; backup terenkripsi; prosedur rotasi dan respons insiden.
- **Privasi:** pengumpulan minimum, pemberitahuan yang jelas, persetujuan bila diperlukan, pembatasan akses, jadwal penghapusan, pengelolaan subprosesor, dan mekanisme pemenuhan hak data. Tinjau kepatuhan terhadap peraturan perlindungan data yang berlaku di Indonesia bersama pihak yang kompeten.
- **Ketersediaan:** target layanan dan maintenance window ditetapkan dengan pengelola; sistem memantau API kritis, database, antrean, pembayaran, serta kegagalan notifikasi. Tetapkan RPO/RTO berdasarkan kebutuhan operasi.
- **Kinerja:** target teknis disepakati setelah baseline dan pengujian; halaman konten harus tetap ringan di jaringan seluler lambat; daftar petugas harus paginasi dan mendukung beban puncak sebelum tanggal populer.
- **Aksesibilitas:** sasaran WCAG 2.2 AA; navigasi keyboard, label form, pesan kesalahan terkait field, kontras yang cukup, fokus terlihat, teks alternatif, dan bahasa Indonesia yang jelas.
- **Kompatibilitas:** desain mobile-first; dukungan browser modern yang ditentukan pada awal proyek; formulir tidak kehilangan data akibat kesalahan validasi yang dapat dipulihkan.
- **Keandalan data:** pembuatan permohonan, perubahan kuota, pembayaran, dan penerbitan izin konsisten; operasi ulang aman; waktu sistem tersinkron; audit trail tidak dapat diubah oleh petugas biasa.
- **Lokalisasi:** bahasa Indonesia sebagai bahasa MVP; format tanggal, zona waktu, dan format kontak sesuai ketentuan lokal; mata uang hanya ditampilkan jika tarif telah disahkan.

## 14. Keamanan, privasi, dan keselamatan operasi

Data identitas dan kontak pendaki harus dianggap sensitif secara operasional. Hindari data sungguhan dalam staging, screenshot, analitik, dan tiket dukungan. Tetapkan peran pengendali/pemroses data, pihak yang dapat mengakses, tujuan, dasar pemrosesan, masa simpan, retensi backup, proses penghapusan, serta prosedur insiden sebelum onboarding.

QR izin sebaiknya memuat token acak atau referensi yang tidak mengungkap nama/NIK; validasi dilakukan server-side dan token dapat dicabut. Hindari QR yang dapat ditebak. Untuk check-in offline, lakukan threat modeling dan sepakati batas sinkronisasi serta risiko penggunaan ulang sebelum implementasi.

Informasi ketinggian, estimasi waktu, kondisi medan, kontak darurat, prosedur tutup jalur, serta batas operasional harus ditinjau dan diberi tanggal oleh pengelola. Portal bukan pengganti arahan petugas atau penilaian kondisi lapangan.

## 15. UX dan konten

Antarmuka mengikuti identitas SAMSARA yang tenang dan terinspirasi vegetasi pegunungan, namun informasi keselamatan dan status harus lebih menonjol daripada dekorasi. Aksi menggunakan label yang menyebut hasilnya, seperti “Ajukan permohonan” dan “Lihat status”, bukan label umum. Pesan sukses harus menjelaskan apakah pengajuan benar-benar tersimpan dan langkah berikutnya.

Konten awal yang harus disediakan pengelola: deskripsi resmi jalur; titik awal dan koordinat bila boleh dipublikasikan; estimasi yang disahkan; tingkat kesulitan yang didefinisikan; daftar perlengkapan; persyaratan dan aturan; prosedur check-in; tarif dan cara pembayaran bila ada; kuota; kebijakan pembatalan; informasi penutupan/cuaca; nomor kontak resmi; dan tanggal pembaruan.

## 16. Metrik, analitik, dan observabilitas

Kumpulkan metrik funnel secara agregat: kunjungan halaman info → pemeriksaan tanggal → mulai formulir → pengajuan berhasil → verifikasi → pembayaran (bila ada) → izin terbit → check-in. Hindari memasukkan nama, email, telepon, nomor identitas, atau isi formulir ke event analytics.

Operasional harus memantau waktu respons API, error rate, kegagalan sinkronisasi SIMAKSI, callback pembayaran tertunda/ganda, konflik kuota, permohonan yang melewati SLA, notifikasi gagal, dan aktivitas administratif berisiko. Sediakan alarm dan runbook pemilik on-call sesuai kapasitas pengelola.

## 16. Kriteria penerimaan MVP

1. Pengunjung dapat membaca persyaratan resmi, melihat waktu pembaruan, serta menemukan kontak dan pengumuman penutupan.
2. Pengajuan dengan field wajib kosong atau format tidak valid ditolak dengan pesan spesifik; tidak ada permohonan setengah jadi yang dianggap berhasil.
3. Pengajuan valid menghasilkan satu nomor referensi dan konfirmasi yang menjelaskan status berikutnya.
4. Dua pengajuan bersamaan untuk slot terakhir tidak dapat sama-sama mengonsumsi kapasitas.
5. Pendaki hanya dapat melihat permohonan miliknya melalui mekanisme akses yang aman.
6. Petugas tanpa izin tidak dapat mengakses data; semua keputusan dan perubahan status tercatat di audit log.
7. Permohonan yang memerlukan perbaikan menampilkan alasan dan instruksi; penolakan memiliki alasan yang dapat ditinjau.
8. Jika pembayaran wajib, izin tidak diterbitkan sebelum status pembayaran diverifikasi oleh backend dan semua syarat lain terpenuhi.
9. QR/bukti izin dapat diverifikasi, tidak membocorkan PII, serta dapat dicabut saat izin dibatalkan.
10. Kegagalan integrasi tidak menggandakan permohonan atau pembayaran dan tidak menampilkan status berhasil palsu.
11. Formulir dapat diselesaikan menggunakan keyboard dan diuji pada ukuran layar ponsel.
12. Uji aksesibilitas, keamanan, backup-restore, dan prosedur respons gangguan selesai sebelum peluncuran.
17. Untuk layanan ojek, transaksi terhubung dengan penugasan/operator dan tercatat di kasir; model pembayaran dan rekonsiliasi mengikuti kebijakan pengelola.
18. Reservasi serta pengembalian alat memperbarui ketersediaan dan mencatat kondisi barang.
19. Penjualan produk/makanan mencatat item dan stok; retur/koreksi memiliki jejak audit.
20. Tindakan kasir sensitif seperti refund/void dan perubahan stok mengikuti otorisasi berjenjang; penutupan kas menampilkan rekonsiliasi.
21. Uji akses peran, uji transaksi ganda, uji overselling inventori, aksesibilitas, keamanan, backup-restore, dan prosedur respons gangguan selesai sebelum peluncuran.
22. Konten operasional yang belum disahkan tidak dipublikasikan sebagai fakta resmi.



## 16. Persyaratan layanan ojek, sewa, dan penjualan

Produk tambahan tersedia sebagai layanan yang dapat ditambahkan ke transaksi pendaki, tetapi tidak boleh mengaburkan bahwa layanan ini terpisah dari penerbitan SIMAKSI. Pendaki dapat memilih jasa ojek lokal, menyewa perlengkapan dan membeli produk/makanan jika layanan tersedia. Informasi harga, stok, waktu layanan, serta syarat pembatalan harus jelas sebelum konfirmasi.

**Ojek lokal:** sistem mencatat permintaan dan transaksi untuk setiap perjalanan. Status minimal: **diminta → menunggu penugasan → ditugaskan → dalam layanan → selesai** dengan jalur alternatif **ditolak**, **dibatalkan**, **no-show**, atau **perlu bantuan**. Catatan kasir wajib memiliki referensi tugas dan jumlah yang dibayar/ditagihkan, walaupun model pembayaran akhirnya ditetapkan sebagai pembayaran langsung ke pengemudi. Sistem harus mendukung rekonsiliasi pembayaran langsung dan pembayaran terpusat tanpa membuat catatan ganda. Pengelola menetapkan titik layanan, area operasional, tarif, kapasitas, jam layanan, penanganan cuaca/penutupan, keselamatan kendaraan dan pengemudi, pembagian pendapatan, bukti penyelesaian, komplain, serta siapa yang bertanggung jawab kepada pendaki. Nomor telepon atau identitas pendaki tidak dibagikan ke pengemudi kecuali benar-benar diperlukan dan diberitahukan.

**Penyewaan alat:** katalog menampilkan barang, spesifikasi, ukuran/varian, harga dan ketersediaan yang disahkan. Sistem harus menangani reservasi berdasarkan tanggal/periode, ketersediaan aktual, status pemeriksaan/perawatan, serah-terima, pengembalian, kondisi, keterlambatan, kehilangan/kerusakan, deposit dan refund apabila kebijakan menentukan. Pendaki dan petugas mendapat bukti transaksi. Biaya tambahan tidak boleh ditetapkan atau dibebankan otomatis tanpa dasar dan proses persetujuan yang jelas.

**Produk dan makanan/minuman:** kasir dapat menjual produk dengan SKU, variasi, kuantitas dan harga yang berlaku. Stok diperbarui secara konsisten saat transaksi berhasil dan dikembalikan melalui proses retur/void yang disetujui. Untuk makanan/minuman, kebutuhan pengelolaan batch, masa kedaluwarsa, suhu penyimpanan, komposisi/alergen, label, keamanan pangan, perizinan dan pencatatan produk ditentukan oleh pengelola sesuai aturan yang berlaku; sistem hanya mendukung pencatatan dan tidak menyatakan produk aman/bersertifikat tanpa bukti.

**Struk dan rekonsiliasi:** struk (digital/cetak sesuai fasilitas) menampilkan nomor transaksi, waktu, item/layanan, kuantitas, harga satuan dan total, pajak/biaya bila berlaku, metode pembayaran dan kontak layanan. Informasi sensitif tidak dicetak. Penutupan shift mencocokkan transaksi kasir dengan uang tunai dan settlement kanal digital; selisih dilaporkan dan ditinjau. Tidak ada penghapusan diam-diam atas transaksi, perubahan stok atau log tugas.

### Kriteria penerimaan layanan dan kasir

1. Hanya peran yang berwenang dapat membuka POS; kasir tidak dapat mengubah master harga atau menghapus transaksi yang sudah disimpan.
2. Setiap layanan ojek yang dipesan atau diterima di lokasi memiliki satu referensi tugas dan transaksi kasir, status layanan, penerima pembayaran, serta cara rekonsiliasi yang konsisten.
3. Operator hanya melihat penugasan miliknya dan data minimum; ketika tugas dibatalkan atau selesai, perubahan status tercatat.
4. Reservasi alat tidak dapat melebihi ketersediaan selama periode sewa; alat yang belum kembali atau sedang diperiksa tidak dapat disewakan kembali.
5. Serah-terima dan pengembalian alat mencatat petugas, waktu, jumlah dan kondisi; biaya kerusakan/deposit hanya mengikuti aturan yang disahkan dan tindakan kasir berwenang.
6. Penjualan mengurangi stok sekali saja; pengulangan callback atau permintaan tidak menggandakan transaksi atau pengurangan stok.
7. Void, refund, retur, koreksi stok dan penyesuaian transaksi memiliki alasan, aktor, waktu, dan persetujuan sesuai batas kewenangan; transaksi asal tetap dapat ditelusuri.
8. Penutupan kas menampilkan total per metode bayar dan selisih terhadap hitung aktual; selisih tidak dapat disembunyikan dengan mengubah transaksi lama.
9. Setiap peran lulus pengujian akses positif dan negatif: akses yang diizinkan berfungsi, akses terlarang ditolak dan tercatat.
10. Struk tidak menampilkan NIK atau data identitas pendaki yang tidak diperlukan.

## 17. Pengujian dan rilis

Rencana uji mencakup unit test validasi/status; integration test untuk API SIMAKSI dan pembayaran pada sandbox; end-to-end test alur pendaki dan petugas; pengujian konkurensi kuota; uji akses kontrol dan OWASP; uji aksesibilitas; uji jaringan lambat/perangkat mobile; uji notifikasi; uji pemulihan backup; serta simulasi insiden dan penutupan jalur.

- Uji layanan ojek lokal, pembagian akses operator, pembayaran/rekonsiliasi dan status tugas.
- Uji POS untuk transaksi campuran, void/refund, tutup shift, rekonsiliasi dan jejak audit.
- Uji inventori sewa berdasarkan periode, keterlambatan, kerusakan, retur produk, dan pengurangan stok yang idempoten.

Tahapan rilis yang disarankan: penemuan proses dan persetujuan kebijakan; prototipe dan uji kegunaan; implementasi sandbox; UAT bersama petugas; pilot terbatas dengan dukungan langsung; evaluasi metrik dan insiden; lalu peluncuran bertahap. Go-live memerlukan persetujuan tertulis pemilik SIMAKSI, pengelola jalur, penanggung jawab privasi, penanggung jawab operasi, serta pemilik proses kasir, ojek, inventori dan penjualan makanan.

## 18. Risiko dan mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Penjualan atau pengelolaan makanan tidak sesuai aturan | Risiko kesehatan, hukum, dan reputasi | Tetapkan pemilik kepatuhan; validasi izin, keamanan, label/alergen, masa simpan dan prosedur penarikan sebelum katalog makanan aktif. |
| Kecurangan kasir atau transaksi tidak cocok | Kehilangan pendapatan dan laporan tidak akurat | Akun individual, pemisahan tugas, otorisasi void/refund, tutup shift, rekonsiliasi, dan audit log yang tidak dapat diubah. |
| Stok alat/produk tidak akurat | Overbooking sewa atau produk terjual padahal kosong | Pergerakan stok atomik, pencatatan serah-terima, stock opname, penandaan alat rusak/terlambat, dan peringatan stok rendah. |
| Ojek salah ditugaskan atau pembayaran tidak jelas | Layanan gagal, sengketa, keselamatan dan reputasi terdampak | Tetapkan SOP, titik layanan, aturan pembayaran/komisi, status tugas, bukti penyelesaian, pengelolaan komplain, dan batas data yang dibagikan. |
| API SIMAKSI tidak tersedia atau tidak memberi otorisasi | Integrasi tidak dapat dilakukan; status berbeda | Pastikan pemilik dan API sebelum komitmen; rancang mode manual sementara hanya jika disetujui, dengan rekonsiliasi dan label jelas. |
| Aturan/tarif/kuota belum ditetapkan | Informasi salah atau konflik kapasitas | Jangan mengarang nilai; tetapkan pemilik konten dan persetujuan sebelum konfigurasi dipublikasikan. |
| Data pribadi berlebihan atau bocor | Risiko bagi pendaki dan pengelola | Minimisasi, kontrol akses, enkripsi, retensi, audit, pengujian keamanan, prosedur insiden. |
| Pembayaran terkonfirmasi ganda atau palsu | Kerugian finansial dan izin salah terbit | Callback bertanda tangan, idempotensi, rekonsiliasi, tanpa mempercayai status browser. |
| Lonjakan permohonan | Situs lambat, kuota tidak akurat | Uji beban, antrean, penguncian kuota atomik, pemantauan, rencana kapasitas. |
| Kondisi lapangan berubah | Informasi portal usang dan risiko keselamatan | Pengumuman dengan cap waktu, pemilik piket konten, mekanisme menutup tanggal/jalur cepat. |
| Petugas salah memberi keputusan | Izin tidak sesuai kebijakan | RBAC, konfirmasi tindakan penting, alasan wajib, audit log dan mekanisme eskalasi. |
| Koneksi internet lemah di basecamp | Check-in terhambat | Prosedur fallback yang disetujui; evaluasi mode offline dengan rekonsiliasi dan perlindungan penggunaan ulang. |

## 19. Dependensi dan keputusan terbuka

Sebelum estimasi dan implementasi produksi, pemilik produk perlu menetapkan: siapa operator dan pemilik SIMAKSI; apakah sistem SIMAKSI yang sudah ada dapat diintegrasikan; proses pengajuan dan alur status resmi; kalender, kuota, dan mekanisme reservasi; data/dokumen wajib serta dasar pengumpulan; tarif, penyedia pembayaran, refund, pajak dan rekonsiliasi bila berlaku; pembatalan/perubahan/no-show; definisi penerbitan izin dan check-in; kanal notifikasi; kebijakan privasi, retensi dan respons insiden; SLA dukungan; sumber informasi kondisi jalur; akses dan kewenangan petugas; estimasi volume/puncak; serta pemilik biaya operasi dan pemeliharaan.

Keputusan tambahan untuk layanan lokal dan POS: apakah ojek adalah layanan pengemudi lokal saja atau perlu integrasi dengan platform eksternal; siapa operator yang bertanggung jawab dan bagaimana verifikasi pengemudi/kendaraan; titik jemput/tujuan, jam operasi, kapasitas, harga, komisi/pembagian pendapatan dan model pembayaran; SOP keselamatan, cuaca buruk, pembatalan, no-show dan komplain; daftar alat sewa, kondisi, periode sewa, deposit/denda serta aturan kehilangan/kerusakan; katalog SKU, satuan, harga, pajak dan kebijakan retur; makanan/minuman yang boleh dijual, pemilik tanggung jawab keamanan pangan, label/alergen, masa simpan, perizinan dan prosedur penarikan; daftar metode pembayaran dan pemilik settlement; batas nominal refund/void serta persetujuan tingkat kedua; pembukaan/penutupan shift, setoran kas dan rekonsiliasi; pengguna/role nyata, cakupan akses cabang/lokasi, dan proses audit stok; serta apakah MVP mencakup kasir langsung atau hanya pencatatan layanan yang direkonsiliasi manual.


## 20. Rekomendasi prioritas berikutnya

Mulai dengan lokakarya pengelola jalur dan petugas untuk memetakan proses nyata dan memverifikasi kebijakan. Secara paralel, minta dokumentasi resmi SIMAKSI dan sandbox integrasi. Setelah keputusan sumber kebenaran, data minimum, kuota, pembayaran, serta penerbitan izin disepakati, ubah dokumen ini menjadi backlog dengan pemilik, estimasi, dan target terukur. Mockup saat ini dapat dipakai untuk uji pemahaman navigasi/formulir, bukan sebagai bukti kesiapan integrasi atau validasi proses resmi.
