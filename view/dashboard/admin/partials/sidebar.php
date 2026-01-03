<?php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    exit('Akses ditolak');
}
?>

<div class="bg-dark text-white p-3" style="min-height:100vh;width:250px;">
    <h4 class="text-center mb-4">POLIBATAM ADMIN</h4>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link text-white" href="/view/dashboard/admin/admin_dashboard.php">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white" href="/view/dashboard/admin/mahasiswa/kelola_mahasiswa.php">
                <i class="bi bi-person-badge"></i> Kelola Mahasiswa
            </a>
        </li>

        <!-- TAMBAHAN: Kelola Dosen -->
        <li class="nav-item">
            <a class="nav-link text-white" href="/view/dashboard/admin/dosen/kelola_dosen.php">
                <i class="bi bi-person-workspace"></i> Kelola Dosen
            </a>
        </li>

        <!-- TAMBAHAN: Kelola Ruangan -->
        <li class="nav-item">
            <a class="nav-link text-white" href="/view/dashboard/admin/ruangan/kelola_rungan.php">
                <i class="bi bi-building"></i> Kelola Ruangan
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link text-white" href="/view/dashboard/admin/jadwal/data_jadwal.php">
                <i class="bi bi-calendar-week"></i> Kelola Jadwal
            </a>
        </li>

        <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="/config/proces/logout.php">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </li>
    </ul>
</div>  