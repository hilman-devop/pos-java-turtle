<?php
/**
 * -------------------------------------------------------------------------
 * Detail Spesifik Menu Restoran - POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * File ini merender detail data master satu menu terpilih secara lengkap,
 * termasuk preview foto menu berukuran besar, kategori, harga jual, tipe,
 * satuan, serta log tanggal pendaftaran dan pembaruan data menu.
 */
        // Memvalidasi parameter kode menu yang dilewatkan di GET
        $barangParamRaw = filter_input(INPUT_GET, 'menu', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
        $idParam = (is_string($barangParamRaw) && preg_match('/^[A-Za-z0-9-]+$/', $barangParamRaw)) ? $barangParamRaw : '';
        
        // Memuat detail menu dari database
        $hasil = $lihat->barang_edit($idParam);
        if (!$hasil) {
                echo '<div class="alert alert-danger">Data menu tidak ditemukan.</div>';
                return;
        }
?>
<!-- Tombol pintas kembali ke halaman kelola menu -->
<a href="index.php?page=menu" class="btn btn-primary mb-3"><i class="fa fa-angle-left"></i> Kembali </a>
<h4>Detail Menu Restoran</h4>

<div class="card card-body">
	<div class="table-responsive">
		<table class="table table-striped">
			<tr>
				<td>Kode Menu</td>
                                <td><b><?php echo htmlspecialchars($hasil['id_barang'], ENT_QUOTES, 'UTF-8');?></b></td>
			</tr>
			<tr>
				<td>Foto Menu</td>
				<td>
                    <!-- Menampilkan preview foto menu utama secara responsif -->
					<?php if (!empty($hasil['foto']) && is_file('assets/img/menu/' . $hasil['foto'])) { ?>
						<img src="assets/img/menu/<?php echo htmlspecialchars($hasil['foto'], ENT_QUOTES, 'UTF-8'); ?>" class="img-thumbnail" style="max-width:200px;max-height:200px;object-fit:cover;" alt="Foto Menu">
					<?php } else { ?>
						<span class="text-muted"><i class="fa fa-cutlery" style="font-size:48px;"></i> Belum ada foto</span>
					<?php } ?>
				</td>
			</tr>
			<tr>
				<td>Kategori</td>
                                <td><?php echo htmlspecialchars($hasil['nama_kategori'], ENT_QUOTES, 'UTF-8');?></td>
			</tr>
			<tr>
				<td>Nama Menu Makanan/Minuman</td>
                                <td><b><?php echo htmlspecialchars($hasil['nama_barang'], ENT_QUOTES, 'UTF-8');?></b></td>
			</tr>
			<tr>
				<td>Tipe</td>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars(ucfirst($hasil['tipe']), ENT_QUOTES, 'UTF-8');?></span></td>
			</tr>
			<tr>
				<td>Harga Menu (Rp)</td>
                                <td><b>Rp. <?php echo number_format((float)$hasil['harga_jual']);?>,-</b></td>
			</tr>
			<tr>
				<td>Satuan</td>
                                <td>/ <?php echo htmlspecialchars($hasil['satuan_barang'], ENT_QUOTES, 'UTF-8');?></td>
			</tr>

			<tr>
				<td>Tanggal Input</td>
                                <td><?php echo htmlspecialchars($hasil['tgl_input'], ENT_QUOTES, 'UTF-8');?></td>
			</tr>
			<tr>
				<td>Tanggal Update</td>
                                <td><?php echo htmlspecialchars($hasil['tgl_update'] ?? '-', ENT_QUOTES, 'UTF-8');?></td>
			</tr>
		</table>
	</div>
</div>
