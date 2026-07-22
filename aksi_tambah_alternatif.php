<?php
include 'koneksi.php';

$kode = $_POST['kode'];
$nama = $_POST['nama'];

$query = "INSERT INTO alternatif (kode_alternatif, nama_alternatif) VALUES ('$kode', '$nama')";
mysqli_query($conn, $query);

header("location:alternatif.php");
exit();
