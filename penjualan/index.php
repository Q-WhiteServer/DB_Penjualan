<?php require 'config/koneksi.php'; require 'includes/fungsi.php';
$judul = 'Beranda'; include 'includes/header.php';
$ver = $pdo->query('SELECT version()')->fetchColumn(); ?>
<h2>Aplikasi Penjualan (PHP Native + PostgreSQL)</h2>
<div class="ok">Koneksi database berhasil.<br><small><?= e($ver) ?></small></div>
<ul>
 <li><a href="produk.php">CRUD Produk</a></li>
 <li><a href="pelanggan.php">CRUD Pelanggan</a></li>
 <li><a href="penjualan.php">Form Transaksi Penjualan</a></li>
</ul>
<?php include 'includes/footer.php'; ?>
