-- ============================================================
-- DATABASE: Website Laporan Masyarakat KPID
-- Cara pakai: Import file ini lewat phpMyAdmin
-- (Hosting > phpMyAdmin > pilih database kosong > tab Import)
-- ============================================================

CREATE TABLE IF NOT EXISTS `admin` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `no_whatsapp` VARCHAR(20) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `laporan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kode_laporan` VARCHAR(30) DEFAULT NULL UNIQUE,
  `nama_pelapor` VARCHAR(100) NOT NULL,
  `no_whatsapp` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `jenis_media` ENUM('Televisi','Radio') NOT NULL,
  `nama_stasiun` VARCHAR(100) NOT NULL,
  `nama_program` VARCHAR(150) DEFAULT NULL,
  `tanggal_kejadian` DATE NOT NULL,
  `jam_kejadian` VARCHAR(10) DEFAULT NULL,
  `jenis_pelanggaran` VARCHAR(150) NOT NULL,
  `deskripsi` TEXT NOT NULL,
  `bukti_file` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Baru','Diproses','Selesai','Ditolak') NOT NULL DEFAULT 'Baru',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `catatan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `laporan_id` INT NOT NULL,
  `admin_id` INT NOT NULL,
  `isi_catatan` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`laporan_id`) REFERENCES `laporan`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`admin_id`) REFERENCES `admin`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Akun admin default
-- Username : admin
-- Password : admin123
-- >>> SEGERA GANTI PASSWORD INI SETELAH LOGIN PERTAMA KALI <<<
-- ============================================================
INSERT INTO `admin` (`username`, `password`, `nama_lengkap`, `no_whatsapp`)
VALUES (
  'admin',
  '$2b$12$imCYWR0HrfMqhgYAc1lf2OQ70zlZFc1RDQznUwLG78I8hc..C1DXy',
  'Administrator KPID',
  '6281234567890'
);
