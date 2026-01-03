<?php
// Mulai session
session_start();

// Sertakan file koneksi database
require_once __DIR__ . '/../koneksi.php'; // Pastikan path koneksi benar

// Cek apakah form sudah disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $nim = trim($_POST['nim']);
    $nama = trim($_POST['nama']);
    $password = trim($_POST['password']);

    // Validasi input
    if (empty($nim) || empty($nama) || empty($password)) {
        $_SESSION['error'] = "Harap isi semua field!";
        header("Location: register.php");
        exit();
    }

    // Validasi format NIM (misalnya 10 digit angka)
    if (!preg_match('/^[0-9]{10}$/', $nim)) {
        $_SESSION['error'] = "NIM harus terdiri dari 10 digit angka!";
        header("Location: register.php");
        exit();
    }

    // Validasi kata sandi (minimal 6 karakter)
    if (strlen($password) < 6) {
        $_SESSION['error'] = "Kata sandi harus terdiri dari minimal 6 karakter!";
        header("Location: register.php");
        exit();
    }

    // Cek apakah NIM sudah terdaftar
    $sql = "SELECT * FROM users WHERE nim = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "NIM sudah terdaftar. Silakan login!";
        header("Location: register.php");
        exit();
    }

    // Hash kata sandi
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Simpan pengguna baru ke database
    $sql = "INSERT INTO users (nim, nama, password, role) VALUES (?, ?, ?, 'mahasiswa')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nim, $nama, $hashed_password);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Akun berhasil dibuat. Silakan login!";
        header("Location: ../../index.html");
        exit();
    } else {
        $_SESSION['error'] = "Terjadi kesalahan. Coba lagi nanti.";
        header("Location: register.php");
        exit();
    }
}

// Tutup koneksi
$conn->close();
?>
