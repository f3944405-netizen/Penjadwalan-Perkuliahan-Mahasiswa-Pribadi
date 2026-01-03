<?php
require_once __DIR__ . '/../koneksi.php';

$sql = "INSERT INTO jadwal_kuliah
        (mata_kuliah, hari, jam_mulai, jam_selesai, id_dosen, id_ruang)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssii",
    $_POST['mata_kuliah'],
    $_POST['hari'],
    $_POST['jam_mulai'],
    $_POST['jam_selesai'],
    $_POST['id_dosen'],
    $_POST['id_ruang']
);

$stmt->execute();
header("Location: /view/dashboard/admin/jadwal/data_jadwal.php");
