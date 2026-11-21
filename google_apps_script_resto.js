/**
 * Google Apps Script - Java Turtle Resto POS Cloud Sync Web App
 * 
 * Silakan salin kode ini ke Google Apps Script Spreadsheet milikmu!
 * Cara Setup:
 * 1. Buka Google Sheets baru.
 * 2. Klik menu Ekstensi (Extensions) -> Apps Script.
 * 3. Hapus kode default, lalu paste kode di bawah ini.
 * 4. Klik ikon Save.
 * 5. Klik Deploy -> New Deployment.
 * 6. Pilih tipe Web App (Aplikasi Web).
 * 7. Konfigurasi: Execute as "Me", Who has access "Anyone".
 * 8. Klik Deploy, setujui izin akses akun Google kamu, lalu salin URL Web App yang didapat.
 * 9. Tempelkan URL tersebut ke file `fungsi/spreadsheet.php` di baris ke-18.
 */

function doPost(e) {
  var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
  
  // Buat header otomatis jika sheet masih kosong
  if (sheet.getLastRow() === 0) {
    sheet.appendRow([
      "Tanggal", 
      "Kasir", 
      "Nama Pelanggan", 
      "Meja", 
      "Tipe Pesanan", 
      "Nama Menu Makanan/Minuman", 
      "Catatan", 
      "Qty / Porsi", 
      "Subtotal", 
      "Grand Total", 
      "Metode Pembayaran", 
      "Status Pembayaran"
    ]);
    
    // Format bold untuk header
    sheet.getRange("A1:L1").setFontWeight("bold").setBackground("#dff0d8").setHorizontalAlignment("center");
  }
  
  try {
    var data = JSON.parse(e.postData.contents);
    
    var tanggal = data.tanggal;
    var namaPelanggan = data.nama_pelanggan;
    var meja = data.meja;
    var tipePesanan = data.tipe_pesanan;
    var totalBayar = data.total_bayar;
    var metodePembayaran = data.metode_pembayaran;
    var statusPembayaran = data.status_pembayaran;
    var kasir = data.kasir;
    var items = data.items; // array of items
    
    // Tulis ke sheet untuk setiap item pesanan (Multi-row per transaction)
    for (var i = 0; i < items.length; i++) {
      var item = items[i];
      sheet.appendRow([
        tanggal,
        kasir,
        namaPelanggan,
        meja,
        tipePesanan,
        item.nama_menu,
        item.catatan,
        item.jumlah,
        item.subtotal,
        totalBayar,
        metodePembayaran,
        statusPembayaran
      ]);
    }
    
    return ContentService.createTextOutput(JSON.stringify({
      "status": "success",
      "message": "Data transaksi resto berhasil disinkronkan!",
      "rows_added": items.length
    })).setMimeType(ContentService.MimeType.JSON);
    
  } catch(error) {
    return ContentService.createTextOutput(JSON.stringify({
      "status": "error",
      "message": error.toString()
    })).setMimeType(ContentService.MimeType.JSON);
  }
}
