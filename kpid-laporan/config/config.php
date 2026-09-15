<?php
/**
 * ============================================================
 * KONFIGURASI UMUM WEBSITE
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Jakarta');

// ---------------- Informasi Lembaga ----------------
// Ganti sesuai nama instansi Anda
define('NAMA_INSTANSI', 'KPID BABEL');
define('SITE_NAME', 'Portal Laporan Masyarakat - ' . NAMA_INSTANSI);

// Alamat website Anda saat sudah online, TANPA garis miring "/" di akhir.
// Contoh: 'https://laporan.kpidjatim.go.id'
// Boleh dikosongkan '' saat masih tes di komputer lokal (localhost).
define('SITE_URL', '');

// ---------------- WhatsApp Gateway (Fonnte) ----------------
// 1. Daftar & login di https://fonnte.com
// 2. Hubungkan nomor WhatsApp Admin (scan QR) di menu "Connected Device"
// 3. Salin "Token Device" dari dashboard Fonnte, tempel di bawah ini
define('FONNTE_TOKEN', 'SqGFKezmb2QuuLHy4uCh');

// Nomor WhatsApp admin/petugas yang menerima notifikasi laporan baru.
// Format: 62 di depan, tanpa tanda + dan tanpa angka 0 di awal.
// Contoh nomor 0812xxxxxxx ditulis: 62812xxxxxxx
define('ADMIN_WHATSAPP', '6281539927596');

// Kirim juga pesan konfirmasi otomatis ke WhatsApp pelapor? (true / false)
define('KIRIM_KONFIRMASI_PELAPOR', true);

// ---------------- Upload Bukti ----------------
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_EXT', ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'pdf']);

// ---------------- Daftar Jenis Pelanggaran ----------------
// Silakan tambah/ubah sesuai kebutuhan (mengacu pada P3SPS)
define('JENIS_PELANGGARAN', [
    'Muatan Pornografi/Kesusilaan',
    'Muatan Kekerasan',
    'Perjudian',
    'Penghinaan/Isu SARA',
    'Iklan Tidak Sesuai Ketentuan',
    'Muatan Rokok/Zat Adiktif',
    'Muatan Mistik/Horor Berlebihan',
    'Bahasa Kasar/Tidak Pantas',
    'Pelanggaran Privasi/Pencemaran Nama Baik',
    'Siaran Tidak Berizin',
    'Lainnya',
]);
