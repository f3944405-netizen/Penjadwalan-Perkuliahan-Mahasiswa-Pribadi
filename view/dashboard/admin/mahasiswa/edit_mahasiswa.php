<?php
session_start();
require_once __DIR__ . '/../../../../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('Akses ditolak');
}

$id = $_GET['id'] ?? null;
if (!$id) {
    exit('ID tidak ditemukan');
}

/* Ambil data mahasiswa */
$sql = "
    SELECT u.id, u.nim, u.nama, u.jurusan_id
    FROM users u
    WHERE u.id = ? AND u.role = 'mahasiswa'
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$mahasiswa = $stmt->get_result()->fetch_assoc();

if (!$mahasiswa) {
    exit('Data mahasiswa tidak ditemukan');
}

/* Ambil semua jurusan */
$jurusan = $conn->query("SELECT * FROM jurusan ORDER BY nama_jurusan ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Mahasiswa</title>
       <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <!-- Content -->
    <div class="container-fluid p-4">
        <h3 class="mb-4">Edit Data Mahasiswa</h3>

        <form method="POST" action="/config/proces/proses_edit_mahasiswa_admin.php">
            <input type="hidden" name="id" value="<?= $mahasiswa['id']; ?>">

            <div class="mb-3">
                <label class="form-label">NIM</label>
                <input type="text" class="form-control" name="nim"
                       value="<?= htmlspecialchars($mahasiswa['nim']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" name="nama"
                       value="<?= htmlspecialchars($mahasiswa['nama']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Jurusan</label>
                <select class="form-select" name="jurusan_id" required>
                    <option value="">-- Pilih Jurusan --</option>
                    <?php while ($j = $jurusan->fetch_assoc()): ?>
                        <option value="<?= $j['id']; ?>"
                            <?= $mahasiswa['jurusan_id'] == $j['id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($j['nama_jurusan']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button class="btn btn-primary">Simpan Perubahan</button>
            <a href="./kelola_mahasiswa.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

</body>
</html>
