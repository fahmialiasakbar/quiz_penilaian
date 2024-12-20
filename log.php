<?php
require 'db.php';

$pdo = getDbConnection();

// Ambil data log nilai dengan informasi detail
$queryLog = $pdo->prepare(
    "SELECT 
        log_nilai.id AS log_id,
        log_nilai.waktu AS waktu_perubahan,
        penilaian.idpeserta,
        peserta.nama AS nama_peserta,
        penilaian.idkomposisi,
        komposisi_nilai.value AS nama_komposisi,
        penilaian.idkelas,
        kelas.namamk AS nama_kelas,
        penilaian.nilai
     FROM log_nilai
     JOIN penilaian ON log_nilai.idpenilaian = penilaian.id
     JOIN peserta ON penilaian.idpeserta = peserta.id
     JOIN komposisi_nilai ON penilaian.idkomposisi = komposisi_nilai.id
     JOIN kelas ON penilaian.idkelas = kelas.id
     ORDER BY log_nilai.waktu DESC"
);
$queryLog->execute();
$logs = $queryLog->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Nilai</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Log Nilai</h1>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Waktu Perubahan</th>
                <th>Peserta</th>
                <th>Komposisi Nilai</th>
                <th>Kelas</th>
                <th>Nilai</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($logs) > 0): ?>
                <?php foreach ($logs as $index => $log): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= $log['waktu_perubahan'] ?></td>
                        <td><?= $log['nama_peserta'] ?></td>
                        <td><?= $log['nama_komposisi'] ?></td>
                        <td><?= $log['nama_kelas'] ?></td>
                        <td><?= $log['nilai'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">Tidak ada log nilai</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS and Popper.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
