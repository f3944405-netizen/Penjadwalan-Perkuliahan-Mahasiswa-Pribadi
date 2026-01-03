<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['notif'] = ['type' => 'danger', 'message' => 'Akses ditolak.'];
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit;
}

$aksi = $_POST['aksi'] ?? '';
$nama = trim($_POST['nama_ruang'] ?? '');

if (empty($nama)) {
    $_SESSION['notif'] = ['type' => 'warning', 'message' => 'Nama ruang wajib diisi.'];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

if ($aksi === 'tambah') {
    $stmt = $conn->prepare("INSERT INTO ruang (nama_ruang) VALUES (?)");
    $stmt->bind_param("s", $nama);
    $ok = $stmt->execute();
    $msg = $ok ? 'Ruang berhasil ditambahkan.' : 'Gagal menambah ruang.';
    $_SESSION['notif'] = ['type' => $ok ? 'success' : 'danger', 'message' => $msg];

} elseif ($aksi === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        $_SESSION['notif'] = ['type' => 'danger', 'message' => 'ID tidak valid.'];
    } else {
        $stmt = $conn->prepare("UPDATE ruang SET nama_ruang = ? WHERE id = ?");
        $stmt->bind_param("si", $nama, $id);
        $ok = $stmt->execute();
        $msg = $ok ? 'Data ruang diperbarui.' : 'Gagal memperbarui ruang.';
        $_SESSION['notif'] = ['type' => $ok ? 'success' : 'danger', 'message' => $msg];
    }
} else {
    $_SESSION['notif'] = ['type' => 'warning', 'message' => 'Aksi tidak dikenali.'];
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>