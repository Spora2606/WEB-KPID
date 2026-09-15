<?php
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$token = csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf_token'] ?? '')) {
        $error = 'Sesi kedaluwarsa, silakan coba lagi.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']     = $admin['id'];
            $_SESSION['admin_nama']   = $admin['nama_lengkap'];
            $_SESSION['admin_user']   = $admin['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin - <?php echo SITE_NAME; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="login-wrapper">
  <div class="card card-form" style="width: 100%; max-width: 400px;">
    <div class="card-body p-4">
      <div class="text-center mb-4">
        <i class="bi bi-shield-lock text-primary" style="font-size: 2.5rem;"></i>
        <h4 class="fw-bold mt-2">Login Admin</h4>
        <p class="text-muted small mb-0"><?php echo NAMA_INSTANSI; ?></p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2 small"><?php echo bersihkan($error); ?></div>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Masuk</button>
        </div>
      </form>
      <div class="text-center mt-3">
        <a href="../index.php" class="small text-muted"><i class="bi bi-arrow-left"></i> Kembali ke Portal Laporan</a>
      </div>
    </div>
  </div>
</div>

</body>
</html>
