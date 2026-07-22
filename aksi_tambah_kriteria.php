<?php
include 'koneksi.php';

$kode = $_POST['kode'];
$nama = $_POST['nama'];
$jenis = $_POST['jenis'];

$query = "INSERT INTO kriteria (kode_kriteria, nama_kriteria, jenis) VALUES ('$kode', '$nama', '$jenis')";
mysqli_query($conn, $query);

header("location:kriteria.php");
exit();
