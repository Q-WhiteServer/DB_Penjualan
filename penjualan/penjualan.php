<?php require 'config/koneksi.php'; require 'includes/fungsi.php';
$ok = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $items = [];
    foreach (($_POST['kode_produk'] ?? []) as $i => $k) $items[] = ['kode_produk' => $k, 'jumlah' => $_POST['jumlah'][$i] ?? 0];
    try {
        $no = simpanPenjualan($pdo, $_POST['kode_pelanggan'] ?? '', $items, $_POST['keterangan'] ?? '');
        $ok = "Transaksi BERHASIL disimpan (COMMIT). No. penjualan: $no";
    } catch (Throwable $ex) {
        $err = 'Transaksi GAGAL (ROLLBACK, tidak ada data yang tersimpan): ' . $ex->getMessage();
    }
}
$pel  = $pdo->query('SELECT kode_pelanggan, nama_pelanggan FROM pelanggan ORDER BY nama_pelanggan')->fetchAll();
$prod = $pdo->query('SELECT kode_produk, nama_produk, harga_jual, stok FROM produk ORDER BY kode_produk')->fetchAll();
$riwayat = $pdo->query('SELECT p.*, c.nama_pelanggan FROM penjualan p JOIN pelanggan c USING (kode_pelanggan) ORDER BY no_penjualan DESC LIMIT 10')->fetchAll();
$detail = [];
if (!empty($_GET['lihat'])) { $s = $pdo->prepare('SELECT d.*, pr.nama_produk FROM detil_penjualan d JOIN produk pr USING (kode_produk) WHERE no_penjualan = ? ORDER BY no_urut'); $s->execute([$_GET['lihat']]); $detail = $s->fetchAll(); }

$judul = 'Transaksi Penjualan'; include 'includes/header.php';
if ($ok)  echo '<div class="ok">' . e($ok) . '</div>';
if ($err) echo '<div class="err">' . e($err) . '</div>'; ?>
<h2>Form Transaksi Penjualan</h2>
<form method="post">
 <label>Pelanggan</label>
 <select name="kode_pelanggan" required><option value="">-- pilih pelanggan --</option>
 <?php foreach ($pel as $p) echo '<option value="' . e($p['kode_pelanggan']) . '">' . e($p['kode_pelanggan'] . ' - ' . $p['nama_pelanggan']) . '</option>'; ?></select>
 <label>Keterangan</label><input name="keterangan">
 <h3>Detail Produk (minimal 2 baris)</h3>
 <p><small>Kode produk bisa dipilih dari daftar atau diketik manual (untuk uji coba kode invalid, mis. <b>XXX999</b>).</small></p>
 <datalist id="daftarProduk"><?php foreach ($prod as $p) echo '<option value="' . e($p['kode_produk']) . '">' . e($p['nama_produk'] . ' | ' . rupiah($p['harga_jual']) . ' | stok ' . $p['stok']) . '</option>'; ?></datalist>
 <table id="tbl"><tr><th>Kode Produk</th><th style="width:140px">Jumlah</th><th style="width:60px"></th></tr>
  <?php for ($i = 0; $i < 2; $i++): ?>
  <tr><td><input name="kode_produk[]" list="daftarProduk" autocomplete="off"></td><td><input type="number" name="jumlah[]" min="1" value="1"></td><td></td></tr>
  <?php endfor; ?></table>
 <p><button type="button" class="btn abu" onclick="tambahBaris()">+ Tambah Baris</button>
 <button class="btn hijau">Simpan Transaksi</button></p>
</form>
<script>
function tambahBaris(){
  const t=document.getElementById('tbl'),r=t.insertRow();
  r.innerHTML='<td><input name="kode_produk[]" list="daftarProduk" autocomplete="off"></td><td><input type="number" name="jumlah[]" min="1" value="1"></td><td><button type="button" class="btn merah" onclick="this.closest(\'tr\').remove()">x</button></td>';
}
</script>

<h2>Riwayat Penjualan (10 terakhir)</h2>
<table><tr><th>No</th><th>Tanggal</th><th>Pelanggan</th><th>Total</th><th>Keterangan</th><th></th></tr>
<?php foreach ($riwayat as $r): ?>
<tr><td><?= e($r['no_penjualan']) ?></td><td><?= e($r['tgl_penjualan']) ?></td><td><?= e($r['nama_pelanggan']) ?></td><td><?= rupiah($r['total']) ?></td><td><?= e($r['keterangan']) ?></td>
<td><a class="btn" href="?lihat=<?= urlencode($r['no_penjualan']) ?>">Detail</a></td></tr>
<?php endforeach; if (!$riwayat) echo '<tr><td colspan="6">Belum ada transaksi.</td></tr>'; ?></table>
<?php if ($detail): ?><h3>Detail <?= e($_GET['lihat']) ?></h3>
<table><tr><th>No Urut</th><th>Kode</th><th>Produk</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th></tr>
<?php foreach ($detail as $d) echo '<tr><td>' . e($d['no_urut']) . '</td><td>' . e($d['kode_produk']) . '</td><td>' . e($d['nama_produk']) . '</td><td>' . e($d['jumlah']) . '</td><td>' . rupiah($d['harga']) . '</td><td>' . rupiah($d['subtotal']) . '</td></tr>'; ?></table>
<?php endif; include 'includes/footer.php'; ?>
