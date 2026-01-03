<?php
session_start();
require_once __DIR__ . '/../koneksi.php'; 

if ($_SESSION['role'] !== 'admin') {
    exit('Akses ditolak');
}

$nim = trim($_POST['nim']);
$nama = trim($_POST['nama']);
$jurusan_id = $_POST['jurusan_id'];

$password = password_hash($nim, PASSWORD_DEFAULT);
$role = 'mahasiswa';

$sql = "INSERT INTO users (nim, nama, jurusan_id, password, role)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssiss", $nim, $nama, $jurusan_id, $password, $role);

if ($stmt->execute()) {
    header("Location: ../../view/dashboard/admin/admin_dashboard.php?success=1");
} else {
    header("Location: ../../view/dashboard/admin/admin_dashboard.php?error=1");
}
