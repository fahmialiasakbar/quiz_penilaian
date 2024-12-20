<?php include 'penilaian.php'; ?>
<!-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <title>Penilaian Kelas</title>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Penilaian Kelas</h1>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Nama</th>
            <th>Komposisi</th>
            <th>Rata-rata</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($hasil as $nama => $detail): ?>
            <tr>
                <td><?= $nama ?></td>
                <td><?= implode(', ', $detail['komposisi']); ?></td>
                <td><?= number_format($detail['rata-rata'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html> -->
