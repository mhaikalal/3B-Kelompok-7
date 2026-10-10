<?php
session_start();

// untuk inisialisasi queue antrean jika belum ada
if (!isset($_SESSION['antrean'])) {
    $_SESSION['antrean'] = [];
}

// untuk inisialisasi nomor antrean saat ini
if (!isset($_SESSION['nomor_terpanggil'])) {
    $_SESSION['nomor_terpanggil'] = 0;
}

// ini buat ambil nomor antrean baru
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
        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            padding: 30px;
            background: #e8f6f3;
            font-family: 'Inter', sans-serif;
        }

        .container {
            max-width: 800px;
            margin: auto;
            padding: 35px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(8px);
        }

        h1 {
            margin-bottom: 5px;
            color: #006064;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .subtitle {
            margin-bottom: 25px;
            color: #4a6b6e;
            font-size: 15px;
        }

        .card {
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #e0f2f1;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 150, 136, 0.05);
        }

        .card-panggil {
            text-align: center;
            background: #fff5e6;
        }

        .pesan-panggil {
            margin-top: 10px;
            color: #d35400;
            font-size: 18px;
            font-weight: 600;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-ambil {
            color: white;
            background: #00b894;
        }

        .btn-ambil:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 184, 148, 0.3);
        }

        .btn-panggil {
            color: white;
            background: #e17055;
        }

        .btn-panggil:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(225, 112, 85, 0.3);
        }

        .btn-cari {
            color: white;
            background: #0984e3;
        }

        .btn-reset {
            color: white;
            background: #636e72;
        }

        input {
            width: 60%;
            padding: 10px 14px;
            border: 2px solid #dfe6e9;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.2s;
        }

        input:focus {
            border-color: #00b894;
            outline: none;
        }

        .input-pasien,
        .input-cari {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .input-pasien {
            width: 60%;
        }

        .input-cari {
            width: 60%;
        }

        .label-cari {
            display: block;
        }

        .form-spaced {
            margin-top: 10px;
        }

        .antrean-box {
            padding: 20px;
            border-left: 5px solid #00b894;
            border-radius: 14px;
            background: #f8f9fa;
        }

        .item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }

        .item:last-child {
            border-bottom: none;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            color: white;
            background: #00b894;
            font-size: 13px;
            font-weight: 700;
        }

        .pesan {
            margin: 12px 0;
            padding: 14px;
            border-radius: 10px;
            font-weight: 500;
        }

        .pesan-ok {
            border-left: 4px solid #2e7d32;
            color: #2e7d32;
            background: #e8f5e9;
        }

        .pesan-info {
            border-left: 4px solid #1565c0;
            color: #1565c0;
            background: #e3f2fd;
        }

        .nomor-besar {
            color: #e17055;
            font-size: 72px;
            font-weight: 800;
            text-align: center;
            letter-spacing: -4px;
        }

        .label-kecil {
            color: #636e72;
            font-size: 13px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nomor-terakhir {
            margin-top: 20px;
            color: #7f8c8d;
            font-size: 14px;
        }
    </style>
    <script>
        async function playTingTung(event) {
            event.preventDefault();

            const button = event.currentTarget;
            const form = button.form;
            const audio = new Audio(new URL('assets/audio/Announcement%20sound%20effect.mp3', document.baseURI));
            audio.volume = 1.0;

            try {
                const playbackEnded = new Promise((resolve, reject) => {
                    audio.addEventListener('ended', resolve, { once: true });
                    audio.addEventListener('error', () => reject(audio.error || new Error('File audio tidak dapat dimuat.')), { once: true });
                });
                await audio.play();
                await playbackEnded;
            } catch (error) {
                console.error('Suara panggilan gagal diputar:', error);
                window.alert('Suara panggilan tidak dapat diputar. Pemanggilan antrean tetap dilanjutkan.');
            }

            form.requestSubmit(button);
        }
    </script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
<div class="container">
    <h1><i class='bx bx-clinic'></i> Antrean Klinik</h1>

    <?php if (!empty($pesan_panggil)): ?>
        <div class="card card-panggil">
            <p class="label-kecil">Nomor Terpanggil</p>
            <div class="nomor-besar"><?= $_SESSION['nomor_terpanggil'] ?></div>
            <p class="pesan-panggil"><?= htmlspecialchars($pesan_panggil) ?></p>
        </div>
    <?php endif; ?>
    <?php if (!empty($pesan)): ?><div class="pesan pesan-ok"><?= htmlspecialchars($pesan) ?></div><?php endif; ?>
    <?php if (!empty($pesan_panggil)): ?><div class="pesan pesan-info"><?= htmlspecialchars($pesan_panggil) ?></div><?php endif; ?>
    <?php if (!empty($pesan_cari)): ?><div class="pesan pesan-info"><?= htmlspecialchars($pesan_cari) ?></div><?php endif; ?>

    <form method="post">
        <label>Nama Pasien:</label><br>
        <input type="text" name="nama_pasien" placeholder="Masukkan nama" class="input-pasien">
        <button type="submit" name="ambil" class="btn btn-ambil"><i class='bx bx-plus-circle'></i> Ambil Nomor Antrean</button>
    </form>

    <form method="post" class="form-spaced">
        <button type="submit" name="reset" class="btn btn-reset"><i class='bx bx-refresh'></i> Reset Antrean</button>
    </form>

    <form method="post" class="form-spaced">
        <button type="submit" name="panggil" class="btn btn-panggil" onclick="playTingTung(event);"><i class='bx bx-bell'></i> Panggil Nomor Berikutnya</button>
    </form>

    <form method="post" class="form-spaced">
        <label for="cari-nomor" class="label-cari">Cari Nomor:</label>
        <input type="number" id="cari-nomor" name="cari_nomor" placeholder="Nomor antrean" class="input-cari">
        <button type="submit" name="cari" class="btn btn-cari"><i class='bx bx-search'></i> Cari</button>
    </form>

    <?php if ($hasil_cari): ?>
        <div class="pesan pesan-ok">
            Ditemukan: Nomor <?= $hasil_cari['nomor'] ?> - <?= htmlspecialchars($hasil_cari['nama']) ?> (<?= $hasil_cari['waktu'] ?>)
        </div>
    <?php endif; ?>

    <h3>Daftar Antrean Saat Ini</h3>
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

    <p class="nomor-terakhir">
        Nomor terakhir terpanggil: <strong><?= $_SESSION['nomor_terpanggil'] ?></strong>
    </p>
</div>
</body>
</html>
