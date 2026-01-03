<?php
// Start session and include database connection
session_start();
require_once __DIR__ . '/../../../config/koneksi.php'; 

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.html");
    exit();
}

// Data untuk cards
$jumlahMahasiswa = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='mahasiswa'")->fetch_assoc();
$jumlahDosen     = $conn->query("SELECT COUNT(*) as total FROM dosen")->fetch_assoc();
$jumlahJadwal    = $conn->query("SELECT COUNT(*) as total FROM jadwal_kuliah")->fetch_assoc();
$jumlahRuang     = $conn->query("SELECT COUNT(*) as total FROM ruang")->fetch_assoc();

// Data statistik tambahan
$jadwalHariIni = $conn->query("SELECT COUNT(*) as total FROM jadwal_kuliah WHERE hari = DAYNAME(CURDATE())")->fetch_assoc();

// Data untuk chart - Jadwal per hari
$jadwalPerHari = $conn->query("
    SELECT hari, COUNT(*) as jumlah 
    FROM jadwal_kuliah 
    GROUP BY hari 
    ORDER BY FIELD(hari, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
");
$dataHari = [];
$dataJumlah = [];
while($row = $jadwalPerHari->fetch_assoc()) {
    $hariIndo = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa', 
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu'
    ];
    $dataHari[] = $hariIndo[$row['hari']] ?? $row['hari'];
    $dataJumlah[] = $row['jumlah'];
}



// Jadwal terbaru
$jadwalTerbaru = $conn->query("
    SELECT j.*, d.nama_dosen as nama_dosen, r.nama_ruang, j.mata_kuliah
    FROM jadwal_kuliah j
    LEFT JOIN dosen d ON j.id_dosen = d.id
    LEFT JOIN ruang r ON j.id_ruang = r.id
    ORDER BY j.id DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        .stat-card.primary { border-left-color: #0d6efd; }
        .stat-card.success { border-left-color: #198754; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.danger { border-left-color: #dc3545; }
        .stat-card.info { border-left-color: #0dcaf0; }
        .stat-card.secondary { border-left-color: #6c757d; }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .table-hover tbody tr:hover {
            background-color: #f1f3f5;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="container-fluid p-4">
            <!-- Welcome Banner -->
            <div class="welcome-banner shadow">
                <h2 class="mb-2"><i class="bi bi-speedometer2"></i> Dashboard Admin</h2>
                <p class="mb-0 opacity-75">Selamat datang kembali! Berikut adalah ringkasan sistem akademik Anda.</p>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card primary shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 small">Total Mahasiswa</p>
                                    <h2 class="mb-0"><?= $jumlahMahasiswa['total'] ?></h2>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                    <i class="bi bi-people fs-1 text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card success shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 small">Total Dosen</p>
                                    <h2 class="mb-0"><?= $jumlahDosen['total'] ?></h2>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                                    <i class="bi bi-person-badge fs-1 text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card warning shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 small">Total Jadwal</p>
                                    <h2 class="mb-0"><?= $jumlahJadwal['total'] ?></h2>
                                </div>
                                <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                                    <i class="bi bi-calendar-event fs-1 text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card stat-card danger shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 small">Total Ruangan</p>
                                    <h2 class="mb-0"><?= $jumlahRuang['total'] ?></h2>
                                </div>
                                <div class="bg-danger bg-opacity-10 p-3 rounded-circle">
                                    <i class="bi bi-door-open fs-1 text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row g-4 mb-4">
                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 pt-3">
                            <h5 class="mb-0"><i class="bi bi-bar-chart-fill text-primary"></i> Statistik Jadwal per Hari</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="jadwalChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

              
            </div>

            <!-- Recent Schedule Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 pt-3">
                            <h5 class="mb-0"><i class="bi bi-list-ul text-warning"></i> Jadwal Terbaru</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Mata Kuliah</th>
                                            <th>Dosen</th>
                                            <th>Hari</th>
                                            <th>Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
<?php if($jadwalTerbaru && $jadwalTerbaru->num_rows > 0): ?>
    <?php while($row = $jadwalTerbaru->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['mata_kuliah']) ?></td>
            <td><?= htmlspecialchars($row['nama_dosen'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['hari']) ?></td>
            <td><?= htmlspecialchars($row['waktu']) ?></td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="4" class="text-center text-muted">Tidak ada jadwal</td>
    </tr>
<?php endif; ?>
</tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chart Jadwal per Hari
        const ctxJadwal = document.getElementById('jadwalChart').getContext('2d');
        new Chart(ctxJadwal, {
            type: 'bar',
            data: {
                labels: <?= json_encode($dataHari) ?>,
                datasets: [{
                    label: 'Jumlah Jadwal',
                    data: <?= json_encode($dataJumlah) ?>,
                    backgroundColor: 'rgba(13, 110, 253, 0.8)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 2,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Chart Mahasiswa per Prodi
        <?php if(!empty($dataProdi)): ?>
        const ctxProdi = document.getElementById('prodiChart').getContext('2d');
        new Chart(ctxProdi, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($dataProdi) ?>,
                datasets: [{
                    data: <?= json_encode($dataJumlahMhs) ?>,
                    backgroundColor: [
                        'rgba(13, 110, 253, 0.8)',
                        'rgba(25, 135, 84, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(13, 202, 240, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>