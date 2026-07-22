<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
    header("location:login.php");
    exit();
}
include 'koneksi.php';

// Ambil data
$kriteria = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id_kriteria");
$list_kriteria = [];
while ($k = mysqli_fetch_assoc($kriteria)) $list_kriteria[] = $k;

$alternatif = mysqli_query($conn, "SELECT * FROM alternatif ORDER BY id_alternatif");
$list_alternatif = [];
while ($a = mysqli_fetch_assoc($alternatif)) $list_alternatif[] = $a;

// Ambil nilai keputusan
$nilai = [];
foreach ($list_alternatif as $alt) {
    foreach ($list_kriteria as $krit) {
        $q = mysqli_query($conn, "SELECT nilai_akhir FROM nilai_topsis WHERE id_alternatif={$alt['id_alternatif']} AND id_kriteria={$krit['id_kriteria']}");
        $row = mysqli_fetch_assoc($q);
        $nilai[$alt['id_alternatif']][$krit['id_kriteria']] = $row ? $row['nilai_akhir'] : 0;
    }
}

// Normalisasi
$normalisasi = [];
$divisor = [];
foreach ($list_kriteria as $krit) {
    $id_krit = $krit['id_kriteria'];
    $sum_sq = 0;
    foreach ($list_alternatif as $alt) {
        $sum_sq += pow($nilai[$alt['id_alternatif']][$id_krit], 2);
    }
    $divisor[$id_krit] = sqrt($sum_sq);

    foreach ($list_alternatif as $alt) {
        $normalisasi[$alt['id_alternatif']][$id_krit] = $divisor[$id_krit] != 0 ? $nilai[$alt['id_alternatif']][$id_krit] / $divisor[$id_krit] : 0;
    }
}

// Normalisasi Terbobot
$terbobot = [];
foreach ($list_alternatif as $alt) {
    foreach ($list_kriteria as $krit) {
        $id_krit = $krit['id_kriteria'];
        $terbobot[$alt['id_alternatif']][$id_krit] = $normalisasi[$alt['id_alternatif']][$id_krit] * $krit['bobot'];
    }
}

// Solusi ideal positif & negatif
$ideal_plus = $ideal_min = [];
foreach ($list_kriteria as $krit) {
    $id_krit = $krit['id_kriteria'];
    $jenis = strtolower($krit['jenis']);
    $values = [];
    foreach ($list_alternatif as $alt) {
        $values[] = $terbobot[$alt['id_alternatif']][$id_krit];
    }

    if ($jenis == 'benefit') {
        $ideal_plus[$id_krit] = max($values);
        $ideal_min[$id_krit]  = min($values);
    } else {
        $ideal_plus[$id_krit] = min($values);
        $ideal_min[$id_krit]  = max($values);
    }
}

// Hitung jarak solusi ideal
$jarak_plus = $jarak_min = [];
foreach ($list_alternatif as $alt) {
    $sum_plus = $sum_min = 0;
    foreach ($list_kriteria as $krit) {
        $id_krit = $krit['id_kriteria'];
        $v = $terbobot[$alt['id_alternatif']][$id_krit];
        $sum_plus += pow($v - $ideal_plus[$id_krit], 2);
        $sum_min  += pow($v - $ideal_min[$id_krit], 2);
    }
    $jarak_plus[$alt['id_alternatif']] = sqrt($sum_plus);
    $jarak_min[$alt['id_alternatif']]  = sqrt($sum_min);
}

// Nilai preferensi
$preferensi = [];
foreach ($list_alternatif as $alt) {
    $id_alt = $alt['id_alternatif'];
    $cc = ($jarak_plus[$id_alt] + $jarak_min[$id_alt]) != 0
        ? $jarak_min[$id_alt] / ($jarak_plus[$id_alt] + $jarak_min[$id_alt])
        : 0;
    $preferensi[] = ['nama' => $alt['nama_alternatif'], 'nilai' => $cc];
}
usort($preferensi, fn($a, $b) => $b['nilai'] <=> $a['nilai']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hasil Akhir TOPSIS</title>
    <link rel="shortcut icon" href="./assets/compiled/svg/favicon.svg" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" />
    <link rel="stylesheet" href="./assets/compiled/css/app.css">
    <link rel="stylesheet" href="./assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="./assets/compiled/css/iconly.css">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f6fa;
        }

        .sidebar-fixed {
            position: fixed;
            top: 0;
            bottom: 0;
            width: 250px;
            background-color: #fff;
            border-right: 1px solid #dee2e6;
            overflow: hidden;
			height: 100vh;
			z-index: 1050;
			transition: left 0.3s ease;

        }

        .main-wrapper {
            margin-left: 250px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            padding: 20px;
            flex: 1;
            overflow-y: auto;
          
        }

        .container {
			padding-left: 40px;
			padding-right: 40px;
		}
        .table th {
            color: #333;
        }
        .table td {
            color: #333;
        }
		
				/* Tombol toggle sidebar */
		.sidebar-toggle-btn {
			display: none;
			position: fixed;
			bottom: 20px;
			left: 20px;
			z-index: 1100;
			background-color: #4B64C7;
			color: #fff;
			padding: 10px 15px;
			border: none;
			border-radius: 5px;
			font-size: 1rem;
		}

		/* Responsif untuk layar kecil */
		@media (max-width: 768px) {
			.sidebar-fixed {
				left: -250px;
				transition: left 0.3s ease;
			}

			.sidebar-fixed.active {
				left: 0;
			}

			.main-wrapper {
				margin-left: 0;
			}

			.sidebar-toggle-btn {
				display: block;
			}
		}


    </style>
</head>
<body>
<button class="sidebar-toggle-btn" onclick="toggleSidebar()">☰ </button>
    <!-- Sidebar -->
<div class="sidebar-fixed">
    <?php include 'sidebar.php'; ?>
</div>

<!-- Main Content -->
<div class="main-wrapper">
    <?php include 'navbar.php'; ?>
    
<div class="container py-4">
    <h5 class="mb-3"><i class="fas fa-table"></i> Hasil Akhir Metode TOPSIS</h5>

    <!-- 1. Bobot dan Jenis -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">1. Bobot dan Jenis Kriteria</div>
        <div class="card-body p-0">
            <table class="table table-bordered text-center mb-0">
                <thead class="table-light">
                    <tr><?php foreach ($list_kriteria as $i => $krit): ?><th><?= 'C'.($i+1) ?> (<?= ucfirst($krit['jenis']) ?>)</th><?php endforeach; ?></tr>
                </thead>
                <tbody>
                    <tr><?php foreach ($list_kriteria as $krit): ?><td><?= round($krit['bobot'], 4) ?></td><?php endforeach; ?></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Matriks Keputusan -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">2. Matriks Keputusan</div>
        <div class="card-body p-0">
            <table class="table table-bordered text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Alternatif</th>
                        <?php foreach ($list_kriteria as $k): ?><th><?= $k['nama_kriteria'] ?></th><?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list_alternatif as $alt): ?>
                    <tr>
                        <td><?= $alt['nama_alternatif'] ?></td>
                        <?php foreach ($list_kriteria as $k): ?>
                        <td><?= $nilai[$alt['id_alternatif']][$k['id_kriteria']] ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Matriks Normalisasi -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">3. Matriks Normalisasi</div>
        <div class="card-body p-0">
            <table class="table table-bordered text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Alternatif</th>
                        <?php foreach ($list_kriteria as $k): ?><th><?= $k['nama_kriteria'] ?></th><?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list_alternatif as $alt): ?>
                    <tr>
                        <td><?= $alt['nama_alternatif'] ?></td>
                        <?php foreach ($list_kriteria as $k): ?>
                        <td><?= round($normalisasi[$alt['id_alternatif']][$k['id_kriteria']], 3) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. Matriks Normalisasi Terbobot -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">4. Matriks Normalisasi Terbobot</div>
        <div class="card-body p-0">
            <table class="table table-bordered text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Alternatif</th>
                        <?php foreach ($list_kriteria as $k): ?><th><?= $k['nama_kriteria'] ?></th><?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list_alternatif as $alt): ?>
                    <tr>
                        <td><?= $alt['nama_alternatif'] ?></td>
                        <?php foreach ($list_kriteria as $k): ?>
                        <td><?= round($terbobot[$alt['id_alternatif']][$k['id_kriteria']], 3) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. Solusi Ideal -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">5. Solusi Ideal Positif dan Negatif</div>
        <div class="card-body p-0">
            <table class="table table-bordered text-center mb-0">
                <thead class="table-light">
                    <tr><th></th><?php foreach ($list_kriteria as $k): ?><th><?= $k['nama_kriteria'] ?></th><?php endforeach; ?></tr>
                </thead>
                <tbody>
                    <tr><td>Ideal +</td><?php foreach ($list_kriteria as $k): ?><td><?= round($ideal_plus[$k['id_kriteria']], 3) ?></td><?php endforeach; ?></tr>
                    <tr><td>Ideal -</td><?php foreach ($list_kriteria as $k): ?><td><?= round($ideal_min[$k['id_kriteria']], 3) ?></td><?php endforeach; ?></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 6. Jarak ke Solusi Ideal -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">6. Jarak ke Solusi Ideal</div>
        <div class="card-body p-0">
            <table class="table table-bordered text-center mb-0">
                <thead class="table-light">
                    <tr><th>Alternatif</th><th>D+</th><th>D-</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($list_alternatif as $alt): ?>
                    <tr>
                        <td><?= $alt['nama_alternatif'] ?></td>
                        <td><?= round($jarak_plus[$alt['id_alternatif']], 3) ?></td>
                        <td><?= round($jarak_min[$alt['id_alternatif']], 3) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 7. Nilai Preferensi dan Ranking -->
    <div class="card mb-4">
    <div class="card-header bg-primary text-white">4. Nilai Preferensi dan Ranking</div>
    <div class="card-body p-0">
        <table class="table table-bordered text-center mb-0">
            <thead class="table-light">
                <tr>
                    <th>Alternatif</th>
                    <th>Preferensi</th>
                    <th>Ranking</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Urutkan preferensi berdasarkan nilai preferensi (dari yang tertinggi)
                usort($preferensi, fn($a, $b) => $b['nilai'] <=> $a['nilai']); 
                foreach ($preferensi as $rank => $alt): ?>
                    <tr>
                        <td><?= $alt['nama'] ?></td>
                        <td><?= round($alt['nilai'], 3) ?></td>
                        <td><?= $rank + 1 ?></td> <!-- Ranking dimulai dari 1 -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div>
    <script>
    function toggleSidebar() {
      document.querySelector('.sidebar-fixed').classList.toggle('active');
    }
  </script>
</body>
</html>
