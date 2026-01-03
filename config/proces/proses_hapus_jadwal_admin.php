<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Proteksi akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['notif'] = ['type' => 'danger', 'message' => 'Akses ditolak.'];
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../../view/dashboard/admin/jadwal/data_jadwal.php'));
    exit;
}

// Ambil ID dari QUERY STRING (bukan POST!)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['notif'] = ['type' => 'warning', 'message' => ' ID jadwal tidak valid.'];
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'data_jadwal.php'));
    exit;
}

$id = (int)$_GET['id'];

// Eksekusi hapus
$stmt = $conn->prepare("DELETE FROM jadwal_kuliah WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $_SESSION['notif'] = ['type' => 'success', 'message' => 'Jadwal berhasil dihapus.'];
    } else {
        $_SESSION['notif'] = ['type' => 'warning', 'message' => ' Jadwal tidak ditemukan.'];
    }
} else {
    error_log("Gagal hapus jadwal ID $id: " . $stmt->error);
    $_SESSION['notif'] = ['type' => 'danger', 'message' => ' Gagal menghapus jadwal. Coba lagi.'];
}

$stmt->close();
$conn->close();

// Redirect kembali ke daftar jadwal
header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../../view/dashboard/admin/jadwal/data_jadwal.php'));
exit;
?>