<?php
/**
 * ============================================================
 * KONFIGURASI DATABASE
 * Silakan sesuaikan 4 baris "define" di bawah ini dengan data
 * database yang Anda buat di hosting (cPanel > MySQL Databases).
 * ============================================================
 */

define('DB_HOST', 'localhost');       // biasanya 'localhost'
define('DB_NAME', 'kpid_laporan');    // nama database Anda
define('DB_USER', 'root');            // username database Anda
define('DB_PASS', '');                // password database Anda

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Koneksi database gagal. Silakan periksa kembali file config/database.php. Detail: " . htmlspecialchars($e->getMessage()));
}
