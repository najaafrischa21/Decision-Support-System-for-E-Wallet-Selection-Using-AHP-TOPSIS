<?php
include "koneksi.php"; // Pastikan ini berada di folder yang sama

if (isset($_GET['id_alternatif'])) {
  $id = mysqli_real_escape_string($conn, $_GET['id_alternatif']);
  $sql = "DELETE FROM alternatif WHERE id_alternatif = '$id'";
  if (mysqli_query($conn, $sql)) {
    header("Location: alternatif.php");
    exit();
  } else {
    echo "Gagal menghapus data: " . mysqli_error($conn);
  }
} else {
  echo "ID tidak ditemukan.";
}
