<?php
session_start();
require_once __DIR__ . '/../../../../config/koneksi.php';

if (isset($_SESSION['notif'])) {
    $notif = $_SESSION['notif'];
    $type = $notif['type'] ?? 'danger';
    $message = $notif['message'] ?? 'Terjadi kesalahan.';
    echo "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
            <i class='bi bi-exclamation-triangle-fill me-2'></i> {$message}
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
          </div>";
    unset($_SESSION['notif']); // hapus setelah ditampilkan
}


if (!isset($_SESSION['nim']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: /login.php");
    exit;
}

// Ambil daftar mata kuliah dari jadwal resmi
$result = $conn->query("
    SELECT 
        mata_kuliah,
        GROUP_CONCAT(DISTINCT hari ORDER BY hari SEPARATOR '|') as hari_list,
        MIN(jam_mulai) as jam_mulai,
        MAX(jam_selesai) as jam_selesai
    FROM jadwal_kuliah
    GROUP BY mata_kuliah
    ORDER BY mata_kuliah ASC
");
$jadwalResmiList = $result->fetch_all(MYSQLI_ASSOC);
$mataKuliahList = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Jadwal Pribadi</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
                    <h3 class="mb-2"><i class="bi bi-plus-circle"></i> Tambah Jadwal Pribadi</h3>
                    <p class="mb-0 opacity-75">Buat jadwal kuliah versi Anda sendiri</p>
                </div>

                <div class="form-container">
                    <form method="POST" action="../../../../config/proces/proses_tambah_jadwal_pribadi.php">

            <!-- Pilih Mata Kuliah -->
<div class="mb-4">
    <label for="mata_kuliah" class="form-label">
        <i class="bi bi-book"></i> Mata Kuliah *
    </label>
    <select name="mata_kuliah" id="mata_kuliah" class="form-select" required>
        <option value="">-- Pilih Mata Kuliah --</option>
        <?php foreach ($jadwalResmiList as $jr): ?>
            <option value="<?= htmlspecialchars($jr['mata_kuliah']) ?>"
                    data-hari="<?= htmlspecialchars($jr['hari_list']) ?>"
                    data-jam-mulai="<?= htmlspecialchars($jr['jam_mulai']) ?>"
                    data-jam-selesai="<?= htmlspecialchars($jr['jam_selesai']) ?>">
                <?= htmlspecialchars($jr['mata_kuliah']) ?>
            </option>
        <?php endforeach; ?>
        <option value="custom">+ Input Manual</option>
    </select>

    <!-- Input manual -->
    <input type="text" name="mata_kuliah_custom" id="mata_kuliah_custom" 
           class="form-control mt-2" placeholder="Masukkan nama mata kuliah" 
           style="display: none;">
</div>

                        <!-- Pilih Hari -->
                   <!-- Hari -->
<div class="mb-4">
    <label class="form-label">
        <i class="bi bi-calendar"></i> Hari *
    </label>
    <select name="hari" id="hari" class="form-select" required>
        <option value="">-- Pilih Hari --</option>
        <option value="Senin">Senin</option>
        <option value="Selasa">Selasa</option>
        <option value="Rabu">Rabu</option>
        <option value="Kamis">Kamis</option>
        <option value="Jumat">Jumat</option>
        <option value="Sabtu">Sabtu</option>
    </select>
</div>

                        <!-- Waktu -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-clock"></i> Jam Mulai
                                </label>
                                <input type="time" name="jam_mulai" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="bi bi-clock-history"></i> Jam Selesai
                                </label>
                                <input type="time" name="jam_selesai" class="form-control" required>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="bi bi-pencil-square"></i> Keterangan (Opsional)
                            </label>
                            <textarea name="keterangan" class="form-control" rows="3"
                                      placeholder="Contoh: Ujian Tengah Semester, Presentasi Kelompok, dll"></textarea>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Info dosen dan ruang akan diambil dari jadwal resmi jika tersedia
                            </small>
                        </div>

                        <!-- Tombol -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-save"></i> Simpan Jadwal
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
<script>
document.getElementById('mata_kuliah').addEventListener('change', function() {
    const customInput = document.getElementById('mata_kuliah_custom');
    const hariSelect = document.getElementById('hari');
    const jamMulai = document.querySelector('[name="jam_mulai"]');
    const jamSelesai = document.querySelector('[name="jam_selesai"]');

    const selectedOption = this.options[this.selectedIndex];
    const hariList = selectedOption.dataset.hari;
    const jamMulaiVal = selectedOption.dataset.jamMulai;
    const jamSelesaiVal = selectedOption.dataset.jamSelesai;

    if (this.value === 'custom') {
        // Mode manual
        customInput.style.display = 'block';
        customInput.required = true;
        this.required = false;
        
        // Kosongkan & disable otomatis
        hariSelect.value = '';
        jamMulai.value = '';
        jamSelesai.value = '';
    } else {
        // Mode otomatis dari jadwal resmi
        customInput.style.display = 'none';
        customInput.required = false;
        this.required = true;

        // Isi waktu otomatis
        if (jamMulaiVal && jamSelesaiVal) {
            jamMulai.value = jamMulaiVal;
            jamSelesai.value = jamSelesaiVal;
        }

        // Atur dropdown hari: jika hanya 1 hari → auto-pilih, jika >1 → biarkan pilih
        if (hariList) {
            const hariArray = hariList.split('|');
            if (hariArray.length === 1) {
                hariSelect.value = hariArray[0];
            } else {
                // Opsional: beri hint di placeholder
                hariSelect.value = '';
                // Tidak auto-pilih, biar user pilih sesuai sesi yang diinginkan
            }
        }
    }
});

// Validasi waktu (tetap dipertahankan)
document.querySelector('form').addEventListener('submit', function(e) {
    const jamMulai = document.querySelector('[name="jam_mulai"]').value;
    const jamSelesai = document.querySelector('[name="jam_selesai"]').value;
    if (jamMulai && jamSelesai && jamMulai >= jamSelesai) {
        e.preventDefault();
        alert('❌ Jam mulai tidak boleh lebih besar/sama dengan jam selesai!');
        return false;
    }
});
</script>
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const jamMulai = document.querySelector('[name="jam_mulai"]').value;
    const jamSelesai = document.querySelector('[name="jam_selesai"]').value;
    if (jamMulai && jamSelesai && jamMulai >= jamSelesai) {
        e.preventDefault();
        alert(' Jam mulai tidak boleh lebih besar/sama dengan jam selesai!');
        return false;
    }
});
</script>

</body>
</html>