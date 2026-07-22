<!-- Tambahkan ini di atas DOCTYPE -->
<?php
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

// Ambil semua kriteria
$kriteria = [];
$kriteria_result = $conn->query("SELECT * FROM kriteria ORDER BY id_kriteria ASC");
while ($row = $kriteria_result->fetch_assoc()) {
    $kriteria[] = $row;
}
$jumlah_kriteria = count($kriteria);

// Ambil data perbandingan untuk responden ini
$perbandingan = [];
$result = $conn->query("SELECT * FROM perbandingan_kriteria WHERE id_responden = $id_responden");
while ($row = $result->fetch_assoc()) {
    $key = "{$row['id_kriteria1']}_{$row['id_kriteria2']}";
    $perbandingan[$key] = $row['nilai'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update semua perbandingan
    for ($i = 0; $i < $jumlah_kriteria - 1; $i++) {
        for ($j = $i + 1; $j < $jumlah_kriteria; $j++) {
            $field_name = "nilai_{$i}_{$j}";
            if (isset($_POST[$field_name])) {
                $nilai = floatval($_POST[$field_name]);
                $id_kriteria1 = $kriteria[$i]['id_kriteria'];
                $id_kriteria2 = $kriteria[$j]['id_kriteria'];

                $stmt = $conn->prepare("UPDATE perbandingan_kriteria SET nilai = ? WHERE id_responden = ? AND id_kriteria1 = ? AND id_kriteria2 = ?");
                $stmt->bind_param("diii", $nilai, $id_responden, $id_kriteria1, $id_kriteria2);
                $stmt->execute();
            }
        }
    }

    echo "<script>alert('Perbandingan berhasil diperbarui!'); window.location.href='detail_perbandingan.php?id=$id_responden';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Perbandingan - Responden <?= $id_responden ?></title>
    <link rel="shortcut icon" href="./assets/compiled/svg/favicon.svg" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" />
    <link rel="stylesheet" href="./assets/compiled/css/app.css">
    <link rel="stylesheet" href="./assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="./assets/compiled/css/iconly.css">
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

    body {
      background-color: #f4f6fa;
      font-family: Arial, sans-serif;
    }

	.form-container {
	  padding: 30px;
	}
    .card {
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .card-header h5 {
      color: #ffffff;
      margin-bottom: 0;
    }

    .form-body {
      padding-top: 20px;
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
	
        .scale-options {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.25rem;
        }

        .scale-options input[type="radio"] {
            display: none;
        }


        .container {
			padding-left: 30px;
			padding-right: 30px;
		}

        .scale-options .btn {
            width: 38px;
            padding: 0.4rem;
            font-weight: bold;
        }

        .scale-options input[type="radio"]:checked + .btn {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
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

            <!-- Page Content -->
            <div class="container mt-4">
                <div class="card shadow-sm">
                    <h5 class="mb-0 mt-3 ps-3"><i class="fas fa-pen"></i> Edit Perbandingan Kriteria - Responden <?= $nama_responden ?></h5>
                    <div class="card-body bg-white">
                        <form method="post">
                            <table class="table table-bordered text-center align-middle">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Kriteria</th>
                                        <th>Skala Perbandingan</th>
                                        <th>Kriteria</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for ($i = 0; $i < $jumlah_kriteria - 1; $i++): ?>
                                        <?php for ($j = $i + 1; $j < $jumlah_kriteria; $j++): ?>
                                            <?php
                                            $id_kriteria1 = $kriteria[$i]['id_kriteria'];
                                            $id_kriteria2 = $kriteria[$j]['id_kriteria'];
                                            $key = "{$id_kriteria1}_{$id_kriteria2}";
                                            $selected = $perbandingan[$key] ?? 1;
                                            ?>
                                            <tr>
                                                <td><?= $kriteria[$i]['nama_kriteria'] ?></td>
                                                <td>
                                                    <div class="scale-options">
                                                        <?php
                                                        for ($val = 9; $val >= 2; $val--) {
                                                            $input_id = "left_{$i}_{$j}_$val";
                                                            $checked = ((string)$selected === (string)$val) ? 'checked' : '';
                                                            echo "<input type='radio' name='nilai_{$i}_{$j}' id='$input_id' value='$val' $checked>";
                                                            echo "<label class='btn btn-outline-primary btn-sm' for='$input_id'>$val</label>";
                                                        }

                                                        $input_id = "equal_{$i}_{$j}";
                                                        $checked = ($selected == 1.0) ? 'checked' : '';
                                                        echo "<input type='radio' name='nilai_{$i}_{$j}' id='$input_id' value='1' $checked>";
                                                        echo "<label class='btn btn-outline-success btn-sm' for='$input_id'>1</label>";

                                                        for ($val = 2; $val <= 9; $val++) {
                                                            $recip = round(1 / $val, 4);
                                                            $input_id = "right_{$i}_{$j}_$val";
                                                            $checked = ((string)$selected === (string)$recip) ? 'checked' : '';
                                                            echo "<input type='radio' name='nilai_{$i}_{$j}' id='$input_id' value='$recip' $checked>";
                                                            echo "<label class='btn btn-outline-danger btn-sm' for='$input_id'>$val</label>";
                                                        }
                                                        ?>
                                                    </div>
                                                </td>
                                                <td><?= $kriteria[$j]['nama_kriteria'] ?></td>
                                            </tr>
                                        <?php endfor; ?>
                                    <?php endfor; ?>
                                </tbody>
                            </table>

                            <div class="text-end">
                                <a href="detail_perbandingan.php?id=<?= $id_responden ?>" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div> <!-- /container -->
        </div> <!-- /Main Content -->
    </div> <!-- /d-flex -->
	<script>
	  function toggleSidebar() {
		document.querySelector('.sidebar-fixed').classList.toggle('active');
	  }
	</script>
</body>
</html>
