<?php
/**
 * -------------------------------------------------------------------------
 * Halaman Cetak Nota/Struk Penjualan - POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * Berkas ini menampilkan struk belanja restoran dalam format cetak (print view).
 * Data dapat dimuat dari keranjang belanja aktif (penjualan_temp) jika
 * dicetak sebelum bayar, atau dari riwayat penjualan berdasarkan No. Invoice.
 */

declare(strict_types=1);
@ob_start();
session_start();

// Memastikan pengguna telah masuk sesi admin/kasir sebelum mencetak nota
if (empty($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Mengatur header HTTP agar memuat tipe HTML dengan UTF-8 dan mencegah caching halaman
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require 'config.php';
include $view;

// Instansiasi class helper view untuk query data
$lihat = new view($config);
$toko  = $lihat->toko();

// Memeriksa parameter nomor invoice untuk pencetakan riwayat transaksi
$invoice = filter_input(INPUT_GET, 'invoice', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
if (!empty($invoice)) {
    // Bersihkan nilai invoice untuk menghindari serangan keamanan
    $invoice = htmlspecialchars(trim((string)$invoice), ENT_QUOTES, 'UTF-8');
    // Ambil data transaksi berdasarkan nomor invoice
    $hsl     = $lihat->penjualan_invoice($invoice);
    // Hitung total belanja dari database untuk invoice ini
    $hasil   = $lihat->jumlah_invoice($invoice);
} else {
    // Jika tidak ada nomor invoice, tampilkan data dari keranjang aktif (penjualan_temp)
    $hsl   = $lihat->penjualan();
    $hasil = $lihat->jumlah();
}

/**
 * Memformat angka nominal menjadi format mata uang Rupiah
 * 
 * @param float $n Nilai angka yang akan diformat
 * @return string String terformat (contoh: Rp 25.000)
 */
function rupiah(float $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}

// Memuat data kasir, pelanggan, nomor meja/kamar, dan tipe pesanan dari input GET
$nmMember        = (string) filter_input(INPUT_GET, 'nm_member', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
$kasir           = htmlspecialchars($nmMember !== '' ? $nmMember : ($_SESSION['admin']['nm_member'] ?? 'Kasir'), ENT_QUOTES, 'UTF-8');

$namaPelangganRaw = (string) filter_input(INPUT_GET, 'nama_pelanggan', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
$namaPelanggan    = htmlspecialchars($namaPelangganRaw !== '' ? $namaPelangganRaw : 'Pelanggan Umum', ENT_QUOTES, 'UTF-8');

$mejaRaw   = (string) filter_input(INPUT_GET, 'meja', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
$meja      = htmlspecialchars($mejaRaw !== '' ? $mejaRaw : '-', ENT_QUOTES, 'UTF-8');

$tipePesananRaw = (string) filter_input(INPUT_GET, 'tipe_pesanan', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
$tipePesanan    = htmlspecialchars($tipePesananRaw !== '' ? $tipePesananRaw : 'dine-in', ENT_QUOTES, 'UTF-8');

// Memeriksa nominal bayar dan kembalian
$bayarInput     = filter_input(INPUT_GET, 'bayar', FILTER_VALIDATE_FLOAT);
$kembaliInput   = filter_input(INPUT_GET, 'kembali', FILTER_VALIDATE_FLOAT);
$bayarNominal   = ($bayarInput !== false && $bayarInput !== null) ? (float) $bayarInput : 0.0;
$kembaliNominal = ($kembaliInput !== false && $kembaliInput !== null) ? (float) $kembaliInput : 0.0;

$metodePembayaranRaw = (string) filter_input(INPUT_GET, 'metode_pembayaran', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
$metodePembayaran    = htmlspecialchars($metodePembayaranRaw !== '' ? $metodePembayaranRaw : 'Tunai', ENT_QUOTES, 'UTF-8');

$statusPembayaranRaw = (string) filter_input(INPUT_GET, 'status_pembayaran', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
$statusPembayaran    = htmlspecialchars($statusPembayaranRaw !== '' ? $statusPembayaranRaw : 'Lunas', ENT_QUOTES, 'UTF-8');

$totalBayar = isset($hasil['bayar']) ? (float) $hasil['bayar'] : 0.0;

// Override data transaksi jika menggunakan data riwayat transaksi (berdasarkan invoice) dari database
$tanggalTransaksi = date('d/m/Y H:i');
if (!empty($invoice) && !empty($hsl)) {
    $firstItem        = $hsl[0];
    $namaPelanggan    = htmlspecialchars($firstItem['nama_pelanggan'] ?? 'Pelanggan Umum', ENT_QUOTES, 'UTF-8');
    $meja             = htmlspecialchars($firstItem['meja'] ?? '-', ENT_QUOTES, 'UTF-8');
    $tipePesanan      = htmlspecialchars($firstItem['tipe_pesanan'] ?? 'dine-in', ENT_QUOTES, 'UTF-8');
    $metodePembayaran = htmlspecialchars($firstItem['metode_pembayaran'] ?? 'Tunai', ENT_QUOTES, 'UTF-8');
    $statusPembayaran = htmlspecialchars($firstItem['status_pembayaran'] ?? 'Lunas', ENT_QUOTES, 'UTF-8');
    $kasir            = htmlspecialchars($firstItem['nm_member'] ?? $_SESSION['admin']['nm_member'] ?? 'Kasir', ENT_QUOTES, 'UTF-8');
    $tanggalTransaksi = htmlspecialchars($firstItem['tanggal_input'] ?? date('d/m/Y H:i'), ENT_QUOTES, 'UTF-8');

    if ($bayarNominal > 0.0) {
        $kembaliNominal = max(0.0, $bayarNominal - $totalBayar);
    } else {
        $bayarNominal   = $totalBayar;
        $kembaliNominal = 0.0;
    }
}

// Formatting tampilan display status pembayaran dan metode pembayaran
$statusPembayaranLower = strtolower($statusPembayaran);
$statusPembayaranDisplay = (strpos($statusPembayaranLower, 'belum') === false && (strpos($statusPembayaranLower, 'lunas') !== false || $statusPembayaranLower === 'paid')) ? 'PAID' : 'UNPAID';
$metodePembayaranDisplay = (strtolower($metodePembayaran) === 'tunai') ? 'Cash' : $metodePembayaran;
$tipePesananDisplay = (strtolower($tipePesanan) === 'dine-in') ? 'Dine In' : ((strtolower($tipePesanan) === 'take-away' || strtolower($tipePesanan) === 'takeaway') ? 'Take Away' : $tipePesanan);

$namaTokoDisplay = htmlspecialchars($toko['nama_toko'] ?? 'Java Turtle Resto & Cafe', ENT_QUOTES, 'UTF-8');
$alamatToko      = htmlspecialchars($toko['alamat_toko'] ?? 'Sarongan, Banyuwangi', ENT_QUOTES, 'UTF-8');
$tlpToko         = htmlspecialchars($toko['tlp'] ?? '-', ENT_QUOTES, 'UTF-8');
$footerNota      = nl2br(htmlspecialchars($toko['footer_nota'] ?? "Thank you for your visit!\nHope our food makes your day pleasant.", ENT_QUOTES, 'UTF-8'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Kasir — <?= $namaTokoDisplay ?></title>
    <style>
        /* ============================================================
         * BASE & PRINT MEDIA LAYOUT
         * ============================================================ */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        @media print {
            html, body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
                min-height: auto !important;
                height: auto !important;
                width: 100% !important;
            }
            .no-print { display: none !important; }
            .receipt-container {
                width: 100% !important;
                max-width: 100% !important;
                box-shadow: none !important;
                border: none !important;
                padding: 2mm !important;
                margin: 0 auto !important;
            }
            .receipt-container * {
                font-weight: 800 !important;
            }
        }

        @page {
            size: auto;
            margin: 0;
        }

        /* ============================================================
         * SCREEN LAYOUT (STYLE TAMPILAN MONITOR & THERMAL PRINT)
         * ============================================================ */
        body {
            background: #f0f4f8;
            font-family: 'Consolas', 'Courier New', Courier, monospace;
            font-size: 13px;
            font-weight: 600;
            color: #000;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 10px 40px;
            -webkit-font-smoothing: antialiased;
        }

        /* Bar tombol aksi (hanya di layar monitor) */
        .action-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.15s, box-shadow 0.15s;
            font-family: 'Segoe UI', sans-serif;
        }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 14px rgba(0,0,0,0.18); }
        .btn-print  { background: #285c64; color: #fff; }
        .btn-close  { background: #6c757d; color: #fff; }
        .btn-kasir  { background: #17a2b8; color: #fff; }

        /* Wadah Struk Belanja (Receipt Container 80mm) */
        .receipt-container {
            width: 78mm;
            background: #fff;
            padding: 14px 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            border-radius: 6px;
            color: #000;
        }
        .receipt-container,
        .receipt-container * {
            font-weight: 800 !important;
        }

        /* Bagian Header Struk */
        .header { text-align: center; margin-bottom: 12px; }
        .store-logo { font-size: 28px; margin-bottom: 6px; }
        .store-logo img { filter: contrast(180%) grayscale(100%); }
        .store-name { font-size: 18px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; color: #000; }
        .store-info { font-size: 13px; font-weight: 700; color: #000; line-height: 1.45; margin-top: 4px; }

        /* Pembatas Garis */
        .line-solid  { border-top: 2px solid #000; margin: 8px 0; }
        .line-dashed { border-top: 1.5px dashed #000; margin: 8px 0; }

        /* Tabel Data Transaksi (Meta) */
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { font-size: 13px; font-weight: 700; padding: 2.5px 0; vertical-align: top; color: #000; }
        .meta-table .label { color: #000; width: 38%; font-weight: 700; }
        .meta-table .colon { width: 5%; font-weight: 700; }
        .meta-table .value { font-weight: 800; color: #000; }

        .invoice-row {
            text-align: center;
            font-size: 13px;
            font-weight: 800;
            margin: 6px 0;
            color: #000;
        }
        .invoice-code {
            font-weight: 800;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        /* Tabel Rincian Menu Dipesan */
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table td { font-size: 13.5px; font-weight: 700; padding: 3px 0; vertical-align: top; color: #000; }
        .item-name { font-weight: 800; font-size: 14.5px; color: #000; }
        .item-qty-price { color: #000; font-size: 13px; font-weight: 700; padding-left: 2px; }
        .item-subtotal { text-align: right; font-weight: 800; font-size: 14.5px; color: #000; }
        .item-notes { font-style: italic; color: #000; font-size: 12px; font-weight: 700; padding-left: 6px; }

        /* Bagian Perhitungan Total Belanja */
        .total-table { width: 100%; border-collapse: collapse; }
        .total-table td { font-size: 14px; font-weight: 700; padding: 3.5px 0; vertical-align: top; color: #000; }
        .total-table .tr-value { text-align: right; font-weight: 800; }
        .total-table .grand-total td { font-size: 17px; font-weight: 800; padding-top: 6px; color: #000; }
        .total-table .kembalian td { font-size: 15px; font-weight: 800; border-top: 1.5px dashed #000; padding-top: 6px; color: #000; }

        /* Label Badge Status Pembayaran */
        .status-badge {
            border: 2px solid #000;
            padding: 2px 7px;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.5px;
            border-radius: 4px;
            color: #000 !important;
            display: inline-block;
        }
        .status-lunas { border-color: #000; color: #000; }
        .status-belum { border-color: #000; color: #000; }

        .footer { text-align: center; margin-top: 12px; font-size: 13px; font-weight: 700; color: #000; line-height: 1.5; }
        .footer-small { font-size: 11px; font-weight: 700; color: #000; margin-top: 8px; }

        .barcode-placeholder {
            text-align: center;
            font-size: 9px;
            color: #aaa;
            margin-top: 8px;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

    <!-- ══ BAR TOMBOL AKSI (Hanya muncul di browser layar) ══ -->
    <div class="action-bar no-print">
        <button class="btn-action btn-print" onclick="window.print()">
            🖨️ Cetak Struk
        </button>
        <a class="btn-action btn-kasir" href="index.php?page=jual">
            🛒 Kembali ke Kasir
        </a>
        <button class="btn-action btn-close" onclick="window.close()">
            ✕ Tutup
        </button>
    </div>

    <!-- ══ STRUK KASIR THERMAL ══ -->
    <div class="receipt-container">

        <!-- Logo & Profil Toko -->
        <div class="header">
            <div class="store-logo"><img src="assets/img/logo.jpeg" alt="Logo JTL" style="max-height: 48px; border-radius: 6px;"></div>
            <div class="store-name"><?= $namaTokoDisplay ?></div>
            <div class="store-info">
                <?= $alamatToko ?><br>
                Telp: <?= $tlpToko ?>
            </div>
        </div>

        <div class="line-solid"></div>

        <!-- Nomor Invoice / Nota Penjualan -->
        <?php if (!empty($invoice)): ?>
        <div class="invoice-row">
            No. Invoice: <span class="invoice-code"><?= $invoice ?></span>
        </div>
        <?php endif; ?>

        <!-- Informasi Detail Transaksi -->
        <table class="meta-table">
            <tr>
                <td class="label">Day/Time</td>
                <td class="colon">:</td>
                <td class="value"><?= $tanggalTransaksi ?></td>
            </tr>
            <tr>
                <td class="label">Chasier</td>
                <td class="colon">:</td>
                <td class="value"><?= $kasir ?></td>
            </tr>
            <tr>
                <td class="label">Costumer</td>
                <td class="colon">:</td>
                <td class="value"><?= $namaPelanggan ?></td>
            </tr>
            <tr>
                <td class="label">Room</td>
                <td class="colon">:</td>
                <td class="value"><?= $meja ?></td>
            </tr>
            <tr>
                <td class="label">Type Order</td>
                <td class="colon">:</td>
                <td class="value" style="text-transform: uppercase;"><?= $tipePesananDisplay ?></td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="colon">:</td>
                <td>
                    <span class="status-badge <?= ($statusPembayaranDisplay === 'PAID') ? 'status-lunas' : 'status-belum' ?>">
                        <?= $statusPembayaranDisplay ?>
                    </span>
                </td>
            </tr>
        </table>

        <div class="line-dashed"></div>

        <!-- Rincian Daftar Menu Pesanan -->
        <table class="items-table">
            <tr>
                <td colspan="2" style="font-size:12px;color:#000;padding-bottom:4px;font-weight:800;letter-spacing:0.5px;">MENU DETAILS</td>
                <td style="text-align:right;font-size:12px;color:#000;font-weight:800;letter-spacing:0.5px;">TOTAL</td>
            </tr>
        <?php if (is_iterable($hsl) && !empty($hsl)): ?>
        <?php foreach ($hsl as $isi):
            $namaItem  = htmlspecialchars((string)($isi['nama_barang'] ?? ''), ENT_QUOTES, 'UTF-8');
            $jumlahItem = (int)($isi['jumlah'] ?? 1);
            $totalItem  = (float)($isi['total'] ?? 0.0);
            $hargaSat   = $jumlahItem > 0 ? $totalItem / $jumlahItem : $totalItem;
            $catatan    = htmlspecialchars((string)($isi['catatan'] ?? ''), ENT_QUOTES, 'UTF-8');
        ?>
            <tr>
                <td colspan="2" class="item-name"><?= $namaItem ?></td>
                <td class="item-subtotal" rowspan="2"><?= rupiah($totalItem) ?></td>
            </tr>
            <tr>
                <td colspan="2" class="item-qty-price"><?= $jumlahItem ?> x <?= rupiah($hargaSat) ?></td>
            </tr>
            <?php if ($catatan !== ''): ?>
            <tr>
                <td colspan="3" class="item-notes">* Notes: <?= $catatan ?></td>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3" style="text-align:center;padding:8px 0;color:#888;">Belum ada menu dipesan.</td>
            </tr>
        <?php endif; ?>
        </table>

        <div class="line-solid"></div>

        <!-- Tabel Perhitungan Pembayaran -->
        <table class="total-table">
            <tr class="grand-total">
                <td class="tr-label">TOTAL SPENDING</td>
                <td class="tr-value"><?= rupiah($totalBayar) ?></td>
            </tr>
            <tr>
                <td class="tr-label">PAYMENT (<?= $metodePembayaranDisplay ?>)</td>
                <td class="tr-value"><?= rupiah($bayarNominal) ?></td>
            </tr>
            <tr class="kembalian">
                <td class="tr-label">REFOUND</td>
                <td class="tr-value"><?= rupiah(max(0.0, $kembaliNominal)) ?></td>
            </tr>
        </table>

        <div class="line-dashed"></div>

        <!-- Footer Struk & Catatan Kaki -->
        <div class="footer">
            <?= $footerNota ?>
            <div class="barcode-placeholder"></div>
            <div class="footer-small">POS Kasir Resto &mdash; Powered by Java Turtle Lodge</div>
        </div>

    </div>

    <script>
        // Otomatis memicu fungsi cetak browser setelah halaman selesai dimuat sepenuhnya
        window.addEventListener('load', function () {
            if (window.opener || window.name === '_blank') {
                setTimeout(function () {
                    window.print();
                }, 400);
            }
        });
    </script>

</body>
</html>