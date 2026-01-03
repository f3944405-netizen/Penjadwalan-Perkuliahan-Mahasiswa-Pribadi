<?php
// Mulai session
session_start();

// Sertakan koneksi database
require_once __DIR__ . '/config/koneksi.php'; 
// Periksa apakah admin dengan NIM tertentu sudah ada di dalam database
$sql = "SELECT * FROM users WHERE nim = '1234567890' AND role = 'admin'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Jika admin dengan NIM tertentu belum ada, buatkan admin baru
    $nim = '1234567890'; 
    $nama = 'Admin'; 
    $password = 'admin123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Query untuk menyisipkan data admin baru
    $sql_insert = "INSERT INTO users (nim, nama, password, role) VALUES (?, ?, ?, 'admin')";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("sss", $nim, $nama, $hashed_password);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Akun admin berhasil dibuat!";
        echo "Akun admin berhasil dibuat.";
    } else {
        $_SESSION['error'] = "Terjadi kesalahan saat membuat akun admin.";
        echo "Gagal membuat akun admin.";
    }
} else {
    // Jika admin dengan NIM sudah ada
    $_SESSION['info'] = "Akun admin sudah ada.";
    echo "Akun admin sudah ada.";
}
// Tutup koneksi
$conn->close();