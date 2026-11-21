<?php
/**
 * -------------------------------------------------------------------------
 * Pengaturan Profil Restoran & Struk - POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * Modul ini menampilkan form konfigurasi global restoran yang dicetak di struk,
 * meliputi nama resto, alamat, kontak telp, nama pemilik, dan catatan kaki
 * nota (footer nota) yang interaktif.
 */
        // Banner status update pengaturan
        $successParam = filter_input(INPUT_GET, 'success', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
        $showSuccess = is_string($successParam) && $successParam !== '';
?>
<h4>Pengaturan Restoran</h4>
<br>

<?php if($showSuccess){?>
<div class="alert alert-success">
	<p>Ubah Data Berhasil !</p>
</div>
<?php }?>

<div class="card">
	<div class="card-body">
                <!-- Form edit detail data toko yang diproses oleh fungsi/edit/edit.php -->
                <form method="post" action="fungsi/edit/edit.php?pengaturan=ubah">
                        <?php echo csrf_field(); ?>
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label for="">Nama Resto / Toko</label>
						<input class="form-control" name="namatoko" value="<?= htmlspecialchars($toko['nama_toko'] ?? '', ENT_QUOTES, 'UTF-8');?>"
									placeholder="Nama Resto">
					</div>
					<div class="form-group">
						<label for="">Alamat Resto</label>
						<input class="form-control" name="alamat" value="<?= htmlspecialchars($toko['alamat_toko'] ?? '', ENT_QUOTES, 'UTF-8');?>"
									placeholder="Alamat Resto">
					</div>
					<div class="form-group">
						<label for="">Kontak (Hp/Telp)</label>
						<input class="form-control" name="kontak" value="<?= htmlspecialchars($toko['tlp'] ?? '', ENT_QUOTES, 'UTF-8');?>"
									placeholder="Kontak (Hp)">
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label for="">Nama Pemilik / Pengelola</label>
						<input class="form-control" name="pemilik" value="<?= htmlspecialchars($toko['nama_pemilik'] ?? '', ENT_QUOTES, 'UTF-8');?>"
									placeholder="Nama Pemilik">
					</div>
					<div class="form-group">
						<label for="">Catatan Kaki Struk (Footer Nota)</label>
						<textarea class="form-control" name="footer_nota" placeholder="Contoh: Terima kasih telah berkunjung ke Turtle Resto! 🐢✨" rows="3"><?= htmlspecialchars($toko['footer_nota'] ?? '', ENT_QUOTES, 'UTF-8');?></textarea>
					</div>
				</div>
			</div>
			<button id="tombol-simpan" class="btn btn-primary"><i class="fas fa-edit"></i> Update Pengaturan</button>
		</form>
	</div>
</div>