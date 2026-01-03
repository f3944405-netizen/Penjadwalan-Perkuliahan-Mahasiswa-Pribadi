<?php
// FILE: config/proces/proses_hapus_jadwal.php
session_start();
require_once __DIR__ . '/../koneksi.php';

// Cek autentikasi
if (!isset($_SESSION['nim']) || $_SESSION['role'] !== 'mahasiswa') {
    die("Akses ditolak - Anda harus login sebagai mahasiswa");
}

// Ambil parameter
$id = intval($_GET['id'] ?? 0);
$nim = (string)$_SESSION['nim']; // Pastikan string karena di DB varchar(50)

// Validasi ID
if ($id <= 0) {
    die("ID jadwal tidak valid");
}

// Hapus langsung dengan 2 kondisi (id DAN nim_mahasiswa)
$stmt = $conn->prepare("DELETE FROM jadwal_pribadi WHERE id = ? AND nim_mahasiswa = ?");
$stmt->bind_param("is", $id, $nim); // i = integer, s = string

if ($stmt->execute()) {
    // Cek apakah ada baris yang terhapus
    if ($stmt->affected_rows > 0) {
        // Berhasil hapus
        header("Location: ../../view/dashboard/mahasiswa/jadwal/jadwal_kuliah.php?success=deleted");
        exit;
    } else {
        // Tidak ada baris terhapus = jadwal tidak ditemukan atau bukan milik user
        die("Gagal hapus: Jadwal tidak ditemukan atau bukan milik Anda");
    }
} else {
    die("Gagal menghapus jadwal: " . $stmt->error);
}

$conn->close();
?>