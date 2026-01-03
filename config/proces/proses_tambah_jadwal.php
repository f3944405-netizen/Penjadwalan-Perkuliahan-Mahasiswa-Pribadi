<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Validasi login
if (!isset($_SESSION['nim']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: /login.php");
    exit;
}

$nim = $_SESSION['nim'];

// Ambil data form
$mata_kuliah = $_POST['mata_kuliah'] ?? '';
$hari        = $_POST['hari'] ?? '';
$waktu       = $_POST['waktu'] ?? '';
$id_dosen = $_POST['id_dosen'] ?? '';
$id_ruang = $_POST['id_ruang'] ?? '';

// Validasi
if (!$mata_kuliah || !$hari || !$waktu || !$id_dosen || !$id_ruang) {
    die("Data tidak lengkap");
}

// Pastikan format TIME
$waktu = date("H:i:s", strtotime($waktu));

$sql = "INSERT INTO jadwal_kuliah
        (nim_mahasiswa, mata_kuliah, hari, waktu, id_dosen, id_ruang)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssii",
    $nim,
    $mata_kuliah,
    $hari,
    $waktu,
    $id_dosen,
    $id_ruang
);

if ($stmt->execute()) {
    header("Location: ../../view/dashboard/mahasiswa/jadwal/jadwal_kuliah.php?success=1");
    exit;
} else {
    echo "Gagal menyimpan jadwal";
}

$stmt->close();
$conn->close();
