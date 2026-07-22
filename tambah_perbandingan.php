<?php
session_start();
include 'koneksi.php';

// Ambil kriteria dari database
$kriteria = [];
$kriteria_result = $conn->query("SELECT * FROM kriteria ORDER BY id_kriteria ASC");
while ($row = $kriteria_result->fetch_assoc()) {
  $kriteria[] = $row;
}
$jumlah_kriteria = count($kriteria);

// Simpan data jika form disubmit
if (isset($_POST['submit'])) {
  $nama_responden = trim($_POST['nama_responden']);
  if ($nama_responden !== '') {
    // Simpan ke tabel responden
    $stmt = $conn->prepare("INSERT INTO responden (nama_responden) VALUES (?)");
    $stmt->bind_param("s", $nama_responden);
    $stmt->execute();
    $id_responden = $conn->insert_id;

// Simpan perbandingan kriteria
for ($i = 0; $i < $jumlah_kriteria - 1; $i++) {
  for ($j = $i + 1; $j < $jumlah_kriteria; $j++) {
    $field_name = "nilai_{$i}_{$j}";
    if (isset($_POST[$field_name])) {
      $nilai_input = floatval($_POST[$field_name]);
      $id_kriteria1 = $kriteria[$i]['id_kriteria'];
      $id_kriteria2 = $kriteria[$j]['id_kriteria'];

      // Pastikan id_kriteria1 < id_kriteria2, jika tidak, balik dan invers nilai
      if ($id_kriteria1 > $id_kriteria2) {
        // Tukar id_kriteria1 dan id_kriteria2
        $tmp = $id_kriteria1;
        $id_kriteria1 = $id_kriteria2;
        $id_kriteria2 = $tmp;

        // Invers nilai (hindari div0)
        $nilai = ($nilai_input != 0) ? 1 / $nilai_input : 0;
      } else {
        $nilai = $nilai_input;
      }

      $stmt2 = $conn->prepare("INSERT INTO perbandingan_kriteria (id_responden, id_kriteria1, id_kriteria2, nilai) VALUES (?, ?, ?, ?)");
      $stmt2->bind_param("iiid", $id_responden, $id_kriteria1, $id_kriteria2, $nilai);
      $stmt2->execute();
    }
  }
    }
    // Redirect hanya setelah penyimpanan berhasil
    header("Location: tampil_responden.php");
    exit;
  }
}
?>


<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Perbandingan Kriteria</title>
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

      <!-- Konten Utama -->
      <div class="container mt-4">
        <div class="card shadow">
          <div class="card-body">
            <h4 class="card-title text-primary">Perbandingan Kriteria</h4>
            <form method="post">
              <div class="mb-3">
                <label for="nama_responden" class="form-label">Nama Responden</label>
                <input type="text" name="nama_responden" id="nama_responden" class="form-control" required>
              </div>

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
                      <tr>
                        <td><?= $kriteria[$i]['nama_kriteria'] ?></td>
                        <td>
                          <div class="scale-options">
                            <?php
                            for ($val = 9; $val >= 2; $val--) {
                              $input_id = "left_{$i}_{$j}_$val";
                              echo "
                                <input type='radio' class='btn-check' name='nilai_{$i}_{$j}' id='{$input_id}' value='{$val}' autocomplete='off'>
                                <label class='btn btn-outline-primary btn-sm' for='{$input_id}'>{$val}</label>
                              ";
                            }

                            $input_id = "equal_{$i}_{$j}";
                            echo "
                              <input type='radio' class='btn-check' name='nilai_{$i}_{$j}' id='{$input_id}' value='1' autocomplete='off'>
                              <label class='btn btn-outline-success btn-sm' for='{$input_id}'>1</label>
                            ";

                            for ($val = 2; $val <= 9; $val++) {
                              $input_id = "right_{$i}_{$j}_$val";
                              $value = round(1 / $val, 4);
                              echo "
                                <input type='radio' class='btn-check' name='nilai_{$i}_{$j}' id='{$input_id}' value='{$value}' autocomplete='off'>
                                <label class='btn btn-outline-danger btn-sm' for='{$input_id}'>$val</label>
                              ";
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

              <div class="d-flex justify-content-end mt-3 gap-2">
                <button type="button" onclick="history.back()" class="btn btn-secondary">Kembali</button>
                <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div> <!-- /Main Content -->
  </div> <!-- /d-flex -->
  <script>
  function toggleSidebar() {
    document.querySelector('.sidebar-fixed').classList.toggle('active');
  }
</script>
</body>
</html>
