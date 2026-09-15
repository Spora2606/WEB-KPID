<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($judul_halaman) ? $judul_halaman . ' - ' : ''; ?>Admin - <?php echo SITE_NAME; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
  <div class="container-fluid px-4">
    <a class="navbar-brand fw-bold" href="dashboard.php">
      <i class="bi bi-speedometer2"></i> Admin - <?php echo NAMA_INSTANSI; ?>
    </a>
    <div class="d-flex align-items-center">
      <span class="text-light small me-3">
        <i class="bi bi-person-circle"></i> <?php echo bersihkan($_SESSION['admin_nama'] ?? ''); ?>
      </span>
      <a href="logout.php" class="btn btn-sm btn-outline-light">
        <i class="bi bi-box-arrow-right"></i> Keluar
      </a>
    </div>
  </div>
</nav>

<main class="container-fluid px-4 py-4">
