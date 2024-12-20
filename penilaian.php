<?php
require 'db.php';

$pdo = getDbConnection();

// Ambil data peserta dan komposisi nilai
$idKelas = 1; // ID kelas yang akan dinilai
$query = $pdo->prepare("SELECT namamk FROM kelas WHERE id = ?");
$query->execute([$idKelas]);
$kelas = $query->fetch();

$queryPeserta = $pdo->prepare("SELECT id, nama FROM peserta");
$queryPeserta->execute();
$mahasiswa = $queryPeserta->fetchAll();

$queryKomposisi = $pdo->prepare("SELECT id, value FROM komposisi_nilai");
$queryKomposisi->execute();
$komposisi = $queryKomposisi->fetchAll();

// echo "<pre>";
// print_r($_POST);
// die;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['nilai'] as $idPeserta => $nilaiKomponen) {
        foreach ($nilaiKomponen as $idKomposisi => $nilai) {
            if ($nilai !== null && $nilai !== '') {
                // Cek apakah data sudah ada
                $queryCheck = $pdo->prepare(
                    "SELECT id FROM penilaian 
                     WHERE idpeserta = ? AND idkomposisi = ? AND idkelas = ?"
                );
                $queryCheck->execute([$idPeserta, $idKomposisi, $idKelas]);
                $existingRecord = $queryCheck->fetch();

                if ($existingRecord) {
                    // Jika ada, update nilai
                    $queryUpdate = $pdo->prepare(
                        "UPDATE penilaian 
                         SET nilai = ? 
                         WHERE idpeserta = ? AND idkomposisi = ? AND idkelas = ?"
                    );
                    $queryUpdate->execute([$nilai, $idPeserta, $idKomposisi, $idKelas]);
                } else {
                    // Jika tidak ada, insert data baru
                    $queryInsert = $pdo->prepare(
                        "INSERT INTO penilaian (idpeserta, idkomposisi, idkelas, nilai) 
                         VALUES (?, ?, ?, ?)"
                    );
                    $queryInsert->execute([$idPeserta, $idKomposisi, $idKelas, $nilai]);
                }
                $id = $existingRecord ? $existingRecord['id'] : $pdo->lastInsertId();

                // insert into log
                $queryLog = $pdo->prepare(
                    "INSERT INTO log_nilai (idpenilaian) 
                     VALUES (?)"
                );
                $queryLog->execute([$id]);

            }
        }
    }
    echo "<div class='alert alert-success'>Nilai berhasil disimpan!</div>";
}


// Ambil nilai yang sudah disimpan
$queryNilai = $pdo->prepare(
    "SELECT idpeserta, idkomposisi, nilai 
     FROM penilaian 
     WHERE idkelas = ?"
);
$queryNilai->execute([$idKelas]);
$nilaiTersimpan = $queryNilai->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);


$komposisi = array_column($komposisi, 'value', 'id');
foreach ($nilaiTersimpan as $idPeserta => $nilai) {
    $nilaiTersimpan[$idPeserta] = array_column($nilai, 'nilai', 'idkomposisi');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian Kelas</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Penilaian Kelas: <?= $kelas['namamk'] ?></h1>
    <form method="POST" action="">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama</th>
                    <?php foreach ($komposisi as $k): ?>
                        <th><?= $k ?></th>
                    <?php endforeach; ?>
                    <th>Rata-rata</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mahasiswa as $m): ?>
                    <tr>
                        <td width="400"><?= $m['nama'] ?></td>
                        <?php
                        $totalNilai = 0;
                        $jumlahKomponen = 0;
                        ?>
                        <?php foreach ($komposisi as $idx => $k): ?>
                            <?php
                            $nilai = $nilaiTersimpan[$m['id']][$idx] ?? '';
                            if ($nilai !== '') {
                                $totalNilai += $nilai;
                                $jumlahKomponen++;
                            }
                            ?>
                            <td>
                                <input 
                                    type="number" 
                                    name="nilai[<?= $m['id'] ?>][<?= $idx ?>]"
                                    class="form-control nilai-input" 
                                    value="<?= $nilai ?>" 
                                    min="0" max="100" >
                            </td>
                        <?php endforeach; ?>
                        <td>
                            <input type="text" class="form-control rata-rata" value="<?= $jumlahKomponen ? round($totalNilai / $jumlahKomponen, 2) : '' ?>" readonly>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">Simpan Nilai</button>
    </form>
</div>

<!-- Bootstrap JS and Popper.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<script>
    // Script untuk menghitung rata-rata
    document.querySelectorAll('.nilai-input').forEach(input => {
        input.addEventListener('input', () => {
            const row = input.closest('tr');
            const nilaiInputs = row.querySelectorAll('.nilai-input');
            let total = 0, count = 0;

            nilaiInputs.forEach(n => {
                if (n.value) {
                    total += parseFloat(n.value);
                    count++;
                }
            });

            const rataRataInput = row.querySelector('.rata-rata');
            rataRataInput.value = count > 0 ? (total / count).toFixed(2) : '';
        });
    });
</script>
</body>
</html>
