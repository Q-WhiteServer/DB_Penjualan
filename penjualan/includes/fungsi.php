<?php
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); }

/**
 * Simpan 1 header penjualan + >=2 detail dalam satu transaksi.
 * Jika ada error apa pun => rollBack() dan exception dilempar ulang.
 */
function simpanPenjualan(PDO $pdo, string $kodePelanggan, array $items, string $ket = ''): string
{
    // buang baris kosong
    $items = array_values(array_filter($items, fn($i) => trim($i['kode_produk'] ?? '') !== ''));
    $no = 'PJ' . date('YmdHis') . random_int(10, 99);

    try {
        $pdo->beginTransaction();

        $cek = $pdo->prepare('SELECT 1 FROM pelanggan WHERE kode_pelanggan = ?');
        $cek->execute([$kodePelanggan]);
        if (!$cek->fetch()) throw new Exception("Pelanggan '$kodePelanggan' tidak ditemukan.");

        // 1) header
        $pdo->prepare('INSERT INTO penjualan (no_penjualan, tgl_penjualan, kode_pelanggan, total, keterangan)
                       VALUES (?, CURRENT_DATE, ?, 0, ?)')
            ->execute([$no, $kodePelanggan, $ket]);

        // 2) detail
        $urut = 0; $total = 0;
        $selP = $pdo->prepare('SELECT harga_jual, stok FROM produk WHERE kode_produk = ? FOR UPDATE');
        $insD = $pdo->prepare('INSERT INTO detil_penjualan (no_penjualan, no_urut, kode_produk, jumlah, harga, subtotal)
                               VALUES (?, ?, ?, ?, ?, ?)');
        $updS = $pdo->prepare('UPDATE produk SET stok = stok - ? WHERE kode_produk = ?');

        foreach ($items as $it) {
            $kode = trim($it['kode_produk']);
            $jml  = (int)($it['jumlah'] ?? 0);
            if ($jml <= 0) throw new Exception("Jumlah untuk produk '$kode' harus lebih dari 0.");

            $selP->execute([$kode]);
            $p = $selP->fetch();
            if (!$p) throw new Exception("Kode produk '$kode' tidak valid / tidak ditemukan.");
            if ($p['stok'] < $jml) throw new Exception("Stok produk '$kode' tidak cukup (tersisa {$p['stok']}).");

            $urut++;
            $sub = $p['harga_jual'] * $jml;
            $insD->execute([$no, $urut, $kode, $jml, $p['harga_jual'], $sub]);
            $updS->execute([$jml, $kode]);
            $total += $sub;
        }

        if ($urut < 2) throw new Exception('Transaksi minimal memiliki 2 baris detail produk.');

        // 3) update total header
        $pdo->prepare('UPDATE penjualan SET total = ? WHERE no_penjualan = ?')->execute([$total, $no]);

        $pdo->commit();
        return $no;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
