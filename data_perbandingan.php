<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
    header("location:login.php");
    exit();
}

include 'koneksi.php';

if (!isset($_GET['id'])) {
    echo "ID responden tidak ditemukan.";
    exit();
}

$id_responden = $_GET['id'];

// Ambil nama responden (jika ada)
$responden_result = mysqli_query($conn, "SELECT nama_responden FROM responden WHERE id_responden = '$id_responden'");
$responden_data = mysqli_fetch_assoc($responden_result);

// Ambil data perbandingan untuk responden ini
$query = "
    SELECT 
        pk.nilai,
        k1.nama_kriteria AS kriteria_kiri,
        k2.nama_kriteria AS kriteria_kanan
    FROM perbandingan_kriteria pk
    JOIN kriteria k1 ON pk.id_kriteria1 = k1.id_kriteria
    JOIN kriteria k2 ON pk.id_kriteria2 = k2.id_kriteria
    WHERE pk.id_responden = '$id_responden'
    ORDER BY pk.id
";
$result = mysqli_query($conn, $query);
$data_perbandingan = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data_perbandingan[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Detail Perbandingan Responden</title>
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
			z-index: 1050;
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
        .btn-green-light {
            background-color: rgb(103, 175, 106);
            color: #fff;
            border: none;
        }
        .btn-green-light:hover {
            background-color: #81c784;
            color: #fff;
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

<!-- Konten Utama -->
<div class="main-wrapper">
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Konten -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4><i class="fas fa-balance-scale"></i> Detail Perbandingan - Responden <?= htmlspecialchars($id_responden) ?><?= $responden_data ? ' (' . htmlspecialchars($responden_data['nama_responden']) . ')' : '' ?></h4>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span>Responden <?= $id_responden ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Kriteria</th>
                                <th>Skala Perbandingan</th>
                                <th>Kriteria</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data_perbandingan)): ?>
                                <tr><td colspan="3">Belum ada data perbandingan untuk responden ini.</td></tr>
                            <?php else: ?>
                                <?php foreach ($data_perbandingan as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['kriteria_kiri']) ?></td>
                                        <td><?= htmlspecialchars($row['nilai']) ?></td>
                                        <td><?= htmlspecialchars($row['kriteria_kanan']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-end">
                <a href="edit_perbandingan.php?id=<?= $id_responden ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
                <a href="cek_konsistensi.php?id=<?= $id_responden ?>" class="btn btn-warning btn-sm text-white">
                    <i class="fas fa-brain me-1"></i> Cek Konsistensi
                </a>
                <a href="hapus_responden.php?id=<?= $id_responden ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Yakin ingin menghapus data responden ini?')">
                    <i class="fas fa-trash me-1"></i> Hapus
                </a>
            </div>
        </div>

    </div>
</div>
  <script>
    function toggleSidebar() {
      document.querySelector('.sidebar-fixed').classList.toggle('active');
    }
  </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
