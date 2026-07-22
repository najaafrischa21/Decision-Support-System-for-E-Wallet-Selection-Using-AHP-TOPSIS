<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
  header("location:login.php");
  exit();
}

include 'koneksi.php'; // koneksi database

$halaman = isset($_GET['halaman']) ? $_GET['halaman'] : 'home';
$kriteria = mysqli_query($conn, "SELECT * FROM kriteria");
if (!$kriteria) {
  die("Query error: " . mysqli_error($conn));
}

// Cek apakah semua bobot sudah dihitung
$cek_bobot = mysqli_query($conn, "SELECT COUNT(*) AS kosong FROM kriteria WHERE bobot IS NULL OR bobot = ''");
$data_bobot = mysqli_fetch_assoc($cek_bobot);
$tampilkan_bobot = $data_bobot['kosong'] == 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Data Kriteria - SPK AHP-TOPSIS</title>

  <link rel="shortcut icon" href="./assets/compiled/svg/favicon.svg" type="image/x-icon" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" />
  <link rel="stylesheet" href="./assets/compiled/css/app.css">
  <link rel="stylesheet" href="./assets/compiled/css/app-dark.css">
  <link rel="stylesheet" href="./assets/compiled/css/iconly.css">

  <!-- ... link stylesheet ... -->
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

    .table td, .table th {
      color: #333;
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
<button class="sidebar-toggle-btn" onclick="toggleSidebar()">☰</button>


	<div class="sidebar-fixed">
	  <?php include 'sidebar.php'; ?>
	</div>

    <!-- Main Content -->
    <div class="main-wrapper">
      <!-- Navbar -->
      <?php include 'navbar.php'; ?>

      <!-- Content -->
      <div class="main-content p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4><i class="fas fa-list"></i> Data Kriteria</h4>
          <div>
            <a href="tambah_kriteria.php" class="btn btn-success"><i class="fas fa-plus me-1"></i>Tambah</a>
          </div>
        </div>

        <div class="alert alert-info">
          Silakan input data kriteria terlebih dahulu. Setelah semua data terisi, klik tombol Bobot Preferensi AHP dimenu data Responden untuk secara otomatis menghitung dan mengisi nilai bobot berdasarkan metode AHP.
        </div>

        <div class="card">
          <div class="card-body">
            <h6 class="mb-3"><i class="fas fa-table"></i> Daftar Data Kriteria</h6>
            <div class="table-responsive">
              <table class="table table-bordered table-striped">
                <thead class="table-primary text-center">
                  <tr>
                    <th>No</th>
                    <th>Kode Kriteria</th>
                    <th>Nama Kriteria</th>
                    <?php if ($tampilkan_bobot): ?>
                      <th>Bobot</th>
                    <?php endif; ?>
                    <th>Jenis</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody class="text-center">
                  <?php
                  $no = 1;
                  mysqli_data_seek($kriteria, 0); // Reset pointer hasil query
                  while ($row = mysqli_fetch_assoc($kriteria)) {
                    echo "<tr>
                      <td>{$no}</td>
                      <td>{$row['kode_kriteria']}</td>
                      <td>{$row['nama_kriteria']}</td>";
                    if ($tampilkan_bobot) {
                      echo "<td>" . ($row['bobot'] !== null && $row['bobot'] !== '' ? number_format($row['bobot'], 4) : '-') . "</td>";
                    }
                    echo "<td>{$row['jenis']}</td>
                      <td>
                        <a href='edit_kriteria.php?id={$row['id_kriteria']}' class='btn btn-warning btn-sm me-1'><i class='fas fa-edit'></i></a>
                        <a href='aksi_hapus_kriteria.php?id={$row['id_kriteria']}' onclick=\"return confirm('Yakin ingin hapus?')\" class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
                      </td>
                    </tr>";
                    $no++;
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div> <!-- end flex-grow -->

  <script>
    function toggleSidebar() {
      document.querySelector('.sidebar-fixed').classList.toggle('active');
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
