<!DOCTYPE html>
<html lang="id">

<head>
    <!--
     * -------------------------------------------------------------------------
     * Template Header Navigasi & Styling - POS Resto Java Turtle
     * -------------------------------------------------------------------------
     * File ini bertindak sebagai berkas pembuka HTML, mengimpor stylesheet CSS
     * eksternal, font khusus (Outfit), inisialisasi dark mode bebas kedipan,
     * perpustakaan SweetAlert2 CDN, jQuery, serta inisialisasi token CSRF global.
    -->

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Java Turtle Lodge Management System">
    <meta name="author" content="Yui & Elz">

    <title>Java Turtle Lodge - Reservation & Tour Management</title>

    <script>
        // Mencegah kedipan layar (flicker-free) saat memuat tema gelap dari penyimpanan lokal (localStorage)
        (function() {
            var theme = localStorage.getItem('theme');
            if (theme === 'dark') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>

    <!-- SweetAlert2 CDN untuk notifikasi pop-up modern premium -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Mengimpor ikon Font Awesome -->
    <link href="sb-admin/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <!-- Mengimpor Font Google Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Memuat style CSS template SB Admin 2 -->
    <link href="sb-admin/css/sb-admin-2.min.css" rel="stylesheet">
    <!-- Memuat style CSS DataTables Bootstrap 4 -->
    <link rel="stylesheet" href="sb-admin/vendor/datatables/dataTables.bootstrap4.css" />
    
    <!-- Custom styling untuk tema warna Java Turtle Lodge (Light & Dark Mode) -->
    <style>
        :root {
            --bg-color: #f7f9fa;
            --card-bg: #ffffff;
            --text-color: #333333;
            --text-muted: #858796;
            --border-color: rgba(0, 0, 0, 0.08);
            --topbar-bg: #ffffff;
            --light-bg: #f8f9fc;
            --table-striped-bg: rgba(0, 0, 0, 0.02);
            --table-hover-bg: rgba(0, 0, 0, 0.04);
            --input-bg: #ffffff;
            --input-border: rgba(0, 0, 0, 0.12);
        }

        /* Variabel warna khusus ketika class dark-mode aktif pada tag html */
        html.dark-mode body {
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --text-color: #e0e0e0;
            --text-muted: #a0a0a0;
            --border-color: rgba(255, 255, 255, 0.1);
            --topbar-bg: #1e1e1e;
            --light-bg: #2d2d2d;
            --table-striped-bg: rgba(255, 255, 255, 0.02);
            --table-hover-bg: rgba(255, 255, 255, 0.04);
            --input-bg: #2d2d2d;
            --input-border: rgba(255, 255, 255, 0.15);
        }

        /* 🌟 OVERRIDE TEMA GELAP BOOTSTRAP (DARK MODE FIX) 🌟 */
        html.dark-mode #content-wrapper {
            background-color: var(--bg-color) !important;
        }

        html.dark-mode .bg-white {
            background-color: var(--card-bg) !important;
        }

        html.dark-mode .bg-light {
            background-color: var(--light-bg) !important;
        }

        html.dark-mode .card-footer {
            background-color: var(--card-bg) !important;
            border-top: 1px solid var(--border-color) !important;
        }

        html.dark-mode .text-gray-800 {
            color: var(--text-color) !important;
        }

        html.dark-mode .text-gray-600 {
            color: var(--text-muted) !important;
        }

        html.dark-mode .card[style*="background"] {
            background: var(--card-bg) !important;
            background-color: var(--card-bg) !important;
        }

        html.dark-mode thead[style*="background-color"] {
            background-color: var(--light-bg) !important;
        }

        html.dark-mode div[style*="color: #333"],
        html.dark-mode div[style*="color:#333"],
        html.dark-mode span[style*="color: #333"],
        html.dark-mode span[style*="color:#333"],
        html.dark-mode td[style*="color: #333"],
        html.dark-mode td[style*="color:#333"] {
            color: var(--text-color) !important;
        }

        html.dark-mode .sidebar {
            border-right: 1px solid var(--border-color) !important;
        }

        /* Pengaturan Layout Global & Font */
        body {
            font-family: 'Outfit', sans-serif !important;
            background-color: var(--bg-color) !important;
            color: var(--text-color) !important;
            font-size: 15px !important;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        
        /* Gradasi Warna Sidebar (Teal & Golden Premium) */
        .bg-gradient-primary {
            background: linear-gradient(180deg, #285c64 10%, #153439 100%) !important;
        }
        
        .sidebar .nav-item .nav-link {
            font-weight: 500;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-item .nav-link:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            color: #f5ebd6 !important;
        }
        
        .sidebar-brand-text {
            font-weight: 700 !important;
            letter-spacing: 1px;
            color: #4ed9c3 !important;
        }
        
        .collapse-inner {
            background-color: var(--card-bg) !important;
            border: 1px solid var(--border-color);
            transition: background-color 0.2s ease;
        }
        
        .collapse-inner .collapse-item {
            font-weight: 500 !important;
            color: #285c64 !important;
            transition: all 0.2s ease;
        }
        
        .collapse-inner .collapse-item:hover {
            background-color: #f5ebd6 !important;
            color: #153439 !important;
            font-weight: 600 !important;
            transform: translateX(4px);
        }
        
        /* Desain Premium Card */
        .card {
            border: none !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
            overflow: hidden;
            background: var(--card-bg) !important;
            margin-bottom: 25px;
            color: var(--text-color) !important;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        
        .card-header {
            background: #285c64 !important;
            border-bottom: none !important;
            padding: 15px 20px !important;
        }
        
        .card-header h5 {
            margin: 0;
            font-weight: 600;
            color: #ffffff !important;
            font-size: 17px !important;
            letter-spacing: 0.3px;
        }
        
        .card-body {
            padding: 20px !important;
            background-color: var(--card-bg) !important;
            color: var(--text-color) !important;
        }
        
        /* Desain Elemen Input Form */
        .form-control {
            border-radius: 8px !important;
            border: 1px solid var(--input-border) !important;
            padding: 8px 12px !important;
            font-size: 15px !important;
            background-color: var(--input-bg) !important;
            color: var(--text-color) !important;
            transition: all 0.2s ease-in-out;
        }
        
        .form-control:focus {
            border-color: #285c64 !important;
            box-shadow: 0 0 0 0.2rem rgba(40, 92, 100, 0.15) !important;
            background-color: var(--input-bg) !important;
            color: var(--text-color) !important;
        }

        select.form-control {
            background-color: var(--input-bg) !important;
            color: var(--text-color) !important;
        }
        
        /* Desain Tombol Aksi */
        .btn {
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            padding: 9px 18px !important;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        .btn-primary {
            background-color: #285c64 !important;
            border-color: #285c64 !important;
            color: #ffffff !important;
        }
        
        .btn-primary:hover {
            background-color: #1b4146 !important;
            border-color: #1b4146 !important;
            box-shadow: 0 4px 12px rgba(40, 92, 100, 0.25) !important;
        }
        
        .btn-success {
            background-color: #3bbfa8 !important;
            border-color: #3bbfa8 !important;
            color: #153439 !important;
        }
        
        .btn-success:hover {
            background-color: #2aa18c !important;
            border-color: #2aa18c !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(59, 191, 168, 0.3) !important;
        }
        
        .btn-warning {
            background-color: #e3a830 !important;
            border-color: #e3a830 !important;
            color: #ffffff !important;
        }
        
        .btn-warning:hover {
            background-color: #c99324 !important;
            border-color: #c99324 !important;
            box-shadow: 0 4px 12px rgba(227, 168, 48, 0.25) !important;
        }
 
        .btn-danger {
            background-color: #d9534f !important;
            border-color: #d9534f !important;
        }
 
        .btn-danger:hover {
            background-color: #c9302c !important;
            box-shadow: 0 4px 12px rgba(217, 83, 79, 0.25) !important;
        }
 
        .btn-xs {
            padding: 4px 10px !important;
            font-size: 12px !important;
            border-radius: 4px !important;
        }
        
        h4 {
            color: #285c64;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .text-gray-800 {
            color: var(--text-color) !important;
        }

        .text-gray-600 {
            color: var(--text-muted) !important;
        }

        .bg-white {
            background-color: var(--card-bg) !important;
        }

        .bg-light {
            background-color: var(--light-bg) !important;
        }
        
        /* Desain Tabel */
        .table {
            color: var(--text-color) !important;
        }
        
        .table thead th {
            font-weight: 600;
            font-size: 14px !important;
            letter-spacing: 0.3px;
            border-bottom: none;
            text-transform: uppercase;
            color: var(--text-color) !important;
        }
        
        .table td {
            font-size: 15px !important;
            vertical-align: middle !important;
            color: var(--text-color) !important;
            border-top: 1px solid var(--border-color) !important;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: var(--table-striped-bg) !important;
        }

        .table-hover tbody tr:hover {
            background-color: var(--table-hover-bg) !important;
        }

        .navbar-light {
            background-color: var(--topbar-bg) !important;
            border-bottom: 1px solid var(--border-color) !important;
        }
        
        /* Desain Pop-up Kustom SweetAlert2 di Mode Gelap */
        .swal2-popup {
            background-color: var(--card-bg) !important;
            color: var(--text-color) !important;
            font-family: 'Outfit', sans-serif !important;
            border: 1px solid var(--border-color) !important;
        }
        .swal2-title, .swal2-html-container {
            color: var(--text-color) !important;
        }

        /* Penyesuaian Komponen DataTables Mode Gelap */
        html.dark-mode thead th,
        html.dark-mode thead td,
        html.dark-mode .table thead th,
        html.dark-mode .thead-light th,
        html.dark-mode thead.bg-light th,
        html.dark-mode thead[style*="background-color"] th,
        html.dark-mode thead[style*="background-color"] td {
            background-color: var(--light-bg) !important;
            background: var(--light-bg) !important;
            color: var(--text-color) !important;
            border-bottom: 2px solid var(--border-color) !important;
        }

        html.dark-mode tr[style*="background:#fff"] td,
        html.dark-mode tr[style*="background:#ffffff"] td,
        html.dark-mode tr[style*="background: #fff"] td,
        html.dark-mode tr[style*="background: #ffffff"] td,
        html.dark-mode td[style*="background:#fff"],
        html.dark-mode td[style*="background:#ffffff"],
        html.dark-mode td[style*="background: #fff"],
        html.dark-mode td[style*="background: #ffffff"],
        html.dark-mode tfoot tr,
        html.dark-mode tfoot td {
            background: var(--light-bg) !important;
            background-color: var(--light-bg) !important;
            color: var(--text-color) !important;
            border-top: 2px solid var(--border-color) !important;
        }

        html.dark-mode .custom-select,
        html.dark-mode select.form-control,
        html.dark-mode input[type="search"],
        html.dark-mode .dataTables_filter input {
            background-color: var(--input-bg) !important;
            color: var(--text-color) !important;
            border: 1px solid var(--input-border) !important;
        }

        html.dark-mode .page-link {
            background-color: var(--card-bg) !important;
            border-color: var(--border-color) !important;
            color: var(--text-color) !important;
        }

        html.dark-mode .page-item.disabled .page-link {
            background-color: var(--light-bg) !important;
            border-color: var(--border-color) !important;
            color: var(--text-muted) !important;
        }

        html.dark-mode .page-item.active .page-link {
            background-color: #285c64 !important;
            border-color: #285c64 !important;
            color: #ffffff !important;
        }

        html.dark-mode .dataTables_info,
        html.dark-mode .dataTables_length,
        html.dark-mode .dataTables_filter {
            color: var(--text-color) !important;
        }

        html.dark-mode .form-control:disabled, 
        html.dark-mode .form-control[readonly] {
            background-color: var(--light-bg) !important;
            color: var(--text-muted) !important;
            border-color: var(--border-color) !important;
        }
    </style>

    <!-- Interseptor JavaScript alert() standar dialihkan ke SweetAlert2 secara global -->
    <script>
        window.alert = function(message) {
            Swal.fire({
                title: 'Pemberitahuan',
                text: message,
                icon: 'info',
                confirmButtonColor: '#285c64'
            });
        };
    </script>

    <!-- Javascript inti Bootstrap & JQuery -->
    <script src="sb-admin/vendor/jquery/jquery.min.js"></script>
    <script src="sb-admin/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="sb-admin/vendor/jquery-easing/jquery.easing.min.js"></script>
    
    <!-- Meneruskan token CSRF dinamis ke dalam variabel global Javascript -->
    <?php if (function_exists('csrf_get_token')): ?>
    <script>
        window.csrfToken = '<?php echo htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8'); ?>';
    </script>
    <?php endif; ?>
</head>

<body id="page-top">
    <!-- Pembungkus Halaman Utama -->
    <div id="wrapper">