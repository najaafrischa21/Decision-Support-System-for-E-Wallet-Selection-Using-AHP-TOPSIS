<?php
session_start();
include 'koneksi.php';

if (!isset($_GET['id'])) {
    echo "ID responden tidak ditemukan.";
    exit();
}

$id_responden = $_GET['id'];

// Ambil data nama responden
$query_responden = $conn->query("SELECT * FROM responden WHERE id_responden = $id_responden");
$data_responden = $query_responden->fetch_assoc();
$nama_responden = $data_responden['nama_responden'] ?? 'Responden';

$query_kriteria = $conn->query("SELECT * FROM kriteria ORDER BY id_kriteria");
$kriteria = [];
while ($row = $query_kriteria->fetch_assoc()) {
    $kriteria[] = $row;
}
$jumlah_kriteria = count($kriteria);

function ahp_round($value) {
    $skala = [1/9, 1/8, 1/7, 1/6, 1/5, 1/4, 1/3, 1/2, 1, 2, 3, 4, 5, 6, 7, 8, 9];
    $closest = $skala[0];
    $min_diff = abs($value - $closest);
    foreach ($skala as $s) {
        $diff = abs($value - $s);
        if ($diff < $min_diff) {
            $min_diff = $diff;
            $closest = $s;
        }
    }
    return $closest;
}

$matriks = array_fill(0, $jumlah_kriteria, array_fill(0, $jumlah_kriteria, 1));
$query_nilai = $conn->query("SELECT * FROM perbandingan_kriteria WHERE id_responden = $id_responden");
while ($row = $query_nilai->fetch_assoc()) {
    $id1 = array_search($row['id_kriteria1'], array_column($kriteria, 'id_kriteria'));
    $id2 = array_search($row['id_kriteria2'], array_column($kriteria, 'id_kriteria'));
    $nilai = $row['nilai'];
    if ($id1 !== false && $id2 !== false && $nilai != 0) {
        $matriks[$id1][$id2] = $nilai;
        $matriks[$id2][$id1] = ahp_round(1 / $nilai);
    }
}

$jumlah_kolom = array_fill(0, $jumlah_kriteria, 0);
for ($j = 0; $j < $jumlah_kriteria; $j++) {
    for ($i = 0; $i < $jumlah_kriteria; $i++) {
        $jumlah_kolom[$j] += $matriks[$i][$j];
    }
}

$normalisasi = array_fill(0, $jumlah_kriteria, array_fill(0, $jumlah_kriteria, 0));
$prioritas = array_fill(0, $jumlah_kriteria, 0);
for ($i = 0; $i < $jumlah_kriteria; $i++) {
    for ($j = 0; $j < $jumlah_kriteria; $j++) {
        $normalisasi[$i][$j] = $matriks[$i][$j] / $jumlah_kolom[$j];
    }
    $prioritas[$i] = array_sum($normalisasi[$i]) / $jumlah_kriteria;
}

$lambda_max = 0;
for ($i = 0; $i < $jumlah_kriteria; $i++) {
    $lambda_max += $jumlah_kolom[$i] * $prioritas[$i];
}

$ci = ($jumlah_kriteria > 1) ? ($lambda_max - $jumlah_kriteria) / ($jumlah_kriteria - 1) : 0;
$ri_values = [0, 0, 0.58, 0.90, 1.12, 1.24, 1.32, 1.41, 1.45];
$ri = $ri_values[$jumlah_kriteria - 1] ?? 1.49;
$cr = ($ri == 0) ? 0 : $ci / $ri;

$conn->query("DELETE FROM priority_vector WHERE id_responden = $id_responden");
$last_pv_id = null;

for ($i = 0; $i < $jumlah_kriteria; $i++) {
    $id_kriteria = $kriteria[$i]['id_kriteria'];
    $nilai_pv = round($prioritas[$i], 6);
    $stmt = $conn->prepare("INSERT INTO priority_vector (id_responden, id_kriteria, nilai_pv, nilai_cr) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iidd", $id_responden, $id_kriteria, $nilai_pv, $cr);
    $stmt->execute();
    if ($i == 0) {
        $last_pv_id = $conn->insert_id;
    }
    $stmt->close();
}

if ($last_pv_id !== null) {
    $stmt = $conn->prepare("UPDATE responden SET id_pv = ? WHERE id_responden = ?");
    $stmt->bind_param("ii", $last_pv_id, $id_responden);
    $stmt->execute();
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cek Konsistensi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="./assets/compiled/css/app.css">
    <link rel="stylesheet" href="./assets/compiled/css/app-dark.css">
    <style>
	
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
		.table-bordered td, .table-bordered th {
			border: 1px solid #dee2e6 !important;
		}

		.table {
			border-collapse: collapse !important;
		}

		.table th, .table td {
			padding: 0.5rem;
			font-size: 0.9rem;
			vertical-align: middle;
		}

        h4.page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #4B64C7;
        }

        .container {
			padding-left: 30px;
			padding-right: 30px;
		}
        h5.section-title {
            font-size: 1.15rem;
            font-weight: 600;
            margin-top: 2rem;
            color: #4B64C7;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 4px;
        }
		        .table th {
            color: #333;
        }

        .badge-consistent {
            background-color: #0d6efd;
            font-size: 0.85rem;
            padding: 0.4em 0.7em;
            border-radius: 0.25rem;
        }

        .badge-inconsistent {
            background-color: #dc3545;
            font-size: 0.85rem;
            padding: 0.4em 0.7em;
            border-radius: 0.25rem;
        }
		 .table td {
            color: #333;
            text-align: center;
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
    <div class="sidebar-fixed">
        <?php include 'sidebar.php'; ?>
    </div>

    <div class="flex-grow-1">
        <?php include 'navbar.php'; ?>
        <div class="container mt-4">
            <div class="card shadow-sm">
                <div class="card-body bg-white p-0">
				
				 <div class="container mt-4">
					<h5 class="text-left mb-4">
						  <i class="fas fa-calculator me-2 text-primary"></i>
						  Cek Konsistensi - <?= $nama_responden ?>
					</h5>

					<div class="card mb-4">
						<div class="card-header bg-primary text-white">
							<strong>1. Matriks Perbandingan Kriteria</strong>
						</div>
						<div class="card-body p-0">
							<table class="table table-bordered text-center mb-0">
								<thead class="table-light">
									<tr>
										<th>Kriteria</th>
										<?php foreach ($kriteria as $k): ?>
											<th><?= $k['nama_kriteria'] ?></th>
										<?php endforeach; ?>
									</tr>
								</thead>

								<tbody>
									<?php for ($i = 0; $i < $jumlah_kriteria; $i++): ?>
										<tr>
											<th><?= $kriteria[$i]['nama_kriteria'] ?></th>
											<?php for ($j = 0; $j < $jumlah_kriteria; $j++): ?>
												<td><?= number_format($matriks[$i][$j], 3) ?></td>
											<?php endfor; ?>
										</tr>
									<?php endfor; ?>
										<tr class="table-secondary">
											<th>Jumlah</th>
											<?php foreach ($jumlah_kolom as $jml): ?>
												<td><?= round($jml, 3) ?></td>
											<?php endforeach; ?>
										</tr>
								</tbody>
							</table>
						</div>
					</div>

					<!-- Tambahan blok: Matriks Normalisasi -->
					<div class="card mb-4">
						<div class="card-header bg-primary text-white">
							<strong>2. Matriks Normalisasi</strong>
						</div>
						<div class="card-body p-0">
							<table class="table table-bordered text-center mb-0">
								<thead class="table-light">
									<tr>
										<th>Kriteria</th>
										<?php foreach ($kriteria as $k): ?>
											<th><?= $k['nama_kriteria'] ?></th>
										<?php endforeach; ?>
									</tr>
								</thead>


								<tbody>
									<?php for ($i = 0; $i < $jumlah_kriteria; $i++): ?>
										<tr>
											<th><?= $kriteria[$i]['nama_kriteria'] ?></th>
											<?php for ($j = 0; $j < $jumlah_kriteria; $j++): ?>
												<td><?= number_format($normalisasi[$i][$j], 3) ?></td>
											<?php endfor; ?>
										</tr>
									<?php endfor; ?>
								</tbody>


								</thead>
							   
							</table>
						</div>
					</div>

					<!-- Blok Nilai Prioritas dan Konsistensi -->
					<div class="card mb-4">
						<div class="card-header bg-primary text-white">
							<strong>3. Vektor Prioritas</strong>
						</div>
						<div class="card-body p-0">
							<table class="table table-bordered text-center mb-0">
								<thead class="table-light">
									<tr>
										<th>Kriteria</th>
										<th>Vektor Prioritas</th>
									</tr>
								</thead>
								<tbody>
									<?php for ($i = 0; $i < $jumlah_kriteria; $i++): ?>
										<tr>
											<td><?= $kriteria[$i]['nama_kriteria'] ?></td>
											<td><?= round($prioritas[$i], 4) ?></td>
										</tr>
									<?php endfor; ?>
								</tbody>
							</table>
						</div>
					</div>

					
					

					    <div class="card mb-4">
							<div class="card-header bg-primary text-white">
							<strong>4. Konsitensi</strong>
							</div>
							<div class="card-body p-0">
								<table class="table table-bordered text-center">
								<thead class="table-light">
									<tr>
										<th>λ max</th>
										<th>CI</th>
										<th>CR</th>
										<th>Keterangan</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><?= round($lambda_max, 4) ?></td>
										<td><?= round($ci, 4) ?></td>
										<td><?= round($cr, 4) ?></td>
										<td>
											<?= $cr <= 0.1 ? "<span class='text-success fw-bold'>Konsisten</span>" : "<span class='text-danger fw-bold'>Tidak Konsisten</span>" ?>
										</td>
									</tr>
								</tbody>
							</table>
							</div>
						</div>
						</div>

					</div>


					<!-- Tombol Kembali -->
					<div class="text-end mb-4 me-3">
						<a href="detail_perbandingan.php?id=<?= $id_responden ?>" class="btn btn-secondary">
							Kembali 
						</a>
					</div>

			</div>
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
