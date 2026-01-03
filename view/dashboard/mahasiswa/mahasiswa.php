<?php
session_start();
require_once __DIR__ . '/../../../config/koneksi.php'; 

$nim = $_SESSION['nim'];

$sql_mahasiswa = "SELECT * FROM users WHERE nim = ? AND role = 'mahasiswa'";
$stmt_mahasiswa = $conn->prepare($sql_mahasiswa);
$stmt_mahasiswa->bind_param("s", $nim);
$stmt_mahasiswa->execute();
$result_mahasiswa = $stmt_mahasiswa->get_result();

if ($result_mahasiswa->num_rows > 0) {
    $mahasiswa = $result_mahasiswa->fetch_assoc();
} else {
    echo "Data mahasiswa tidak ditemukan.";
    exit();
}


$sql_jadwal = "SELECT jk.hari, jk.mata_kuliah, jk.jam_mulai, jk.jam_selesai,
                      d.nama_dosen, r.nama_ruang
               FROM jadwal_kuliah jk
               LEFT JOIN dosen d ON jk.id_dosen = d.id
               LEFT JOIN ruang r ON jk.id_ruang = r.id
               ORDER BY FIELD(jk.hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), jk.jam_mulai ASC";

$stmt_jadwal = $conn->prepare($sql_jadwal);
$stmt_jadwal->execute();
$result_jadwal = $stmt_jadwal->get_result();


$sql = "SELECT u.nim, u.nama, j.nama_jurusan
        FROM users u
        JOIN jurusan j ON u.jurusan_id = j.id
        WHERE u.nim = ? AND u.role = 'mahasiswa'";

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body>

    <div class="d-flex">
        <!-- Sidebar -->
       <?php include __DIR__ . '/../mahasiswa/partials/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="container-fluid p-4">
          
<div class="container-fluid p-4">

    <!-- Header -->
    <h3 class="mb-1">Dashboard Mahasiswa</h3>
    <p class="text-muted mb-4">
        Selamat datang, <strong><?= htmlspecialchars($mahasiswa['nama']) ?></strong>
        (NIM: <?= htmlspecialchars($mahasiswa['nim']) ?>)
    </p>

    <!-- Info Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Jadwal Kuliah</h6>
                    <h3><?= $result_jadwal->num_rows ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Jadwal Preview -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            Jadwal Kuliah Anda
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Hari</th>
                        <th>Mata Kuliah</th>
                        <th>Dosen</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_jadwal->num_rows > 0): ?>
                        <?php while ($row = $result_jadwal->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['hari']) ?></td>
                                <td><?= htmlspecialchars($row['mata_kuliah']) ?></td>
                                <td><?= htmlspecialchars($row['nama_dosen'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['jam_mulai']) ?></td>
                                <td><?= htmlspecialchars($row['jam_selesai']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Jadwal belum tersedia
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card-footer text-end">
            <a href="/view/dashboard/mahasiswa/jadwal/jadwal_kuliah.php"
               class="btn btn-sm btn-primary">
                Lihat Semua Jadwal
            </a>
        </div>
    </div>

</div>

 

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
