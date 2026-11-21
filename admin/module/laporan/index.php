<?php
/**
 * -------------------------------------------------------------------------
 * Laporan Transaksi Keuangan - POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * Halaman ini menampilkan histori transaksi penjualan. Admin dapat memfilter
 * data per hari atau per bulan, mengekspor laporan ke file excel (Spreadsheet),
 * serta melihat total pendapatan dan porsi terjual secara kumulatif.
 */
       // Array pemetaan kode bulan ke nama bulan dalam Bahasa Indonesia
       $bulan_tes = array(
                '01'=>"Januari",
                '02'=>"Februari",
                '03'=>"Maret",
                '04'=>"April",
                '05'=>"Mei",
                '06'=>"Juni",
                '07'=>"Juli",
                '08'=>"Agustus",
                '09'=>"September",
                '10'=>"Oktober",
                '11'=>"November",
                '12'=>"Desember"
       );

       // Membaca status filter aktif yang dikirimkan via form POST/GET
       $cariParamRaw = filter_input(INPUT_GET, 'cari', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
       $cariActive = is_string($cariParamRaw) && $cariParamRaw !== '';

       $hariParamRaw = filter_input(INPUT_GET, 'hari', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
       $hariActive = ($hariParamRaw === 'cek');

       $bulanPostRaw = filter_input(INPUT_POST, 'bln', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
       $bulanPost = (is_string($bulanPostRaw) && preg_match('/^(0[1-9]|1[0-2])$/', $bulanPostRaw)) ? $bulanPostRaw : '';

       $tahunPostRaw = filter_input(INPUT_POST, 'thn', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
       $tahunPost = (is_string($tahunPostRaw) && preg_match('/^\d{4}$/', $tahunPostRaw)) ? $tahunPostRaw : '';

       $hariPostRaw = filter_input(INPUT_POST, 'hari', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
       $hariPost = is_string($hariPostRaw) ? trim($hariPostRaw) : '';
?>
<div class="row">
	<div class="col-md-12">
        <!-- Merender Judul Laporan secara dinamis sesuai filter yang dipilih -->
		<h4>
            <?php if($cariActive && $bulanPost !== '' && $tahunPost !== ''){ ?>
            Laporan Penjualan - Bulan <?= htmlspecialchars($bulan_tes[$bulanPost] ?? $bulanPost, ENT_QUOTES, 'UTF-8');?> <?= htmlspecialchars($tahunPost, ENT_QUOTES, 'UTF-8');?>
            <?php }elseif($hariActive && $hariPost !== ''){?>
            Laporan Penjualan - Tanggal <?= htmlspecialchars($hariPost, ENT_QUOTES, 'UTF-8');?>
            <?php }else{?>
            Laporan Penjualan - Bulan <?= htmlspecialchars($bulan_tes[date('m')], ENT_QUOTES, 'UTF-8');?> <?= date('Y');?>
            <?php }?>
		</h4>
		<br />
        
        <!-- Panel Filter Pencarian Laporan -->
		<div class="card">
			<div class="card-header bg-primary text-white">
				<h5 class="card-title mt-2">Filter Laporan Per Bulan / Per Hari</h5>
			</div>
			<div class="card-body">
                <div class="row">
                    <!-- FILTER BULANAN -->
                    <div class="col-md-6 border-right">
                        <form method="post" action="index.php?page=laporan&cari=ok">
                                <?php echo csrf_field(); ?>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th>Pilih Bulan</th>
                                    <th>Pilih Tahun</th>
                                    <th>Aksi</th>
                                </tr>
                                <tr>
                                    <td>
                                        <select name="bln" class="form-control" required>
                                            <option value="">Bulan</option>
                                            <?php
                                        $bulan=array("Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember");
                                        $jlh_bln=count($bulan);
                                        $bln1 = array('01','02','03','04','05','06','07','08','09','10','11','12');
                                        for($c=0; $c<$jlh_bln; $c+=1){
                                            $selected = $bulanPost === $bln1[$c] ? 'selected' : '';
                                            echo"<option value='$bln1[$c]' $selected> $bulan[$c] </option>";
                                        }
                                    ?>
                                        </select>
                                    </td>
                                    <td>
                                    <?php
                                        $now=date('Y');
                                        echo "<select name='thn' class='form-control' required>";
                                        echo '<option value="">Tahun</option>';
                                        for ($a=2024;$a<=$now;$a++)
                                        {
                                            $selected = $tahunPost === (string)$a ? 'selected' : '';
                                            echo "<option value='$a' $selected>$a</option>";
                                        }
                                        echo "</select>";
                                    ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-block">
                                            <i class="fa fa-search"></i> Filter Bulan
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                    
                    <!-- FILTER HARIAN -->
                    <div class="col-md-6">
                        <form method="post" action="index.php?page=laporan&hari=cek">
                                <?php echo csrf_field(); ?>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th>Pilih Hari</th>
                                    <th>Aksi</th>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="date" value="<?= $hariPost !== '' ? htmlspecialchars($hariPost, ENT_QUOTES, 'UTF-8') : date('Y-m-d');?>" class="form-control" name="hari" required>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-block">
                                            <i class="fa fa-search"></i> Filter Hari
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </div>
                
                <!-- Opsi Export Excel (Spreadsheet) & Reset Filter -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <?php 
                            // Tentukan link unduh excel berdasarkan filter aktif
                            if ($cariActive && $bulanPost !== '' && $tahunPost !== '') {
                                $excelLink = "excel.php?cari=yes&bln=".urlencode($bulanPost)."&thn=".urlencode($tahunPost);
                            } elseif ($hariActive && $hariPost !== '') {
                                $excelLink = "excel.php?hari=cek&tgl=".urlencode($hariPost);
                            } else {
                                $excelLink = "excel.php";
                            }
                        ?>
                        <a href="<?= $excelLink; ?>" class="btn btn-success">
                            <i class="fa fa-file-excel"></i> Export Excel (Download Laporan)
                        </a>
                        <a href="index.php?page=laporan" class="btn btn-secondary">
                            <i class="fa fa-refresh"></i> Reset Filter
                        </a>
                    </div>
                </div>
			</div>
		</div>
         <br />
         
         <!-- Tabel Log Transaksi Laporan -->
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-bordered w-100 table-sm" id="example1">
						<thead>
							<tr style="background:#DFF0D8;color:#333;">
								<th> No</th>
								<th> Kode Menu</th>
								<th> Nama Pelanggan</th>
								<th> Nama Menu</th>
								<th> No Kamar</th>
								<th> Tipe Pesanan</th>
								<th> Qty</th>
								<th> Total Bayar</th>
								<th> Kasir</th>
								<th> Waktu Transaksi</th>
								<th> Aksi</th>
							</tr>
						</thead>
						<tbody>
							<?php 
								$no=1; 
                                // Query data transaksi dari helper view berdasarkan filter aktif
                                if($cariActive && $bulanPost !== '' && $tahunPost !== ''){
                                        $periode = $bulanPost.'-'.$tahunPost;
                                        $hasil = $lihat->periode_jual($periode);
                                }elseif($hariActive && $hariPost !== ''){
                                        $hari = $hariPost;
									$hasil = $lihat->hari_jual($hari);
								}else{
									$hasil = $lihat->jual();
								}
							?>
							<?php 
								$total_pendapatan = 0;
								$total_kuantitas = 0;
								foreach($hasil as $isi){ 
									$total_pendapatan += (float) $isi['total'];
									$total_kuantitas += (int) $isi['jumlah'];
							?>
							<tr>
								<td><?= htmlspecialchars((string) $no, ENT_QUOTES, 'UTF-8');?></td>
								<td><?= htmlspecialchars($isi['id_barang'], ENT_QUOTES, 'UTF-8');?></td>
								<td><b><?= htmlspecialchars($isi['nama_pelanggan'] ?? '', ENT_QUOTES, 'UTF-8');?></b></td>
								<td><?= htmlspecialchars($isi['nama_barang'], ENT_QUOTES, 'UTF-8');?></td>
								<td><?= htmlspecialchars($isi['meja'] ?? '-', ENT_QUOTES, 'UTF-8');?></td>
								<td><?= htmlspecialchars(ucfirst($isi['tipe_pesanan'] ?? 'dine-in'), ENT_QUOTES, 'UTF-8');?></td>
								<td><?= htmlspecialchars($isi['jumlah'], ENT_QUOTES, 'UTF-8');?> Porsi</td>
								<td><b>Rp. <?php echo number_format((float)$isi['total']);?>,-</b></td>
								<td><?= htmlspecialchars($isi['nm_member'], ENT_QUOTES, 'UTF-8');?></td>
								<td><?= htmlspecialchars($isi['tanggal_input'], ENT_QUOTES, 'UTF-8');?></td>
								<td>
                                    <!-- Tombol print ulang struk belanja -->
									<?php if (!empty($isi['no_invoice'])): ?>
										<a href="print.php?invoice=<?= urlencode($isi['no_invoice']); ?>" class="btn btn-secondary btn-sm" target="_blank" title="Cetak Struk">
											<i class="fa fa-print"></i> Struk
										</a>
									<?php else: ?>
										-
									<?php endif; ?>
								</td>
							</tr>
							<?php $no++; }?>
						</tbody>
						<tfoot>
                            <!-- Baris kalkulasi total porsi dan nominal pendapatan secara keseluruhan -->
							<tr style="background:#F2F2F2; font-weight:bold;">
								<th colspan="6">TOTAL PENJUALAN</th>
								<th><?= htmlspecialchars((string)$total_kuantitas, ENT_QUOTES, 'UTF-8');?> Porsi</th>
								<th colspan="4" style="background:#0bb365;color:#fff;">
									TOTAL PENDAPATAN: Rp. <?php echo number_format($total_pendapatan);?>,-
                                </th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
     </div>
 </div>