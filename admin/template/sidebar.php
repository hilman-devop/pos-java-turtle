<!-- 
  * -------------------------------------------------------------------------
  * Template Sidebar & Topbar - POS Resto Java Turtle
  * -------------------------------------------------------------------------
  * File ini merender menu samping (sidebar) dan bilah navigasi atas (topbar).
  * Di dalamnya terdapat menu dinamis sesuai hak akses (role), profil pengguna,
  * serta saklar interaktif (switch button) untuk memicu Light/Dark mode.
-->
<?php 
    // Mengambil ID Member dari sesi login aktif untuk memuat foto profil terbarunya
    $id = $_SESSION['admin']['id_member'];
    $hasil_profil = $lihat->member_edit($id);
    
    // Sesuai revisi satu role admin: set default role ke admin
    $role = 'admin';
?>
<!-- Sidebar Utama -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Identitas Brand / Logo Resto -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
        <img src="assets/img/logo.jpeg" alt="Logo JTL" style="max-height: 40px; border-radius: 6px; margin-right: 8px;">
        <div class="sidebar-brand-text">POS JTL</div>
    </a>

    <!-- Garis Pembatas -->
    <hr class="sidebar-divider my-0">

    <!-- Menu Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="index.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider">
    
    <!-- Menu Dropdown: Data Master (Kelola Menu & Kategori) -->
    <li class="nav-item active">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true"
            aria-controls="collapseTwo">
            <i class="fas fa-fw fa-hamburger"></i>
            <span>Data Master</span>
        </a>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="index.php?page=menu"><?= ($role === 'admin') ? 'Kelola Menu' : 'Lihat Menu'; ?></a>
                <?php if ($role === 'admin') { ?>
                <a class="collapse-item" href="index.php?page=kategori">Kategori</a>
                <?php } ?>
            </div>
        </div>
    </li>
    
    <!-- Menu Dropdown: Transaksi (Kasir & Laporan Keuangan) -->
    <li class="nav-item active">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapse3" aria-expanded="true"
            aria-controls="collapse3">
            <i class="fas fa-fw fa-cash-register"></i>
            <span>Transaksi</span>
        </a>
        <div id="collapse3" class="collapse" aria-labelledby="heading3" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="index.php?page=jual">Kasir</a>
                <?php if ($role === 'admin') { ?>
                <a class="collapse-item" href="index.php?page=laporan">Laporan Penjualan</a>
                <?php } ?>
            </div>
        </div>
    </li>
    
    <!-- Menu Pengaturan Identitas Resto (Khusus Admin) -->
    <?php if ($role === 'admin') { ?>
    <hr class="sidebar-divider">
    <li class="nav-item active">
        <a class="nav-link" href="index.php?page=pengaturan">
            <i class="fas fa-fw fa-cogs"></i>
            <span>Pengaturan Resto</span></a>
    </li>
    <?php } ?>
    
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Tombol untuk menyusutkan sidebar -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- Akhir dari Sidebar -->

<!-- Pembungkus Konten Utama -->
<div id="content-wrapper" class="d-flex flex-column">

    <!-- Konten Utama (Main Content) -->
    <div id="content">

        <!-- Bilah Navigasi Atas (Topbar) -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

            <!-- Tombol Hamburger (Hanya muncul di tampilan Mobile) -->
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>
            
            <!-- Judul Identitas Toko/Restoran -->
            <h5 class="d-lg-block d-none mt-2"><b><?php echo htmlspecialchars($toko['nama_toko'] ?? 'Resto', ENT_QUOTES, 'UTF-8');?>, <?php echo htmlspecialchars($toko['alamat_toko'] ?? '', ENT_QUOTES, 'UTF-8');?></b></h5>
            
            <ul class="navbar-nav ml-auto">
                
                <!-- Saklar Pengubah Tema (Theme Toggler - Light / Dark Mode) -->
                <li class="nav-item d-flex align-items-center mr-3">
                    <button id="themeToggleBtn" class="btn btn-link p-0 border-0" style="font-size: 20px; outline: none; box-shadow: none; text-decoration: none;" title="Ubah Mode Terang/Gelap">
                        <i class="fas fa-moon text-primary" id="themeToggleIcon" style="font-size: 20px; transition: transform 0.3s ease;"></i>
                    </button>
                </li>

                <!-- Menu Dropdown Informasi & Profil Pengguna -->
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <img class="img-profile rounded-circle"
                            src="assets/img/user/<?php echo htmlspecialchars($hasil_profil['gambar'] ?? 'unnamed.jpg', ENT_QUOTES, 'UTF-8');?>">
                        <span
                            class="mr-2 d-none d-lg-inline text-gray-600 small ml-2"><?php echo htmlspecialchars($hasil_profil['nm_member'] ?? 'Admin', ENT_QUOTES, 'UTF-8');?></span>
                        <i class="fas fa-angle-down"></i>
                    </a>
                    
                    <!-- Isi Dropdown Profil (Profile & Logout) -->
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                        aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="index.php?page=user">
                            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                            Profil Pengguna
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Keluar (Logout)
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
        <!-- Akhir dari Topbar -->
        
        <!-- Kontainer Halaman Dinamis (Fluid Grid) -->
        <div class="container-fluid">

<!-- Script Pengubah Tema Light/Dark Mode -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggleBtn = document.getElementById('themeToggleBtn');
    var toggleIcon = document.getElementById('themeToggleIcon');
    
    // Mengubah ikon tombol berdasarkan status dark-mode yang aktif
    function updateIcon() {
        if (document.documentElement.classList.contains('dark-mode')) {
            toggleIcon.className = 'fas fa-sun text-warning';
            toggleIcon.style.transform = 'rotate(360deg)';
        } else {
            toggleIcon.className = 'fas fa-moon text-primary';
            toggleIcon.style.transform = 'rotate(0deg)';
        }
    }
    
    if (toggleBtn) {
        updateIcon();
        
        // Pemicu event klik tombol saklar tema
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (document.documentElement.classList.contains('dark-mode')) {
                document.documentElement.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            }
            updateIcon();
        });
    }
});
</script>