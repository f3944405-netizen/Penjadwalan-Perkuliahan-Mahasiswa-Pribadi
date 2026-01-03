<?php
// ✅ Hanya 1x session_start() di awal
session_start();
require_once __DIR__ . '/../../../../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('<h2 class="text-danger text-center mt-5">⛔ Akses Ditolak</h2>');
}

// Query data mahasiswa & jurusan
$mahasiswa = $conn->query("
    SELECT u.id, u.nim, u.nama, j.nama_jurusan
    FROM users u
    LEFT JOIN jurusan j ON u.jurusan_id = j.id
    WHERE u.role = 'mahasiswa'
    ORDER BY u.nama ASC
");

$jurusan = $conn->query("SELECT id, nama_jurusan FROM jurusan ORDER BY nama_jurusan ASC");

if (!$mahasiswa || !$jurusan) {
    die("Gagal mengambil data: " . htmlspecialchars($conn->error));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Mahasiswa</title>
    <!-- ✅ Perbaiki spasi di CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>body{background:#f8f9fa}</style>
</head>
<body>

<div class="d-flex">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-people"></i> Kelola Mahasiswa</h3>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahModal">
                <i class="bi bi-plus-circle"></i> Tambah Mahasiswa
            </button>
        </div>

        <?php if (isset($_SESSION['notif'])): ?>
            <?php $n = $_SESSION['notif']; unset($_SESSION['notif']); ?>
            <div class="alert alert-<?= htmlspecialchars($n['type']) ?> alert-dismissible fade show">
                <?= htmlspecialchars($n['message'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($mahasiswa->num_rows === 0): ?>
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle me-2"></i> Belum ada data mahasiswa.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No.</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Jurusan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($m = $mahasiswa->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($m['nim']) ?></td>
                            <td><?= htmlspecialchars($m['nama']) ?></td>
                            <td><?= htmlspecialchars($m['nama_jurusan'] ?? '-') ?></td>
                            <td class="text-nowrap">
                                <!-- ✅ Perbaiki path edit & hapus -->
                                <a href="edit_mahasiswa.php?id=<?= (int)$m['id'] ?>" 
                                   class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="../../../../config/proces/proses_hapus_mahasiswa.php?id=<?= (int)$m['id'] ?>"
                                   class="btn btn-sm btn-outline-danger" title="Hapus"
                                   onclick="return confirm('Yakin hapus <?= addslashes($m['nama']) ?>?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="tambahModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="/config/proces/proses_tambah_mahasiswa.php" class="modal-content">
            <div class="modal-header">
                <h5><i class="bi bi-person-plus"></i> Tambah Mahasiswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">NIM *</label>
                    <input type="text" name="nim" class="form-control" placeholder="Contoh: 3311XXXX" required maxlength="20">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Jurusan *</label>
                    <select name="jurusan_id" class="form-select" required>
                        <option value="">-- Pilih Jurusan --</option>
                        <?php
                        $jurusan->data_seek(0); // reset pointer
                        while ($j = $jurusan->fetch_assoc()):
                        ?>
                        <option value="<?= (int)$j['id'] ?>"><?= htmlspecialchars($j['nama_jurusan']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ✅ Perbaiki spasi di CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
// Tutup koneksi sekali saja di akhir
$conn->close();
?>