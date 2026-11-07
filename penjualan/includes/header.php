<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul ?? 'Aplikasi Penjualan') ?></title>
<style>
 body{font-family:Arial,sans-serif;margin:0;background:#f4f6f9;color:#222}
 nav{background:#1f3a5f;padding:12px 24px}
 nav a{color:#fff;text-decoration:none;margin-right:18px;font-weight:bold}
 .wrap{max-width:1000px;margin:24px auto;background:#fff;padding:24px;border-radius:8px}
 table{width:100%;border-collapse:collapse;margin-top:12px}
 th,td{border:1px solid #ddd;padding:8px;text-align:left;font-size:14px}
 th{background:#e8eef7}
 input,select,textarea{padding:7px;width:100%;box-sizing:border-box;margin-top:4px}
 label{display:block;margin-top:12px;font-weight:bold;font-size:14px}
 .btn{display:inline-block;padding:7px 14px;background:#2d6cdf;color:#fff;border:0;border-radius:4px;cursor:pointer;text-decoration:none;font-size:14px;width:auto}
 .btn.merah{background:#c62828}.btn.abu{background:#777}.btn.hijau{background:#2e7d32}
 .ok{background:#e6f4ea;color:#1e6b34;padding:10px;border-radius:4px;margin-bottom:12px}
 .err{background:#fdecea;color:#a12622;padding:10px;border-radius:4px;margin-bottom:12px}
 form.inline{display:inline}.cari{display:flex;gap:8px}.cari input{margin:0}
</style></head><body>
<nav><a href="index.php">Beranda</a><a href="produk.php">Produk</a><a href="pelanggan.php">Pelanggan</a>
<a href="penjualan.php">Transaksi Penjualan</a><a href="uji_transaksi.php">Uji Transaksi</a></nav>
<div class="wrap">
