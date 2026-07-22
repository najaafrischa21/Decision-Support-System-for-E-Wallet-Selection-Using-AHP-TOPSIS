<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
  header("location:login.php");
  exit();
}
include 'koneksi.php';

// Validasi input
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (
    isset($_POST['id_alternatif'], $_POST['kode'], $_POST['nama']) &&
    is_numeric($_POST['id_alternatif']) &&
    !empty(trim($_POST['kode'])) &&
    !empty(trim($_POST['nama']))
  ) {
    $id = (int) $_POST['id_alternatif'];
    $kode = mysqli_real_escape_string($conn, trim($_POST['kode']));
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama']));

    // Update data
    $sql = "UPDATE alternatif SET kode_alternatif = '$kode', nama_alternatif = '$nama' WHERE id_alternatif = $id";
    if (mysqli_query($conn, $sql)) {
      header("Location: alternatif.php?pesan=sukses_update");
      exit();
    } else {
      echo "Gagal memperbarui data: " . mysqli_error($conn);
    }
  } else {
    echo "Input tidak valid.";
  }
} else {
  echo "Metode tidak diizinkan.";
}
?>
