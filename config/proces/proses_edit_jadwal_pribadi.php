<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Proteksi akses
if (!isset($_SESSION['nim']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: /login.php");
    exit;
}

// CSRF protection (opsional tapi bagus)
// if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
//     die("Invalid CSRF token");
// }

// Validasi input
$required = ['id', 'hari', 'mata_kuliah', 'jam_mulai', 'jam_selesai', 'id_dosen', 'id_ruang'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        header("Location: ../../view/dashboard/mahasiswa/jadwal/jadwal_kuliah.php?status=error&msg=Field '$field' wajib diisi");
        exit;
    }
}

$nim = $_SESSION['nim'];

// Convert ke tipe yang benar
$id = (int) $_POST['id'];
$id_dosen = (int) $_POST['id_dosen'];
$id_ruang = (int) $_POST['id_ruang'];

$hari        = trim($_POST['hari']);
$mata_kuliah = trim($_POST['mata_kuliah']);
$jam_mulai   = trim($_POST['jam_mulai']);
$jam_selesai = trim($_POST['jam_selesai']);

// Validasi tambahan
if ($id <= 0) {
    header("Location: ../../view/dashboard/mahasiswa/jadwal/jadwal_kuliah.php?status=error&msg=ID tidak valid");
    exit;
}

// Prepare query
$sql = "UPDATE jadwal_pribadi SET
        hari = ?,
        mata_kuliah = ?,
        jam_mulai = ?,
        jam_selesai = ?,
        id_dosen = ?,
        id_ruang = ?
        WHERE id = ? AND nim_mahasiswa = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssiiis",
    $hari,
    $mata_kuliah,
    $jam_mulai,
    $jam_selesai,
    $id_dosen,
    $id_ruang,
    $id,
    $nim
);

$stmt->execute();

if ($stmt->affected_rows > 0) {
    $redirect = "../../view/dashboard/mahasiswa/jadwal/jadwal_kuliah.php?status=updated";
} else {
    $redirect = "../../view/dashboard/mahasiswa/jadwal/jadwal_kuliah.php?status=error&msg=Tidak ada data yang diubah (ID mungkin salah)";
}

$stmt->close();
$conn->close();

header("Location: " . $redirect);
exit;