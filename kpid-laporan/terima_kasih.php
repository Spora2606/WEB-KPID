<?php
require_once __DIR__ . '/includes/functions.php';
$kode = bersihkan($_GET['kode'] ?? '');
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7 text-center">
      <i class="bi bi-check-circle-fill text-success" style="font-size: 4.5rem;"></i>
      <h2 class="mt-3 fw-bold">Laporan Anda Berhasil Dikirim!</h2>
      <p class="text-muted">Terima kasih telah berpartisipasi mengawal kualitas siaran.</p>

      <?php if ($kode !== ''): ?>
        <div class="card card-form my-4">
          <div class="card-body p-4">
            <p class="mb-1 text-muted">Kode Laporan Anda</p>
            <h3 class="fw-bold text-primary"><?php echo $kode; ?></h3>
            <p class="small text-muted mb-0">
              Simpan kode ini untuk memantau status tindak lanjut laporan Anda.
              Notifikasi juga telah/akan kami kirimkan lewat WhatsApp Anda.
            </p>
          </div>
        </div>
      <?php endif; ?>

      <div class="d-flex gap-2 justify-content-center flex-wrap">
        <a href="cek_status.php<?php echo $kode !== '' ? '?kode=' . urlencode($kode) : ''; ?>" class="btn btn-primary">
          <i class="bi bi-search"></i> Cek Status Laporan
        </a>
        <a href="index.php" class="btn btn-outline-secondary">
          <i class="bi bi-plus-circle"></i> Buat Laporan Lain
        </a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
