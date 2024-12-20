<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getDbConnection();

    // echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.', 'data' => $_POST]);

    // Ambil data dari request
    $idPeserta = $_POST['idPeserta'] ?? null;
    $idKomposisi = $_POST['idKomposisi'] ?? null;
    $idKelas = $_POST['idKelas'] ?? null;
    $nilai = $_POST['nilai'] ?? null;

    if ($idPeserta && $idKomposisi && $idKelas && ($nilai !== null)) {
        try {
            // Cek apakah data sudah ada
            $queryCheck = $pdo->prepare(
                "SELECT id, nilai FROM penilaian 
                 WHERE idpeserta = ? AND idkomposisi = ? AND idkelas = ?"
            );
            $queryCheck->execute([$idPeserta, $idKomposisi, $idKelas]);
            $existingRecord = $queryCheck->fetch();

            $log = true;
            if ($existingRecord) {
                // check value
                if ($existingRecord['nilai'] == $nilai) {
                    $log = false;
                } else {
                    // Jika ada, update nilai
                    $queryUpdate = $pdo->prepare(
                        "UPDATE penilaian 
                        SET nilai = ? 
                        WHERE idpeserta = ? AND idkomposisi = ? AND idkelas = ?"
                    );
                    $queryUpdate->execute([$nilai, $idPeserta, $idKomposisi, $idKelas]);
                    $id = $existingRecord['id'];
                }
            } else {
                // Jika tidak ada, insert data baru
                $queryInsert = $pdo->prepare(
                    "INSERT INTO penilaian (idpeserta, idkomposisi, idkelas, nilai) 
                     VALUES (?, ?, ?, ?)"
                );
                $queryInsert->execute([$idPeserta, $idKomposisi, $idKelas, $nilai]);
                $id = $pdo->lastInsertId();
            }

            // Simpan log perubahan
            if ($log) {
                $queryLog = $pdo->prepare("INSERT INTO log_nilai (idpenilaian) VALUES (?)");
                $queryLog->execute([$id]);
            }
           
            echo json_encode(['success' => true, 'message' => 'Nilai berhasil disimpan.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan nilai: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.', 'data' => $_POST]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode tidak valid.']);
}
