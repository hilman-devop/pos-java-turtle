<?php 
/**
 * -------------------------------------------------------------------------
 * Ekspor Laporan Penjualan ke Excel - POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * Berkas ini memformat dan mengunduh laporan penjualan dalam format spreadsheet
 * (.xls). Data transaksi yang diambil didasarkan pada filter parameter GET
 * (harian atau bulanan/tahunan) dan dimuat ke dalam tabel HTML yang terstruktur.
 */

    declare(strict_types=1);
	@ob_start();
	session_start();

    // Memastikan user yang mengakses telah terotentikasi sebagai admin
    if (empty($_SESSION['admin'])) {
        header('Location: login.php');
        exit;
    }

    // Mengirimkan header HTTP agar browser mengunduh halaman ini sebagai file MS Excel (.xls)
    header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=data-laporan-resto-".date('Y-m-d').".xls");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false); 

    require 'config.php';
    include $view;
    $lihat = new view($config);

    // Array pemetaan nama-nama bulan untuk tampilan judul laporan
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

    // Membaca parameter pencarian laporan bulanan/tahunan
    $cariParamRaw = filter_input(INPUT_GET, 'cari', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
    $cariParam = is_string($cariParamRaw) ? trim($cariParamRaw) : '';
    $cariActive = in_array($cariParam, ['yes', 'ok'], true);

    // Membaca parameter pencarian laporan harian
    $hariParamRaw = filter_input(INPUT_GET, 'hari', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
    $hariParam = is_string($hariParamRaw) ? trim($hariParamRaw) : '';
    $hariActive = ($hariParam === 'cek');

    // Membaca dan memvalidasi nilai bulan pencarian (harus berformat dua digit 01-12)
    $bulanRaw = filter_input(INPUT_GET, 'bln', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
    $bulanParam = (is_string($bulanRaw) && preg_match('/^(0[1-9]|1[0-2])$/', $bulanRaw)) ? $bulanRaw : '';

    // Membaca dan memvalidasi nilai tahun pencarian (harus berformat 4 digit angka)
    $tahunRaw = filter_input(INPUT_GET, 'thn', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
    $tahunParam = (is_string($tahunRaw) && preg_match('/^\d{4}$/', $tahunRaw)) ? $tahunRaw : '';

    // Membaca dan memvalidasi nilai tanggal pencarian (harus berformat YYYY-MM-DD)
    $tanggalRaw = filter_input(INPUT_GET, 'tgl', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
    $tanggalParam = (is_string($tanggalRaw) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalRaw)) ? $tanggalRaw : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Restoran & Cafe</title>
</head>
<body>
    <div class="modal-view">
        
        <!-- Judul Dinamis Laporan Berdasarkan Filter -->
        <h3 style="text-align:center;">
                <?php if($cariActive && $bulanParam !== '' && $tahunParam !== ''){ ?>
                    Laporan Keuangan Penjualan Resto - Bulan <?= htmlspecialchars($bulan_tes[$bulanParam] ?? $bulanParam, ENT_QUOTES, 'UTF-8');?> <?= htmlspecialchars($tahunParam, ENT_QUOTES, 'UTF-8');?>
                <?php }elseif($hariActive && $tanggalParam !== ''){?>
                    Laporan Keuangan Penjualan Resto - Tanggal <?= htmlspecialchars($tanggalParam, ENT_QUOTES, 'UTF-8');?>
                <?php }else{?>
                    Laporan Keuangan Penjualan Resto - Bulan <?= htmlspecialchars($bulan_tes[date('m')], ENT_QUOTES, 'UTF-8');?> <?= date('Y');?>
                <?php }?>
        </h3>
        
        <!-- Tabel Laporan Utama -->
        <table border="1" width="100%" cellpadding="3" cellspacing="4">
            <thead>
                <tr bgcolor="yellow" style="font-weight:bold;">
                    <th> No</th>
                    <th> No. Invoice</th>
                    <th> Kode Menu</th>
                    <th> Nama Pelanggan</th>
                    <th> Nama Menu Makanan/Minuman</th>
                    <th> Nomor Kamar</th>
                    <th> Tipe Pesanan</th>
                    <th> Qty/Porsi</th>
                    <th> Total Harga</th>
                    <th> Metode Pembayaran</th>
                    <th> Status Pembayaran</th>
                    <th> Penerima (Kasir)</th>
                    <th> Waktu Transaksi</th>
                    <th> Catatan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $no=1;
                    // Memuat data dari database berdasarkan status filter parameter pencarian aktif
                    if($cariActive && $bulanParam !== '' && $tahunParam !== ''){
                        $periode = $bulanParam.'-'.$tahunParam;
                        $hasil = $lihat->periode_jual($periode);
                    }elseif($hariActive && $tanggalParam !== ''){
                        $hasil = $lihat->hari_jual($tanggalParam);
                    }else{
                        // Default memuat data penjualan pada bulan aktif saat ini
                        $hasil = $lihat->jual();
                    }
                ?>
                <?php 
                    $total_pendapatan = 0;
                    $total_kuantitas = 0;
                    
                    // Melakukan perulangan untuk mencetak baris demi baris transaksi penjualan
                    foreach($hasil as $isi){ 
                        $total_pendapatan += (float)$isi['total'];
                        $total_kuantitas += (int)$isi['jumlah'];
                ?>
                <tr>
                    <td><?php echo $no;?></td>
                    <td><?= htmlspecialchars($isi['no_invoice'] ?? '-', ENT_QUOTES, 'UTF-8');?></td>
                    <td><?= htmlspecialchars($isi['id_barang'], ENT_QUOTES, 'UTF-8');?></td>
                    <td><b><?= htmlspecialchars($isi['nama_pelanggan'] ?? '', ENT_QUOTES, 'UTF-8');?></b></td>
                    <td><?= htmlspecialchars($isi['nama_barang'], ENT_QUOTES, 'UTF-8');?></td>
                    <td><?= htmlspecialchars($isi['meja'] ?? '-', ENT_QUOTES, 'UTF-8');?></td>
                    <td><?= htmlspecialchars(ucfirst($isi['tipe_pesanan'] ?? 'dine-in'), ENT_QUOTES, 'UTF-8');?></td>
                    <td><?= htmlspecialchars($isi['jumlah'], ENT_QUOTES, 'UTF-8');?> </td>
                    <td>Rp. <?php echo number_format((float)$isi['total']);?>,-</td>
                    <td><?= htmlspecialchars($isi['metode_pembayaran'] ?? 'Tunai', ENT_QUOTES, 'UTF-8');?></td>
                    <td><?= htmlspecialchars($isi['status_pembayaran'] ?? 'Lunas', ENT_QUOTES, 'UTF-8');?></td>
                    <td><?= htmlspecialchars($isi['nm_member'], ENT_QUOTES, 'UTF-8');?></td>
                    <td><?= htmlspecialchars($isi['tanggal_input'], ENT_QUOTES, 'UTF-8');?></td>
                    <td><?= htmlspecialchars($isi['catatan'] ?? '', ENT_QUOTES, 'UTF-8');?></td>
                </tr>
                <?php $no++; }?>
                
                <!-- Baris Akumulasi Total -->
                <tr bgcolor="#E2E2E2" style="font-weight:bold;">
                    <td colspan="7"><b>TOTAL TRANSAKSI</b></td>
                    <td><b><?php echo $total_kuantitas;?> Porsi/Pcs</b></td>
                    <td colspan="6"><b>TOTAL PENDAPATAN: Rp. <?php echo number_format($total_pendapatan);?>,-</b></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
