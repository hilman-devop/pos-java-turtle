<?php
/**
 * -------------------------------------------------------------------------
 * Halaman Logout Pengguna - POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * Berkas ini menangani proses keluar log (logout) dari aplikasi resto.
 * File ini akan menghapus semua data sesi (session_destroy) dan mengarahkan
 * kembali pengguna ke halaman login dengan notifikasi SweetAlert2 yang menawan.
 */

	session_start();
	
	// Menghancurkan seluruh sesi login aktif
	session_destroy();
	
	// Menampilkan pesan sukses keluar menggunakan SweetAlert2 dan mengalihkan ke login.php
	echo '
<!DOCTYPE html>
<html>
<head>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { font-family: "Outfit", sans-serif; }</style>
</head>
<body>
<script>
    Swal.fire({
        title: "Sukses Logout!",
        text: "Anda Telah Berhasil Keluar.",
        icon: "success",
        confirmButtonColor: "#285c64"
    }).then(() => {
        window.location.href = "login.php";
    });
</script>
</body>
</html>';
?>
