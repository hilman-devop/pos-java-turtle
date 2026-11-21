<?php
/**
 * -------------------------------------------------------------------------
 * Form Ubah Detail Menu - POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * Halaman ini menyediakan form untuk memperbarui data menu restoran, meliputi
 * kode menu (id_menu), nama kategori, nama menu, harga jual, foto menu (disertai
 * preview foto lama), dan menyimpan tanggal perubahan terakhir.
 */
        // Membaca dan memvalidasi kode menu dari query parameter GET
        $barangParamRaw = filter_input(INPUT_GET, 'menu', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
        $idParam = (is_string($barangParamRaw) && preg_match('/^[A-Za-z0-9-]+$/', $barangParamRaw)) ? $barangParamRaw : '';
        
        // Memuat detail menu lama dari database
        $hasil = $lihat->barang_edit($idParam);
        if (!$hasil) {
                echo '<div class="alert alert-danger">Data menu tidak ditemukan.</div>';
                return;
        }
?>
<!-- Tombol kembali ke master data menu -->
<a href="index.php?page=menu" class="btn btn-primary mb-3"><i class="fa fa-angle-left"></i> Kembali </a>
<h4>Edit Menu Restoran</h4>

<?php if(isset($_GET['success'])){?>
<div class="alert alert-success">
    <p>Edit Data Berhasil !</p>
</div>
<?php }?>

<div class="card card-body">
	<div class="table-responsive">
		<table class="table table-striped">
            <!-- Form untuk mengupdate detail menu (mendukung upload gambar multipart/form-data) -->
            <form action="fungsi/edit/edit.php?menu=edit" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
				<tr>
					<td>Kode Menu</td>
                    <td>
                        <!-- Menyimpan ID menu lama di input hidden agar bisa dicari di query database UPDATE -->
                        <input type="hidden" name="old_id" value="<?php echo htmlspecialchars($hasil['id_barang'], ENT_QUOTES, 'UTF-8');?>">
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($hasil['id_barang'], ENT_QUOTES, 'UTF-8');?>" name="id">
                    </td>
				</tr>
				<tr>
					<td>Kategori</td>
					<td>
						<select name="kategori" class="form-control" required>
							<option value="<?php echo htmlspecialchars($hasil['id_kategori'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($hasil['nama_kategori'], ENT_QUOTES, 'UTF-8'); ?></option>
							<option value="">Pilih Kategori</option>
							<?php $kat = $lihat->kategori(); foreach($kat as $isi){ 	?>
							<option value="<?php echo htmlspecialchars($isi['id_kategori'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($isi['nama_kategori'], ENT_QUOTES, 'UTF-8'); ?></option>
							<?php }?>
						</select>
					</td>
				</tr>
				<tr>
					<td>Nama Menu Makanan/Minuman</td>
					<td><input type="text" class="form-control" required value="<?php echo htmlspecialchars($hasil['nama_barang'], ENT_QUOTES, 'UTF-8');?>" name="nama"></td>
				</tr>
				<tr>
					<td>Harga Menu (Rp)</td>
					<td><input type="number" class="form-control" required value="<?php echo htmlspecialchars($hasil['harga_jual'], ENT_QUOTES, 'UTF-8');?>" name="jual"></td>
				</tr>
				<input type="hidden" name="stok" value="<?php echo htmlspecialchars($hasil['stok'], ENT_QUOTES, 'UTF-8');?>">
				<tr>
					<td>Foto Menu Saat Ini</td>
					<td>
                        <!-- Menampilkan gambar menu saat ini -->
						<?php if (!empty($hasil['foto']) && is_file('assets/img/menu/' . $hasil['foto'])) { ?>
							<img src="assets/img/menu/<?php echo htmlspecialchars($hasil['foto'], ENT_QUOTES, 'UTF-8'); ?>" class="img-thumbnail" style="width:100px;height:100px;object-fit:cover;" alt="Foto Menu">
						<?php } else { ?>
							<span class="text-muted"><i class="fa fa-cutlery" style="font-size:32px;"></i> Belum ada foto</span>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<td>Ganti Foto Menu</td>
					<td><input type="file" class="form-control" name="foto" accept="image/*"></td>
				</tr>
				<tr>
					<td>Tanggal Update</td>
					<td><input type="text" readonly="readonly" class="form-control" value="<?php echo date("j F Y, G:i");?>"
							name="tgl"></td>
				</tr>
				<tr>
					<td></td>
					<td><button class="btn btn-primary"><i class="fa fa-edit"></i> Update Data</button></td>
				</tr>
			</form>
		</table>
	</div>
</div>
