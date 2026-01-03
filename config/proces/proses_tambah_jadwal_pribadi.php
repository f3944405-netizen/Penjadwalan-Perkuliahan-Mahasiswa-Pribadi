<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Helper: set notifikasi & redirect
function setNotifAndRedirect($type, $message, $url = null) {
    $_SESSION['notif'] = [
        'type' => $type,
        'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8')
    ];
    $redirect = $url ?? ($_SERVER['HTTP_REFERER'] ?? '../../view/dashboard/mahasiswa/jadwal/tambah_jadwal_pribadi.php');
    header("Location: " . $redirect);
    exit;
}

if (!isset($_SESSION['nim']) || $_SESSION['role'] !== 'mahasiswa') {
    setNotifAndRedirect('danger', '⚠️ Akses ditolak. Silakan login sebagai mahasiswa.');
}

$nim = (string)$_SESSION['nim'];

// Ambil & sanitasi input
$mataKuliah = trim($_POST['mata_kuliah'] ?? '');
$mataKuliahCustom = trim($_POST['mata_kuliah_custom'] ?? '');
$hari = trim($_POST['hari'] ?? '');
$jamMulai = trim($_POST['jam_mulai'] ?? '');
$jamSelesai = trim($_POST['jam_selesai'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');

// Tentukan mata kuliah akhir
$finalMataKuliah = ($mataKuliah === 'custom') ? $mataKuliahCustom : $mataKuliah;

// Validasi wajib
if (empty($finalMataKuliah)) {
    setNotifAndRedirect('warning', ' Nama mata kuliah wajib diisi.');
}
if (empty($hari)) {
    setNotifAndRedirect('warning', 'Hari wajib dipilih.');
}
if (empty($jamMulai) || empty($jamSelesai)) {
    setNotifAndRedirect('warning', 'Jam mulai dan jam selesai wajib diisi.');
}

// Validasi waktu
$timeMulai = strtotime($jamMulai);
$timeSelesai = strtotime($jamSelesai);
if ($timeSelesai <= $timeMulai) {
    setNotifAndRedirect('warning', 'Jam selesai harus lebih besar dari jam mulai.');
}

// 🔍 Cek bentrok dengan jadwal pribadi mahasiswa
$stmtCheck = $conn->prepare("
    SELECT mata_kuliah, jam_mulai, jam_selesai FROM jadwal_pribadi
    WHERE nim_mahasiswa = ? AND hari = ?
      AND NOT (jam_selesai <= ? OR jam_mulai >= ?)
");
// Kondisi bentrok: dua interval [A,B] dan [C,D] bentrok jika NOT (B ≤ C OR A ≥ D)
if (!$stmtCheck) {
    setNotifAndRedirect('danger', 'Gagal menyiapkan pengecekan bentrok: ' . $conn->error);
}

$stmtCheck->bind_param("ssss", $nim, $hari, $jamMulai, $jamSelesai);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows > 0) {
    $bentrok = $resultCheck->fetch_assoc();
    $msg = "Jadwal bentrok dengan: <strong>" . htmlspecialchars($bentrok['mata_kuliah']) . "</strong> ";
    $msg .= "(" . htmlspecialchars($bentrok['jam_mulai']) . "–" . htmlspecialchars($bentrok['jam_selesai']) . ")";
    setNotifAndRedirect('danger', $msg);
}

// Simpan ke database
$stmtInsert = $conn->prepare("
    INSERT INTO jadwal_pribadi (nim_mahasiswa, mata_kuliah, hari, jam_mulai, jam_selesai, keterangan)
    VALUES (?, ?, ?, ?, ?, ?)
");
if (!$stmtInsert) {
    setNotifAndRedirect('danger', ' Gagal menyiapkan query penyimpanan.');
}

$stmtInsert->bind_param("ssssss", $nim, $finalMataKuliah, $hari, $jamMulai, $jamSelesai, $keterangan);

if ($stmtInsert->execute()) {
    setNotifAndRedirect(
        'success',
        'Jadwal pribadi berhasil ditambahkan.',
        '../../view/dashboard/mahasiswa/jadwal/jadwal_kuliah.php'
    );
} else {
    // Hindari tampilkan error SQL mentah ke user (risiko keamanan)
    error_log("Gagal insert jadwal pribadi: " . $stmtInsert->error);
    setNotifAndRedirect('danger', ' Gagal menyimpan jadwal. Silakan coba lagi.');
}

$stmtCheck->close();
$stmtInsert->close();
$conn->close();
?>