<?php
function getDbConnection() {
    $host = 'localhost'; // Ganti dengan host database Anda
    $db = 'penilaian'; // Ganti dengan nama database Anda
    $user = 'postgres'; // Ganti dengan username Anda
    $pass = 'root'; // Ganti dengan password Anda
    $charset = 'utf8';

    $dsn = "pgsql:host=$host;dbname=$db;options='--client_encoding=$charset'";
    try {
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Mengaktifkan mode error
        return $pdo;
    } catch (PDOException $e) {
        die("Koneksi Gagal: " . $e->getMessage());
    }
}
?>