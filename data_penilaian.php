<?php
session_start();
if (!isset($_SESSION['id_admin'])) {
    header("location:login.php");
    exit();
}

include 'koneksi.php';

$alternatif = mysqli_query($conn, "SELECT * FROM alternatif ORDER BY id_alternatif");
$kriteria = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id_kriteria");
$responden = mysqli_query($conn, "SELECT * FROM responden WHERE Keterangan = 'Konsisten' ORDER BY id_responden");

// Ambil nilai sebelumnya dari database untuk menampilkan di form
$nilai_tersimpan = [];
$q_nilai = mysqli_query($conn, "SELECT * FROM nilai_topsis_responden");
while ($n = mysqli_fetch_assoc($q_nilai)) {
    $nilai_tersimpan[$n['id_responden']][$n['id_alternatif']][$n['id_kriteria']] = $n['nilai'];
}

if (isset($_POST['simpan'])) {
    mysqli_query($conn, "DELETE FROM nilai_topsis_responden");
    mysqli_query($conn, "DELETE FROM nilai_topsis");

    foreach ($_POST['nilai'] as $id_responden => $alt_data) {
        foreach ($alt_data as $id_alternatif => $krit_data) {
            foreach ($krit_data as $id_kriteria => $nilai) {
                $id_r = intval($id_responden);
                $id_a = intval($id_alternatif);
                $id_k = intval($id_kriteria);
                $val = floatval($nilai);
                mysqli_query($conn, "INSERT INTO nilai_topsis_responden (id_responden, id_alternatif, id_kriteria, nilai) VALUES ($id_r, $id_a, $id_k, $val)");
            }
        }
    }

    // Hitung rata-rata untuk setiap alternatif dan kriteria
    $query_rata = mysqli_query($conn, "
        SELECT id_alternatif, id_kriteria, AVG(nilai) as rata 
        FROM nilai_topsis_responden 
        GROUP BY id_alternatif, id_kriteria
    ");

    while ($row = mysqli_fetch_assoc($query_rata)) {
        $id_k = $row['id_kriteria'];
        $id_a = $row['id_alternatif'];
        $rata = round($row['rata'], 3);
        mysqli_query($conn, "INSERT INTO nilai_topsis (id_kriteria, id_alternatif, nilai_akhir) VALUES ($id_k, $id_a, $rata)");
    }

    $_SESSION['pesan'] = "Data berhasil disimpan dan dihitung rata-ratanya.";
    header("Location: data_penilaian.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Matriks Keputusan topsis</title>
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

        .btn-green-light {
            background-color: rgb(103, 175, 106);
            color: #fff;
            border: none;
        }

        .btn-green-light:hover {
            background-color: #81c784;
            color: #fff;
        }

        input[type="number"] {
            max-width: 80px;
            margin: 0 auto;
        }
        .table th {
            color: #333;
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
<!-- Sidebar -->
<div class="sidebar-fixed">
    <?php include 'sidebar.php'; ?>
</div>

<!-- Konten Utama -->
<div class="main-wrapper">
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Page Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5><i class="fas fa-table"></i> Matriks Keputusan TOPSIS</h5>
        </div>

        <?php if (isset($_SESSION['pesan'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['pesan']; unset($_SESSION['pesan']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

<form method="POST">
    <div class="accordion" id="respondenAccordion">
        <?php
        $index = 0;
        mysqli_data_seek($responden, 0);
        while ($r = mysqli_fetch_assoc($responden)) {
            echo '
            <div class="accordion-item mb-3">
                <h2 class="accordion-header" id="heading'.$index.'">
                    <button class="accordion-button'.($index !== 0 ? ' collapsed' : '').'" type="button" data-bs-toggle="collapse" data-bs-target="#collapse'.$index.'" aria-expanded="'.($index === 0 ? 'true' : 'false').'" aria-controls="collapse'.$index.'">
                        Penilaian Responden: '.$r['nama_responden'].'
                    </button>
                </h2>
                <div id="collapse'.$index.'" class="accordion-collapse collapse'.($index === 0 ? ' show' : '').'" aria-labelledby="heading'.$index.'" data-bs-parent="#respondenAccordion">
                    <div class="accordion-body">
                        <div class="table-responsive">
                            <table class="table table-bordered text-center align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Alternatif</th>';
                                        mysqli_data_seek($kriteria, 0);
                                        while ($k = mysqli_fetch_assoc($kriteria)) {
                                            echo '<th>'.$k['nama_kriteria'].'</th>';
                                        }
            echo '                  </tr>
                                </thead>
                                <tbody>';
                                    mysqli_data_seek($alternatif, 0);
                                    while ($a = mysqli_fetch_assoc($alternatif)) {
                                        echo '<tr>';
                                        echo '<td>'.$a['nama_alternatif'].'</td>';
                                        mysqli_data_seek($kriteria, 0);
                                        while ($k = mysqli_fetch_assoc($kriteria)) {
                                            $nilai_default = isset($nilai_tersimpan[$r['id_responden']][$a['id_alternatif']][$k['id_kriteria']])
                                                ? $nilai_tersimpan[$r['id_responden']][$a['id_alternatif']][$k['id_kriteria']]
                                                : '';
                                            echo '<td>
                                                <input type="number" step="0.01" min="0" class="form-control"
                                                name="nilai['.$r['id_responden'].']['.$a['id_alternatif'].']['.$k['id_kriteria'].']"
                                                value="'.$nilai_default.'" required>
                                            </td>';
                                        }
                                        echo '</tr>';
                                    }
            echo '              </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>';
            $index++;
        }
        ?>
    </div>

    <div class="text-end mt-3">
        <button type="submit" name="simpan" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Simpan Semua Penilaian
        </button>
    </div>
</form>

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
