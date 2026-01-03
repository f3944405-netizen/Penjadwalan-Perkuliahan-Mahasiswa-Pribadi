<?php
session_start();
require_once __DIR__ . '/../../../../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('Akses ditolak');
}

$dosen = $conn->query("SELECT id, nama_dosen FROM dosen ORDER BY nama_dosen ASC");
$ruang = $conn->query("SELECT id, nama_ruang FROM ruang ORDER BY nama_ruang ASC");

if (!$dosen || !$ruang) {
    die("Gagal mengambil data dosen / ruang");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Jadwal Kuliah</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
                <h3 class="mb-4">
                    <i class="bi bi-calendar-plus"></i> Tambah Jadwal Kuliah
                </h3>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="/config/proces/proses_tambah_jadwal_admin.php">

                            <!-- Mata Kuliah -->
                            <div class="mb-3">
                                <label class="form-label">Mata Kuliah *</label>
                                <input type="text" name="mata_kuliah" class="form-control" required maxlength="100">
                            </div>

                            <!-- Hari -->
                            <div class="mb-3">
                                <label class="form-label">Hari *</label>
                                <select name="hari" class="form-select" required>
                                    <option value="">-- Pilih Hari --</option>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                </select>
                            </div>

                            <!-- Waktu (Mulai & Selesai) -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Jam Mulai *</label>
                                    <input type="time" name="jam_mulai" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jam Selesai *</label>
                                    <input type="time" name="jam_selesai" class="form-control" required>
                                </div>
                            </div>

                            <!-- Dosen & Ruang -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Dosen</label>
                                    <select name="id_dosen" class="form-select">
                                        <option value="">-- Opsional --</option>
                                        <?php
                                        // Reset pointer karena query dipakai sekali
                                        $dosen->data_seek(0);
                                        while ($d = $dosen->fetch_assoc()):
                                        ?>
                                        <option value="<?= $d['id'] ?>">
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
                                        $ruang->data_seek(0);
                                        while ($r = $ruang->fetch_assoc()):
                                        ?>
                                        <option value="<?= $r['id'] ?>">
                                            <?= htmlspecialchars($r['nama_ruang']) ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Tombol -->
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Simpan Jadwal
                                </button>
                                <a href="data_jadwal.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
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

</body>
</html>