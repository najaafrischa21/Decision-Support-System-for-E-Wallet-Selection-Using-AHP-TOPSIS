<?php
include "koneksi.php"; // Pastikan ini berada di folder yang sama

if (isset($_GET['id'])) {
  $id = mysqli_real_escape_string($conn, $_GET['id']);
  $sql = "DELETE FROM kriteria WHERE id_kriteria = '$id'";
  if (mysqli_query($conn, $sql)) {
    header("Location: kriteria.php");
    exit();
  } else {
    echo "Gagal menghapus data: " . mysqli_error($conn);
  }
} else {
  echo "ID tidak ditemukan.";
}
