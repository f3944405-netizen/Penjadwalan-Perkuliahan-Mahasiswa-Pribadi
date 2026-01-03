<?php
session_start();
require_once __DIR__ . '/../../../../config/koneksi.php';

if (!isset($_SESSION['nim']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: /login.php");
    exit;
}

$nim = $_SESSION['nim'];

/* Ambil data mahasiswa + jurusan */
$sql = "
    SELECT u.id, u.nim, u.nama, u.jurusan_id, j.nama_jurusan
    FROM users u
    LEFT JOIN jurusan j ON u.jurusan_id = j.id
    WHERE u.nim = ? AND u.role = 'mahasiswa'
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Data mahasiswa tidak ditemukan.");
}

$mahasiswa = $result->fetch_assoc();

/* Ambil semua jurusan */
$jurusanQuery = $conn->query("SELECT * FROM jurusan ORDER BY nama_jurusan ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pribadi Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">

    <!-- SIDEBAR -->
       <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <!-- CONTENT -->
    <div class="container-fluid p-4">

        <h3 class="mb-4">Data Pribadi Mahasiswa</h3>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                Data berhasil diperbarui.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                Gagal menyimpan data. Pastikan semua field terisi.
            </div>
        <?php endif; ?>

        <form method="POST" action="/config/proces/proses_edit_mahasiswa.php" class="col-md-6">

            <div class="mb-3">
                <label class="form-label">NIM</label>
                <input type="text" class="form-control"
                       value="<?= htmlspecialchars($mahasiswa['nim']) ?>" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" name="nama"
                       value="<?= htmlspecialchars($mahasiswa['nama']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Jurusan</label>
                <select class="form-select" name="jurusan_id" required>
                    <option value="">-- Pilih Jurusan --</option>
                    <?php while ($j = $jurusanQuery->fetch_assoc()): ?>
                        <option value="<?= $j['id'] ?>"
                            <?= $mahasiswa['jurusan_id'] == $j['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($j['nama_jurusan']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button class="btn btn-primary">Simpan Perubahan</button>
            <a href="../mahasiswa.php" class="btn btn-secondary">Kembali</a>
        </form>

    </div>
</div>

</body>
</html>
