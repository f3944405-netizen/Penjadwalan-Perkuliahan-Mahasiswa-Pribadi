<?php
session_start();
require_once __DIR__ . '/../../../../config/koneksi.php';

if (!isset($_SESSION['nim']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: /login.php");
    exit;
}

$id = $_GET['id'] ?? 0;
$nim = $_SESSION['nim'];

// Ambil data jadwal yang akan diedit
$stmt = $conn->prepare("SELECT * FROM jadwal_pribadi WHERE id = ? AND nim_mahasiswa = ?");
$stmt->bind_param("is", $id, $nim);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Jadwal tidak ditemukan atau bukan milik Anda");
}

$jadwal = $result->fetch_assoc();
// Ambil daftar dosen
$dosen_stmt = $conn->prepare("SELECT id, nama_dosen FROM dosen");
$dosen_stmt->execute();
$dosen_list = $dosen_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$dosen_stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Jadwal Pribadi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<div class="d-flex">
       <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="container-fluid p-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-header">
                    <h3 class="mb-2"><i class="bi bi-pencil-square"></i> Edit Jadwal Pribadi</h3>
                    <p class="mb-0 opacity-75">Ubah jadwal kuliah Anda</p>
                </div>

                <div class="form-container">
                    <form method="POST" action="../../../../config/proces/proses_edit_jadwal_pribadi.php">
                        <input type="hidden" name="id" value="<?= $jadwal['id'] ?>">

                        <!-- Mata Kuliah -->
                        <div class="mb-4">
                            <label for="mata_kuliah" class="form-label">
                                <i class="bi bi-book"></i> Mata Kuliah
                            </label>
                            <input type="text" name="mata_kuliah" id="mata_kuliah" 
                                   class="form-control" required
                                   value="<?= htmlspecialchars($jadwal['mata_kuliah']) ?>">
                        </div>

                        <!-- Pilih Hari -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="bi bi-calendar"></i> Hari
                            </label>
                            <select name="hari" class="form-select" required>
                                <option value="">-- Pilih Hari --</option>
                                <option value="Senin" <?= $jadwal['hari'] === 'Senin' ? 'selected' : '' ?>>Senin</option>
                                <option value="Selasa" <?= $jadwal['hari'] === 'Selasa' ? 'selected' : '' ?>>Selasa</option>
                                <option value="Rabu" <?= $jadwal['hari'] === 'Rabu' ? 'selected' : '' ?>>Rabu</option>
                                <option value="Kamis" <?= $jadwal['hari'] === 'Kamis' ? 'selected' : '' ?>>Kamis</option>
                                <option value="Jumat" <?= $jadwal['hari'] === 'Jumat' ? 'selected' : '' ?>>Jumat</option>
                                <option value="Sabtu" <?= $jadwal['hari'] === 'Sabtu' ? 'selected' : '' ?>>Sabtu</option>
                            </select>
                        </div>

                        <!-- Waktu -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-clock"></i> Jam Mulai
                                </label>
                                <input type="time" name="jam_mulai" class="form-control" required
                                       value="<?= htmlspecialchars($jadwal['jam_mulai']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-clock-history"></i> Jam Selesai
                                </label>
                                <input type="time" name="jam_selesai" class="form-control" required
                                       value="<?= htmlspecialchars($jadwal['jam_selesai']) ?>">
                            </div>
                        </div>
                        <!-- Dosen Pengampu -->
<div class="mb-4">
    <label class="form-label">
        <i class="bi bi-person-chalkboard"></i> Dosen Pengampu
    </label>
    <select name="id_dosen" class="form-select" required>
        <option value="">-- Pilih Dosen --</option>
        <?php foreach ($dosen_list as $dosen): ?>
            <option value="<?= $dosen['id'] ?>" 
                <?= $jadwal['id_dosen'] == $dosen['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($dosen['nama']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

                        <!-- Keterangan -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="bi bi-pencil-square"></i> Keterangan (Opsional)
                            </label>
                            <textarea name="keterangan" class="form-control" rows="3"
                                      placeholder="Catatan tambahan"><?= htmlspecialchars($jadwal['keterangan']) ?></textarea>
                        </div>

                        <!-- Tombol -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-save"></i> Update Jadwal
                            </button>
                            <a href="./jadwal_kuliah.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>