<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: dashboard.php');
    exit;
}

$pesan_sukses = '';
$pesan_error  = '';
$status_valid = ['Baru', 'Diproses', 'Selesai', 'Ditolak'];

// --- Proses update status & tambah catatan ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? '')) {
        $pesan_error = 'Sesi kedaluwarsa, silakan muat ulang halaman dan coba lagi.';
    } else {
        $status_baru   = trim($_POST['status'] ?? '');
        $isi_catatan   = trim($_POST['isi_catatan'] ?? '');
        $kirim_wa_flag = isset($_POST['kirim_wa']);

        if (!in_array($status_baru, $status_valid, true)) {
            $pesan_error = 'Status tidak valid.';
        } elseif ($isi_catatan === '' && $_POST['status'] === $_POST['status_lama']) {
            $pesan_error = 'Isi catatan tidak boleh kosong jika status tidak diubah.';
        } else {
            try {
                $pdo->beginTransaction();

                $stmtUpdate = $pdo->prepare("UPDATE laporan SET status = :status WHERE id = :id");
                $stmtUpdate->execute([':status' => $status_baru, ':id' => $id]);

                if ($isi_catatan !== '') {
                    $stmtCatatan = $pdo->prepare("
                        INSERT INTO catatan (laporan_id, admin_id, isi_catatan)
                        VALUES (:laporan_id, :admin_id, :isi)
                    ");
                    $stmtCatatan->execute([
                        ':laporan_id' => $id,
                        ':admin_id'   => $_SESSION['admin_id'],
                        ':isi'        => $isi_catatan,
                    ]);
                }

                $pdo->commit();
                $pesan_sukses = 'Laporan berhasil diperbarui.';

                if ($kirim_wa_flag) {
                    $stmtLp = $pdo->prepare("SELECT kode_laporan, no_whatsapp FROM laporan WHERE id = :id");
                    $stmtLp->execute([':id' => $id]);
                    $lp = $stmtLp->fetch();
                    if ($lp) {
                        $terkirim = kirim_whatsapp(
                            $lp['no_whatsapp'],
                            pesan_update_status($lp['kode_laporan'], $status_baru, $isi_catatan)
                        );
                        $pesan_sukses .= $terkirim
                            ? ' Notifikasi WhatsApp telah dikirim ke pelapor.'
                            : ' Namun notifikasi WhatsApp gagal terkirim (periksa konfigurasi Fonnte).';
                    }
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log('Gagal update laporan: ' . $e->getMessage());
                $pesan_error = 'Terjadi kesalahan saat menyimpan perubahan.';
            }
        }
    }
}

// --- Ambil data laporan ---
$stmt = $pdo->prepare("SELECT * FROM laporan WHERE id = :id");
$stmt->execute([':id' => $id]);
$laporan = $stmt->fetch();

if (!$laporan) {
    header('Location: dashboard.php');
    exit;
}

$stmtCatatan = $pdo->prepare("
    SELECT catatan.isi_catatan, catatan.created_at, admin.nama_lengkap
    FROM catatan
    JOIN admin ON admin.id = catatan.admin_id
    WHERE laporan_id = :id
    ORDER BY catatan.created_at DESC
");
$stmtCatatan->execute([':id' => $id]);
$daftar_catatan = $stmtCatatan->fetchAll();

$token = csrf_token();
$judul_halaman = 'Detail Laporan';
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0"><i class="bi bi-file-earmark-text"></i> Detail Laporan</h4>
  <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<?php if ($pesan_sukses): ?>
  <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?php echo bersihkan($pesan_sukses); ?></div>
<?php endif; ?>
<?php if ($pesan_error): ?>
  <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?php echo bersihkan($pesan_error); ?></div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
        <span><?php echo bersihkan($laporan['kode_laporan']); ?></span>
        <span class="badge bg-<?php echo status_badge_class($laporan['status']); ?> badge-status">
          <?php echo bersihkan($laporan['status']); ?>
        </span>
      </div>
      <div class="card-body">
        <table class="table table-borderless table-sm mb-0">
          <tr><td class="text-muted" width="200">Nama Pelapor</td><td>: <?php echo bersihkan($laporan['nama_pelapor']); ?></td></tr>
          <tr><td class="text-muted">No. WhatsApp</td><td>: <?php echo bersihkan($laporan['no_whatsapp']); ?></td></tr>
          <tr><td class="text-muted">Email</td><td>: <?php echo bersihkan($laporan['email'] ?: '-'); ?></td></tr>
          <tr><td class="text-muted">Jenis Media</td><td>: <?php echo bersihkan($laporan['jenis_media']); ?></td></tr>
          <tr><td class="text-muted">Nama Stasiun</td><td>: <?php echo bersihkan($laporan['nama_stasiun']); ?></td></tr>
          <tr><td class="text-muted">Nama Program</td><td>: <?php echo bersihkan($laporan['nama_program'] ?: '-'); ?></td></tr>
          <tr><td class="text-muted">Tanggal Kejadian</td><td>: <?php echo format_tanggal($laporan['tanggal_kejadian']); ?> <?php echo $laporan['jam_kejadian'] ? '(' . bersihkan($laporan['jam_kejadian']) . ' WIB)' : ''; ?></td></tr>
          <tr><td class="text-muted">Jenis Pelanggaran</td><td>: <?php echo bersihkan($laporan['jenis_pelanggaran']); ?></td></tr>
          <tr><td class="text-muted">Tanggal Lapor</td><td>: <?php echo format_tanggal_jam($laporan['created_at']); ?></td></tr>
        </table>
      </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-semibold">Deskripsi/Kronologi</div>
      <div class="card-body">
        <p class="mb-0" style="white-space: pre-line;"><?php echo bersihkan($laporan['deskripsi']); ?></p>
      </div>
    </div>

    <?php if (!empty($laporan['bukti_file'])): ?>
      <?php
        $ext = strtolower(pathinfo($laporan['bukti_file'], PATHINFO_EXTENSION));
        $url_bukti = '../uploads/' . $laporan['bukti_file'];
      ?>
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">Bukti Lampiran</div>
        <div class="card-body">
          <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true)): ?>
            <img src="<?php echo $url_bukti; ?>" class="img-fluid rounded" alt="Bukti laporan">
          <?php elseif ($ext === 'mp4'): ?>
            <video controls class="w-100 rounded"><source src="<?php echo $url_bukti; ?>" type="video/mp4"></video>
          <?php else: ?>
            <a href="<?php echo $url_bukti; ?>" target="_blank" class="btn btn-outline-primary">
              <i class="bi bi-file-earmark-arrow-down"></i> Buka File Bukti
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-5">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white fw-semibold"><i class="bi bi-pencil-square"></i> Update Status & Catatan</div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
          <input type="hidden" name="status_lama" value="<?php echo bersihkan($laporan['status']); ?>">

          <div class="mb-3">
            <label class="form-label">Status Laporan</label>
            <select name="status" class="form-select">
              <?php foreach ($status_valid as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo $laporan['status'] === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Catatan/Balasan (opsional)</label>
            <textarea name="isi_catatan" class="form-control" rows="4" placeholder="Tulis catatan tindak lanjut..."></textarea>
          </div>

          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="kirim_wa" id="kirim_wa" checked>
            <label class="form-check-label" for="kirim_wa">
              Kirim notifikasi update ini ke WhatsApp pelapor
            </label>
          </div>

          <div class="d-grid">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white fw-semibold"><i class="bi bi-clock-history"></i> Riwayat Catatan</div>
      <div class="card-body">
        <?php if (empty($daftar_catatan)): ?>
          <p class="text-muted small mb-0">Belum ada catatan.</p>
        <?php else: ?>
          <?php foreach ($daftar_catatan as $c): ?>
            <div class="catatan-item">
              <p class="mb-1" style="white-space: pre-line;"><?php echo bersihkan($c['isi_catatan']); ?></p>
              <p class="mb-0 small text-muted">
                oleh <?php echo bersihkan($c['nama_lengkap']); ?> &middot; <?php echo format_tanggal_jam($c['created_at']); ?>
              </p>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
