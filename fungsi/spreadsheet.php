<?php
declare(strict_types=1);

/**
 * -------------------------------------------------------------------------
 * Sinkronisasi Transaksi ke Google Sheets - POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * Fungsi ini bertugas mengirimkan data hasil transaksi penjualan secara
 * asinkron (real-time) ke Google Sheets menggunakan API Google Apps Script.
 * Data dikirim dalam format JSON melalui metode cURL POST.
 */

/**
 * Mengirimkan data transaksi penjualan ke Web App Google Sheets
 *
 * @param string $namaTamu Nama tamu/pelanggan resto
 * @param string $meja Nomor meja atau nomor kamar
 * @param string $tipePesanan Tipe pesanan (dine-in / take-away)
 * @param float $totalBayar Grand total nilai transaksi belanja
 * @param array $items Array berisi menu apa saja yang dipesan
 * @param string $metodePembayaran Metode pembayaran (Tunai, QRIS, dll)
 * @param string $statusPembayaran Status nota (Lunas / Belum Lunas)
 */
function sync_to_spreadsheet(
    string $namaTamu,
    string $meja,
    string $tipePesanan,
    float $totalBayar,
    array $items,
    string $metodePembayaran = 'Tunai',
    string $statusPembayaran = 'Lunas'
): void {
    // URL Web App Google Apps Script hasil deploy dari Google Sheets
    $webAppUrl = 'https://script.google.com/macros/s/AKfycby1kJegdGVIdkzdUWZUEtO6qwHmty11QTZsdd3FfLe6dJu4pgp-Y0AI3-N-dwKpoBnl2g/exec';

    // Menyusun format item menu ke dalam bentuk array berstruktur agar mudah dibaca di Apps Script
    $transactionDetails = [];
    foreach ($items as $isi) {
        $transactionDetails[] = [
            'nama_menu' => $isi['nama_barang'],
            'jumlah' => $isi['jumlah'],
            'catatan' => $isi['catatan'] ?? '-',
            'subtotal' => (float) $isi['total']
        ];
    }

    // Payload JSON utama yang akan dikirimkan
    $postData = [
        'tanggal' => date('Y-m-d H:i:s'),
        'nama_pelanggan' => $namaTamu,
        'meja' => $meja,
        'tipe_pesanan' => $tipePesanan,
        'total_bayar' => $totalBayar,
        'metode_pembayaran' => $metodePembayaran,
        'status_pembayaran' => $statusPembayaran,
        'kasir' => $_SESSION['admin']['nm_member'] ?? 'Kasir Resto',
        'items' => $transactionDetails
    ];

    // Direktori dan nama berkas pencatatan log sinkronisasi
    $logDir = __DIR__ . '/../scratch';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/spreadsheet_sync_log.txt';

    // Jika URL Apps Script belum diatur/masih bernilai kosong, simpan log lokal saja
    if (strpos($webAppUrl, 'HTTPS_YOUR_') === 0) {
        $logMessage = "[" . date('Y-m-d H:i:s') . "] LOG ONLY (Webhook not set): Pelanggan: $namaTamu, Meja: $meja, Total: Rp." . number_format($totalBayar) . "\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        return;
    }

    // Melakukan inisialisasi cURL untuk request POST ke Google Sheets
    $ch = curl_init($webAppUrl);
    if ($ch !== false) {
        $jsonData = json_encode($postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout maksimal 5 detik agar kasir tidak lag/macet
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Dinonaktifkan untuk mempermudah development lokal

        // Menjalankan request cURL
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        // Menuliskan hasil sinkronisasi (Sukses / Gagal) ke dalam berkas log
        if ($err) {
            $logMessage = "[" . date('Y-m-d H:i:s') . "] SYNC ERROR: $err | Pelanggan: $namaTamu, Meja: $meja\n";
        } else {
            $logMessage = "[" . date('Y-m-d H:i:s') . "] SYNC SUCCESS: Response: $response | Pelanggan: $namaTamu, Meja: $meja\n";
        }
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
?>
