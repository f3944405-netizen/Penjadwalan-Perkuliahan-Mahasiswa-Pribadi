<?php
session_start();
require_once __DIR__ . '/../../../../config/koneksi.php';

// Autentikasi
if (!isset($_SESSION['nim']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: /login.php");
    exit;
}

$nim = $_SESSION['nim'];
$keyword = $_GET['q'] ?? '';
$search = "%$keyword%";

// Query jadwal pribadi - ambil data dari jadwal_kuliah untuk info dosen & ruang
$sql = "SELECT 
            jp.id,
            jp.hari,
            jp.mata_kuliah,
            jp.jam_mulai AS waktu,
            jp.jam_selesai,
            jp.keterangan,
            COALESCE(d.nama_dosen, 'Tidak Ada') AS nama_dosen,
            COALESCE(r.nama_ruang, 'Tidak Ada') AS nama_ruang
        FROM jadwal_pribadi jp
        LEFT JOIN jadwal_kuliah jk ON jp.mata_kuliah = jk.mata_kuliah
        LEFT JOIN dosen d ON jk.id_dosen = d.id
        LEFT JOIN ruang r ON jk.id_ruang = r.id
        WHERE jp.nim_mahasiswa = ? 
          AND (jp.mata_kuliah LIKE ? OR jp.keterangan LIKE ?)
        ORDER BY 
          CASE jp.hari
              WHEN 'Senin' THEN 1
              WHEN 'Selasa' THEN 2
              WHEN 'Rabu' THEN 3
              WHEN 'Kamis' THEN 4
              WHEN 'Jumat' THEN 5
              WHEN 'Sabtu' THEN 6
              ELSE 7
          END,
          jp.jam_mulai";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $nim, $search, $search);
$stmt->execute();
$result = $stmt->get_result();

if(!$result){
    die("Query gagal: " . $conn->error);
}

// Kelompokkan jadwal berdasarkan hari untuk kalender
$jadwalPerHari = [
    'Senin' => [],
    'Selasa' => [],
    'Rabu' => [],
    'Kamis' => [],
    'Jumat' => [],
    'Sabtu' => []
];

$allJadwal = [];
while ($row = $result->fetch_assoc()) {
    $allJadwal[] = $row;
    if (isset($jadwalPerHari[$row['hari']])) {
        $jadwalPerHari[$row['hari']][] = $row;
    }
}

// Hitung statistik
$totalJadwal = count($allJadwal);
$hariAktif = count(array_filter($jadwalPerHari, function($jadwal) { return count($jadwal) > 0; }));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Perkuliahan Mahasiswa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .calendar-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .calendar-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .day-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            font-weight: 600;
            border-radius: 10px 10px 0 0;
        }
        
        .schedule-item {
            background: white;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .schedule-item:hover {
            background: #f8f9fa;
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .empty-day {
            padding: 40px 20px;
            text-align: center;
            color: #adb5bd;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 10px 0;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .view-toggle {
            background: white;
            border-radius: 10px;
            padding: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .table-view {
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .badge-custom {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .calendar-card {
                margin-bottom: 15px;
            }
            
            .day-header {
                font-size: 14px;
                padding: 12px;
            }
            
            .schedule-item {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
       <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        
        <!-- Konten -->
        <div class="container-fluid p-4">
            <!-- Alert Notifikasi -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <?php
                    switch ($_GET['success']) {
                        case '1':
                        case 'added':
                            echo 'Jadwal berhasil ditambahkan!';
                            break;
                        case 'updated':
                            echo 'Jadwal berhasil diupdate!';
                            break;
                        case 'deleted':
                            echo 'Jadwal berhasil dihapus!';
                            break;
                        default:
                            echo 'Operasi berhasil!';
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <h3 class="mb-2"><i class="bi bi-calendar-week"></i> Jadwal Perkuliahan</h3>
                    <p class="text-muted mb-0">
                        Kelola dan lihat jadwal kuliah Anda dalam tampilan kalender atau tabel
                    </p>
                </div>
            </div>

            <!-- Statistik -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 opacity-75">Total Jadwal</h6>
                                <h2 class="mb-0"><?= $totalJadwal ?> Mata Kuliah</h2>
                            </div>
                            <i class="bi bi-book fs-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 opacity-75">Hari Aktif</h6>
                                <h2 class="mb-0"><?= $hariAktif ?> Hari / Minggu</h2>
                            </div>
                            <i class="bi bi-calendar-check fs-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search & Toggle View -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="view-toggle">
                        <form class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small text-muted">Cari Jadwal</label>
                                <input type="text" name="q" class="form-control"
                                       placeholder="Mata kuliah atau dosen..."
                                       value="<?= htmlspecialchars($keyword) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                            </div>
                            <div class="col-md-3">
                                <a href="./tambah_jadwal.php" class="btn btn-success w-100">
                                    <i class="bi bi-plus-circle"></i> Tambah Jadwal
                                </a>
                            </div>
                            <div class="col-md-3">
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-outline-primary active" onclick="showCalendar()">
                                        <i class="bi bi-calendar3"></i> Kalender
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" onclick="showTable()">
                                        <i class="bi bi-table"></i> Tabel
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Calendar View -->
            <div id="calendarView">
                <div class="row">
                    <?php foreach ($jadwalPerHari as $hari => $jadwalList): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="calendar-card shadow-sm">
                            <div class="day-header">
                                <i class="bi bi-calendar-day"></i> <?= $hari ?>
                                <span class="badge bg-light text-dark float-end"><?= count($jadwalList) ?> Jadwal</span>
                            </div>
                            <div class="p-3">
                                <?php if (count($jadwalList) > 0): ?>
                                    <?php foreach ($jadwalList as $jadwal): ?>
                                    <div class="schedule-item">
                                        <h6 class="mb-2 text-primary">
                                            <i class="bi bi-book-fill"></i> <?= htmlspecialchars($jadwal['mata_kuliah']) ?>
                                        </h6>
                                        <div class="small text-muted mb-2">
                                            <i class="bi bi-person"></i> <?= htmlspecialchars($jadwal['nama_dosen']) ?>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="small">
                                                <span class="badge badge-custom bg-info">
                                                    <i class="bi bi-clock"></i> <?= htmlspecialchars($jadwal['waktu']) ?>
                                                </span>
                                                <span class="badge badge-custom bg-warning text-dark">
                                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($jadwal['nama_ruang']) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mt-2 pt-2 border-top">
                                            <a href="./edit_jadwal.php?id=<?= $jadwal['id'] ?>" 
                                               class="btn btn-sm btn-outline-warning me-1">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="../../../../config/proces/proses_hapus_jadwal.php?id=<?= $jadwal['id'] ?>"
                                               onclick="return confirm('Yakin hapus jadwal?')"
                                               class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="empty-day">
                                        <i class="bi bi-calendar-x fs-1 mb-2"></i>
                                        <div>Tidak ada jadwal</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Table View (Hidden by default) -->
            <div id="tableView" style="display: none;">
                <div class="table-view shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th><i class="bi bi-calendar"></i> Hari</th>
                                    <th><i class="bi bi-book"></i> Mata Kuliah</th>
                                    <th><i class="bi bi-person"></i> Dosen</th>
                                    <th><i class="bi bi-door-open"></i> Ruang</th>
                                    <th><i class="bi bi-clock"></i> Waktu</th>
                                    <th class="text-center"><i class="bi bi-gear"></i> Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($allJadwal) > 0): ?>
                                    <?php foreach ($allJadwal as $row): ?>
                                    <tr>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($row['hari']) ?></span></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($row['mata_kuliah']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_dosen']) ?></td>
                                        <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($row['nama_ruang']) ?></span></td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($row['waktu']) ?></span></td>
                                        <td class="text-center">
                                            <a href="./edit_jadwal.php?id=<?= $row['id'] ?>" 
                                               class="btn btn-warning btn-sm me-1">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <a href="../../../../config/proces/proses_hapus_jadwal.php?id=<?= $row['id'] ?>"
                                               onclick="return confirm('Yakin hapus jadwal?')"
                                               class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            <div>Tidak ada jadwal perkuliahan</div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showCalendar() {
            document.getElementById('calendarView').style.display = 'block';
            document.getElementById('tableView').style.display = 'none';
            
            // Update button state
            document.querySelectorAll('.btn-group button').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('button').classList.add('active');
        }

        function showTable() {
            document.getElementById('calendarView').style.display = 'none';
            document.getElementById('tableView').style.display = 'block';
            
            // Update button state
            document.querySelectorAll('.btn-group button').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('button').classList.add('active');
        }
    </script>
</body>
</html>