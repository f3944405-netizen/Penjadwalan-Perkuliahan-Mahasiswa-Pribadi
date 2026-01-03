<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

if ($_SESSION['role'] !== 'admin') {
    exit('Akses ditolak');
}

$id         = $_POST['id'];
$nim        = $_POST['nim'];
$nama       = $_POST['nama'];
$jurusan_id = $_POST['jurusan_id'];

$sql = "
    UPDATE users 
    SET nim = ?, nama = ?, jurusan_id = ?, updated_at = NOW()
    WHERE id = ? AND role = 'mahasiswa'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $nim, $nama, $jurusan_id, $id);
$stmt->execute();

header("Location: ../../view/dashboard/admin/mahasiswa/kelola_mahasiswa.php?edit=success");
