<?php
/**
 * -------------------------------------------------------------------------
 * Proses Tambah Data (INSERT) - POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * Berkas ini memproses semua aksi penambahan data (INSERT) ke dalam database,
 * seperti penambahan kategori baru, penambahan menu baru (beserta upload foto),
 * serta penambahan item pesanan ke dalam keranjang penjualan sementara.
 */

session_start();
// Memastikan user harus sudah login sebagai admin untuk mengakses aksi ini
if (!empty($_SESSION['admin'])) {
    require '../../config.php';
    require_once __DIR__.'/../csrf.php';
    
    // Validasi token CSRF untuk memastikan request aman dan sah
    csrf_guard();

    // -------------------------------------------------------------------------
    // UTILITY FUNCTIONS (FUNGSI PEMBANTU SANITASI & VALIDASI)
    // -------------------------------------------------------------------------

    if (!function_exists('sanitize_scalar_input')) {
        /**
         * Membersihkan input string dari karakter kontrol ASCII berbahaya
         */
        function sanitize_scalar_input($value, bool $allowNewlines = false): string
        {
            $stringValue = trim((string) $value);
            $pattern = $allowNewlines ? '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u' : '/[\x00-\x1F\x7F]/u';
            $cleaned = preg_replace($pattern, '', $stringValue);

            return $cleaned === null ? '' : trim($cleaned);
        }
    }

    if (!function_exists('get_post_string')) {
        /**
         * Mengambil data POST string secara aman
         */
        function get_post_string(string $key, bool $allowNewlines = false): string
        {
            $value = filter_input(
                INPUT_POST,
                $key,
                FILTER_UNSAFE_RAW,
                ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]
            );

            if ($value === null) {
                return '';
            }

            return sanitize_scalar_input($value, $allowNewlines);
        }
    }

    if (!function_exists('get_post_float')) {
        /**
         * Mengambil data POST float secara aman
         */
        function get_post_float(string $key): float
        {
            $value = filter_input(
                INPUT_POST,
                $key,
                FILTER_SANITIZE_NUMBER_FLOAT,
                ['flags' => FILTER_FLAG_ALLOW_FRACTION]
            );

            if ($value === null || $value === false || $value === '') {
                return 0.0;
            }

            return (float) $value;
        }
    }

    if (!function_exists('get_post_int')) {
        /**
         * Mengambil data POST integer secara aman
         */
        function get_post_int(string $key): int
        {
            $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT);
            if ($value === null || $value === false) {
                return 0;
            }

            return (int) $value;
        }
    }

    if (!function_exists('get_get_param')) {
        /**
         * Mengambil data GET string secara aman
         */
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

    // -------------------------------------------------------------------------
    // AKSI 1: TAMBAH KATEGORI MENU (GET ?kategori=tambah)
    // -------------------------------------------------------------------------
    $kategoriAction = get_get_param('kategori');
    if ($kategoriAction !== '') {
        $nama = get_post_string('kategori');
        if ($nama === '') {
            echo '<script>alert("Kategori tidak valid");history.go(-1);</script>';
            exit;
        }

        $tgl = date('j F Y, G:i');
        $data = [$nama, $tgl];
        $sql = 'INSERT INTO kategori (nama_kategori,tgl_input) VALUES(?,?)';
        $row = $config->prepare($sql);
        $row->execute($data);
        echo '<script>window.location="../../index.php?page=kategori&&success=tambah-data"</script>';
    }

    // -------------------------------------------------------------------------
    // AKSI 2: TAMBAH MENU LAYANAN BARU (GET ?menu=tambah)
    // -------------------------------------------------------------------------
    $menuAction = get_get_param('menu');
    if ($menuAction !== '') {
        $id = get_post_string('id');
        // Validasi format ID Menu (harus alfanumerik & minus, contoh: MN-01 atau MN001)
        if ($id === '' || !preg_match('/^[A-Za-z0-9-]+$/', $id)) {
            echo '<script>alert("ID Menu tidak valid");history.go(-1);</script>';
            exit;
        }

        $kategori = get_post_string('kategori');
        $nama = get_post_string('nama');
        $jual = get_post_float('jual');
        $stok = get_post_int('stok');
        $tgl = get_post_string('tgl');
        
        // Tentukan klasifikasi tipe menu berdasarkan ID kategori terpilih
        // Kategori: 1=Makanan Utama, 2=Minuman Segar, 3=Cemilan/Snack, 4=Dessert
        $katId = (int)$kategori;
        if ($katId === 2) {
            $tipe = 'minuman';
        } elseif ($katId === 3) {
            $tipe = 'snack';
        } elseif ($katId === 4) {
            $tipe = 'dessert';
        } else {
            $tipe = 'makanan';
        }

        // Memproses Upload File Foto Menu
        $fotoName = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['foto']['tmp_name'];
            $fileName = $_FILES['foto']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Whitelist ekstensi berkas gambar yang diperbolehkan demi keamanan
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($fileExtension, $allowedExtensions, true)) {
                // Generate nama berkas unik menggunakan MD5 hash untuk menghindari duplikasi
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $uploadFileDir = '../../assets/img/menu/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0777, true);
                }
                $dest_path = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $fotoName = $newFileName;
                }
            }
        }

        $data = [
            $id,
            $kategori,
            $nama,
            $jual,
            $tipe,
            $stok,
            $fotoName,
            $tgl,
        ];

        $sql = 'INSERT INTO menu_layanan (id_menu,id_kategori,nama,harga,tipe,stok,foto,tgl_input)
                            VALUES (?,?,?,?,?,?,?,?) ';
        $row = $config->prepare($sql);
        if ($row->execute($data)) {
            echo '<script>window.location="../../index.php?page=menu&success=tambah-data"</script>';
        } else {
            $errorInfo = $row->errorInfo();
            echo '<script>alert("Gagal menambahkan menu: ' . addslashes($errorInfo[2] ?? 'Unknown error') . '");history.go(-1);</script>';
        }
    }

    // -------------------------------------------------------------------------
    // AKSI 3: TAMBAH ITEM KE KERANJANG PENJUALAN (GET ?jual=tambah)
    // -------------------------------------------------------------------------
    $jualAction = get_get_param('jual');
    if ($jualAction !== '') {
        // Melakukan validasi token CSRF khusus yang dilewatkan lewat parameter GET URL
        csrf_require_token(get_get_param('csrf_token'));

        $id = get_get_param('id');
        if ($id === '' || !preg_match('/^[A-Za-z0-9-]+$/', $id)) {
            echo '<script>alert("Menu tidak valid");window.location="../../index.php?page=jual"</script>';
            exit;
        }

        $kasir = get_get_param('id_kasir');
        if ($kasir === '' || !ctype_digit($kasir)) {
            echo '<script>alert("Kasir tidak valid");window.location="../../index.php?page=jual"</script>';
            exit;
        }

        // Mengambil data detail menu untuk mendapatkan harga jual terbarunya
        $sql = 'SELECT * FROM menu_layanan WHERE id_menu = ?';
        $row = $config->prepare($sql);
        $row->execute([$id]);
        $hsl = $row->fetch();

        if ($hsl) {
            $jumlah = 1; // Default tambah porsi awal = 1
            $total = (float) $hsl['harga'];
            $tgl = date('j F Y, G:i');
            
            // Meja default '1', tipe order default 'dine-in'
            $mejaDefault = '1';
            $tipePesananDefault = 'dine-in';

            $data1 = [$id, $kasir, $jumlah, $mejaDefault, $tipePesananDefault, $total, $tgl];

            // Masukkan data menu ke keranjang belanja sementara (penjualan_temp)
            $sql1 = 'INSERT INTO penjualan_temp (id_menu,id_member,jumlah,meja,tipe_pesanan,total,tanggal_input) VALUES (?,?,?,?,?,?,?)';
            $row1 = $config->prepare($sql1);
            $row1->execute($data1);

            echo '<script>window.location="../../index.php?page=jual&success=tambah-data"</script>';
        } else {
            echo '<script>alert("Menu tidak ditemukan!");
                                        window.location="../../index.php?page=jual#keranjang"</script>';
        }
    }
}
?>
