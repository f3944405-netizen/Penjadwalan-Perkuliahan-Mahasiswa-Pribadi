<?php
session_start();
require_once __DIR__ . '/../../../../config/koneksi.php';

//  Proteksi akses lebih baik
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('<h2 class="text-danger text-center mt-5">Akses Ditolak</h2><p class="text-center">Login sebagai admin untuk mengakses halaman ini.</p>');
}

$sql = "
    SELECT jk.id, 
           jk.mata_kuliah, 
           jk.hari, 
           jk.jam_mulai, 
           jk.jam_selesai,
           d.nama_dosen, 
           r.nama_ruang
    FROM jadwal_kuliah jk
    LEFT JOIN dosen d ON jk.id_dosen = d.id
    LEFT JOIN ruang r ON jk.id_ruang = r.id
    ORDER BY 
        FIELD(jk.hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'),
        jk.jam_mulai
";
$result = $conn->query($sql);

if (!$result) {
    die("Query error: " . htmlspecialchars($conn->error));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Jadwal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .time-badge {
            font-weight: 600;
            background: #e9ecef;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-calendar-week"></i> Kelola Jadwal Perkuliahan</h3>
            <a href="tambah_jadwal.php" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Tambah Jadwal
            </a>
        </div>

        <?php if (isset($_SESSION['notif'])): ?>
            <?php $n = $_SESSION['notif']; unset($_SESSION['notif']); ?>
            <div class="alert alert-<?= htmlspecialchars($n['type']) ?> alert-dismissible fade show">
                <?= htmlspecialchars($n['message'], ENT_QUOTES, 'UTF-8') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($result->num_rows === 0): ?>
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle me-2"></i> Belum ada jadwal kuliah.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Mata Kuliah</th>
                            <th>Hari</th>
                            <th>Waktu</th>
                            <th>Dosen</th>
                            <th>Ruang</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['mata_kuliah']) ?></strong></td>
                            <td><?= htmlspecialchars($row['hari']) ?></td>
                            <td>
                                <span class="time-badge">
                                    <?= htmlspecialchars($row['jam_mulai']) ?> – 
                                    <?= htmlspecialchars($row['jam_selesai']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['nama_dosen'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['nama_ruang'] ?? '-') ?></td>
                            <td class="text-nowrap">
                                <!-- Cast ID ke int -->
                                <a href="edit_jadwal.php?id=<?= (int)$row['id'] ?>" 
                                   class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="../../../../config/proces/proses_hapus_jadwal_admin.php?id=<?= (int)$row['id'] ?>"
                                   onclick="return confirm('Yakin hapus jadwal <?= addslashes(htmlspecialchars($row['mata_kuliah'])) ?>?')"
                                   class="btn btn-sm btn-danger" title="Hapus">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>