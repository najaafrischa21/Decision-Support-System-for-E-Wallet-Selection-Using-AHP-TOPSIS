<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    $id_responden = intval($_GET['id']);

    // Mulai transaksi (opsional tapi direkomendasikan)
    $conn->begin_transaction();

    try {
        // 1. Hapus dari perbandingan_kriteria
        $query1 = "DELETE FROM perbandingan_kriteria WHERE id_responden = ?";
        $stmt1 = $conn->prepare($query1);
        $stmt1->bind_param("i", $id_responden);
        $stmt1->execute();

        // 2. Hapus dari responden
        $query2 = "DELETE FROM responden WHERE id_responden = ?";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param("i", $id_responden);
        $stmt2->execute();

        // Commit transaksi
        $conn->commit();

        // Redirect kembali
        header("Location: tampil_responden.php");
        exit();
    } catch (Exception $e) {
        // Rollback jika gagal
        $conn->rollback();
        echo "Gagal menghapus data. Error: " . $e->getMessage();
    }
} else {
    echo "ID tidak ditemukan.";
}
?>
