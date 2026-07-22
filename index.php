<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
  header("location:login.php");
  exit();
}

$halaman = isset($_GET['halaman']) ? $_GET['halaman'] : 'home';

// Ambil nama admin dari session
$nama_admin = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Data Perbandingan Kriteria AHP</title>
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
    
        
    .main-content,
    .main-content p,
    .main-content h1,
    .main-content h2,
    .main-content h3,
    .main-content h4,
    .main-content h5,
    .main-content h6,
    .main-content ol,
    .main-content ul,
    .main-content li,
    .main-content strong {
      color: #333 !important;
    }

    .main-content table {
      color: #333;
    }

    .blue-header {
      background-color: #4B64C7 !important; 
      color: #fff !important; 
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
<div class="sidebar-fixed">
  <?php include 'sidebar.php'; ?>
</div>

<div class="main-wrapper">
  <?php include 'navbar.php'; ?>

  <div class="main-content p-4">
    <h5 class="mb-4">Selamat Datang, <?= htmlspecialchars($nama_admin); ?>!</h5>
    <p>Ini adalah dashboard untuk mengelola sistem pemilihan e-wallet terbaik menggunakan metode AHP-TOPSIS.</p>

    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Petunjuk Pengisian</h5>
        <ol>
          <li>
            Input jawaban perbandingan kriteria responden pada menu <strong>Data Responden</strong>, kemudian klik <strong>Tambah Responden</strong> dan <strong>Cek Konsistensi</strong>. 
            Lakukan untuk seluruh responden kemudian hitung bobot dengan klik <strong>bobot aggregate</strong>.
          </li>
          <li>
            Input jawaban penilaian alternatif dari jawaban responden kemudian masukkan jawaban tersebut ke menu <strong>Data Penilaian</strong> kemudian klik <strong>Simpan Semua Penilaian</strong>.Berikut ini adalah tabel konversi untuk kriteria keamanan:
          </li>
        </ol>

		<div class="d-flex justify-content-center">
		  <div class="col-md-6">
			<strong>Keamanan</strong>
			<table class="table table-bordered table-striped">
			  <thead class="text-center">
				<tr>
				  <th class="blue-header">Keamanan</th>
				  <th class="blue-header">Nilai</th>
				</tr>
			  </thead>
			  <tbody class="text-center">
				<tr><td>Tidak diawasi BI atau OJK</td><td>1</td></tr>
				<tr><td>Diawasi oleh satu lembaga (BI atau OJK)</td><td>2</td></tr>
				<tr><td>Diawasi oleh dua lembaga (BI dan OJK)</td><td>3</td></tr>
			  </tbody>
			</table>
		  </div>
		</div>

        <ol start="3">
          <li>
            Lihat hasil alternatif terbaik pada menu <strong>Data Hasil Akhir</strong>.
          </li>
        </ol>

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
