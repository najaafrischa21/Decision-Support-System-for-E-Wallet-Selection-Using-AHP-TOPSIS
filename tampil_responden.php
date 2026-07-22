<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
    header("location:login.php");
    exit();
}

include 'koneksi.php';

// Cek jika tombol tambah responden ditekan
if (isset($_POST['tambah_responden'])) {
    header("Location: tambah_perbandingan.php");
    exit;
}?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Data Responden - SPK AHP-TOPSIS</title>

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
		   z-index: 1050; /* ✅ Tambahkan ini */
		}
	  .flex-grow-1 {
		margin-left: 250px;
	  }

	  .table td {
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

	.table td{
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

  .flex-grow-1 {
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

  <div class="d-flex">
    <!-- Sidebar -->
	<div class="sidebar-fixed">
		<?php include 'sidebar.php'; ?>
	</div>

    <!-- Main Content -->
    <div class="flex-grow-1">
      <!-- Navbar -->
      <?php include 'navbar.php'; ?>

      <div class="container-fluid py-4 px-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4> <i class="fas fa-list"></i> Data Responden</h4>
        </div>
        <div class="d-flex justify-content-end mb-4 gap-2">
            <form method="POST">
                <button type="submit" name="tambah_responden" class="btn btn-success">
                    <i class="fas fa-plus-circle me-1"></i> Tambah 
                </button>
            </form>

            <a href="bobot_kriteria_ahp.php" class="btn btn-green-light">
                <i class="fas fa-percentage me-1"></i> Bobot Aggregate
            </a>
        </div>
		
		
        <!-- Card Wrapper for Table -->
        <div class="card">
          <div class="card-body">
            <h6 class="mb-3"><i class="fas fa-table"></i> Daftar Data Responden</h6>
            <div class="table-responsive">
              <table class="table table-bordered table-striped">
				<thead class="table-primary text-center">
				  <tr>
					<th>No</th>
					<th>Nama Responden</th>
					<th>Keterangan</th> <!-- ✅ Tambahkan ini -->
					<th>Aksi</th>
				  </tr>
				</thead>
				<tbody class="text-center">
				<?php
				$result = mysqli_query($conn, "
					SELECT r.*, pv.nilai_cr 
					FROM responden r 
					LEFT JOIN priority_vector pv ON r.id_pv = pv.id
					GROUP BY r.id_responden
				");

				$no = 1;
				while ($row = mysqli_fetch_assoc($result)) {
					$cr = isset($row['nilai_cr']) ? floatval($row['nilai_cr']) : null;
					$keterangan = "Belum Cek Konsistensi";
					if ($cr !== null) {
						$keterangan = $cr < 0.1 ? "Konsisten" : "Tidak Konsisten";
					}

					// Simpan atau update keterangan ke database
					$id_responden = intval($row['id_responden']);
					$keterangan_escaped = $conn->real_escape_string($keterangan);
					$update_sql = "UPDATE responden SET keterangan = '$keterangan_escaped' WHERE id_responden = $id_responden";
					$conn->query($update_sql);

					echo "<tr>
							<td>{$no}</td>
							<td>" . htmlspecialchars($row['nama_responden']) . "</td>
							<td>{$keterangan}</td>
							<td>
								<a href='detail_perbandingan.php?id={$row['id_responden']}' class='btn btn-primary me-2'>
									<i class='fas fa-eye'></i> Detail
								</a>
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
    </div>
  </div>
<script>
  function toggleSidebar() {
    document.querySelector('.sidebar-fixed').classList.toggle('active');
  }
</script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
