<?php
$currentFile = basename($_SERVER['PHP_SELF']);

function isActive($filename)
{
  global $currentFile;
  return $currentFile === $filename ? 'active' : '';
}
?>

<div class="sidebar bg-white border-end vh-100 p-3" style="width: 260px;">
  <h4 class="text-primary text-center fw-bold mb-4">SPK AHP-TOPSIS</h4>

  <a href="index.php" class="d-block py-2 px-3 mb-1 <?= isActive('index.php'); ?>">
    <i class="fas fa-home me-2"></i>Dashboard
  </a>

  <a href="kriteria.php" class="d-block py-2 px-3 mb-1 <?= isActive('kriteria.php'); ?>">
    <i class="fas fa-sliders-h me-2"></i>Data Kriteria
  </a>
  <a href="alternatif.php" class="d-block py-2 px-3 mb-1 <?= isActive('alternatif.php'); ?>">
    <i class="fas fa-database me-2"></i>Data Alternatif
  </a>
  <a href="tampil_responden.php" class="d-block py-2 px-3 mb-1 <?= isActive('tampil_responden.php'); ?>">
    <i class="fas fa-user me-2"></i>Data Responden
  </a>

  <a href="data_penilaian.php" class="d-block py-2 px-3 mb-1 <?= isActive('data_penilaian.php'); ?>">
    <i class="fas fa-edit me-2"></i>Data Penilaian
  </a>

  <a href="data_hasil_topsis.php" class="d-block py-2 px-3 mb-1 <?= isActive('data_hasil_topsis.php'); ?>">
    <i class="fas fa-star me-2"></i>Data Hasil Akhir
  </a>

  <a href="logout.php" class="d-block py-2 px-3 text-danger">
    <i class="fas fa-sign-out-alt me-2"></i>Logout
  </a>
</div>
