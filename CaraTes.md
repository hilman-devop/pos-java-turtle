# 📝 Panduan Pengujian & Fitur POS Kasir Resto — Java Turtle Resto 🐢🍔

Dokumen ini berisi panduan lengkap untuk melakukan pengujian fungsionalitas dan fitur pada aplikasi **POS Kasir Resto** (Java Turtle Resto & Cafe). Panduan ini dirancang agar developer atau penguji baru dapat langsung memahami alur kerja aplikasi secara mendalam dan melakukan verifikasi end-to-end.

---

## 🛠️ Persyaratan Awal Sebelum Pengujian

Sebelum memulai pengujian, pastikan lingkungan server lokal Anda memenuhi kriteria berikut:

1. **Web Server**: Apache (melalui XAMPP, Laragon, atau sejenisnya).
2. **PHP Version**: Minimal PHP 7.4 atau versi di atasnya.
3. **Database**: MySQL / MariaDB.
4. **Ekstensi PHP Aktif**: `PDO`, `pdo_mysql`, dan `curl` (untuk integrasi Google Sheets).

---

## 🔑 1. Setup Awal & Kredensial Pengguna

### Langkah Inisialisasi Database Otomatis:

1. Pastikan server Apache dan MySQL di XAMPP Anda aktif.
2. Akses halaman migrasi otomatis melalui browser Anda:
   ```
   http://localhost/pos-resto-php/migrate.php
   ```
3. Skrip migrasi akan secara otomatis membuat database `db_resto`, menyusun tabel-tabel (`toko`, `member`, `login`, `kategori`, `menu_layanan`, `penjualan_temp`, `penjualan`), dan melakukan *seeding* data awal restoran termasuk kolom `no_invoice`.
4. Setelah muncul notifikasi **Migrasi Sukses**, klik tombol **Masuk Ke Aplikasi Resto** untuk dialihkan ke halaman login.

> **Catatan**: Jika database sudah ada sebelumnya, `config.php` akan otomatis melakukan *auto-patch* untuk menambahkan kolom `no_invoice` ke tabel `penjualan` jika belum ada.

### Akun Uji Coba Default:

| Peran (Role) | Username | Password | Deskripsi Otoritas |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin` | `123` | Kontrol Penuh (Mengelola Menu, Kategori, Pengaturan Resto, Laporan Penjualan & Ekspor Excel) |
| **Kasir** | `kasir` | `123` | Kasir Utama (Mencari Menu, Input Keranjang Pesanan, Proses Bayar, Cetak Struk — Tanpa Akses Master Data & Laporan) |

---

## 🚀 2. Skenario Pengujian Alur Kerja (Workflow)

### 💻 POV 1: PENGUJIAN HAK AKSES & MANAJEMEN MASTER DATA (ADMIN)

#### **Uji 1: Dashboard Analytics Resto**

1. Buka halaman login (`http://localhost/pos-resto-php/login.php`).
2. Login menggunakan akun Admin (`admin` / `123`).
3. Perhatikan SweetAlert2 sambutan setelah login berhasil — klik **OK** untuk masuk dashboard.
4. Perhatikan dashboard **Restoran & Cafe**:
   - **Widget Utama**: Total Menu, Total Porsi Tersedia, Transaksi Lunas, dan Kategori Menu.
   - **Ketersediaan Real-Time**: Status porsi menu (Tersedia, Sisa Porsi, atau Habis) dengan ikon dinamis.
   - **Alert Warning**: Muncul otomatis jika ada porsi menu yang menipis (di bawah 10 porsi).
   - **Mode Tampilan**: Coba klik tombol Switcher Mode di kanan atas navbar untuk berganti ke **Dark Mode** / **Light Mode**.

#### **Uji 2: CRUD Master Menu Makanan & Minuman**

1. Pada menu sidebar kiri, klik **Data Master** → pilih **Menu Restoran**.
2. Klik tombol **[+] Tambah Menu Makanan/Minuman**.
3. Isi form dengan data berikut:
   - *Kategori*: Makanan Utama (atau lainnya)
   - *Nama Menu*: `Nasi Goreng Gila Level 5`
   - *Harga Jual*: `25000`
   - *Stok Porsi awal*: `50`
   - *Foto*: Upload gambar menu makanan dalam format JPG/PNG/WebP (max 4MB).
4. Klik **Tambah Data**.
5. *Verifikasi*: Menu baru muncul di tabel master dengan kode otomatis (misal: `MN007`). Lakukan aksi **Edit** untuk mengubah harga, dan **Hapus** untuk menghapus menu uji coba.

---

### 🛎️ POV 2: PENGUJIAN TRANSAKSI & PEMBAYARAN KASIR (END-TO-END)

> Skenario ini adalah alur inti sistem. Ikuti urutan langkah secara berurutan.

#### **Uji 3: Pencarian & Input Keranjang Aktif**

1. Logout dari Admin, lalu login kembali sebagai Kasir (`kasir` / `123`).
2. Masuk ke menu **Kasir Resto** di sidebar kiri.
3. Pada input pencarian, ketik nama menu (contoh: `Nasi`) lalu tekan **ENTER**.
4. Klik tombol **Pilih** (ikon keranjang hijau) pada menu yang muncul di hasil pencarian.
5. *Verifikasi*:
   - Menu masuk ke keranjang aktif di bawah secara instan.
   - Thumbnail foto menu muncul (atau ikon sendok-garpu jika tidak ada foto).
   - Stok tersisa ditampilkan.

#### **Uji 4: Update Kuantitas & Catatan Pesanan**

1. Ubah jumlah porsi pada kolom **Porsi / Qty** menjadi `2`.
2. Tulis catatan pada input **Catatan Pesanan** (contoh: *Pedas gila, tanpa acar*).
3. Klik tombol refresh kuning (ikon sync) di sebelahnya.
4. *Verifikasi*: Subtotal harga terhitung otomatis (`2 × Harga Porsi`), dan catatan tersimpan di database sementara.

#### **Uji 5: Input Nominal Bayar Terformat Rupiah**

1. Scroll ke bawah ke bagian **Total Tagihan**.
2. Ketik nominal pembayaran di kolom **Bayar (Nominal)** (misalnya `50000`).
3. *Verifikasi*: Input otomatis terformat menjadi `Rp. 50,000` saat mengetik — mencegah kesalahan digit.

#### **Uji 6: Checkout Tunai — Alur Sukses Utama** ⭐

1. Isi data checkout di bagian kasir:
   - *Nama Pelanggan*: `Kak Elz`
   - *Nomor Meja*: `Meja 08` (atau `Takeaway`)
   - *Tipe Pesanan*: `Makan di tempat (Dine-in)` atau `Bawa pulang (Take-away)`.
   - *Metode Pembayaran*: `Tunai / Cash`
   - *Status Pembayaran*: `Lunas (Paid)`
   - *Nominal Bayar*: Ketik nominal ≥ total tagihan.
2. Klik tombol **Proses Bayar**.
3. *Verifikasi setelah submit*:
   - Halaman redirect ke URL `?success_pay=yes&invoice=INV-...`
   - **SweetAlert2 popup hijau** muncul: *"🎉 Pembayaran Berhasil!"*
   - Menampilkan: kode invoice, jumlah kembalian.
   - Dua tombol pilihan: **🖨️ Cetak Struk** dan **🔄 Transaksi Baru**.
   - Keranjang aktif (`penjualan_temp`) sudah dikosongkan.
   - Data transaksi masuk ke tabel `penjualan` permanen.
   - Stok menu berkurang otomatis sesuai jumlah porsi yang dibeli.

#### **Uji 7: Checkout QRIS — Dialog Interaktif**

1. Tambahkan menu ke keranjang.
2. Isi data checkout, pilih *Metode Pembayaran*: **QRIS / E-Wallet**.
3. Klik **Proses Bayar**.
4. *Verifikasi*: Muncul SweetAlert2 premium berisi:
   - QR Code dinamis dari API QR Server.
   - Nominal tagihan yang harus dibayar.
   - Timer hitung mundur **5 menit** (`05:00`).
5. Klik **⚡ Konfirmasi Sudah Bayar** — form dikirim dan alur lanjut sama seperti Uji 6.

#### **Uji 8: Validasi Bayar Kurang**

1. Tambahkan menu ke keranjang (misal: total Rp 25.000).
2. Isi nominal bayar yang **kurang** dari total (misal: Rp 10.000).
3. Klik **Proses Bayar**.
4. *Verifikasi*: Muncul alert: *"Pembayaran Kurang! Kurang: Rp. 15.000"* — transaksi tidak diproses dan keranjang tetap ada.

---

### 🖨️ 3. Pengujian Cetak Struk Kasir

#### **Cetak Struk via SweetAlert Popup** (Alur Normal)

1. Setelah checkout berhasil (Uji 6), popup SweetAlert menampilkan kembalian.
2. Klik tombol **🖨️ Cetak Struk**.
3. Tab baru terbuka mengarah ke `print.php?invoice=INV-YYYYMMDD-HHIISS-XXXX&bayar=...`.
4. *Verifikasi tampilan struk*:
   - Format **80mm thermal print layout** dengan font Courier monospace.
   - Kop toko (nama, alamat, telepon) dari pengaturan.
   - Nomor invoice ditampilkan di bagian atas struk.
   - Data transaksi (pelanggan, meja, kasir, waktu, tipe pesanan, status) lengkap.
   - Daftar item pesanan: nama, `qty × harga satuan`, subtotal.
   - Catatan khusus per item (misal: *Pedas gila, tanpa acar*) muncul di bawah nama menu.
   - Total belanja, nominal bayar, dan **kembalian** ditampilkan jelas.
   - Footer kustom (dari Pengaturan Toko) tercetak di bagian bawah.
   - Dialog print browser **terbuka otomatis** karena dibuka sebagai popup.
5. *Verifikasi tombol aksi* (tidak ikut tercetak):
   - **🖨️ Cetak Struk** — cetak ulang manual.
   - **🛒 Kembali ke Kasir** — navigasi ke halaman jual.
   - **✕ Tutup** — tutup tab ini.

#### **Cetak Struk dari Laporan Keuangan** (Akses Historis)

1. Login sebagai **Admin**.
2. Buka **Laporan Keuangan** di sidebar.
3. Cari transaksi yang sudah ada (per bulan atau per hari).
4. Klik tombol **🖨️ Struk** pada baris transaksi yang memiliki invoice.
5. *Verifikasi*: Struk terbuka di tab baru dengan data historis dari database berdasarkan `no_invoice`.

---

## ☁️ 4. Pengujian Integrasi Cloud (Google Sheets)

Setiap transaksi yang berhasil akan mengirim data ke URL webhook Google Apps Script di `fungsi/spreadsheet.php`.

### Langkah Verifikasi:

1. Selesaikan satu transaksi pembayaran di halaman kasir.
2. Buka folder `scratch/` di dalam direktori proyek.
3. Buka file `spreadsheet_sync_log.txt`.
4. *Verifikasi log*:
   - Jika webhook belum dikonfigurasi:
     ```
     [YYYY-MM-DD HH:MM:SS] LOG ONLY (Webhook not set): Pelanggan: Kak Elz, Meja: Meja 08, Total: Rp.50,000
     ```
   - Jika webhook sudah diset dan berhasil:
     ```
     [YYYY-MM-DD HH:MM:SS] SYNC SUCCESS: Response: ... | Pelanggan: Kak Elz, Meja: Meja 08
     ```

---

## 🛡️ 5. Pengujian Keamanan & CSRF Guard

#### **Uji CSRF — Aksi Tanpa Token Valid**

1. Salin URL tambah item keranjang (contoh):
   ```
   http://localhost/pos-resto-php/fungsi/tambah/tambah.php?jual=jual&id=MN001&id_kasir=1&csrf_token=TOKENSALAH
   ```
2. Buka URL tersebut di tab baru (tanpa token CSRF yang valid dari session).
3. *Verifikasi*: Server menolak request dengan response `Invalid CSRF token` (HTTP 400).

#### **Uji Role — Kasir Akses Halaman Admin**

1. Login sebagai **Kasir** (`kasir` / `123`).
2. Coba akses langsung URL laporan keuangan:
   ```
   http://localhost/pos-resto-php/index.php?page=laporan
   ```
3. *Verifikasi*: Sistem memblokir akses — halaman tidak tampil, dialihkan ke dashboard/home kasir tanpa pesan error.

#### **Uji Role — Kasir Hapus Data Master**

1. Masih login sebagai Kasir.
2. Coba akses URL hapus menu:
   ```
   http://localhost/pos-resto-php/fungsi/hapus/hapus.php?menu=hapus&id=MN001&csrf_token=[TOKEN_VALID]
   ```
3. *Verifikasi*: Muncul alert *"Akses Ditolak! Kasir tidak memiliki hak akses untuk tindakan ini."*

---

Selamat menguji! Sistem POS Kasir Restoran siap digunakan untuk operasional kuliner yang super mulus! 🚀🐢🍔🍕🥤✨
