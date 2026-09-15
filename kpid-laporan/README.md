# Portal Laporan Masyarakat + Admin Notifikasi WhatsApp (untuk KPID)

Website siap pakai untuk menerima laporan masyarakat terkait siaran TV/Radio,
lengkap dengan panel admin dan notifikasi WhatsApp otomatis (via Fonnte)
setiap ada laporan baru.

## Fitur

- Form laporan publik (nama, WA, media, jenis pelanggaran, kronologi, upload bukti)
- Kode laporan otomatis + halaman "Cek Status Laporan" untuk masyarakat
- Notifikasi WhatsApp otomatis ke Admin setiap ada laporan baru
- Notifikasi WhatsApp konfirmasi otomatis ke pelapor
- Login Admin, dashboard, filter & pencarian laporan
- Ubah status laporan (Baru / Diproses / Selesai / Ditolak) + catatan/balasan
- Opsi kirim notifikasi WhatsApp ke pelapor saat status diperbarui
- Proteksi dasar: CSRF token, password ter-enkripsi (bcrypt), validasi upload file

---

## LANGKAH INSTALASI (ikuti berurutan)

### 1. Upload File
Upload **seluruh isi folder ini** ke `public_html` (atau subfolder) di hosting Anda,
lewat File Manager cPanel atau FTP.

### 2. Buat Database
1. Masuk cPanel > **MySQL Databases**, buat database baru, misal `kpid_laporan`.
2. Buat user database baru, beri password, lalu **hubungkan (Add User to Database)**
   user tersebut ke database tadi dengan hak akses **All Privileges**.
3. Masuk **phpMyAdmin**, pilih database yang baru dibuat, buka tab **Import**,
   lalu upload file `database.sql` yang ada di folder ini. Klik **Go/Kirim**.
4. Ini otomatis membuat tabel `laporan`, `catatan`, `admin`, dan 1 akun admin default:
   - **Username:** `admin`
   - **Password:** `admin123`
   - ⚠️ **WAJIB diganti setelah login pertama kali** (lihat bagian "Ganti Password" di bawah).

### 3. Atur Koneksi Database
Buka file **`config/database.php`**, ganti bagian berikut sesuai data database Anda:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'kpid_laporan');   // nama database Anda
define('DB_USER', 'root');           // username database Anda
define('DB_PASS', '');               // password database Anda
```

### 4. Atur Konfigurasi Umum & WhatsApp
Buka file **`config/config.php`**, ganti bagian berikut:

```php
define('NAMA_INSTANSI', 'KPID Provinsi Anda');   // ganti sesuai nama instansi
define('SITE_URL', '');                          // isi URL website Anda saat live,
                                                   // contoh: 'https://laporan.kpidjatim.go.id'
                                                   // (tanpa garis miring / di akhir)

define('FONNTE_TOKEN', 'ISI_TOKEN_FONNTE_ANDA_DISINI');
define('ADMIN_WHATSAPP', '628123456789');         // nomor WA admin penerima notifikasi
```

### 5. Setup WhatsApp Gateway (Fonnte) — GRATIS untuk mulai
1. Buka **https://fonnte.com**, daftar akun baru.
2. Di dashboard, klik **Tambah Device / Connected Device**.
3. Scan QR Code menggunakan WhatsApp nomor Admin (WhatsApp di HP > Perangkat Tertaut > Tautkan Perangkat).
4. Setelah tersambung, salin **Token** yang muncul di dashboard.
5. Tempel token tersebut ke `FONNTE_TOKEN` di `config/config.php` (langkah 4 di atas).
6. Isi `ADMIN_WHATSAPP` dengan nomor WA admin, format `62...` tanpa tanda `+` dan tanpa `0` di depan.
   Contoh: nomor `0812xxxxxxx` ditulis `62812xxxxxxx`.

> Catatan: WhatsApp yang dipakai untuk mengirim notifikasi adalah nomor yang di-scan di Fonnte
> (bisa nomor pribadi admin/petugas khusus). Laporan tetap tersimpan ke database walaupun
> WhatsApp gagal terkirim (misal token belum diisi), jadi tidak akan membuat data hilang.

### 6. Atur Folder Upload
Pastikan folder `uploads/` memiliki izin tulis. Lewat File Manager cPanel:
klik kanan folder `uploads` > **Permissions** > set ke **755** (atau **775** jika 755 gagal).

### 7. Selesai — Coba Website
- Halaman publik: `https://domainanda.com/`
- Login admin: `https://domainanda.com/admin/login.php`

---

## Ganti Password Admin

Setelah login pertama kali, segera ganti password. Caranya:

1. Buat file sementara bernama `buat_hash.php` di folder utama, isi:
   ```php
   <?php echo password_hash('PASSWORD_BARU_ANDA', PASSWORD_BCRYPT); ?>
   ```
2. Buka file itu lewat browser (`https://domainanda.com/buat_hash.php`), salin hasil kode acak yang muncul.
3. Buka **phpMyAdmin** > tabel `admin` > edit baris admin > tempel hasil salinan tadi ke kolom `password`.
4. **Hapus file `buat_hash.php`** dari server (penting, demi keamanan).

## Menambah Admin Baru

Lewat phpMyAdmin, tambah baris baru di tabel `admin` dengan kolom:
- `username`, `nama_lengkap`, `no_whatsapp` — isi bebas
- `password` — isi dengan hasil generate hash seperti langkah "Ganti Password Admin" di atas

---

## Struktur Folder

```
kpid-laporan/
├── database.sql              -> import ini ke phpMyAdmin
├── config/
│   ├── database.php          -> setting koneksi database
│   └── config.php            -> setting nama instansi & token Fonnte
├── includes/                 -> fungsi & komponen halaman publik
├── assets/                   -> CSS & JS
├── uploads/                  -> tempat file bukti laporan tersimpan
├── index.php                 -> halaman form laporan (beranda)
├── submit.php                -> proses simpan laporan + kirim WA
├── terima_kasih.php          -> halaman sukses setelah lapor
├── cek_status.php            -> halaman cek status untuk masyarakat
└── admin/
    ├── login.php / logout.php
    ├── dashboard.php          -> daftar semua laporan
    └── detail.php             -> detail laporan, ubah status, catatan
```

---

## Troubleshooting

**"Koneksi database gagal"**
→ Periksa kembali `config/database.php`, pastikan nama database/user/password benar.

**Notifikasi WhatsApp tidak terkirim**
→ Pastikan `FONNTE_TOKEN` sudah diisi dan device Fonnte berstatus "Connected" (belum logout/expired).
→ Cek juga saldo/kuota pesan di akun Fonnte Anda.

**Upload bukti gagal**
→ Pastikan folder `uploads/` permission 755/775, dan ukuran file di bawah 5MB.

**Halaman blank/putih**
→ Biasanya karena versi PHP hosting terlalu lama. Pastikan hosting menggunakan **PHP 7.4 ke atas**
  (cek/atur di cPanel > MultiPHP Manager).

---

Dibuat untuk kebutuhan portal laporan masyarakat KPID. Silakan sesuaikan nama instansi,
warna, dan daftar jenis pelanggaran (`JENIS_PELANGGARAN` di `config/config.php`) sesuai kebutuhan.
