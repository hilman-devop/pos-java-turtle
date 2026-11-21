<?php
/**
 * -------------------------------------------------------------------------
 * Proses Hapus Data (DELETE) - POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * Berkas ini menangani semua operasi penghapusan data (DELETE) dari database,
 * meliputi hapus kategori menu, hapus menu makanan/minuman, hapus item dari
 * keranjang kasir (penjualan_temp), hapus seluruh isi keranjang, dan pembersihan
 * riwayat seluruh laporan penjualan. Semua aksi dilindungi validasi CSRF token.
 */

session_start();
// Memastikan user harus sudah login sebagai admin untuk mengakses aksi ini
if (!empty($_SESSION['admin'])) {
    require '../../config.php';
    require_once __DIR__.'/../csrf.php';
    
    // -------------------------------------------------------------------------
    // UTILITY FUNCTIONS (FUNGSI PEMBANTU SANITASI & VALIDASI)
    // -------------------------------------------------------------------------

    if (!function_exists('sanitize_scalar_input')) {
        function sanitize_scalar_input($value, bool $allowNewlines = false): string
        {
            $stringValue = trim((string) $value);
            $pattern = $allowNewlines ? '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u' : '/[\x00-\x1F\x7F]/u';
            $cleaned = preg_replace($pattern, '', $stringValue);

            return $cleaned === null ? '' : trim($cleaned);
        }
    }

    if (!function_exists('get_get_param')) {
        function get_get_param(string $key): string
        {
            $value = filter_input(
                INPUT_GET,
                $key,
                FILTER_UNSAFE_RAW,
                ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]
            );

            if ($value === null) {
                return '';
            }

            return sanitize_scalar_input($value);
        }
    }

    // Mengambil dan memvalidasi token CSRF dari parameter GET untuk keamanan link aksi
    $csrfToken = filter_input(
        INPUT_GET,
        'csrf_token',
        FILTER_UNSAFE_RAW,
        ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]
    );
    csrf_require_token($csrfToken ?? '');

    // Pemeriksaan hak akses pengguna (Role-based access control)
    $role = $_SESSION['admin']['role'] ?? 'kasir';
    if ($role === 'kasir') {
        // Kasir hanya diizinkan menghapus item keranjang belanja atau membersihkan keranjang
        $isJual = (get_get_param('jual') !== '');
        $isPenjualan = (get_get_param('penjualan') !== '');
        if (!$isJual && !$isPenjualan) {
            echo '<script>alert("Akses Ditolak! Kasir tidak memiliki hak akses untuk tindakan ini.");history.go(-1);</script>';
            exit;
        }
    }

    // -------------------------------------------------------------------------
    // AKSI 1: HAPUS KATEGORI MENU (GET ?kategori=hapus)
    // -------------------------------------------------------------------------
    if (get_get_param('kategori') !== '') {
        $id = get_get_param('id');
        if ($id === '' || !ctype_digit($id)) {
            echo '<script>alert("Data kategori tidak valid");history.go(-1);</script>';
            exit;
        }

        $sql = 'DELETE FROM kategori WHERE id_kategori=?';
        $row = $config->prepare($sql);
        $row->execute([$id]);
        echo '<script>window.location="../../index.php?page=kategori&&remove=hapus-data"</script>';
    }

    // -------------------------------------------------------------------------
    // AKSI 2: HAPUS MENU LAYANAN (GET ?menu=hapus)
    // -------------------------------------------------------------------------
    if (get_get_param('menu') !== '') {
        $id = get_get_param('id'); // id_menu
        if ($id === '' || !preg_match('/^[A-Za-z0-9-]+$/', $id)) {
            echo '<script>alert("Data menu tidak valid");history.go(-1);</script>';
            exit;
        }

        $sql = 'DELETE FROM menu_layanan WHERE id_menu=?';
        $row = $config->prepare($sql);
        $row->execute([$id]);
        echo '<script>window.location="../../index.php?page=menu&&remove=hapus-data"</script>';
    }

    // -------------------------------------------------------------------------
    // AKSI 3: HAPUS SATU ITEM DARI KERANJANG KASIR (GET ?jual=hapus)
    // -------------------------------------------------------------------------
    if (get_get_param('jual') !== '') {
        $barangId = get_get_param('brg'); // id_menu
        $penjualanId = get_get_param('id'); // id_penjualan_temp
        if ($barangId === '' || !preg_match('/^[A-Za-z0-9-]+$/', $barangId) || $penjualanId === '' || !ctype_digit($penjualanId)) {
            echo '<script>alert("Data penjualan tidak valid");history.go(-1);</script>';
            exit;
        }

        $sql = 'DELETE FROM penjualan_temp WHERE id_penjualan_temp=?';
        $row = $config->prepare($sql);
        $row->execute([$penjualanId]);
        echo '<script>window.location="../../index.php?page=jual"</script>';
    }

    // -------------------------------------------------------------------------
    // AKSI 4: KOSONGKAN/BERSIHKAN SELURUH KERANJANG KASIR (GET ?penjualan=hapus)
    // -------------------------------------------------------------------------
    if (get_get_param('penjualan') !== '') {
        $sql = 'DELETE FROM penjualan_temp';
        $row = $config->prepare($sql);
        $row->execute();
        echo '<script>window.location="../../index.php?page=jual"</script>';
    }

    // -------------------------------------------------------------------------
    // AKSI 5: RESET / HAPUS SELURUH RIWAYAT TRANSAKSI PENJUALAN (GET ?laporan=hapus)
    // -------------------------------------------------------------------------
    if (get_get_param('laporan') !== '') {
        $sql = 'DELETE FROM penjualan';
        $row = $config->prepare($sql);
        $row->execute();
        echo '<script>window.location="../../index.php?page=laporan&remove=hapus"</script>';
    }
}
?>
