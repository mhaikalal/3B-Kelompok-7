<?php
/*
 * Aplikasi Antrean Klinik - Tugas Algoritma Pertemuan 5-6
 * Struktur Data: Queue (antrean) menggunakan array PHP
 * Algoritma: Linear Search untuk mencari nomor antrean
 *
 * Penjelasan Struktur Data:
 * - $antrean adalah array yang berfungsi sebagai Queue (FIFO: First In First Out)
 * - Setiap elemen array menyimpan array asosiatif: ['nomor' => int, 'nama' => string, 'waktu' => string]
 * - Operasi enqueue: array_push() menambahkan ke akhir
 * - Operasi dequeue: array_shift() mengambil dari depan
 * - Operasi search: loop linear mencari berdasarkan nomor antrean
 */

session_start();

// Inisialisasi queue antrean jika belum ada
if (!isset($_SESSION['antrean'])) {
    $_SESSION['antrean'] = [];
}

// Inisialisasi nomor antrean saat ini
if (!isset($_SESSION['nomor_terpanggil'])) {
    $_SESSION['nomor_terpanggil'] = 0;
}

// Proses ambil nomor antrean baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ambil'])) {
    $nama = trim($_POST['nama_pasien'] ?? 'Pasien');
    $nomor_baru = count($_SESSION['antrean']) + 1;
    // Enqueue: masukkan ke akhir array (queue)
    $_SESSION['antrean'][] = [
        'nomor' => $nomor_baru,
        'nama' => $nama,
        'waktu' => date('H:i:s')
    ];
    $pesan = "Nomor antrean $nomor_baru berhasil diambil oleh $nama.";
}

// Proses reset antrean
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset'])) {
    $_SESSION['antrean'] = [];
    $_SESSION['nomor_terpanggil'] = 0;
    $pesan = "Antrean berhasil direset.";
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['panggil'])) {
    if (!empty($_SESSION['antrean'])) {
        // Dequeue: ambil dari depan array (FIFO)
        $terpanggil = array_shift($_SESSION['antrean']);
        $_SESSION['nomor_terpanggil'] = $terpanggil['nomor'];
        $pesan_panggil = "Memanggil nomor antrean: {$terpanggil['nomor']} - {$terpanggil['nama']}";
    } else {
        $pesan_panggil = "Tidak ada antrean yang tersisa.";
    }
}

// Linear Search: mencari nomor antrean tertentu
$hasil_cari = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cari'])) {
    $cari_nomor = (int) ($_POST['cari_nomor'] ?? 0);
    // Linear Search: iterasi satu per satu
    foreach ($_SESSION['antrean'] as $item) {
        if ($item['nomor'] === $cari_nomor) {
            $hasil_cari = $item;
            break;
        }
    }
    if ($hasil_cari === null) {
        $pesan_cari = "Nomor antrean $cari_nomor tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Antrean Klinik</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;600;800&display=swap');
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #e8f6f3; min-height: 100vh; padding: 30px; margin: 0; }
        .container { max-width: 800px; margin: auto; background: rgba(255,255,255,0.95); padding: 35px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); backdrop-filter: blur(8px); }
        h1 { color: #006064; font-weight: 800; letter-spacing: -1px; margin-bottom: 5px; }
        .subtitle { color: #4a6b6e; font-size: 15px; margin-bottom: 25px; }
        .card { background: #fff; border: 1px solid #e0f2f1; border-radius: 14px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,150,136,0.05); }
        .btn { padding: 12px 24px; border: none; border-radius: 10px; cursor: pointer; font-size: 16px; font-weight: 600; transition: all .2s; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        .btn-ambil { background: #00b894; color: white; }
        .btn-ambil:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,184,148,0.3); }
        .btn-panggil { background: #e17055; color: white; }
        .btn-panggil:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(225,112,85,0.3); }
        .btn-cari { background: #0984e3; color: white; }
        input { padding: 10px 14px; border: 2px solid #dfe6e9; border-radius: 8px; font-size: 15px; width: 60%; transition: border-color .2s; }
        input:focus { border-color: #00b894; outline: none; }
        .antrean-box { background: #f8f9fa; padding: 20px; border-radius: 14px; border-left: 5px solid #00b894; }
        .item { padding: 12px; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center; }
        .item:last-child { border-bottom: none; }
        .badge { background: #00b894; color: white; padding: 4px 10px; border-radius: 20px; font-weight: 700; font-size: 13px; }
        .pesan { padding: 14px; border-radius: 10px; margin: 12px 0; font-weight: 500; }
        .pesan-ok { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #2e7d32; }
        .pesan-info { background: #e3f2fd; color: #1565c0; border-left: 4px solid #1565c0; }
        .nomor-besar { font-size: 72px; font-weight: 800; color: #e17055; text-align: center; letter-spacing: -4px; }
        .label-kecil { text-align: center; color: #636e72; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; }
    </style>
    <script>
        // Suara ting-ting saat tombol panggil ditekan
        function playTingTung() {
            var audio = new Audio('assets/audio/Announcement sound effect.mp3');
            audio.volume = 1.0;
            audio.play();
            return true;
        }
    </script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
<div class="container">
    <h1><i class='bx bx-clinic'></i> Antrean Klinik</h1>

    <?php if (!empty($pesan_panggil)): ?>
        <div class="card" style="text-align:center; background: #fff5e6;">
            <p class="label-kecil">Nomor Terpanggil</p>
            <div class="nomor-besar"><?= $_SESSION['nomor_terpanggil'] ?></div>
            <p style="font-size:18px; color:#d35400; font-weight:600; margin-top:10px;"><?= htmlspecialchars($pesan_panggil) ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($pesan)): ?><div class="pesan pesan-ok"><?= htmlspecialchars($pesan) ?></div><?php endif; ?>
    <?php if (!empty($pesan_panggil)): ?><div class="pesan pesan-info"><?= htmlspecialchars($pesan_panggil) ?></div><?php endif; ?>
    <?php if (!empty($pesan_cari)): ?><div class="pesan pesan-info"><?= htmlspecialchars($pesan_cari) ?></div><?php endif; ?>

    <form method="post">
        <label>Nama Pasien:</label><br>
        <input type="text" name="nama_pasien" placeholder="Masukkan nama" style="padding:8px; width: 60%; border-radius:4px; border:1px solid #ccc;">
        <button type="submit" name="ambil" class="btn btn-ambil"><i class='bx bx-plus-circle'></i> Ambil Nomor Antrean</button>
    </form>

    <form method="post" style="margin-top:10px;">
        <button type="submit" name="reset" class="btn" style="background:#636e72; color:white;"><i class='bx bx-refresh'></i> Reset Antrean</button>
    </form>

    <form method="post" style="margin-top:10px;">
        <button type="submit" name="panggil" class="btn btn-panggil" onclick="playTingTung();"><i class='bx bx-bell'></i> Panggil Nomor Berikutnya</button>
    </form>

    <form method="post" style="margin-top:10px;">
        <label>Cari Nomor:</label>
        <input type="number" name="cari_nomor" placeholder="Nomor antrean" style="padding:8px; width: 40%; border-radius:4px; border:1px solid #ccc;">
        <button type="submit" name="cari" class="btn btn-cari"><i class='bx bx-search'></i> Cari (Linear Search)</button>
    </form>

    <?php if ($hasil_cari): ?>
        <div class="pesan pesan-ok">
            Ditemukan: Nomor <?= $hasil_cari['nomor'] ?> - <?= htmlspecialchars($hasil_cari['nama']) ?> (<?= $hasil_cari['waktu'] ?>)
        </div>
    <?php endif; ?>

    <h3>Daftar Antrean Saat Ini (Queue)</h3>
    <div class="antrean-box">
        <?php if (empty($_SESSION['antrean'])): ?>
            <p>Tidak ada antrean.</p>
        <?php else: ?>
            <?php foreach ($_SESSION['antrean'] as $item): ?>
                <div class="item">
                    <strong>Nomor: <?= $item['nomor'] ?></strong> | Nama: <?= htmlspecialchars($item['nama']) ?> | Waktu: <?= $item['waktu'] ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <p style="margin-top:20px; color:#7f8c8d; font-size:14px;">
        Nomor terakhir terpanggil: <strong><?= $_SESSION['nomor_terpanggil'] ?></strong>
    </p>
</div>
</body>
</html>
