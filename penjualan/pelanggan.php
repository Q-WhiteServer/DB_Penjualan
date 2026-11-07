<?php require 'config/koneksi.php'; require 'includes/fungsi.php';
$aksi = $_GET['aksi'] ?? 'tampil'; $err = '';
$f = ['kode_pelanggan'=>'','nama_pelanggan'=>'','no_telepon'=>'','alamat'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($aksi === 'hapus') {
            $pdo->prepare('DELETE FROM pelanggan WHERE kode_pelanggan = ?')->execute([$_POST['kode_pelanggan']]);
            header('Location: pelanggan.php?pesan=' . urlencode('Pelanggan berhasil dihapus')); exit;
        }
        $f = array_merge($f, array_intersect_key($_POST, $f));
        if ($aksi === 'tambah') {
            $pdo->prepare('INSERT INTO pelanggan (kode_pelanggan,nama_pelanggan,no_telepon,alamat) VALUES (?,?,?,?)')
                ->execute(array_values($f));
            $m = 'Pelanggan berhasil ditambahkan';
        } else {
            $pdo->prepare('UPDATE pelanggan SET nama_pelanggan=?,no_telepon=?,alamat=? WHERE kode_pelanggan=?')
                ->execute([$f['nama_pelanggan'], $f['no_telepon'], $f['alamat'], $f['kode_pelanggan']]);
            $m = 'Pelanggan berhasil diubah';
        }
        header('Location: pelanggan.php?pesan=' . urlencode($m)); exit;
    } catch (PDOException $ex) {
        $err = match ($ex->getCode()) {
            '23505' => 'Kode pelanggan sudah dipakai.',
            '23503' => 'Pelanggan tidak bisa dihapus karena sudah memiliki transaksi penjualan.',
            default => $ex->getMessage(),
        };
        if ($aksi === 'hapus') $aksi = 'tampil';
    }
} elseif ($aksi === 'ubah') {
    $s = $pdo->prepare('SELECT * FROM pelanggan WHERE kode_pelanggan = ?'); $s->execute([$_GET['kode'] ?? '']);
    $f = $s->fetch() ?: $f;
}

$judul = 'Pelanggan'; include 'includes/header.php';
if (!empty($_GET['pesan'])) echo '<div class="ok">' . e($_GET['pesan']) . '</div>';
if ($err) echo '<div class="err">' . e($err) . '</div>';

if ($aksi === 'tambah' || $aksi === 'ubah'): ?>
<h2><?= $aksi === 'tambah' ? 'Tambah' : 'Ubah' ?> Pelanggan</h2>
<form method="post" action="pelanggan.php?aksi=<?= e($aksi) ?>">
 <label>Kode Pelanggan</label><input name="kode_pelanggan" value="<?= e($f['kode_pelanggan']) ?>" required maxlength="20" <?= $aksi==='ubah'?'readonly':'' ?>>
 <label>Nama Pelanggan</label><input name="nama_pelanggan" value="<?= e($f['nama_pelanggan']) ?>" required>
 <label>No. Telepon</label><input name="no_telepon" value="<?= e($f['no_telepon']) ?>">
 <label>Alamat</label><textarea name="alamat" rows="3"><?= e($f['alamat']) ?></textarea>
 <p><button class="btn hijau">Simpan</button> <a class="btn abu" href="pelanggan.php">Batal</a></p>
</form>
<?php else: $rows = $pdo->query('SELECT * FROM pelanggan ORDER BY kode_pelanggan')->fetchAll(); ?>
<h2>Data Pelanggan</h2><a class="btn hijau" href="pelanggan.php?aksi=tambah">+ Tambah Pelanggan</a>
<table><tr><th>Kode</th><th>Nama</th><th>No. Telepon</th><th>Alamat</th><th>Aksi</th></tr>
<?php foreach ($rows as $r): ?>
<tr><td><?= e($r['kode_pelanggan']) ?></td><td><?= e($r['nama_pelanggan']) ?></td><td><?= e($r['no_telepon']) ?></td><td><?= e($r['alamat']) ?></td>
 <td><a class="btn" href="pelanggan.php?aksi=ubah&kode=<?= urlencode($r['kode_pelanggan']) ?>">Ubah</a>
  <form class="inline" method="post" action="pelanggan.php?aksi=hapus" onsubmit="return confirm('Hapus pelanggan ini?')">
   <input type="hidden" name="kode_pelanggan" value="<?= e($r['kode_pelanggan']) ?>"><button class="btn merah">Hapus</button></form></td></tr>
<?php endforeach; if (!$rows) echo '<tr><td colspan="5">Belum ada data.</td></tr>'; ?>
</table>
<?php endif; include 'includes/footer.php'; ?>
