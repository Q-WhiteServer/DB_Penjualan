<?php require 'config/koneksi.php'; require 'includes/fungsi.php';
$aksi = $_GET['aksi'] ?? 'tampil'; $err = '';
$f = ['kode_produk'=>'','nama_produk'=>'','satuan'=>'','harga_jual'=>0,'harga_beli'=>0,'stok'=>0,'deskripsi'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($aksi === 'hapus') {
            $pdo->prepare('DELETE FROM produk WHERE kode_produk = ?')->execute([$_POST['kode_produk']]);
            header('Location: produk.php?pesan=' . urlencode('Produk berhasil dihapus')); exit;
        }
        $f = array_merge($f, array_intersect_key($_POST, $f));
        $data = [$f['nama_produk'], $f['satuan'], $f['harga_jual'], $f['harga_beli'], (int)$f['stok'], $f['deskripsi']];
        if ($aksi === 'tambah') {
            $pdo->prepare('INSERT INTO produk (nama_produk,satuan,harga_jual,harga_beli,stok,deskripsi,kode_produk) VALUES (?,?,?,?,?,?,?)')
                ->execute([...$data, $f['kode_produk']]);
            $m = 'Produk berhasil ditambahkan';
        } else {
            $pdo->prepare('UPDATE produk SET nama_produk=?,satuan=?,harga_jual=?,harga_beli=?,stok=?,deskripsi=? WHERE kode_produk=?')
                ->execute([...$data, $f['kode_produk']]);
            $m = 'Produk berhasil diubah';
        }
        header('Location: produk.php?pesan=' . urlencode($m)); exit;
    } catch (PDOException $ex) {
        $err = match ($ex->getCode()) {
            '23505' => 'Kode produk sudah dipakai.',
            '23503' => 'Produk tidak bisa dihapus karena sudah dipakai di transaksi.',
            default => $ex->getMessage(),
        };
        if ($aksi === 'hapus') $aksi = 'tampil';
    }
} elseif ($aksi === 'ubah') {
    $s = $pdo->prepare('SELECT * FROM produk WHERE kode_produk = ?'); $s->execute([$_GET['kode'] ?? '']);
    $f = $s->fetch() ?: $f;
}

$judul = 'Produk'; include 'includes/header.php';
if (!empty($_GET['pesan'])) echo '<div class="ok">' . e($_GET['pesan']) . '</div>';
if ($err) echo '<div class="err">' . e($err) . '</div>';

if ($aksi === 'tambah' || $aksi === 'ubah'): ?>
<h2><?= $aksi === 'tambah' ? 'Tambah' : 'Ubah' ?> Produk</h2>
<form method="post" action="produk.php?aksi=<?= e($aksi) ?>">
 <label>Kode Produk</label><input name="kode_produk" value="<?= e($f['kode_produk']) ?>" required maxlength="20" <?= $aksi==='ubah'?'readonly':'' ?>>
 <label>Nama Produk</label><input name="nama_produk" value="<?= e($f['nama_produk']) ?>" required>
 <label>Satuan</label><input name="satuan" value="<?= e($f['satuan']) ?>">
 <label>Harga Jual</label><input type="number" step="0.01" min="0" name="harga_jual" value="<?= e($f['harga_jual']) ?>" required>
 <label>Harga Beli</label><input type="number" step="0.01" min="0" name="harga_beli" value="<?= e($f['harga_beli']) ?>" required>
 <label>Stok</label><input type="number" min="0" name="stok" value="<?= e($f['stok']) ?>" required>
 <label>Deskripsi</label><textarea name="deskripsi" rows="3"><?= e($f['deskripsi']) ?></textarea>
 <p><button class="btn hijau">Simpan</button> <a class="btn abu" href="produk.php">Batal</a></p>
</form>
<?php else:
    $q = trim($_GET['q'] ?? '');
    $s = $pdo->prepare('SELECT * FROM produk WHERE nama_produk ILIKE ? ORDER BY kode_produk');
    $s->execute(["%$q%"]); $rows = $s->fetchAll(); ?>
<h2>Data Produk</h2>
<form method="get" class="cari"><input name="q" placeholder="Cari nama produk..." value="<?= e($q) ?>">
 <button class="btn">Cari</button><a class="btn abu" href="produk.php">Reset</a>
 <a class="btn hijau" href="produk.php?aksi=tambah" style="white-space:nowrap">+ Tambah</a></form>
<table><tr><th>Kode</th><th>Nama</th><th>Satuan</th><th>Harga Jual</th><th>Harga Beli</th><th>Stok</th><th>Deskripsi</th><th>Aksi</th></tr>
<?php foreach ($rows as $r): ?>
<tr><td><?= e($r['kode_produk']) ?></td><td><?= e($r['nama_produk']) ?></td><td><?= e($r['satuan']) ?></td>
 <td><?= rupiah($r['harga_jual']) ?></td><td><?= rupiah($r['harga_beli']) ?></td><td><?= e($r['stok']) ?></td><td><?= e($r['deskripsi']) ?></td>
 <td><a class="btn" href="produk.php?aksi=ubah&kode=<?= urlencode($r['kode_produk']) ?>">Ubah</a>
  <form class="inline" method="post" action="produk.php?aksi=hapus" onsubmit="return confirm('Hapus produk ini?')">
   <input type="hidden" name="kode_produk" value="<?= e($r['kode_produk']) ?>"><button class="btn merah">Hapus</button></form></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="8">Data tidak ditemukan.</td></tr>'; ?>
</table>
<?php endif; include 'includes/footer.php'; ?>
