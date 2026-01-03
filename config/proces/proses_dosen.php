<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['notif'] = ['type' => 'danger', 'message' => 'Akses ditolak.'];
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit;
}

$aksi = $_POST['aksi'] ?? '';
$nama = trim($_POST['nama_dosen'] ?? '');

if (empty($nama)) {
    $_SESSION['notif'] = ['type' => 'warning', 'message' => 'Nama dosen wajib diisi.'];
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

if ($aksi === 'tambah') {
    $stmt = $conn->prepare("INSERT INTO dosen (nama_dosen) VALUES (?)");
    $stmt->bind_param("s", $nama);
    $ok = $stmt->execute();
    $msg = $ok ? 'Dosen berhasil ditambahkan.' : ' Gagal menambah dosen.';
    $_SESSION['notif'] = ['type' => $ok ? 'success' : 'danger', 'message' => $msg];

} elseif ($aksi === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        $_SESSION['notif'] = ['type' => 'danger', 'message' => 'ID tidak valid.'];
    } else {
        $stmt = $conn->prepare("UPDATE dosen SET nama_dosen = ? WHERE id = ?");
        $stmt->bind_param("si", $nama, $id);
        $ok = $stmt->execute();
        $msg = $ok ? 'Data dosen diperbarui.' : 'Gagal memperbarui dosen.';
        $_SESSION['notif'] = ['type' => $ok ? 'success' : 'danger', 'message' => $msg];
    }
} else {
    $_SESSION['notif'] = ['type' => 'warning', 'message' => 'Aksi tidak dikenali.'];
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>