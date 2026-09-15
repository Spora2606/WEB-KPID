<?php
/**
 * ============================================================
 * KUMPULAN FUNGSI BANTU
 * ============================================================
 */

/** Membersihkan input teks dari user */
function bersihkan($str)
{
    $str = trim($str ?? '');
    $str = strip_tags($str);
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/** Generate token CSRF dan simpan di session */
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Cek token CSRF valid atau tidak */
function csrf_valid($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string) $token);
}

/**
 * Membuat kode laporan unik, contoh: LP-20260912-0007
 */
function buat_kode_laporan($id)
{
    return 'LP-' . date('Ymd') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
}

/** Format tanggal Indonesia, contoh: 12 September 2026 */
function format_tanggal($tanggal)
{
    if (empty($tanggal)) {
        return '-';
    }
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];
    $ts = strtotime($tanggal);
    return date('d', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

/** Format tanggal + jam Indonesia */
function format_tanggal_jam($datetime)
{
    if (empty($datetime)) {
        return '-';
    }
    return format_tanggal($datetime) . ', ' . date('H:i', strtotime($datetime)) . ' WIB';
}

/** Mengembalikan kelas warna badge Bootstrap sesuai status laporan */
function status_badge_class($status)
{
    switch ($status) {
        case 'Baru':      return 'warning text-dark';
        case 'Diproses':  return 'info text-dark';
        case 'Selesai':   return 'success';
        case 'Ditolak':   return 'danger';
        default:          return 'secondary';
    }
}

/**
 * Menangani upload file bukti laporan.
 * Return: nama file baru jika sukses, null jika user tidak upload apa-apa,
 * atau melempar Exception berisi pesan error jika file bermasalah.
 */
function upload_bukti($file)
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // tidak ada file diupload, tidak masalah karena opsional
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Terjadi kesalahan saat mengunggah file bukti.');
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('Ukuran file bukti maksimal 5MB.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXT, true)) {
        throw new Exception('Format file tidak didukung. Gunakan JPG, PNG, GIF, MP4, atau PDF.');
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $nama_baru = 'bukti_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $tujuan = UPLOAD_DIR . $nama_baru;

    if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
        throw new Exception('Gagal menyimpan file bukti ke server.');
    }

    return $nama_baru;
}

/**
 * Mengirim pesan WhatsApp lewat Fonnte API.
 * Return: true jika request berhasil dikirim, false jika gagal.
 * Kegagalan kirim WA TIDAK menggagalkan proses laporan
 * (laporan tetap masuk ke database walau WA gagal terkirim).
 */
function kirim_whatsapp($nomor_tujuan, $pesan)
{
    if (empty(FONNTE_TOKEN) || FONNTE_TOKEN === 'ISI_TOKEN_FONNTE_ANDA_DISINI') {
        error_log('Fonnte belum dikonfigurasi (token kosong). Pesan WA tidak dikirim.');
        return false;
    }

    $nomor_tujuan = preg_replace('/[^0-9]/', '', $nomor_tujuan);
    if (strpos($nomor_tujuan, '0') === 0) {
        $nomor_tujuan = '62' . substr($nomor_tujuan, 1);
    }

    $ch = curl_init('https://api.fonnte.com/send');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['Authorization: ' . FONNTE_TOKEN],
        CURLOPT_POSTFIELDS     => [
            'target'  => $nomor_tujuan,
            'message' => $pesan,
        ],
    ]);

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log('Gagal kirim WhatsApp (cURL): ' . $error);
        return false;
    }

    error_log('Respon Fonnte: ' . $response);
    return true;
}

/** Susun pesan notifikasi laporan baru untuk Admin */
function pesan_notif_admin($data)
{
    $link = rtrim(SITE_URL, '/') . '/admin/detail.php?id=' . $data['id'];
    $teks  = "*LAPORAN BARU MASUK*\n\n";
    $teks .= "Kode Laporan: *{$data['kode_laporan']}*\n";
    $teks .= "Nama Pelapor: {$data['nama_pelapor']}\n";
    $teks .= "No. WhatsApp: {$data['no_whatsapp']}\n";
    $teks .= "Media: {$data['jenis_media']} - {$data['nama_stasiun']}\n";
    if (!empty($data['nama_program'])) {
        $teks .= "Program: {$data['nama_program']}\n";
    }
    $teks .= "Tanggal Kejadian: " . format_tanggal($data['tanggal_kejadian']) . "\n";
    $teks .= "Jenis Pelanggaran: {$data['jenis_pelanggaran']}\n\n";
    $teks .= "Deskripsi:\n{$data['deskripsi']}\n\n";
    if (!empty(SITE_URL)) {
        $teks .= "Lihat detail: {$link}";
    } else {
        $teks .= "Silakan buka halaman admin untuk melihat detail lengkap.";
    }
    return $teks;
}

/** Susun pesan konfirmasi untuk pelapor */
function pesan_konfirmasi_pelapor($data)
{
    $teks  = "Halo {$data['nama_pelapor']},\n\n";
    $teks .= "Terima kasih, laporan Anda telah kami terima dengan kode:\n";
    $teks .= "*{$data['kode_laporan']}*\n\n";
    $teks .= "Simpan kode ini untuk memantau status laporan Anda melalui halaman \"Cek Status Laporan\" di website kami.\n\n";
    $teks .= "Tim kami akan segera menindaklanjuti laporan Anda.\n\n";
    $teks .= "Terima kasih atas partisipasi Anda dalam mengawal siaran yang sehat.\n";
    $teks .= NAMA_INSTANSI;
    return $teks;
}

/** Susun pesan update status untuk pelapor */
function pesan_update_status($kode_laporan, $status, $catatan = '')
{
    $teks  = "*UPDATE LAPORAN ANDA*\n\n";
    $teks .= "Kode Laporan: *{$kode_laporan}*\n";
    $teks .= "Status terbaru: *{$status}*\n";
    if (!empty($catatan)) {
        $teks .= "\nCatatan dari petugas:\n{$catatan}\n";
    }
    $teks .= "\nTerima kasih atas partisipasi Anda.\n" . NAMA_INSTANSI;
    return $teks;
}
