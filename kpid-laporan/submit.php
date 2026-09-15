<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

function tolak($pesan)
{
    header('Location: index.php?error=' . urlencode($pesan));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// --- Validasi CSRF ---
if (!csrf_valid($_POST['csrf_token'] ?? '')) {
    tolak('Sesi Anda telah kedaluwarsa. Silakan coba kirim ulang laporan.');
}

// --- Ambil & validasi input ---
$nama_pelapor      = trim($_POST['nama_pelapor'] ?? '');
$no_whatsapp       = trim($_POST['no_whatsapp'] ?? '');
$email             = trim($_POST['email'] ?? '');
$jenis_media       = trim($_POST['jenis_media'] ?? '');
$nama_stasiun      = trim($_POST['nama_stasiun'] ?? '');
$nama_program      = trim($_POST['nama_program'] ?? '');
$tanggal_kejadian  = trim($_POST['tanggal_kejadian'] ?? '');
$jam_kejadian      = trim($_POST['jam_kejadian'] ?? '');
$jenis_pelanggaran = trim($_POST['jenis_pelanggaran'] ?? '');
$deskripsi         = trim($_POST['deskripsi'] ?? '');

if ($nama_pelapor === '' || mb_strlen($nama_pelapor) > 100) {
    tolak('Nama pelapor tidak valid.');
}
if (!preg_match('/^0[0-9]{9,13}$/', $no_whatsapp)) {
    tolak('Format nomor WhatsApp tidak valid. Contoh: 08123456789');
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    tolak('Format email tidak valid.');
}
if (!in_array($jenis_media, ['Televisi', 'Radio'], true)) {
    tolak('Jenis media tidak valid.');
}
if ($nama_stasiun === '' || mb_strlen($nama_stasiun) > 100) {
    tolak('Nama stasiun wajib diisi.');
}
$tgl_obj = DateTime::createFromFormat('Y-m-d', $tanggal_kejadian);
if (!$tgl_obj || $tgl_obj->format('Y-m-d') !== $tanggal_kejadian || $tgl_obj > new DateTime()) {
    tolak('Tanggal kejadian tidak valid.');
}
if (!in_array($jenis_pelanggaran, JENIS_PELANGGARAN, true)) {
    tolak('Jenis pelanggaran tidak valid.');
}
if (mb_strlen($deskripsi) < 20) {
    tolak('Deskripsi laporan minimal 20 karakter.');
}

// Normalisasi nomor WA menjadi format 62xxxxxxxxxx untuk disimpan
$wa_disimpan = '62' . ltrim($no_whatsapp, '0');

// --- Upload bukti (opsional) ---
try {
    $nama_file_bukti = upload_bukti($_FILES['bukti_file'] ?? []);
} catch (Exception $e) {
    tolak($e->getMessage());
}

// --- Simpan ke database ---
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO laporan
            (nama_pelapor, no_whatsapp, email, jenis_media, nama_stasiun, nama_program,
             tanggal_kejadian, jam_kejadian, jenis_pelanggaran, deskripsi, bukti_file, status)
        VALUES
            (:nama_pelapor, :no_whatsapp, :email, :jenis_media, :nama_stasiun, :nama_program,
             :tanggal_kejadian, :jam_kejadian, :jenis_pelanggaran, :deskripsi, :bukti_file, 'Baru')
    ");
    $stmt->execute([
        ':nama_pelapor'      => $nama_pelapor,
        ':no_whatsapp'       => $wa_disimpan,
        ':email'             => $email !== '' ? $email : null,
        ':jenis_media'       => $jenis_media,
        ':nama_stasiun'      => $nama_stasiun,
        ':nama_program'      => $nama_program !== '' ? $nama_program : null,
        ':tanggal_kejadian'  => $tanggal_kejadian,
        ':jam_kejadian'      => $jam_kejadian !== '' ? $jam_kejadian : null,
        ':jenis_pelanggaran' => $jenis_pelanggaran,
        ':deskripsi'         => $deskripsi,
        ':bukti_file'        => $nama_file_bukti,
    ]);

    $id_baru = (int) $pdo->lastInsertId();
    $kode    = buat_kode_laporan($id_baru);

    $stmt2 = $pdo->prepare("UPDATE laporan SET kode_laporan = :kode WHERE id = :id");
    $stmt2->execute([':kode' => $kode, ':id' => $id_baru]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Gagal simpan laporan: ' . $e->getMessage());
    tolak('Terjadi kesalahan saat menyimpan laporan. Silakan coba lagi.');
}

// --- Kirim notifikasi WhatsApp ke Admin (kegagalan WA tidak menggagalkan proses) ---
$data_notif = [
    'id'                => $id_baru,
    'kode_laporan'      => $kode,
    'nama_pelapor'      => $nama_pelapor,
    'no_whatsapp'       => $wa_disimpan,
    'jenis_media'       => $jenis_media,
    'nama_stasiun'      => $nama_stasiun,
    'nama_program'      => $nama_program,
    'tanggal_kejadian'  => $tanggal_kejadian,
    'jenis_pelanggaran' => $jenis_pelanggaran,
    'deskripsi'         => $deskripsi,
];

kirim_whatsapp(ADMIN_WHATSAPP, pesan_notif_admin($data_notif));

if (KIRIM_KONFIRMASI_PELAPOR) {
    kirim_whatsapp($wa_disimpan, pesan_konfirmasi_pelapor($data_notif));
}

// --- Selesai, arahkan ke halaman terima kasih ---
header('Location: terima_kasih.php?kode=' . urlencode($kode));
exit;
