<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['notif'] = ['type' => 'danger', 'message' => 'Akses ditolak.'];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

function redirectBack($type, $msg, $id = null) {
    $_SESSION['notif'] = ['type' => $type, 'message' => $msg];
    $url = $id 
        ? "/view/dashboard/admin/jadwal/edit_jadwal.php?id=" . (int)$id 
        : $_SERVER['HTTP_REFERER'];
    header("Location: " . $url);
    exit;
}

// Ambil data
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$mata_kuliah = trim($_POST['mata_kuliah'] ?? '');
$hari = trim($_POST['hari'] ?? '');
$jam_mulai = trim($_POST['jam_mulai'] ?? '');
$jam_selesai = trim($_POST['jam_selesai'] ?? '');
$id_dosen = $_POST['id_dosen'] !== '' ? (int)$_POST['id_dosen'] : null;
$id_ruang = $_POST['id_ruang'] !== '' ? (int)$_POST['id_ruang'] : null;

// Validasi
if (!$id) {
    redirectBack('danger', 'ID javariabeldwal tidak valid.');
}
if (empty($mata_kuliah) || empty($hari) || empty($jam_mulai) || empty($jam_selesai)) {
    redirectBack('warning', 'Semua field bertanda * wajib diisi.', $id);
}
if ($jam_selesai <= $jam_mulai) {
    redirectBack('warning', 'Jam selesai harus lebih besar dari jam mulai.', $id);
}

// 🔍 Cek bentrok (kecuali dengan diri sendiri)
$stmtCheck = $conn->prepare("
    SELECT mata_kuliah, jam_mulai, jam_selesai 
    FROM jadwal_kuliah
    WHERE id != ? AND hari = ? AND id_ruang = ? AND id_ruang IS NOT NULL
      AND NOT (jam_selesai <= ? OR jam_mulai >= ?)
");
$stmtCheck->bind_param("issss", $id, $hari, $id_ruang, $jam_mulai, $jam_selesai);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows > 0) {
    $bentrok = $resultCheck->fetch_assoc();
    $msg = "Bentrok dengan jadwal: <strong>" . htmlspecialchars($bentrok['mata_kuliah']) . "</strong> ";
    $msg .= "(" . $bentrok['jam_mulai'] . "–" . $bentrok['jam_selesai'] . ")";
    redirectBack('danger', $msg, $id);
}

// Siapkan variabel nullable (solusi error bind_param)
$dosen_id_nullable = $id_dosen;  
$ruang_id_nullable = $id_ruang;  
// Update database
$stmt = $conn->prepare("
    UPDATE jadwal_kuliah 
    SET mata_kuliah = ?, hari = ?, jam_mulai = ?, jam_selesai = ?, id_dosen = ?, id_ruang = ?
    WHERE id = ?
");
$stmt->bind_param("ssssiii",
    $mata_kuliah,
    $hari,
    $jam_mulai,
    $jam_selesai,
    $dosen_id_nullable,    
    $ruang_id_nullable,    
    $id
);

if ($stmt->execute()) {
    $_SESSION['notif'] = ['type' => 'success', 'message' => 'Jadwal berhasil diperbarui.'];
    header("Location: ../../view/dashboard/admin/jadwal/data_jadwal.php");
} else {
    error_log("Error update jadwal ID $id: " . $stmt->error);
    redirectBack('danger', 'Gagal memperbarui jadwal. Coba lagi.', $id);
}

$stmt->close();
$stmtCheck->close();
$conn->close();
?>