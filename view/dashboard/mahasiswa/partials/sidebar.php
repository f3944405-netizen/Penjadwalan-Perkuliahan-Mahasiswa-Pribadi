<?php
if (!isset($_SESSION['role'])) {
    exit('Akses ditolak');
}
?>

<div class="bg-dark text-white p-3" style="min-height:100vh;width:250px;">
   
<img src="/public/assets/img/poltek.png"
     class="img-fluid mb-3 mx-auto d-block"
     style="width: 100px;">


    <ul class="nav flex-column">

        <!-- SIDEBAR MAHASISWA -->
        <?php if ($_SESSION['role'] === 'mahasiswa'): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="/view/dashboard/mahasiswa/mahasiswa.php">Beranda</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="/view/dashboard/mahasiswa/jadwal/jadwal_kuliah.php">Jadwal Kuliah</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="/view/dashboard/mahasiswa/data/data_mahasiswa.php">Data Pribadi</a>
            </li>
        <?php endif; ?>

        <!-- SIDEBAR ADMIN -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="/view/dashboard/admin/dashboard_admin.php">Dashboard Admin</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="/view/dashboard/admin/mahasiswa/data_mahasiswa.php">Data Mahasiswa</a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="/view/dashboard/admin/jadwal/data_jadwal.php">Kelola Jadwal</a>
            </li>
        <?php endif; ?>

        <li class="nav-item mt-3">
            <a class="nav-link text-danger" href="/config/proces/logout.php">Logout</a>
        </li>

    </ul>
</div>
