<?php 
/**
 * -------------------------------------------------------------------------
 * Router Utama Aplikasi POS Resto - Java Turtle
 * -------------------------------------------------------------------------
 * File ini bertindak sebagai gerbang masuk utama (entry point) aplikasi.
 * Berkas ini memeriksa sesi login admin, melindunginya dengan CSRF token,
 * dan memuat tampilan template header, sidebar, footer, serta modul
 * halaman yang diminta via parameter $_GET['page'] secara aman.
 */

	@ob_start();
	session_start();

        // Memeriksa apakah pengguna telah berhasil login dan memiliki data sesi 'admin'
        if(!empty($_SESSION['admin'])){
                require 'config.php';
                require_once 'fungsi/csrf.php';
                
                // Membuat token CSRF baru jika belum ada untuk perlindungan form
                csrf_get_token();
                
                // Menjalankan pengecekan token CSRF pada setiap request POST untuk mencegah serangan hijacking
                csrf_guard();
                
                // Memasukkan berkas pembantu query select data
                include $view;
                $lihat = new view($config);
		$toko = $lihat -> toko();
		
		// ---------------------------------------------------------
		// RENDER VIEW TEMPLATE ADMIN
		// ---------------------------------------------------------
		include 'admin/template/header.php';
		include 'admin/template/sidebar.php';
		
                $role = 'admin';
                
                // Mendefinisikan modul/halaman admin yang diizinkan untuk diakses (whitelist)
                $allowedPages = array(
                        'menu',
                        'menu/details',
                        'menu/edit',
                        'kategori',
                        'pengaturan',
                        'jual',
                        'laporan',
                        'user'
                );
                
                // Mengatur routing halaman dinamis secara aman
                if(!empty($_GET['page'])){
                        $requestedPage = (string) $_GET['page'];
                        
                        // Memastikan halaman yang diminta ada dalam daftar whitelist
                        if(in_array($requestedPage, $allowedPages, true)){
                                // Melakukan sanitasi path agar terhindar dari celah Directory Traversal
                                $moduleRoot = realpath(__DIR__.'/admin/module');
                                $modulePath = realpath(__DIR__.'/admin/module/'.$requestedPage.'/index.php');
                                
                                // Memastikan path modul benar-benar berada di bawah folder module dan berkasnya ada
                                if($moduleRoot !== false && $modulePath !== false && strpos($modulePath, $moduleRoot) === 0 && is_file($modulePath)){
                                        include $modulePath;
                                }else{
                                        // Fallback ke halaman dashboard jika path tidak valid
                                        include 'admin/template/home.php';
                                }
                        }else{
                                // Fallback ke dashboard jika halaman tidak ada di whitelist
                                include 'admin/template/home.php';
                        }
                }else{
                        // Tampilan default jika tidak ada parameter page
                        include 'admin/template/home.php';
                }
                
		include 'admin/template/footer.php';
		
	}else{
                // Jika belum login, otomatis alihkan ke halaman login
		echo '<script>window.location="login.php";</script>';
		exit;
	}
?>
