<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

// --- Filter & pencarian ---
$status_filter = $_GET['status'] ?? '';
$cari          = trim($_GET['cari'] ?? '');
$status_valid  = ['Baru', 'Diproses', 'Selesai', 'Ditolak'];

$where  = [];
$params = [];

if (in_array($status_filter, $status_valid, true)) {
    $where[] = 'status = :status';
    $params[':status'] = $status_filter;
}
if ($cari !== '') {
    $where[] = '(nama_pelapor LIKE :cari OR kode_laporan LIKE :cari OR nama_stasiun LIKE :cari)';
    $params[':cari'] = '%' . $cari . '%';
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// --- Pagination sederhana ---
$per_halaman = 10;
$halaman     = max(1, (int) ($_GET['halaman'] ?? 1));
$offset      = ($halaman - 1) * $per_halaman;

$stmtCount = $pdo->prepare("SELECT COUNT(*) AS total FROM laporan $where_sql");
$stmtCount->execute($params);
$total_data   = (int) $stmtCount->fetch()['total'];
$total_halaman = max(1, (int) ceil($total_data / $per_halaman));

$sql = "SELECT * FROM laporan $where_sql ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $per_halaman, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$daftar_laporan = $stmt->fetchAll();

// --- Statistik ringkas ---
$statistik = $pdo->query("
    SELECT status, COUNT(*) AS jumlah FROM laporan GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

$judul_halaman = 'Dashboard';
require_once __DIR__ . '/includes/admin_header.php';
?>

<h4 class="fw-bold mb-4"><i class="bi bi-clipboard-data"></i> Dashboard Laporan Masyarakat</h4>

<div class="row g-3 mb-4">
  <?php
  $kartu = [
      'Baru'     => ['warning', 'bi-envelope-exclamation'],
      'Diproses' => ['info',    'bi-hourglass-split'],
      'Selesai'  => ['success', 'bi-check-circle'],
      'Ditolak'  => ['danger',  'bi-x-circle'],
  ];
  foreach ($kartu as $label => $conf):
      $jml = $statistik[$label] ?? 0;
  ?>
  <div class="col-6 col-md-3">
    <a href="dashboard.php?status=<?php echo urlencode($label); ?>" class="text-decoration-none">
      <div class="card border-0 shadow-sm">
        <div class="card-body d-flex align-items-center gap-3">
          <i class="bi <?php echo $conf[1]; ?> text-<?php echo $conf[0]; ?>" style="font-size: 2rem;"></i>
          <div>
            <div class="fw-bold fs-4"><?php echo $jml; ?></div>
            <div class="text-muted small"><?php echo $label; ?></div>
          </div>
        </div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm mb-3">
  <div class="card-body">
    <form method="GET" class="row g-2 align-items-center">
      <div class="col-md-4">
        <input type="text" name="cari" class="form-control" placeholder="Cari nama / kode / stasiun..."
               value="<?php echo bersihkan($cari); ?>">
      </div>
      <div class="col-md-3">
        <select name="status" class="form-select">
          <option value="">Semua Status</option>
          <?php foreach ($status_valid as $s): ?>
            <option value="<?php echo $s; ?>" <?php echo $status_filter === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel"></i> Filter</button>
      </div>
      <div class="col-md-3 text-md-end">
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Reset Filter</a>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-laporan table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Kode</th>
            <th>Pelapor</th>
            <th>Media / Stasiun</th>
            <th>Jenis Pelanggaran</th>
            <th>Tanggal Lapor</th>
            <th>Status</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($daftar_laporan)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada laporan.</td></tr>
          <?php else: ?>
            <?php foreach ($daftar_laporan as $lp): ?>
              <tr>
                <td class="fw-semibold"><?php echo bersihkan($lp['kode_laporan']); ?></td>
                <td><?php echo bersihkan($lp['nama_pelapor']); ?></td>
                <td><?php echo bersihkan($lp['jenis_media'] . ' - ' . $lp['nama_stasiun']); ?></td>
                <td><?php echo bersihkan($lp['jenis_pelanggaran']); ?></td>
                <td><?php echo format_tanggal($lp['created_at']); ?></td>
                <td>
                  <span class="badge bg-<?php echo status_badge_class($lp['status']); ?> badge-status">
                    <?php echo bersihkan($lp['status']); ?>
                  </span>
                </td>
                <td class="text-end">
                  <a href="detail.php?id=<?php echo (int) $lp['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-eye"></i> Detail
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($total_halaman > 1): ?>
      <nav class="mt-3">
        <ul class="pagination pagination-sm justify-content-center mb-0">
          <?php for ($p = 1; $p <= $total_halaman; $p++): ?>
            <li class="page-item <?php echo $p === $halaman ? 'active' : ''; ?>">
              <a class="page-link" href="?halaman=<?php echo $p; ?>&status=<?php echo urlencode($status_filter); ?>&cari=<?php echo urlencode($cari); ?>">
                <?php echo $p; ?>
              </a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>

  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
