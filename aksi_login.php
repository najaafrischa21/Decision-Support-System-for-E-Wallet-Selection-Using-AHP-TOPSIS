<?php
include "koneksi.php";
$username = $_POST['username'];
$password = $_POST['password'];

$hasil = mysqli_query($conn, "SELECT * FROM admin WHERE username = '$username' AND password = '$password'");
$row = mysqli_fetch_array($hasil);

if ($row['id_admin'] != "") {
  session_start();
  $_SESSION['id_admin'] = $row['id_admin'];
  $_SESSION['nama'] = $row['nama'];
  header("Location: index.php");
} else {
  header("Location: login.php?pesan=gagal_login");
}
