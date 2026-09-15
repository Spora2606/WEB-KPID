<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
$token = csrf_token();
?>

<div class="hero">
  <div class="container text-center">
    <h1><i class="bi bi-megaphone"></i> Sampaikan Laporan Siaran Anda</h1>
    <p class="lead mb-0">Bersama mengawal siaran televisi dan radio yang sehat, mendidik, dan sesuai aturan.</p>
  </div>
</div>

<div class="container mb-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">

      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle"></i> <?php echo bersihkan($_GET['error']); ?>
        </div>
      <?php endif; ?>

      <div class="card card-form">
        <div class="card-header">
          <i class="bi bi-file-earmark-text"></i> Form Laporan Masyarakat
        </div>
        <div class="card-body p-4">
          <form action="submit.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">

            <h6 class="text-primary fw-bold mb-3">1. Data Pelapor</h6>
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label">Nama Lengkap <span class="required-mark">*</span></label>
                <input type="text" name="nama_pelapor" class="form-control" required maxlength="100">
                <div class="invalid-feedback">Nama wajib diisi.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">No. WhatsApp Aktif <span class="required-mark">*</span></label>
                <input type="text" name="no_whatsapp" class="form-control" required
                       pattern="^0[0-9]{9,13}$" placeholder="08123456789" maxlength="15">
                <div class="invalid-feedback">Isi nomor WhatsApp yang aktif, contoh: 08123456789.</div>
                <div class="form-text">Digunakan untuk mengirim notifikasi status laporan Anda.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email (opsional)</label>
                <input type="email" name="email" class="form-control" maxlength="100">
                <div class="invalid-feedback">Format email tidak valid.</div>
              </div>
            </div>

            <h6 class="text-primary fw-bold mb-3">2. Detail Siaran yang Dilaporkan</h6>
            <div class="row g-3 mb-4">
              <div class="col-md-4">
                <label class="form-label">Jenis Media <span class="required-mark">*</span></label>
                <select name="jenis_media" class="form-select" required>
                  <option value="" selected disabled>Pilih...</option>
                  <option value="Televisi">Televisi</option>
                  <option value="Radio">Radio</option>
                </select>
                <div class="invalid-feedback">Pilih jenis media.</div>
              </div>
              <div class="col-md-8">
                <label class="form-label">Nama Stasiun TV/Radio <span class="required-mark">*</span></label>
                <input type="text" name="nama_stasiun" class="form-control" required maxlength="100" placeholder="Contoh: TV ABC / Radio XYZ FM">
                <div class="invalid-feedback">Nama stasiun wajib diisi.</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Nama Program/Acara (opsional)</label>
                <input type="text" name="nama_program" class="form-control" maxlength="150">
              </div>
              <div class="col-md-3">
                <label class="form-label">Tanggal Kejadian <span class="required-mark">*</span></label>
                <input type="date" name="tanggal_kejadian" class="form-control" required max="<?php echo date('Y-m-d'); ?>">
                <div class="invalid-feedback">Tanggal wajib diisi.</div>
              </div>
              <div class="col-md-3">
                <label class="form-label">Jam Tayang (opsional)</label>
                <input type="time" name="jam_kejadian" class="form-control">
              </div>
              <div class="col-md-12">
                <label class="form-label">Jenis Pelanggaran <span class="required-mark">*</span></label>
                <select name="jenis_pelanggaran" class="form-select" required>
                  <option value="" selected disabled>Pilih jenis pelanggaran...</option>
                  <?php foreach (JENIS_PELANGGARAN as $jp): ?>
                    <option value="<?php echo bersihkan($jp); ?>"><?php echo bersihkan($jp); ?></option>
                  <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Pilih jenis pelanggaran.</div>
              </div>
            </div>

            <h6 class="text-primary fw-bold mb-3">3. Kronologi & Bukti</h6>
            <div class="row g-3 mb-4">
              <div class="col-12">
                <label class="form-label">Deskripsi/Kronologi Laporan <span class="required-mark">*</span></label>
                <textarea name="deskripsi" class="form-control" rows="5" required minlength="20"
                          placeholder="Jelaskan secara rinci apa yang terjadi, mengapa Anda melaporkannya..."></textarea>
                <div class="invalid-feedback">Deskripsi minimal 20 karakter.</div>
              </div>
              <div class="col-12">
                <label class="form-label">Upload Bukti (opsional)</label>
                <input type="file" name="bukti_file" id="bukti_file" class="form-control"
                       accept=".jpg,.jpeg,.png,.gif,.mp4,.pdf">
                <div class="form-text">Format: JPG, PNG, GIF, MP4, atau PDF. Maksimal 5MB.</div>
                <div id="bukti_file_label" class="small text-success mt-1"></div>
              </div>
            </div>

            <div class="form-check mb-4">
              <input class="form-check-input" type="checkbox" id="persetujuan" required>
              <label class="form-check-label" for="persetujuan">
                Saya menyatakan bahwa laporan ini saya buat dengan sebenar-benarnya. <span class="required-mark">*</span>
              </label>
              <div class="invalid-feedback">Anda harus menyetujui pernyataan ini.</div>
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-send"></i> Kirim Laporan
              </button>
            </div>
          </form>
        </div>
      </div>

      <p class="text-center text-muted small mt-3">
        Data Anda akan dijaga kerahasiaannya dan hanya digunakan untuk keperluan tindak lanjut laporan.
      </p>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
