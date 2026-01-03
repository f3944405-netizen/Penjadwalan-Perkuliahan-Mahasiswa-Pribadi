<?php
session_start(); 
// Sertakan file koneksi database
require_once __DIR__ . '/../koneksi.php'; 

// Periksa apakah form login telah disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $nim = trim($_POST['nim']);
    $password = trim($_POST['password']);

    // Validasi input
    if (empty($nim) || empty($password)) {
        $error_message = "Harap isi semua field!";
    } else {
        $sql = "SELECT * FROM users WHERE nim = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $nim);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['nim'] = $user['nim'];
                $_SESSION['role'] = $user['role'];

                // Redirect berdasarkan role
                if ($user['role'] == 'admin') {
                    header("Location: ../../view/dashboard/admin/admin_dashboard.php"); // Halaman admin
                } else {
                    header("Location: ../../view/dashboard/mahasiswa/mahasiswa.php"); // Halaman mahasiswa
                }
                exit(); 
            } else {
                $error_message = "Kata sandi salah!";
            }
        } else {
            $error_message = "NIM tidak ditemukan!";
            header("Location: ../../index.html?error=nim_not_found");
        }
    }

    $conn->close();
}

if (isset($error_message)) {
    echo "<p style='color:red;'>$error_message</p>";
}
?>
