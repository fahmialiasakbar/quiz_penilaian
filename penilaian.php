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
                                    data-id-peserta="<?= $m['id'] ?>" 
                                    data-id-komposisi="<?= $idx ?>" 
                                    value="<?= $nilai ?>" 
                                    min="0" max="100">
                            </td>
                        <?php endforeach; ?>
                        <td>
                            <input type="text" class="form-control rata-rata" 
                                   value="<?= $jumlahKomponen ? round($totalNilai / $jumlahKomponen, 2) : '' ?>" 
                                   readonly>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>
</div>

<!-- Bootstrap JS and Popper.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<script>
    const idKelas = <?= $idKelas ?>;

    document.querySelectorAll('.nilai-input').forEach(input => {
        input.addEventListener('keypress', function (event) {
            if (event.key === 'Enter') {
                console.log("Enter pressed");

                const row = this.closest('tr');
                const inputsInRow = Array.from(row.querySelectorAll('.nilai-input'));
                const index = inputsInRow.indexOf(this);
                const nextInput = inputsInRow[index + 1];

                if (nextInput) {
                    nextInput.focus();
                } else {
                    const nextRow = row.nextElementSibling;
                    if (nextRow) {
                        const nextRowInput = nextRow.querySelector('.nilai-input');
                        if (nextRowInput) {
                            nextRowInput.focus();
                        }
                    }
                }
            }
        });
    });

    document.querySelectorAll('.nilai-input').forEach(input => {
        let originalValue = input.value;
        input.addEventListener('focusout', function () {
            if (input.value !== originalValue) {
                saveNilai(input);
            }
        });
    });

    function saveNilai(input) {
        const idPeserta = input.getAttribute('data-id-peserta');
        const idKomposisi = input.getAttribute('data-id-komposisi');
        const nilai = input.value;

        const formData = new URLSearchParams();
        formData.append('idPeserta', idPeserta);
        formData.append('idKomposisi', idKomposisi);
        formData.append('idKelas', idKelas);
        formData.append('nilai', nilai);


        if (nilai !== '') {
            fetch('save_nilai.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString(),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const notif = document.createElement('div');
                    const nama = document.querySelector(`input[data-id-peserta="${idPeserta}"]`).closest('tr').querySelector('td').textContent;
                    const tdIndex = Array.from(input.closest('tr').querySelectorAll('td')).indexOf(input.closest('td'));
                    const komposisi = document.querySelector(`table thead tr`).querySelectorAll('th')[tdIndex].textContent;

                    notif.classList.add('alert', 'alert-success', 'position-absolute', 'end-0', 'top-0', 'mt-5', 'me-5');
                    notif.textContent = `${nama} - ${komposisi} : ${nilai} berhasil disimpan.`;
                    document.body.appendChild(notif);

                    setTimeout(() => {
                        notif.remove();
                    }, 1000);

                } else {
                    console.error(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }
    
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
