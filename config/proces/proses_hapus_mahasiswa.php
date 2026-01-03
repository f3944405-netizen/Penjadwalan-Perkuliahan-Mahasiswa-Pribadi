<?php
session_start();
require_once __DIR__ . '/../koneksi.php';


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['notif'] = ['type' => 'danger', 'message' => 'Akses ditolak.'];
    header("Location: ../../view/dashboard/admin/mahasiswa/kelola_mahasiswa.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['notif'] = ['type' => 'warning', 'message' => 'ID tidak valid.'];
    header("Location: ../../view/dashboard/admin/mahasiswa/kelola_mahasiswa.php");
    exit;
}

$id = (int)$_GET['id'];

// 1. Ambil NIM dari tabel users
$stmtGetNim = $conn->prepare("SELECT nim FROM users WHERE id = ? AND role = 'mahasiswa'");
$stmtGetNim->bind_param("i", $id);
$stmtGetNim->execute();
$result = $stmtGetNim->get_result();

if ($result->num_rows === 0) {
    $_SESSION['notif'] = ['type' => 'warning', 'message' => 'Mahasiswa tidak ditemukan.'];
    header("Location: ../../view/dashboard/admin/mahasiswa/kelola_mahasiswa.php");
    exit;
}

$nim = $result->fetch_assoc()['nim'];


$error = false;

$stmt1 = $conn->prepare("DELETE FROM jadwal_pribadi WHERE nim_mahasiswa = ?");
if ($stmt1) {
    $stmt1->bind_param("s", $nim);
    $stmt1->execute();
    $stmt1->close();
} else {
    error_log("Gagal prepare hapus jadwal_pribadi: " . $conn->error);
    $error = true;
}



$stmt3 = $conn->prepare("DELETE FROM users WHERE id = ?");
if ($stmt3) {
    $stmt3->bind_param("i", $id);
    $stmt3->execute();
    $stmt3->close();
} else {
    error_log("Gagal hapus user: " . $conn->error);
    $error = true;
}


if (!$error) {
    $_SESSION['notif'] = ['type' => 'success', 'message' => 'Mahasiswa dan data terkait berhasil dihapus.'];
} else {
    $_SESSION['notif'] = ['type' => 'warning', 'message' => 'Mahasiswa dihapus, tapi ada data terkait yang gagal dihapus.'];
}

header("Location: ../../view/dashboard/admin/mahasiswa/kelola_mahasiswa.php");
exit;
?>