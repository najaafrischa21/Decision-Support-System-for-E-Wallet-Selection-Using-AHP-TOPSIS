<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
  header("location:login.php");
  exit();
}
include 'koneksi.php';

$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM kriteria WHERE id_kriteria=$id");
$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Kriteria</title>
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

      <!-- Form Content -->
		<!-- Ganti bagian ini -->
		<div class="container form-container">
		  <section class="bg-white border rounded p-4 shadow-sm">
			<div class="mb-4 border-bottom pb-2">
			  <h5 class="text-primary"><i class="fas fa-edit me-2"></i>Edit Kriteria</h5>
			</div>
			<form action="aksi_edit_kriteria.php" method="POST">
				<input type="hidden" name="id" value="<?= $data['id_kriteria'] ?>">
			  <div class="mb-3">
				  <label class="form-label">Kode Kriteria</label>
				  <input type="text" name="kode" class="form-control" value="<?= $data['kode_kriteria'] ?>" required>
			  </div>
			  <div class="mb-3">
				  <label class="form-label">Nama Kriteria</label>
				  <input type="text" name="nama" class="form-control" value="<?= $data['nama_kriteria'] ?>" required>
			  </div>
			  <div class="mb-3">
				  <label class="form-label">Jenis</label>
				  <select name="jenis" class="form-select" required>
					<option value="Benefit" <?= $data['jenis'] == 'Benefit' ? 'selected' : '' ?>>Benefit</option>
					<option value="Cost" <?= $data['jenis'] == 'Cost' ? 'selected' : '' ?>>Cost</option>
				  </select>
			  </div>
			  <div class="d-flex justify-content-end">
				<button type="submit" class="btn btn-primary ms-2">Update</button>
			  </div>
			</form>
		  </section>
		</div>

    </div> <!-- end flex-grow -->
  </div> <!-- end d-flex -->
<script>
  function toggleSidebar() {
    document.querySelector('.sidebar-fixed').classList.toggle('active');
  }
</script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
