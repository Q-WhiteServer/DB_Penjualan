<?php
// Koneksi PostgreSQL menggunakan PDO
$host = 'localhost';
$port = '5432';
$db   = 'db_penjualan';
$user = 'postgres';
$pass = 'nifarasyam123';   // sesuaikan dengan password PostgreSQL kamu

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
}
