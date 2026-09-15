<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$laporan = null;
$notfound = false;
$kode_dicari = trim($_GET['kode'] ?? '');

if ($kode_dicari !== '') {
    $stmt = $pdo->prepare("SELECT * FROM laporan WHERE kode_laporan = :kode LIMIT 1");
    $stmt->execute([':kode' => $kode_dicari]);
    $laporan = $stmt->fetch();
    if (!$laporan) {
        $notfound = true;
    } else {
        $stmtCatatan = $pdo->prepare("
            SELECT catatan.isi_catatan, catatan.created_at, admin.nama_lengkap
            FROM catatan
            JOIN admin ON admin.id = catatan.admin_id
            WHERE laporan_id = :id
            ORDER BY catatan.created_at ASC
        ");
        $stmtCatatan->execute([':id' => $laporan['id']]);
        $daftar_catatan = $stmtCatatan->fetchAll();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7">
      <h3 class="fw-bold text-center mb-4"><i class="bi bi-search"></i> Cek Status Laporan</h3>

      <form method="GET" class="card card-form mb-4">
        <div class="card-body p-4">
          <label class="form-label">Masukkan Kode Laporan</label>
          <div class="input-group">
            <input type="text" name="kode" class="form-control" placeholder="Contoh: LP-20260912-0001"
                   value="<?php echo bersihkan($kode_dicari); ?>" required>
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Cari</button>
          </div>
        </div>
      </form>

      <?php if ($notfound): ?>
        <div class="alert alert-warning">
          <i class="bi bi-exclamation-triangle"></i> Kode laporan tidak ditemukan. Periksa kembali penulisan kode Anda.
        </div>
      <?php endif; ?>

      <?php if ($laporan): ?>
        <div class="card card-form">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-file-earmark-text"></i> <?php echo bersihkan($laporan['kode_laporan']); ?></span>
            <span class="badge bg-<?php echo status_badge_class($laporan['status']); ?> badge-status">
              <?php echo bersihkan($laporan['status']); ?>
            </span>
          </div>
          <div class="card-body p-4">
            <table class="table table-borderless table-sm mb-4">
              <tr>
                <td class="text-muted" width="180">Nama Pelapor</td>
                <td>: <?php echo bersihkan($laporan['nama_pelapor']); ?></td>
              </tr>
              <tr>
                <td class="text-muted">Media Dilaporkan</td>
                <td>: <?php echo bersihkan($laporan['jenis_media'] . ' - ' . $laporan['nama_stasiun']); ?></td>
              </tr>
              <tr>
                <td class="text-muted">Tanggal Kejadian</td>
                <td>: <?php echo format_tanggal($laporan['tanggal_kejadian']); ?></td>
              </tr>
              <tr>
                <td class="text-muted">Jenis Pelanggaran</td>
                <td>: <?php echo bersihkan($laporan['jenis_pelanggaran']); ?></td>
              </tr>
              <tr>
                <td class="text-muted">Tanggal Lapor</td>
                <td>: <?php echo format_tanggal_jam($laporan['created_at']); ?></td>
              </tr>
            </table>

            <h6 class="fw-bold text-primary">Riwayat Tindak Lanjut</h6>
            <?php if (empty($daftar_catatan)): ?>
              <p class="text-muted small">Belum ada catatan tindak lanjut dari petugas.</p>
            <?php else: ?>
              <?php foreach ($daftar_catatan as $c): ?>
                <div class="catatan-item">
                  <p class="mb-1"><?php echo nl2br(bersihkan($c['isi_catatan'])); ?></p>
                  <p class="mb-0 small text-muted">
                    oleh <?php echo bersihkan($c['nama_lengkap']); ?> &middot; <?php echo format_tanggal_jam($c['created_at']); ?>
                  </p>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
