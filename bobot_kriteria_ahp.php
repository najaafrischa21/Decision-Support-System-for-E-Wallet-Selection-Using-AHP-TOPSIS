<?php
include 'koneksi.php';

// Ambil semua kriteria
$kriteria_result = $conn->query("SELECT * FROM kriteria ORDER BY id_kriteria ASC");
$kriteria = [];
while ($row = $kriteria_result->fetch_assoc()) {
    $kriteria[] = $row;
}

// Ambil semua responden unik yang Keterangan = 'Konsisten'
$responden_result = $conn->query("SELECT id_responden FROM responden WHERE Keterangan = 'Konsisten' ORDER BY id_responden ASC");
$responden = [];
while ($row = $responden_result->fetch_assoc()) {
    $responden[] = $row['id_responden'];
}

// Buat array 2 dimensi untuk simpan nilai pv per responden dan kriteria
$data_pv = [];
foreach ($responden as $id_responden) {
    foreach ($kriteria as $k) {
        $id_kriteria = $k['id_kriteria'];
        $query = "SELECT nilai_pv FROM priority_vector WHERE id_responden = $id_responden AND id_kriteria = $id_kriteria LIMIT 1";
        $result = $conn->query($query);
        $nilai_pv = null;
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $nilai_pv = $row['nilai_pv'];
        }
        $data_pv[$id_responden][$id_kriteria] = $nilai_pv;
    }
}

// Buat string daftar id responden yang konsisten untuk filter query
$id_responden_str = implode(',', $responden);

// Array untuk menyimpan bobot akhir setiap kriteria
$bobot_kriteria = [];
$total_bobot = 0;

// Hitung bobot per kriteria dengan rata-rata geometrik
foreach ($kriteria as $k) {
    $id_kriteria = $k['id_kriteria'];

    $query = "SELECT nilai_pv FROM priority_vector WHERE id_kriteria = $id_kriteria AND id_responden IN ($id_responden_str)";
    $result = $conn->query($query);

    $hasil_kali = 1;
    $jumlah_data = 0;

    while ($row = $result->fetch_assoc()) {
        $hasil_kali *= $row['nilai_pv'];
        $jumlah_data++;
    }

    if ($jumlah_data > 0) {
        $bobot = pow($hasil_kali, 1 / $jumlah_data);
        $bobot_kriteria[] = [
            'id_kriteria' => $id_kriteria,
            'nama_kriteria' => $k['nama_kriteria'],
            'bobot_geomean' => $bobot
        ];
        $total_bobot += $bobot;
    }
}

// Normalisasi dan update ke database
foreach ($bobot_kriteria as &$b) {
    $bobot_normalisasi = $b['bobot_geomean'] / $total_bobot;
    $b['bobot_normalisasi'] = $bobot_normalisasi;
    $b['bobot'] = $b['bobot_geomean']; // untuk ditampilkan ke tabel

    // Update ke database
    $bobot_update = $conn->real_escape_string($bobot_normalisasi);
    $id_kriteria = $b['id_kriteria'];
    $update_query = "UPDATE kriteria SET bobot = '$bobot_update' WHERE id_kriteria = $id_kriteria";
    $conn->query($update_query);
}
unset($b);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Bobot Kriteria AHP</title>
    <link rel="shortcut icon" href="./assets/compiled/svg/favicon.svg" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" />
    <link rel="stylesheet" href="./assets/compiled/css/app.css">
    <link rel="stylesheet" href="./assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="./assets/compiled/css/iconly.css">

    <style>
        body {
            background-color: #f0f4f8;
        }

        .sidebar-fixed {
            position: fixed;
            top: 0;
            bottom: 0;
            width: 250px;
            background-color: #fff;
            border-right: 1px solid #dee2e6;
            overflow: hidden;
        }

        .flex-grow-1 {
            margin-left: 250px;
        }

        .card {
            border-radius: 16px;
            border: none;
        }

        .card-title {
            font-weight: bold;
            color: #4B64C7;
        }

        .table thead {
            background-color: #f4f6fa;
            color: #4B64C7;
        }

        .table th {
            text-align: center;
        }

        .table td {
            color: #333;
            text-align: center;
        }

        .btn-secondary {
            border-radius: 8px;
        }

        .container {
            padding-left: 30px;
            padding-right: 30px;
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
		z-index: 1050;
	  }

	  .sidebar-fixed.active {
		left: 0;
	  }

	  .flex-grow-1 {
		margin-left: 0;
	  }

	  .sidebar-toggle-btn {
		display: block;
	  }

	  .form-container {
		padding: 15px;
	  }
	}
    </style>
</head>

<body>
<button class="sidebar-toggle-btn" onclick="toggleSidebar()">☰</button>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar-fixed">
            <?php include 'sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Navbar -->
            <?php include 'navbar.php'; ?>

            <div class="container mt-4">
                <!-- Tabel Nilai PV -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-table me-2 text-primary"></i> Vektor Prioritas Responden Konsisten
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Responden</th>
                                        <?php foreach ($kriteria as $k): ?>
                                            <th><?= htmlspecialchars($k['nama_kriteria']) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($responden as $id_responden): ?>
                                        <tr>
                                            <td><?= $id_responden ?></td>
                                            <?php foreach ($kriteria as $k): 
                                                $nilai = $data_pv[$id_responden][$k['id_kriteria']];
                                            ?>
                                                <td><?= $nilai !== null ? number_format($nilai, 4) : '-' ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tabel Bobot Geomean -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-thumbtack me-2 text-primary"></i> Bobot Kriteria (Geomean)
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Nama Kriteria</th>
                                        <th>Bobot</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bobot_kriteria as $b): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($b['nama_kriteria']) ?></td>
                                            <td><?= number_format($b['bobot'], 4) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tabel Bobot Normalisasi -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-balance-scale me-2 text-success"></i> Bobot Kriteria (Normalisasi)
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-success">
                                    <tr>
                                        <th>Nama Kriteria</th>
                                        <th>Bobot Normalisasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bobot_kriteria as $b): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($b['nama_kriteria']) ?></td>
                                            <td><?= number_format($b['bobot_normalisasi'], 4) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <a href="tampil_responden.php" class="btn btn-secondary">Kembali</a>
                        </div>
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
