<?php
/**
 * -------------------------------------------------------------------------
 * Pengelolaan Kategori Menu - POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * Halaman ini menangani manajemen kategori menu makanan/minuman secara inline.
 * Tersedia form dinamis untuk mengubah status dari mode tambah ke mode edit
 * kategori berdasarkan parameter URL GET, serta tabel daftar kategori terdaftar.
 */
        // Membaca status parameter notifikasi sukses
        $successParam = filter_input(INPUT_GET, 'success', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
        $showSuccess = is_string($successParam) && $successParam !== '';

        $successEditParam = filter_input(INPUT_GET, 'success-edit', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
        $showSuccessEdit = is_string($successEditParam) && $successEditParam !== '';

        $removeParam = filter_input(INPUT_GET, 'remove', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
        $showRemove = is_string($removeParam) && $removeParam !== '';

        // Membaca parameter ID Kategori untuk beralih ke mode edit data
        $uidParamRaw = filter_input(INPUT_GET, 'uid', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
        $uidParam = (is_string($uidParamRaw) && ctype_digit($uidParamRaw)) ? $uidParamRaw : '';
?>
<h4>Kategori</h4>
<br />

<!-- Alert popup status eksekusi query -->
<?php if($showSuccess){?>
<div class="alert alert-success">
    <p>Tambah Data Berhasil !</p>
</div>
<?php }?>
<?php if($showSuccessEdit){?>
<div class="alert alert-success">
    <p>Update Data Berhasil !</p>
</div>
<?php }?>
<?php if($showRemove){?>
<div class="alert alert-danger">
    <p>Hapus Data Berhasil !</p>
</div>
<?php }?>

<?php
        // Jika parameter uid terdefinisi, tampilkan FORM MODE EDIT KATEGORI
        if($uidParam !== ''){
        $sql = "SELECT * FROM kategori WHERE id_kategori = ?";
        $row = $config->prepare($sql);
        $row->execute(array($uidParam));
        $edit = $row->fetch();
?>
<form method="POST" action="fungsi/edit/edit.php?kategori=edit">
    <?php echo csrf_field(); ?>
    <table>
        <tr>
            <td style="width:25pc;"><input type="text" class="form-control" value="<?= htmlspecialchars($edit['nama_kategori'] ?? '', ENT_QUOTES, 'UTF-8');?>"
                    required name="kategori" placeholder="Masukan Kategori Menu Baru">
                <input type="hidden" name="id" value="<?= htmlspecialchars($edit['id_kategori'] ?? '', ENT_QUOTES, 'UTF-8');?>">
            </td>
            <td style="padding-left:10px;"><button id="tombol-simpan" class="btn btn-primary"><i class="fa fa-edit"></i>
                    Ubah Data</button></td>
        </tr>
    </table>
</form>
<?php }else{?>
<!-- Jika parameter uid kosong, tampilkan FORM MODE TAMBAH KATEGORI BARU -->
<form method="POST" action="fungsi/tambah/tambah.php?kategori=tambah">
    <?php echo csrf_field(); ?>
    <table>
        <tr>
            <td style="width:25pc;"><input type="text" class="form-control" required name="kategori"
                    placeholder="Masukan Kategori Menu Baru"></td>
            <td style="padding-left:10px;"><button id="tombol-simpan" class="btn btn-primary"><i class="fa fa-plus"></i>
                    Insert Data</button></td>
        </tr>
    </table>
</form>
<?php }?>

<br />

<!-- Tabel Daftar Kategori Terdaftar -->
<div class="card card-body">
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-sm" id="example1">
            <thead>
                <tr style="background:#DFF0D8;color:#333;">
                    <th>No.</th>
                    <th>Kategori</th>
                    <th>Tanggal Input</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
				$hasil = $lihat->kategori();
				$no=1;
				foreach($hasil as $isi){
			?>
                <tr>
                    <td><?php echo $no;?></td>
                    <td><?= htmlspecialchars($isi['nama_kategori'], ENT_QUOTES, 'UTF-8');?></td>
                    <td><?= htmlspecialchars($isi['tgl_input'], ENT_QUOTES, 'UTF-8');?></td>
                    <td>
                        <!-- Tombol edit memicu redirect halaman dengan menyertakan ?uid=ID -->
                        <a href="index.php?page=kategori&uid=<?= urlencode($isi['id_kategori']);?>"><button
                                class="btn btn-warning">Edit</button></a>
                        <!-- Tombol hapus dilindungi validasi CSRF token -->
                        <a href="fungsi/hapus/hapus.php?kategori=hapus&id=<?= urlencode($isi['id_kategori']);?>&csrf_token=<?= urlencode(csrf_get_token());?>"
                            onclick="javascript:return confirm('Hapus Data Kategori ?');"><button
                                class="btn btn-danger">Hapus</button></a>
                    </td>
                </tr>
                <?php $no++; }?>
            </tbody>
        </table>
    </div>
</div>