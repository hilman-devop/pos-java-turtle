<?php
/**
 * -------------------------------------------------------------------------
 * Proses Edit Data (UPDATE) - POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * Berkas ini menangani semua aksi perubahan data (UPDATE) ke database,
 * seperti konfigurasi toko/resto, edit kategori, restok menu, edit detail menu
 * (termasuk upload gambar baru & pembersihan gambar lama), upload foto profil
 * user, ganti password, update item di keranjang belanja, serta pencarian kamar/menu.
 */

session_start();
// Memastikan user harus sudah login sebagai admin untuk mengakses aksi ini
if (!empty($_SESSION['admin'])) {
    require '../../config.php';
    require_once __DIR__.'/../csrf.php';
    
    // Validasi token CSRF untuk menjamin keamanan dari serangan request forgery
    csrf_guard();

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

    if (!function_exists('get_post_string')) {
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
    // AKSI 1: EDIT PENGATURAN TOKO/RESTO (GET ?pengaturan=ubah)
    // -------------------------------------------------------------------------
    if (get_get_param('pengaturan') !== '') {
        $nama = get_post_string('namatoko');
        $alamat = get_post_string('alamat');
        $kontak = get_post_string('kontak');
        $pemilik = get_post_string('pemilik');
        $footer = get_post_string('footer_nota');
        $id = '1'; // Toko selalu memiliki id = 1

        $data = [$nama, $alamat, $kontak, $pemilik, $footer, $id];
        $sql = 'UPDATE toko SET nama_toko=?, alamat_toko=?, tlp=?, nama_pemilik=?, footer_nota=? WHERE id_toko = ?';
        $row = $config->prepare($sql);
        $row->execute($data);
        echo '<script>window.location="../../index.php?page=pengaturan&success=edit-data"</script>';
    }

    // -------------------------------------------------------------------------
    // AKSI 2: EDIT NAMA KATEGORI MENU (GET ?kategori=ubah)
    // -------------------------------------------------------------------------
    if (get_get_param('kategori') !== '') {
        $nama = get_post_string('kategori');
        $id = get_post_string('id');
        if ($id === '' || !ctype_digit($id)) {
            echo '<script>alert("Data kategori tidak valid");history.go(-1);</script>';
            exit;
        }

        $data = [$nama, $id];
        $sql = 'UPDATE kategori SET nama_kategori=? WHERE id_kategori=?';
        $row = $config->prepare($sql);
        $row->execute($data);
        echo '<script>window.location="../../index.php?page=kategori&uid='.$id.'&success-edit=edit-data"</script>';
    }

    // -------------------------------------------------------------------------
    // AKSI 3: RESTOK / TAMBAH QUANTITY STOK MENU (GET ?stok=tambah)
    // -------------------------------------------------------------------------
    if (get_get_param('stok') !== '') {
        $restok = get_post_int('restok');
        $id = get_post_string('id'); // id_menu
        if ($id === '' || !preg_match('/^[A-Za-z0-9-]+$/', $id)) {
            echo '<script>alert("Menu tidak valid");history.go(-1);</script>';
            exit;
        }

        // Ambil stok lama untuk ditambahkan dengan nilai restok baru
        $sqlS = 'select * from menu_layanan WHERE id_menu=?';
        $rowS = $config->prepare($sqlS);
        $rowS->execute([$id]);
        $hasil = $rowS->fetch();

        $stok = $restok + (int) ($hasil['stok'] ?? 0);

        $sql = 'UPDATE menu_layanan SET stok=? WHERE id_menu=?';
        $row = $config->prepare($sql);
        $row->execute([$stok, $id]);
        echo '<script>window.location="../../index.php?page=menu&success-stok=stok-data"</script>';
    }

    // -------------------------------------------------------------------------
    // AKSI 4: EDIT DETAIL LENGKAP MENU LAYANAN (GET ?menu=ubah)
    // -------------------------------------------------------------------------
    if (get_get_param('menu') !== '') {
        $id = get_post_string('id');
        $old_id = get_post_string('old_id');
        if ($old_id === '') {
            $old_id = $id;
        }
        if ($id === '' || !preg_match('/^[A-Za-z0-9-]+$/', $id) || $old_id === '' || !preg_match('/^[A-Za-z0-9-]+$/', $old_id)) {
            echo '<script>alert("Menu tidak valid");history.go(-1);</script>';
            exit;
        }

        $kategori = get_post_string('kategori');
        $nama = get_post_string('nama');
        $jual = get_post_float('jual');
        $stok = get_post_int('stok');
        $tgl = get_post_string('tgl');
        
        // Pemetaan ulang tipe menu berdasarkan kategori terpilih
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

        // Mengambil berkas foto menu saat ini untuk di-update atau dihapus jika upload baru
        $sql_select = 'SELECT foto FROM menu_layanan WHERE id_menu=?';
        $stmt_select = $config->prepare($sql_select);
        $stmt_select->execute([$old_id]);
        $current_menu = $stmt_select->fetch();
        $old_foto = $current_menu ? $current_menu['foto'] : null;

        $fotoName = $old_foto;

        // Memproses berkas foto baru jika dikirimkan
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['foto']['tmp_name'];
            $fileName = $_FILES['foto']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($fileExtension, $allowedExtensions, true)) {
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $uploadFileDir = '../../assets/img/menu/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0777, true);
                }
                $dest_path = $uploadFileDir . $newFileName;

                // Simpan berkas baru dan hapus berkas gambar lama
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $fotoName = $newFileName;
                    if ($old_foto !== null && $old_foto !== '') {
                        $oldFilePath = $uploadFileDir . $old_foto;
                        if (is_file($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                }
            }
        }

        $data = [$id, $kategori, $nama, $jual, $tipe, $stok, $fotoName, $tgl, $old_id];
        $sql = 'UPDATE menu_layanan SET id_menu=?, id_kategori=?, nama=?, harga=?, tipe=?, stok=?, foto=?, tgl_update=? WHERE id_menu=?';
        $row = $config->prepare($sql);
        if ($row->execute($data)) {
            // Jika kode menu (id_menu) diubah, update juga relasinya di tabel transaksi keranjang dan penjualan
            if ($id !== $old_id) {
                $stmt_p1 = $config->prepare('UPDATE penjualan_temp SET id_menu = ? WHERE id_menu = ?');
                $stmt_p1->execute([$id, $old_id]);
                $stmt_p2 = $config->prepare('UPDATE penjualan SET id_menu = ? WHERE id_menu = ?');
                $stmt_p2->execute([$id, $old_id]);
            }
            echo '<script>window.location="../../index.php?page=menu/edit&menu='.$id.'&success=edit-data"</script>';
        } else {
            $errorInfo = $row->errorInfo();
            echo '<script>alert("Gagal memperbarui menu: ' . addslashes($errorInfo[2] ?? 'Unknown error') . '");history.go(-1);</script>';
        }
    }

    // -------------------------------------------------------------------------
    // AKSI 5: EDIT GAMBAR PROFIL USER/MEMBER (GET ?gambar=ubah)
    // -------------------------------------------------------------------------
    if (get_get_param('gambar') !== '') {
        $id = get_post_int('id'); // id_member
        if ($id <= 0) {
            echo '<script>alert("Data pengguna tidak valid");history.go(-1);</script>';
            exit;
        }
        set_time_limit(0);
        if (!isset($_FILES['foto']) || !is_uploaded_file($_FILES['foto']['tmp_name'])) {
            echo '<script>alert("Masukan Gambar !");window.location="../../index.php?page=user"</script>';
            exit;
        }

        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            echo '<script>alert("You can only upload JPG, PNG and GIF file");window.location="../../index.php?page=user"</script>';
            exit;
        }

        // Pemetaan mimetype ke ekstensi berkas yang aman
        $allowedTypes = [
            'image/png'   => 'png',
            'image/jpeg'  => 'jpg',
            'image/gif'   => 'gif',
            'image/jpg'   => 'jpeg',
            'image/webp'  => 'webp'
        ];

        $tmpName = $_FILES['foto']['tmp_name'];
        $fileinfo = finfo_open(FILEINFO_MIME_TYPE);
        if (!$fileinfo) {
            echo '<script>alert("You can only upload JPG, PNG and GIF file");window.location="../../index.php?page=user"</script>';
            exit;
        }

        $filetype = finfo_file($fileinfo, $tmpName);
        finfo_close($fileinfo);

        if (!isset($allowedTypes[$filetype])) {
            echo '<script>alert("You can only upload JPG, PNG and GIF file");window.location="../../index.php?page=user"</script>';
            exit;
        }

        // Batasi besar berkas gambar maksimal 4 MB
        if (round($_FILES['foto']["size"] / 1024) > 4096) {
            echo '<script>alert("WARNING !!! Besar Gambar Tidak Boleh Lebih Dari 4 MB");window.location="../../index.php?page=user"</script>';
            exit;
        }

        $uploadDir = realpath(__DIR__.'/../../assets/img/user');
        if ($uploadDir === false) {
            echo '<script>alert("Masukan Gambar !");window.location="../../index.php?page=user"</script>';
            exit;
        }

        $uploadDir .= DIRECTORY_SEPARATOR;
        $name = time().'_'.bin2hex(random_bytes(8)).'.'.$allowedTypes[$filetype];

        if (move_uploaded_file($tmpName, $uploadDir.$name)) {
            $foto2Raw = get_post_string('foto2');
            $foto2 = $foto2Raw !== '' ? basename($foto2Raw) : '';
            // Hapus gambar profil lama agar tidak menumpuk di disk
            if ($foto2 !== '') {
                $oldFile = $uploadDir.$foto2;
                if (is_file($oldFile)) {
                    unlink($oldFile);
                }
            }

            $data = [$name, $id];
            $sql = 'UPDATE member SET gambar=? WHERE member.id_member=?';
            $row = $config->prepare($sql);
            $row->execute($data);
            echo '<script>window.location="../../index.php?page=user&success=edit-data"</script>';
        } else {
            echo '<script>alert("Masukan Gambar !");window.location="../../index.php?page=user"</script>';
            exit;
        }
    }

    // -------------------------------------------------------------------------
    // AKSI 6: EDIT PROFIL DETAIL USER (NAMA, ALAMAT, TELEPON) (GET ?profil=ubah)
    // -------------------------------------------------------------------------
    if (get_get_param('profil') !== '') {
        $id = get_post_int('id');
        if ($id <= 0) {
            echo '<script>alert("Data pengguna tidak valid");history.go(-1);</script>';
            exit;
        }

        $nama = get_post_string('nama');
        $alamat = get_post_string('alamat', true);
        $tlp = get_post_string('tlp');
        $email = get_post_string('email');
        $nik = get_post_string('nik');

        $data = [$nama, $alamat, $tlp, $email, $nik, $id];
        $sql = 'UPDATE member SET nm_member=?,alamat_member=?,telepon=?,email=?,NIK=? WHERE id_member=?';
        $row = $config->prepare($sql);
        $row->execute($data);
        echo '<script>window.location="../../index.php?page=user&success=edit-data"</script>';
    }
    
    // -------------------------------------------------------------------------
    // AKSI 7: EDIT USERNAME & PASSWORD LOGIN (GET ?pass=ubah)
    // -------------------------------------------------------------------------
    if (get_get_param('pass') !== '') {
        $id = get_post_int('id');
        if ($id <= 0) {
            echo '<script>alert("Data pengguna tidak valid");history.go(-1);</script>';
            exit;
        }

        $user = get_post_string('user');
        $pass = get_post_string('pass');

        // Update data login pengguna dengan enkripsi password MD5
        $data = [$user, $pass, $id];
        $sql = 'UPDATE login SET user=?,pass=md5(?) WHERE id_member=?';
        $row = $config->prepare($sql);
        $row->execute($data);
        echo '<script>window.location="../../index.php?page=user&success=edit-data"</script>';
    }

    // -------------------------------------------------------------------------
    // AKSI 8: UPDATE QUANTITY & CATATAN ITEM KERANJANG KASIR (GET ?jual=ubah)
    // -------------------------------------------------------------------------
    if (get_get_param('jual') !== '') {
        $id = get_post_int('id'); // id_penjualan_temp
        $id_barang = get_post_string('id_barang'); // id_menu
        $jumlah = get_post_int('jumlah'); // quantity
        $catatan = get_post_string('catatan');

        if ($id <= 0 || $id_barang === '' || !preg_match('/^[A-Za-z0-9-]+$/', $id_barang) || $jumlah <= 0) {
            echo '<script>alert("Data penjualan tidak valid");history.go(-1);</script>';
            exit;
        }

        $sql_tampil = 'select * from menu_layanan where id_menu=?';
        $row_tampil = $config->prepare($sql_tampil);
        $row_tampil->execute([$id_barang]);
        $hasil = $row_tampil->fetch();

        if ($hasil) {
            $harga = (float) $hasil['harga'];
            $total = $harga * $jumlah; // Hitung ulang total belanja item
            
            $data1 = [$jumlah, $total, $catatan, $id];
            $sql1 = 'UPDATE penjualan_temp SET jumlah=?, total=?, catatan=? WHERE id_penjualan_temp=?';
            $row1 = $config->prepare($sql1);
            $row1->execute($data1);
            echo '<script>window.location="../../index.php?page=jual#keranjang"</script>';
        } else {
            echo '<script>alert("Menu tidak ditemukan!");
                                        window.location="../../index.php?page=jual#keranjang"</script>';
        }
    }

    // -------------------------------------------------------------------------
    // AKSI 9: PENCARIAN REALTIME MENU DI HALAMAN KASIR (GET ?cari_kamar=yes)
    // -------------------------------------------------------------------------
    if (!empty($_GET['cari_kamar'])) {
        $cari = trim((string) filter_input(INPUT_POST, 'keyword', FILTER_UNSAFE_RAW, FILTER_FLAG_NO_ENCODE_QUOTES));
        if ($cari !== '') {
            $param = "%{$cari}%";
            $sql = "select menu_layanan.*, kategori.id_kategori, kategori.nama_kategori
                                         from menu_layanan inner join kategori on menu_layanan.id_kategori = kategori.id_kategori
                                         where menu_layanan.id_menu like ? or menu_layanan.nama like ? or menu_layanan.tipe like ?";
            $row = $config -> prepare($sql);
            $row -> execute(array($param, $param, $param));
            $hasil1= $row -> fetchAll();
            ?>
                <!-- Kembalikan render tabel HTML responsif pencarian menu kasir -->
                <table class="table table-stripped" width="100%" id="example2">
                        <tr>
                                <th>ID</th>
                                <th>Foto</th>
                                <th>Menu Makanan / Minuman</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                        </tr>
                <?php foreach ($hasil1 as $hasil) {?>
                        <tr>
                                <td><?php echo htmlspecialchars($hasil['id_menu'], ENT_QUOTES, 'UTF-8');?></td>
                                <td>
                                    <?php if (!empty($hasil['foto']) && is_file('../../assets/img/menu/' . $hasil['foto'])) { ?>
                                        <img src="assets/img/menu/<?php echo htmlspecialchars($hasil['foto'], ENT_QUOTES, 'UTF-8'); ?>" class="img-thumbnail" style="width:50px;height:50px;object-fit:cover;" alt="Foto Menu">
                                    <?php } else { ?>
                                        <span class="text-muted"><i class="fa fa-cutlery" style="font-size:24px;"></i></span>
                                    <?php } ?>
                                </td>
                                <td><?php echo htmlspecialchars($hasil['nama'], ENT_QUOTES, 'UTF-8');?></td>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($hasil['nama_kategori'], ENT_QUOTES, 'UTF-8');?></span></td>
                                <td>Rp.<?php echo number_format((float)$hasil['harga']);?></td>
                                <td>
                                <a href="fungsi/tambah/tambah.php?jual=jual&id=<?php echo urlencode($hasil['id_menu']);?>&id_kasir=<?php echo urlencode($_SESSION['admin']['id_member']);?>&csrf_token=<?php echo urlencode(csrf_get_token());?>"
                                         class="btn btn-success">
                                         <i class="fa fa-shopping-cart"></i> Pilih</a></td>
                        </tr>
                <?php }?>
                </table>
<?php
        }
    }
}
?>
