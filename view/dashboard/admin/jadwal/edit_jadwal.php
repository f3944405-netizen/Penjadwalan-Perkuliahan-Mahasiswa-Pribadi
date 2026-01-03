<?php
session_start();
require_once __DIR__ . '/../../../../config/koneksi.php';

// Proteksi akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit;
}

// Ambil ID dari URL
$id = $_GET['id'] ?? null;
if (!is_numeric($id)) {
    $_SESSION['notif'] = ['type' => 'danger', 'message' => 'ID jadwal tidak valid.'];
    header("Location: data_jadwal.php");
    exit;
}
$id = (int)$id;

// Ambil data jadwal
$stmtJadwal = $conn->prepare("
    SELECT mata_kuliah, hari, jam_mulai, jam_selesai, id_dosen, id_ruang
    FROM jadwal_kuliah 
    WHERE id = ?
");
$stmtJadwal->bind_param("i", $id);
$stmtJadwal->execute();
$jadwal = $stmtJadwal->get_result()->fetch_assoc();

if (!$jadwal) {
    $_SESSION['notif'] = ['type' => 'warning', 'message' => 'ℹ️ Jadwal tidak ditemukan.'];
    header("Location: data_jadwal.php");
    exit;
}

// Ambil data pendukung
$dosenList = $conn->query("SELECT id, nama_dosen FROM dosen ORDER BY nama_dosen ASC");
$ruangList = $conn->query("SELECT id, nama_ruang FROM ruang ORDER BY nama_ruang ASC");

if (!$dosenList || !$ruangList) {
    die("Gagal memuat data dosen/ruang.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Jadwal Kuliah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>body{background:#f8f9fa}</style>
</head>
<body>

<div class="d-flex">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="container-fluid p-4">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3><i class="bi bi-pencil-square"></i> Edit Jadwal Kuliah</h3>
                    <a href="data_jadwal.php" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>

                <?php if (isset($_SESSION['notif'])): ?>
                    <?php $n = $_SESSION['notif']; unset($_SESSION['notif']); ?>
                    <div class="alert alert-<?= htmlspecialchars($n['type']) ?> alert-dismissible fade show">
                        <?= htmlspecialchars($n['message'], ENT_QUOTES, 'UTF-8') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="/config/proces/proses_edit_jadwal_admin.php">
                            <input type="hidden" name="id" value="<?= $id ?>">

                            <!-- Mata Kuliah -->
                            <div class="mb-3">
                                <label class="form-label">Mata Kuliah *</label>
                                <input type="text" name="mata_kuliah" class="form-control" 
                                       value="<?= htmlspecialchars($jadwal['mata_kuliah']) ?>" required>
                            </div>

                            <!-- Hari -->
                            <div class="mb-3">
                                <label class="form-label">Hari *</label>
                                <select name="hari" class="form-select" required>
                                    <option value="">-- Pilih Hari --</option>
                                    <?php
                                    $hariList = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                                    foreach ($hariList as $h):
                                    ?>
                                    <option value="<?= $h ?>" <?= $jadwal['hari'] === $h ? 'selected' : '' ?>>
                                        <?= $h ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Waktu (Mulai & Selesai) -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Jam Mulai *</label>
                                    <input type="time" name="jam_mulai" class="form-control" 
                                           value="<?= $jadwal['jam_mulai'] ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jam Selesai *</label>
                                    <input type="time" name="jam_selesai" class="form-control" 
                                           value="<?= $jadwal['jam_selesai'] ?>" required>
                                </div>
                            </div>

                            <!-- Dosen & Ruang -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Dosen</label>
                                    <select name="id_dosen" class="form-select">
                                        <option value="">-- Opsional --</option>
                                        <?php
                                        $dosenList->data_seek(0);
                                        while ($d = $dosenList->fetch_assoc()):
                                            $selected = ($jadwal['id_dosen'] == $d['id']) ? 'selected' : '';
                                        ?>
                                        <option value="<?= $d['id'] ?>" <?= $selected ?>>
                                            <?= htmlspecialchars($d['nama_dosen']) ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Ruang</label>
                                    <select name="id_ruang" class="form-select">
                                        <option value="">-- Opsional --</option>
                                        <?php
                                        $ruangList->data_seek(0);
                                        while ($r = $ruangList->fetch_assoc()):
                                            $selected = ($jadwal['id_ruang'] == $r['id']) ? 'selected' : '';
                                        ?>
                                        <option value="<?= $r['id'] ?>" <?= $selected ?>>
                                            <?= htmlspecialchars($r['nama_ruang']) ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Action -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-floppy"></i> Simpan Perubahan
                                </button>
                                
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- <script>
// Validasi client-side: jam selesai > jam mulai
document.querySelector('form').addEventListener('submit', function(e) {
    const mulai = document.querySelector('[name="jam_mulai"]').value;
    const selesai = document.querySelector('[name="jam_selesai"]').value;
    if (mulai && selesai && selesai <= mulai) {
        e.preventDefault();
        alert('⚠️ Jam selesai harus lebih besar dari jam mulai!');
        return false;
    }
});
</script> -->

<?php
$stmtJadwal->close();
$conn->close();
?>
</body>
</html>