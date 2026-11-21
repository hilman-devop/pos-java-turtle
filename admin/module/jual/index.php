<!-- 
  * -------------------------------------------------------------------------
  * Halaman Kasir Restoran (Cash Register) - POS Resto Java Turtle
  * -------------------------------------------------------------------------
  * Modul kasir utama untuk menginput pesanan tamu/pelanggan, menentukan nomor
  * kamar, tipe pesanan, metode pembayaran (termasuk generator kode QRIS),
  * menghitung kembalian uang, serta mencetak struk secara real-time.
-->
<?php
        // Ambil data detail profil kasir terotentikasi untuk dihubungkan ke data transaksi
        $id = $_SESSION['admin']['id_member'];
        $hasil = $lihat->member_edit($id);
        
        // Membaca status notifikasi redirect
        $successParam = filter_input(INPUT_GET, 'success', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
        $showSuccess = is_string($successParam) && $successParam !== '';

        $removeParam = filter_input(INPUT_GET, 'remove', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
        $showRemove = is_string($removeParam) && $removeParam !== '';

        $notaParamRaw = filter_input(INPUT_GET, 'nota', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
        $notaAction = is_string($notaParamRaw) ? trim($notaParamRaw) : '';
        $isNotaYes = ($notaAction === 'yes');

        // Parameter sukses transaksi setelah post redirect
        $successPayParam = filter_input(INPUT_GET, 'success_pay', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
        $showSuccessPay = ($successPayParam === 'yes');
        $invoiceVal = filter_input(INPUT_GET, 'invoice', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]) ?? '';
        $changeVal = filter_input(INPUT_GET, 'change', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]) ?? '0';
        $bayarVal = filter_input(INPUT_GET, 'bayar', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]) ?? '0';
?>
        <h4>Kasir </h4>
        <br>
        
        <!-- Notifikasi Banner Alert -->
        <?php if($showSuccess){?>
        <div class="alert alert-success">
            <p>Update Data Berhasil !</p>
        </div>
        <?php }?>
        <?php if($showRemove){?>
        <div class="alert alert-danger">
            <p>Hapus Data Berhasil !</p>
        </div>
        <?php }?>
        
	<div class="row">
		<!-- KOTAK PENCARIAN MENU -->
		<div class="col-sm-4">
			<div class="card card-primary mb-3">
				<div class="card-header bg-primary text-white">
					<h5><i class="fa fa-search"></i> Cari Menu</h5>
				</div>
				<div class="card-body">
					<input type="text" id="cari" class="form-control" name="cari" placeholder="Ketik Kode / Nama lalu tekan [ENTER]">
				</div>
			</div>
		</div>
        
		<!-- KOTAK HASIL PENCARIAN AJAX -->
		<div class="col-sm-8">
			<div class="card card-primary mb-3">
				<div class="card-header bg-primary text-white">
					<h5><i class="fa fa-list"></i> Hasil Pencarian</h5>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<div id="hasil_cari"></div>
						<div id="tunggu"></div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- TABEL KERANJANG PESANAN AKTIF -->
		<div class="col-sm-12">
			<div class="card card-primary">
				<div class="card-header bg-primary text-white">
					<h5><i class="fa fa-shopping-cart"></i> KERANJANG PESANAN AKTIF
                                        <a class="btn btn-danger float-right btn-sm"
                                                onclick="javascript:return confirm('Apakah anda ingin mengosongkan keranjang ?');" href="fungsi/hapus/hapus.php?penjualan=jual&csrf_token=<?php echo urlencode(csrf_get_token());?>">
						<b>RESET KERANJANG</b></a>
					</h5>
				</div>
				<div class="card-body">
					<div id="keranjang" class="table-responsive">
						<table class="table table-bordered">
							<tr>
								<td><b>Waktu Transaksi</b></td>
                                <td><input type="text" readonly="readonly" class="form-control" value="<?= htmlspecialchars(date('j F Y, G:i'), ENT_QUOTES, 'UTF-8');?>" name="tgl"></td>
							</tr>
						</table>
						<table class="table table-bordered w-100" id="example1">
							<thead>
								<tr style="background:#F2F2F2;">
									<th>No.</th>
									<th>Foto</th>
									<th>Nama Menu</th>
									<th>Tipe</th>
									<th style="width:45%;">Jumlah Porsi & Catatan</th>
									<th>Subtotal</th>
									<th>Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php $total_bayar=0; $no=1; $hasil_penjualan = $lihat -> penjualan();?>
								<?php foreach($hasil_penjualan as $isi){?>
								<tr>
									<td><?= htmlspecialchars((string) $no, ENT_QUOTES, 'UTF-8');?></td>
									<td>
                                        <!-- Menampilkan foto menu jika tersedia, jika kosong tampilkan icon sendok garpu -->
										<?php if (!empty($isi['foto']) && is_file('assets/img/menu/' . $isi['foto'])) { ?>
											<img src="assets/img/menu/<?php echo htmlspecialchars($isi['foto'], ENT_QUOTES, 'UTF-8'); ?>" class="img-thumbnail" style="width:50px;height:50px;object-fit:cover;" alt="Foto Menu">
										<?php } else { ?>
											<span class="text-muted"><i class="fa fa-cutlery" style="font-size:24px;"></i></span>
										<?php } ?>
									</td>
									<td><b><?= htmlspecialchars($isi['nama_barang'], ENT_QUOTES, 'UTF-8');?></b></td>
									<td><span class="badge badge-info"><?= htmlspecialchars(ucfirst($isi['tipe'] ?? 'makanan'), ENT_QUOTES, 'UTF-8');?></span></td>
									<td>
                                        <!-- Form Edit Kuantitas & Catatan Tambahan Menu Makanan -->
                                        <form method="POST" action="fungsi/edit/edit.php?jual=jual">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="id" value="<?= htmlspecialchars($isi['id_penjualan'], ENT_QUOTES, 'UTF-8');?>">
                                            <input type="hidden" name="id_barang" value="<?= htmlspecialchars($isi['id_barang'], ENT_QUOTES, 'UTF-8');?>">
                                            
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <small class="text-muted">Porsi / Qty</small>
                                                    <input type="number" name="jumlah" value="<?= htmlspecialchars($isi['jumlah'], ENT_QUOTES, 'UTF-8');?>" class="form-control form-control-sm" required min="1">
                                                </div>
                                                <div class="col-sm-8">
                                                    <small class="text-muted">Catatan Pesanan (e.g. Pedas, Dingin)</small>
                                                    <input type="text" name="catatan" value="<?= htmlspecialchars($isi['catatan'] ?? '', ENT_QUOTES, 'UTF-8');?>" class="form-control form-control-sm" placeholder="Contoh: Tidak pedas, tanpa es batu.">
                                                </div>
                                                <div class="col-sm-1 d-flex align-items-end">
                                                    <button type="submit" class="btn btn-warning btn-sm mt-1" title="Update"><i class="fa fa-sync"></i></button>
                                                </div>
                                            </div>
                                        </form>
									</td>
									<td><b>Rp. <?php echo number_format($isi['total']);?>,-</b></td>
									<td>
                                        <!-- Tombol Hapus Baris Keranjang -->
                                        <a href="fungsi/hapus/hapus.php?jual=jual&id=<?= htmlspecialchars($isi['id_penjualan'], ENT_QUOTES, 'UTF-8');?>&brg=<?= htmlspecialchars($isi['id_barang'], ENT_QUOTES, 'UTF-8');?>&csrf_token=<?php echo urlencode(csrf_get_token());?>" 
                                           onclick="javascript:return confirm('Hapus item ini dari keranjang ?');" class="btn btn-danger btn-sm">
                                           <i class="fa fa-trash"></i>
  										</a>
									</td>
								</tr>
								<?php $no++; $total_bayar += $isi['total'];}?>
							</tbody>
					</table>
					<br/>
					<?php $hasil = $lihat -> jumlah(); ?>
					<div id="kasirnya">
                        <!-- Form Checkout Pembayaran Transaksi -->
						<form id="form-transaksi" method="POST" action="index.php?page=jual&nota=yes#kasirnya">
						<?php echo csrf_field(); ?>
                        
                        <!-- Array tersembunyi berisi detail item belanja untuk di-insert ke tabel penjualan sewaktu submit -->
						<?php foreach($hasil_penjualan as $isi_hidden){ ?>
							<input type="hidden" name="id_barang[]" value="<?= htmlspecialchars($isi_hidden['id_barang'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="id_member[]" value="<?= htmlspecialchars($isi_hidden['id_member'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="jumlah[]" value="<?= htmlspecialchars($isi_hidden['jumlah'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="total1[]" value="<?= htmlspecialchars($isi_hidden['total'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="tgl_input[]" value="<?= htmlspecialchars($isi_hidden['tanggal_input'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="periode[]" value="<?= htmlspecialchars(date('m-Y'), ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="catatan[]" value="<?= htmlspecialchars($isi_hidden['catatan'] ?? '', ENT_QUOTES, 'UTF-8');?>">
						<?php } ?>
							<table class="table table-striped">
							<?php
							// Memproses checkout kasir dan menyimpan data transaksi ke database
                                                        $total = 0.0;
                                                        $bayar = 0.0;
                                                        $hitung = 0.0;
                                                        $nama_tamu_printed = '';
                                                        $meja_printed = '1';
                                                        $tipe_pesanan_printed = 'dine-in';
                                                        $metodePembayaran_printed = 'Tunai';
                                                        $statusPembayaran_printed = 'Lunas';
                                                        
                                                        if($isNotaYes && $_SERVER['REQUEST_METHOD'] === 'POST') {
                                                                 $totalInput = filter_input(INPUT_POST, 'total', FILTER_SANITIZE_NUMBER_FLOAT, ['flags' => FILTER_FLAG_ALLOW_FRACTION]);
                                                                 $bayarInput = filter_input(INPUT_POST, 'bayar', FILTER_SANITIZE_NUMBER_FLOAT, ['flags' => FILTER_FLAG_ALLOW_FRACTION]);
                                                                 $nama_tamu_printed = get_post_string('nama_tamu');
                                                                 $meja_printed = get_post_string('meja');
                                                                 $tipe_pesanan_printed = get_post_string('tipe_pesanan');
                                                                 $metodePembayaran_printed = get_post_string('metode_pembayaran');
                                                                 $statusPembayaran_printed = get_post_string('status_pembayaran');
 
                                                                 if($totalInput !== null && $totalInput !== false && $totalInput !== '') {
                                                                         $total = (float) $totalInput;
                                                                 }
                                                                 if($bayarInput !== null && $bayarInput !== false && $bayarInput !== '') {
                                                                         $bayar = (float) $bayarInput;
                                                                 }
 
                                                                  $hitung = $bayar - $total;
                                                                  if($total > 0.0) {
                                                                          // Pastikan nominal uang bayar cukup
                                                                          if($bayar >= $total) {
                                                                                 $idBarangList = $_POST['id_barang'] ?? [];
                                                                                 $idMemberList = $_POST['id_member'] ?? [];
                                                                                 $jumlahList = $_POST['jumlah'] ?? [];
                                                                                 $totalList = $_POST['total1'] ?? [];
                                                                                 $tglInputList = $_POST['tgl_input'] ?? [];
                                                                                 $periodeList = $_POST['periode'] ?? [];
                                                                                 $catatanList = $_POST['catatan'] ?? [];
                                                                                 $metodePembayaran = get_post_string('metode_pembayaran');
                                                                                 $statusPembayaran = get_post_string('status_pembayaran');
   
                                                                                 // Pembuatan nomor invoice unik acak
                                                                                 $no_invoice = 'INV-' . date('Ymd-His') . '-' . rand(1000, 9999);
 
                                                                                 $jumlahDipilih = is_array($idBarangList) ? count($idBarangList) : 0;
                                                                                 for($x = 0; $x < $jumlahDipilih; $x++) {
                                                                                         $barangId = $idBarangList[$x];
                                                                                         $memberId = (int)$idMemberList[$x];
                                                                                         $jumlahItem = (int)$jumlahList[$x];
                                                                                         $totalItem = (float)$totalList[$x];
                                                                                         $tglInputItem = $tglInputList[$x];
                                                                                         $periodeItem = $periodeList[$x];
                                                                                         $catatanItem = $catatanList[$x] ?? '';
   
                                                                                         // Masukkan masing-masing data item belanja ke tabel laporan penjualan
                                                                                         $d = array($no_invoice, $barangId, $memberId, $nama_tamu_printed, $meja_printed, $tipe_pesanan_printed, $jumlahItem, $totalItem, $catatanItem, $metodePembayaran, $statusPembayaran, $tglInputItem, $periodeItem);
                                                                                         $sql = "INSERT INTO penjualan (no_invoice,id_menu,id_member,nama_pelanggan,meja,tipe_pesanan,jumlah,total,catatan,metode_pembayaran,status_pembayaran,tanggal_input,periode) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)";
                                                                                         $row = $config->prepare($sql);
                                                                                         $row->execute($d);
                                                                                 }
                                                                                 
                                                                                 // 🌟 SINKRONISASI CLOUD GOOGLE SPREADSHEET 🌟
                                                                                 require_once __DIR__ . '/../../../fungsi/spreadsheet.php';
                                                                                 sync_to_spreadsheet($nama_tamu_printed, $meja_printed, $tipe_pesanan_printed, $total, $hasil_penjualan, $metodePembayaran, $statusPembayaran);
   
                                                                                 // Kosongkan keranjang setelah sukses checkout
                                                                                 $config->exec("DELETE FROM penjualan_temp");
   
                                                                                 // Kirimkan response JSON jika dipanggil via AJAX
                                                                                 if (isset($_GET['ajax']) && $_GET['ajax'] === 'yes') {
                                                                                          while (ob_get_level() > 0) {
                                                                                                  ob_end_clean();
                                                                                          }
                                                                                          header('Content-Type: application/json');
                                                                                         echo json_encode(array(
                                                                                                 'status' => 'success',
                                                                                                 'invoice' => $no_invoice,
                                                                                                 'change' => $hitung,
                                                                                                 'bayar' => $bayar
                                                                                         ));
                                                                                         exit;
                                                                                 } else {
                                                                                         echo '<script>window.location="index.php?page=jual&success_pay=yes&invoice=' . urlencode($no_invoice) . '&change=' . urlencode((string)$hitung) . '&bayar=' . urlencode((string)$bayar) . '";</script>';
                                                                                         exit;
                                                                                 }
                                                                          } else {
                                                                                 if (isset($_GET['ajax']) && $_GET['ajax'] === 'yes') {
                                                                                         while (ob_get_level() > 0) {
                                                                                                 ob_end_clean();
                                                                                         }
                                                                                         header('Content-Type: application/json');
                                                                                         echo json_encode(array(
                                                                                                 'status' => 'error',
                                                                                                 'message' => 'Pembayaran Kurang! Kurang: Rp. ' . number_format(abs($hitung))
                                                                                         ));
                                                                                         exit;
                                                                                 } else {
                                                                                         echo '<script>alert("Pembayaran Kurang! Kurang: Rp. ' . number_format(abs($hitung)) . '");</script>';
                                                                                 }
                                                                          }
                                                                  }
                                                         }
  							?>
                                  <tr>
  									<td><b>Nama Pelanggan</b></td>
  									<td><input type="text" class="form-control" name="nama_tamu" required value="<?= htmlspecialchars($nama_tamu_printed, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Masukkan nama pelanggan"></td>
                                      <td><b>Nomor Kamar</b></td>
  									<td><input type="text" class="form-control" name="meja" required value="<?= htmlspecialchars($meja_printed, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Contoh: Kamar 101, Takeaway"></td>
  								</tr>
                                  <tr>
  									<td><b>Tipe Pesanan</b></td>
  									<td>
                                           <select class="form-control" name="tipe_pesanan" required>
                                               <option value="dine-in" <?= $tipe_pesanan_printed === 'dine-in' ? 'selected' : ''; ?>>Makan di tempat (Dine-in)</option>
                                               <option value="take-away" <?= $tipe_pesanan_printed === 'take-away' ? 'selected' : ''; ?>>Bawa pulang (Take-away)</option>
                                           </select>
                                       </td>
                                      <td><b>Metode Pembayaran</b></td>
  									<td>
                                           <select class="form-control" name="metode_pembayaran" required>
                                               <option value="Tunai">Tunai / Cash</option>
                                               <option value="Transfer Bank">Transfer Bank</option>
                                               <option value="QRIS">QRIS / E-Wallet</option>
                                           </select>
                                       </td>
  								</tr>
                                  <tr>
                                      <td><b>Status Pembayaran</b></td>
  									<td>
                                           <select class="form-control" name="status_pembayaran" required>
                                               <option value="Lunas">Lunas (Paid)</option>
                                               <option value="Belum Lunas">Belum Lunas</option>
                                           </select>
                                       </td>
                                      <td colspan="2"></td>
                                  </tr>
  								<tr>
  									<td><b>Total Tagihan</b></td>
  									<td>
  										<input type="text" readonly class="form-control" id="total-display" value="Rp. <?= number_format($total_bayar);?>">
  										<input type="hidden" name="total" value="<?= htmlspecialchars((string) $total_bayar, ENT_QUOTES, 'UTF-8');?>">
  									</td>
  								
  									<td><b>Bayar (Nominal)</b></td>
  									<td>
  										<input type="text" class="form-control" required id="bayar-display" placeholder="Rp. 0">
  										<input type="hidden" name="bayar" id="bayar-raw" value="<?= htmlspecialchars((string) $bayar, ENT_QUOTES, 'UTF-8');?>">
  									</td>
  									<td>
                                          <button class="btn btn-success"><i class="fa fa-shopping-cart"></i> Proses Bayar</button>
                                          <?php if($isNotaYes){?>
                                          <a class="btn btn-danger" href="fungsi/hapus/hapus.php?penjualan=jual&csrf_token=<?php echo urlencode(csrf_get_token());?>"><b>RESET</b></a>
                                          <?php }?>
                                      </td>
  								</tr>
  							<tr>
  								<td><b>Kembalian</b></td>
  								<td><input type="text" readonly class="form-control" value="Rp. <?= number_format($hitung);?>,-"></td>
  								<td></td>
  								<td>
                                      <!-- Pengaturan Tombol Cetak Nota Kasir -->
                                      <?php if (!empty($invoiceVal)) { ?>
                                      <a href="print.php?invoice=<?php echo urlencode($invoiceVal); ?>&bayar=<?php echo urlencode($bayarVal); ?>" target="_blank">
   									<button class="btn btn-secondary btn-block" type="button">
   										<i class="fa fa-print"></i> Cetak Struk Kasir
   									</button></a>
                                      <?php } else if ($isNotaYes && $bayar > 0.0 && $bayar >= $total) { ?>
                                      <a href="print.php?nm_member=<?php echo urlencode($_SESSION['admin']['nm_member']);?>&bayar=<?php echo urlencode((string)$bayar);?>&kembali=<?php echo urlencode((string)$hitung);?>&nama_pelanggan=<?php echo urlencode($nama_tamu_printed);?>&meja=<?php echo urlencode($meja_printed);?>&tipe_pesanan=<?php echo urlencode($tipe_pesanan_printed);?>&metode_pembayaran=<?php echo urlencode($metodePembayaran_printed);?>&status_pembayaran=<?php echo urlencode($statusPembayaran_printed);?>" target="_blank">
   									<button class="btn btn-secondary btn-block" type="button">
   										<i class="fa fa-print"></i> Cetak Struk Kasir
   									</button></a>
                                      <?php } else if ($total_bayar > 0) { ?>
                                      <button class="btn btn-secondary btn-block" type="button" id="btn-cetak-langsung">
   										<i class="fa fa-print"></i> Cetak Struk Kasir
   									</button>
                                      <?php } else { ?>
                                      <button class="btn btn-secondary btn-block" disabled title="Keranjang pesanan masih kosong">
   										<i class="fa fa-print"></i> Cetak Struk Kasir
   									</button>
                                      <?php } ?>
  								</td>
  							</tr>
						</table>
						</form>
						<br/>
						<br/>
					</div>
 				</div>
 			</div>
 		</div>
 	</div>
 	

 <script>
 $(document).ready(function(){
     // Tampilkan popup notifikasi pembayaran lunas SweetAlert2 jika diarahkan dari get params
	<?php if ($showSuccessPay): ?>
		var showSuccessPay = true;
		var invoiceCode = <?= json_encode($invoiceVal); ?>;
		var changeAmount = <?= (float)$changeVal; ?>;
		var bayarValue = <?= (float)$bayarVal; ?>;
	<?php else: ?>
		var showSuccessPay = false;
		var invoiceCode = '';
		var changeAmount = 0;
		var bayarValue = 0;
	<?php endif; ?>

	if (showSuccessPay) {
		var formattedChange = 'Rp. ' + changeAmount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		Swal.fire({
			title: '<h4 style="color: #285c64; font-weight: bold; margin-bottom: 0;">🎉 Pembayaran Berhasil!</h4>',
			html: `
				<div style="text-align: center; padding: 10px;">
					<p style="font-size: 13px; color: #666; margin-bottom: 15px;">Invoice: <b>${invoiceCode}</b></p>
					<div style="background: #f8f9fa; padding: 15px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 15px;">
						<span style="font-size: 12px; color: #777; display: block; margin-bottom: 5px;">KEMBALIAN</span>
						<h3 style="font-weight: bold; color: #2e7d32; margin: 0;">${formattedChange}</h3>
					</div>
					<p style="font-size: 11px; color: #888;">Klik <b>Cetak Struk</b> untuk mencetak nota kasir, atau <b>Transaksi Baru</b> untuk melanjutkan.</p>
				</div>
			`,
			icon: 'success',
			showCancelButton: true,
			confirmButtonColor: '#285c64',
			cancelButtonColor: '#d33',
			confirmButtonText: '🖨️ Cetak Struk',
			cancelButtonText: '🔄 Transaksi Baru',
			allowOutsideClick: false,
			allowEscapeKey: false
		}).then((result) => {
			if (result.isConfirmed) {
				window.open('print.php?invoice=' + encodeURIComponent(invoiceCode) + '&bayar=' + encodeURIComponent(bayarValue), '_blank');
			}
			window.location.href = 'index.php?page=jual';
		});
	}

    // Melakukan pencarian menu secara langsung (real-time autocomplete) via AJAX POST
 	$("#cari").on("keypress change", function(e){
 		if (e.type === 'change' || e.which === 13) {
 			if (e.which === 13) { e.preventDefault(); }
 			$.ajax({
 				type: "POST",
 				url: "fungsi/edit/edit.php?cari_kamar=yes",
 				data:{keyword: $(this).val(), csrf_token: window.csrfToken || ''},
 				beforeSend: function(){
 					$("#hasil_cari").hide();
 					$("#tunggu").html('<p style="color:green"><blink>Mencari menu makanan/minuman...</blink></p>');
 				},
 				success: function(html){
 					$("#tunggu").html('');
 					$("#hasil_cari").show();
 					$("#hasil_cari").html(html);
 				}
 			});
 		}
 	});
 
    // Format input angka menjadi teks rupiah (contoh: 10000 -> Rp. 10,000)
 	function formatRupiah(angka) {
 		var number_string = angka.replace(/[^0-9]/g, '').toString();
 		if (number_string === '') return '';
 		var formatted = number_string.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
 		return 'Rp. ' + formatted;
 	}
 
 	var initBayar = $('#bayar-raw').val();
 	if (initBayar && initBayar !== '0' && initBayar !== '') {
 		$('#bayar-display').val(formatRupiah(initBayar));
 	}
 
 	$('#bayar-display').on('input', function() {
 		var rawValue = $(this).val().replace(/[^0-9]/g, '');
 		$('#bayar-raw').val(rawValue);
 		$(this).val(formatRupiah(rawValue));
 	});
 
    // Menangkap submit transaksi utama dan menghasilkan QR Code dinamis apabila metode pembayaran QRIS dipilih
  	$('#form-transaksi').on('submit', function(e) {
		var displayVal = $('#bayar-display').val() || '';
		var rawValue = displayVal.replace(/[^0-9]/g, '');
		$('#bayar-raw').val(rawValue);

  		var metode = $('select[name="metode_pembayaran"]').val();
  		var bayar = parseInt(rawValue) || 0;
 
 		if (metode === 'QRIS' && !$(this).data('qris-confirmed')) {
 			e.preventDefault(); 
 
 			var formattedNominal = 'Rp. ' + bayar.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
 			var qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent('qris://javaturtleresto/pay?amount=' + bayar + '&merchant=JavaTurtleResto');
 
 			Swal.fire({
 				title: '<h4 style="color: #285c64; font-weight: bold; margin-bottom: 0;">🐢 QRIS Resto Payment</h4>',
 				html: `
 					<div style="text-align: center; padding: 10px;">
 						<p style="font-size: 13px; color: #666; margin-bottom: 15px;">Java Turtle Resto & Cafe</p>
 						<div style="background: white; padding: 15px; display: inline-block; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 15px;">
 							<img src="${qrUrl}" alt="QRIS Code" style="width: 220px; height: 220px; display: block;" />
 						</div>
 						<h3 style="font-weight: bold; color: #e67e22; margin: 5px 0 15px 0;">${formattedNominal}</h3>
 						<div style="background: #f8f9fa; padding: 10px; border-radius: 8px; font-size: 12px; color: #555; text-align: left; line-height: 1.6;">
 							• Scan menggunakan aplikasi e-wallet (GoPay, OVO, Dana, LinkAja) atau Mobile Banking Anda.<br>
 							• Pastikan nominal transfer sesuai dengan yang tertera di atas.<br>
 							• Kode QR ini kedaluwarsa dalam <b id="qris-timer" style="color: #d9534f;">05:00</b>.
 						</div>
 					</div>
 				`,
 				showCancelButton: true,
 				confirmButtonColor: '#285c64',
 				cancelButtonColor: '#d33',
 				confirmButtonText: '⚡ Konfirmasi Sudah Bayar',
 				cancelButtonText: 'Batal',
 				didOpen: () => {
 					var timer = 300;
 					var interval = setInterval(function() {
 						var minutes = Math.floor(timer / 60);
 						var seconds = timer % 60;
 						minutes = minutes < 10 ? '0' + minutes : minutes;
 						seconds = seconds < 10 ? '0' + seconds : seconds;
 						$('#qris-timer').text(minutes + ':' + seconds);
 						timer--;
 						if (timer < 0) {
 							clearInterval(interval);
 							Swal.close();
 						}
 					}, 1000);
 					$(Swal.getPopup()).data('timer-interval', interval);
 				},
 				willClose: () => {
 					var interval = $(Swal.getPopup()).data('timer-interval');
 					if (interval) clearInterval(interval);
 				}
 			}).then((result) => {
 				if (result.isConfirmed) {
 					$('#form-transaksi').data('qris-confirmed', true).submit();
 				}
 			});
 		}
 	});
 
    // AJAX Checkout dan cetak struk kasir langsung tanpa memuat ulang (refresh) halaman utama
 	$('#btn-cetak-langsung').on('click', function(e) {
 		e.preventDefault();
 		
 		var namaPelanggan = $('input[name="nama_tamu"]').val().trim();
 		var meja = $('input[name="meja"]').val().trim();
 		var metodePembayaran = $('select[name="metode_pembayaran"]').val();
 		
 		var totalTagihan = parseInt($('#form-transaksi input[name="total"]').val()) || 0;
 		var rawBayarVal = $('#bayar-raw').val() || '';
 		var bayar = parseInt(rawBayarVal) || 0;
 		
 		if (!namaPelanggan) {
 			Swal.fire('Peringatan', 'Nama Pelanggan harus diisi!', 'warning');
 			return;
 		}
 		if (!meja) {
 			Swal.fire('Peringatan', 'Nomor Kamar harus diisi!', 'warning');
 			return;
 		}
 		if (bayar < totalTagihan) {
 			var kurang = totalTagihan - bayar;
 			Swal.fire('Pembayaran Kurang!', 'Kurang: Rp. ' + kurang.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","), 'error');
 			return;
 		}
 
 		if (metodePembayaran === 'QRIS' && !$('#form-transaksi').data('qris-confirmed')) {
 			var formattedNominal = 'Rp. ' + bayar.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
 			var qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' + encodeURIComponent('qris://javaturtleresto/pay?amount=' + bayar + '&merchant=JavaTurtleResto');
 
 			Swal.fire({
 				title: '<h4 style="color: #285c64; font-weight: bold; margin-bottom: 0;">🐢 QRIS Resto Payment</h4>',
 				html: `
 					<div style="text-align: center; padding: 10px;">
 						<p style="font-size: 13px; color: #666; margin-bottom: 15px;">Java Turtle Resto & Cafe</p>
 						<div style="background: white; padding: 15px; display: inline-block; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); margin-bottom: 15px;">
 							<img src="${qrUrl}" alt="QRIS Code" style="width: 220px; height: 220px; display: block;" />
 						</div>
 						<h3 style="font-weight: bold; color: #e67e22; margin: 5px 0 15px 0;">${formattedNominal}</h3>
 						<div style="background: #f8f9fa; padding: 10px; border-radius: 8px; font-size: 12px; color: #555; text-align: left; line-height: 1.6;">
 							• Scan menggunakan aplikasi e-wallet (GoPay, OVO, Dana, LinkAja) atau Mobile Banking Anda.<br>
 							• Pastikan nominal transfer sesuai dengan yang tertera di atas.<br>
 							• Kode QR ini kedaluwarsa dalam <b id="qris-timer-cetak" style="color: #d9534f;">05:00</b>.
 						</div>
 					</div>
 				`,
 				showCancelButton: true,
 				confirmButtonColor: '#285c64',
 				cancelButtonColor: '#d33',
 				confirmButtonText: '⚡ Konfirmasi Sudah Bayar',
 				cancelButtonText: 'Batal',
 				didOpen: () => {
 					var timer = 300;
 					var interval = setInterval(function() {
 						var minutes = Math.floor(timer / 60);
 						var seconds = timer % 60;
 						minutes = minutes < 10 ? '0' + minutes : minutes;
 						seconds = seconds < 10 ? '0' + seconds : seconds;
 						$('#qris-timer-cetak').text(minutes + ':' + seconds);
 						timer--;
 						if (timer < 0) {
 							clearInterval(interval);
 							Swal.close();
 						}
 					}, 1000);
 					$(Swal.getPopup()).data('timer-interval', interval);
 				},
 				willClose: () => {
 					var interval = $(Swal.getPopup()).data('timer-interval');
 					if (interval) clearInterval(interval);
 				}
 			}).then((result) => {
 				if (result.isConfirmed) {
 					$('#form-transaksi').data('qris-confirmed', true);
 					prosesCheckoutDanCetak(bayar, totalTagihan);
 				}
 			});
 		} else {
 			prosesCheckoutDanCetak(bayar, totalTagihan);
 		}
 	});
 
 	function prosesCheckoutDanCetak(bayar, totalTagihan) {
 		// Buka tab baru kosong untuk mencetak struk kasir
 		var printWindow = window.open('about:blank', '_blank');
 		if (!printWindow) {
 			Swal.fire('Popup Blocked', 'Mohon izinkan popup untuk mencetak struk.', 'warning');
 			return;
 		}
 		printWindow.document.write('<html><head><title>Memuat Struk...</title></head><body style="font-family:sans-serif; text-align:center; padding-top:50px;"><h3>Memproses transaksi dan memuat struk...</h3></body></html>');
 
 		var formData = $('#form-transaksi').serialize();
 		
 		$.ajax({
 			type: 'POST',
 			url: 'index.php?page=jual&nota=yes&ajax=yes',
 			data: formData,
 			dataType: 'json',
 			success: function(response) {
 				if (response.status === 'success') {
 					printWindow.location.href = 'print.php?invoice=' + encodeURIComponent(response.invoice) + '&bayar=' + encodeURIComponent(response.bayar);
 					
 					var formattedChange = 'Rp. ' + response.change.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
 					Swal.fire({
 						title: '<h4 style="color: #285c64; font-weight: bold; margin-bottom: 0;">🎉 Pembayaran Berhasil!</h4>',
 						html: `
 							<div style="text-align: center; padding: 10px;">
 								<p style="font-size: 13px; color: #666; margin-bottom: 15px;">Invoice: <b>${response.invoice}</b></p>
 								<div style="background: #f8f9fa; padding: 15px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 15px;">
 									<span style="font-size: 12px; color: #777; display: block; margin-bottom: 5px;">KEMBALIAN</span>
 									<h3 style="font-weight: bold; color: #2e7d32; margin: 0;">${formattedChange}</h3>
 								</div>
 								<p style="font-size: 11px; color: #888;">Struk telah dibuka di tab baru.</p>
 							</div>
 						`,
 						icon: 'success',
 						confirmButtonColor: '#285c64',
 						confirmButtonText: '🔄 Selesai / Transaksi Baru',
 						allowOutsideClick: false,
 						allowEscapeKey: false
 					}).then(() => {
 						window.location.href = 'index.php?page=jual';
 					});
 				} else {
 					printWindow.close();
 					Swal.fire('Error', 'Gagal memproses transaksi: ' + (response.message || 'Error tidak diketahui'), 'error');
 				}
 			},
 			error: function(xhr, status, error) {
 				printWindow.close();
 				Swal.fire('Error', 'Koneksi server terganggu: ' + error, 'error');
 			}
 		});
 	}
 });
 </script>