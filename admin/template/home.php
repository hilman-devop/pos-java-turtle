<h3>Dashboard</h3>
<div class="subtitle text-muted mb-4">Selamat datang di sistem manajemen kasir Java Turtle Lodge</div>

<?php 
    // Mengambil ringkasan hitungan baris menu, kategori, dan transaksi untuk ditampilkan pada widget dashboard
    $hasil_barang = $lihat->barang_row();
    $hasil_kategori = $lihat->kategori_row();
    $jual = $lihat->jual_row();
    
    // Set default role admin
    $role = 'admin'; 
?>

<div class="row">
    <!-- WIDGET 1: JUMLAH MENU RESTORAN -->
    <div class="col-md-4 mb-4">
        <div class="card border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Menu</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($hasil_barang);?> Menu</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hamburger fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-0 py-2">
                <a href='index.php?page=menu' class="text-xs font-weight-bold text-primary">Kelola Menu <i class='fa fa-angle-double-right'></i></a>
            </div>
        </div>
    </div>

    <!-- WIDGET 2: TOTAL TRANSAKSI PENJUALAN -->
    <div class="col-md-4 mb-4">
        <div class="card border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Transaksi Lunas</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format((float)($jual['stok'] ?? 0));?> Transaksi</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-cash-register fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-0 py-2">
                <a href='index.php?page=laporan' class="text-xs font-weight-bold text-success">Laporan Penjualan <i class='fa fa-angle-double-right'></i></a>
            </div>
        </div>
    </div>

    <!-- WIDGET 3: JUMLAH KATEGORI MENU -->
    <div class="col-md-4 mb-4">
        <div class="card border-left-warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Kategori Menu</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($hasil_kategori);?> Kategori</div>
                    </div>
                    <div class="col-auto">
                        <i class="fa fa-tags fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-0 py-2">
                <a href='index.php?page=kategori' class="text-xs font-weight-bold text-warning">Kelola Kategori <i class='fa fa-angle-double-right'></i></a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- PANEL RIWAYAT TRANSAKSI TERKINI -->
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-primary text-white py-3" style="background-color: #285c64 !important; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h5 class="m-0 font-weight-bold text-white"><i class="fas fa-history mr-2"></i> Riwayat Transaksi Terkini</h5>
            </div>
            <div class="card-body bg-white p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover m-0 align-middle">
                        <thead class="bg-light text-muted text-xs uppercase font-weight-bold" style="background-color: #f8f9fc !important;">
                            <tr>
                                <th class="px-4 py-3" style="font-size: 11px;">Pelanggan</th>
                                <th class="py-3" style="font-size: 11px;">Pesanan</th>
                                <th class="py-3" style="font-size: 11px;">Kamar </th>
                                <th class="px-4 py-3 text-right" style="font-size: 11px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Mengambil 5 transaksi penjualan terakhir beserta relasi data menu makanannya
                            $sql_recent = "SELECT r.*, k.nama as nama_menu, k.tipe as tipe_item
                                           FROM penjualan r
                                           LEFT JOIN menu_layanan k ON r.id_menu = k.id_menu
                                           ORDER BY r.id_penjualan DESC LIMIT 5";
                            $row_recent = $config->prepare($sql_recent);
                            $row_recent->execute();
                            $recents = $row_recent->fetchAll();
                            
                            if (count($recents) > 0) {
                                foreach($recents as $recent) {
                                    $mBayar = htmlspecialchars($recent['metode_pembayaran'] ?? 'Tunai', ENT_QUOTES, 'UTF-8');
                                    $sBayar = htmlspecialchars($recent['status_pembayaran'] ?? 'Lunas', ENT_QUOTES, 'UTF-8');
                                    $sBayarLower = strtolower($sBayar);
                                    // Memeriksa kecocokan status pembayaran lunas atau belum lunas ( paid / lunas )
                                    $isLunas = (strpos($sBayarLower, 'belum') === false && (strpos($sBayarLower, 'lunas') !== false || $sBayarLower === 'paid'));
                                    $statusBadge = $isLunas ? 'badge-success' : 'badge-danger';
                            ?>
                            <tr style="font-size: 13px;">
                                <td class="px-4 font-weight-bold text-gray-800" style="vertical-align: middle;"><?= htmlspecialchars($recent['nama_pelanggan'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td style="vertical-align: middle;">
                                    <div class="font-weight-bold" style="color: #333;"><?= htmlspecialchars($recent['nama_menu'] ?? 'Tidak Diketahui', ENT_QUOTES, 'UTF-8'); ?></div>
                                    <small class="text-muted"><i class="far fa-clock mr-1"></i><?= htmlspecialchars($recent['tanggal_input'], ENT_QUOTES, 'UTF-8'); ?></small>
                                </td>
                                <td style="vertical-align: middle;">
                                    <div class="text-xs text-muted mb-1"><i class="fas fa-bed mr-1"></i>Kamar: <?= htmlspecialchars($recent['meja'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                    <span class="badge <?= $statusBadge; ?> text-xs" style="padding: 3px 6px; border-radius: 4px; font-size: 10px;"><?= strtoupper($sBayar); ?></span>
                                </td>
                                <td class="px-4 text-right font-weight-bold text-primary" style="vertical-align: middle; color: #285c64 !important;">Rp <?= number_format((float)$recent['total']); ?></td>
                            </tr>
                            <?php } } else { ?>
                            <!-- State Tampilan jika belum ada riwayat penjualan sama sekali -->
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <i class="fas fa-receipt text-muted fa-2x mb-2"></i>
                                    <p class="text-muted m-0" style="font-size: 13px;">Belum ada transaksi penjualan hari ini.</p>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>