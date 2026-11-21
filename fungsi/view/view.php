<?php
/**
 * -------------------------------------------------------------------------
 * Class View - Query Data Sistem POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * Class ini berisi kumpulan fungsi SELECT (READ) untuk mengambil data dari
 * database, seperti data member, profil toko, kategori menu, daftar barang/menu,
 * stok barang, pencarian barang, laporan harian/bulanan, dan keranjang belanja.
 */

class view
{
    // Properti pelindung untuk menyimpan instansi koneksi database PDO
    protected $db;

    /**
     * Constructor Class View
     *
     * @param PDO $db Objek koneksi PDO database
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Mengambil semua data member yang terhubung dengan kredensial login-nya
     *
     * @return array
     */
    public function member()
    {
        $sql = "select member.*, login.*
                from member inner join login on member.id_member = login.id_member";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Mengambil detail profil member tertentu untuk keperluan edit profil
     *
     * @param int $id ID member
     * @return array|false
     */
    public function member_edit($id)
    {
        $sql = "select member.*, login.*
                from member inner join login on member.id_member = login.id_member
                where member.id_member= ?";
        $row = $this->db->prepare($sql);
        $row->execute(array($id));
        $hasil = $row->fetch();
        return $hasil;
    }

    /**
     * Mengambil pengaturan identitas restoran/toko
     *
     * @return array|false
     */
    public function toko()
    {
        $sql = "select*from toko where id_toko='1'";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetch();
        return $hasil;
    }

    /**
     * Mengambil semua kategori menu makanan/minuman
     *
     * @return array
     */
    public function kategori()
    {
        $sql = "select*from kategori";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Mengambil daftar seluruh menu resto yang terhubung dengan kategorinya.
     * Disesuaikan dengan alias legacy (id_barang, nama_barang, harga_jual) agar kompatibel dengan sistem lama.
     *
     * @return array
     */
    public function barang()
    {
        $sql = "select menu_layanan.*, menu_layanan.id_menu as id_barang, menu_layanan.nama as nama_barang, 
                menu_layanan.harga as harga_jual, menu_layanan.harga as harga_beli, 
                CASE WHEN menu_layanan.tipe = 'makanan' THEN 'Porsi' ELSE 'Pcs' END as satuan_barang, 
                '' as merk, kategori.id_kategori, kategori.nama_kategori
                from menu_layanan inner join kategori on menu_layanan.id_kategori = kategori.id_kategori 
                ORDER BY id DESC";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Mengambil menu resto yang stoknya menipis (kurang dari atau sama dengan 10)
     *
     * @return array
     */
    public function barang_stok()
    {
        $sql = "select menu_layanan.*, menu_layanan.id_menu as id_barang, menu_layanan.nama as nama_barang, 
                menu_layanan.harga as harga_jual, menu_layanan.harga as harga_beli, 
                CASE WHEN menu_layanan.tipe = 'makanan' THEN 'Porsi' ELSE 'Pcs' END as satuan_barang, 
                '' as merk, kategori.id_kategori, kategori.nama_kategori
                from menu_layanan inner join kategori on menu_layanan.id_kategori = kategori.id_kategori 
                where stok <= 10 
                ORDER BY id DESC";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Mengambil data satu menu resto spesifik untuk form ubah data
     *
     * @param string $id ID Menu (contoh: MN001)
     * @return array|false
     */
    public function barang_edit($id)
    {
        $sql = "select menu_layanan.*, menu_layanan.id_menu as id_barang, menu_layanan.nama as nama_barang, 
                menu_layanan.harga as harga_jual, menu_layanan.harga as harga_beli, 
                CASE WHEN menu_layanan.tipe = 'makanan' THEN 'Porsi' ELSE 'Pcs' END as satuan_barang, 
                '' as merk, kategori.id_kategori, kategori.nama_kategori
                from menu_layanan inner join kategori on menu_layanan.id_kategori = kategori.id_kategori
                where id_menu=?";
        $row = $this->db->prepare($sql);
        $row->execute(array($id));
        $hasil = $row->fetch();
        return $hasil;
    }

    /**
     * Mencari menu resto berdasarkan kata kunci nama, tipe, atau ID menu
     *
     * @param string $cari Kata kunci pencarian
     * @return array
     */
    public function barang_cari($cari)
    {
        $param = "%{$cari}%";
        $sql = "select menu_layanan.*, menu_layanan.id_menu as id_barang, menu_layanan.nama as nama_barang, 
                menu_layanan.harga as harga_jual, menu_layanan.harga as harga_beli, 
                CASE WHEN menu_layanan.tipe = 'makanan' THEN 'Porsi' ELSE 'Pcs' END as satuan_barang, 
                '' as merk, kategori.id_kategori, kategori.nama_kategori
                from menu_layanan inner join kategori on menu_layanan.id_kategori = kategori.id_kategori
                where id_menu like ? or nama like ? or tipe like ?";
        $row = $this->db->prepare($sql);
        $row->execute(array($param, $param, $param));
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Menghasilkan ID Menu unik berikutnya secara otomatis (Auto-increment format kode: MN001, MN002, dst)
     *
     * @return string
     */
    public function barang_id()
    {
        $sql = 'SELECT * FROM menu_layanan ORDER BY id DESC';
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetch();

        // Jika tabel kosong, inisialisasi dengan MN001
        if (!$hasil) {
            return 'MN001';
        }

        $lastId = $hasil['id_menu'];
        $prefix = substr($lastId, 0, 2); // Mengambil prefix "MN"
        $urut = substr($lastId, 2);      // Mengambil bagian nomor urut
        $tambah = (int) $urut + 1;
        
        // Memformat penomoran dengan padding 3 digit (contoh: 001, 012, 123)
        if (strlen((string)$tambah) == 1) {
            $format = $prefix.'00'.$tambah;
        } elseif (strlen((string)$tambah) == 2) {
            $format = $prefix.'0'.$tambah;
        } else {
            $format = $prefix.$tambah;
        }
        return $format;
    }

    /**
     * Mengambil satu kategori menu spesifik untuk edit kategori
     *
     * @param int $id ID kategori
     * @return array|false
     */
    public function kategori_edit($id)
    {
        $sql = "select*from kategori where id_kategori=?";
        $row = $this->db->prepare($sql);
        $row->execute(array($id));
        $hasil = $row->fetch();
        return $hasil;
    }

    /**
     * Menghitung total jumlah baris kategori
     *
     * @return int
     */
    public function kategori_row()
    {
        $sql = "select*from kategori";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->rowCount();
        return $hasil;
    }

    /**
     * Menghitung total macam menu makanan/minuman yang aktif
     *
     * @return int
     */
    public function barang_row()
    {
        $sql = "select*from menu_layanan";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->rowCount();
        return $hasil;
    }

    /**
     * Menghitung total stok seluruh menu pelayanan yang tersedia
     *
     * @return array|false
     */
    public function barang_stok_row()
    {
        $sql = "SELECT SUM(stok) as jml FROM menu_layanan";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetch();
        return $hasil;
    }

    /**
     * Menghitung akumulasi nilai kapital/harga menu terdaftar
     *
     * @return array|false
     */
    public function barang_beli_row()
    {
        $sql = "SELECT SUM(harga) as beli FROM menu_layanan";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetch();
        return $hasil;
    }

    /**
     * Menghitung total porsi/pcs menu yang sudah terjual
     *
     * @return array|false
     */
    public function jual_row()
    {
        $sql = "SELECT SUM(jumlah) as stok FROM penjualan";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetch();
        return $hasil;
    }

    /**
     * Mengambil riwayat penjualan pada periode bulan berjalan (default)
     *
     * @return array
     */
    public function jual()
    {
        $sql = "SELECT penjualan.*, penjualan.id_penjualan as id_nota, penjualan.id_menu as id_barang, 
                menu_layanan.nama as nama_barang, menu_layanan.harga as harga_beli, member.id_member,
                member.nm_member from penjualan 
                left join menu_layanan on menu_layanan.id_menu=penjualan.id_menu 
                left join member on member.id_member=penjualan.id_member 
                where penjualan.periode = ?
                ORDER BY id_penjualan DESC";
        $row = $this->db->prepare($sql);
        $row->execute(array(date('m-Y')));
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Mengambil riwayat penjualan berdasarkan filter periode bulan-tahun (MM-YYYY)
     *
     * @param string $periode Format bulan-tahun (contoh: 06-2026)
     * @return array
     */
    public function periode_jual($periode)
    {
        $sql = "SELECT penjualan.*, penjualan.id_penjualan as id_nota, penjualan.id_menu as id_barang, 
                menu_layanan.nama as nama_barang, menu_layanan.harga as harga_beli, member.id_member,
                member.nm_member from penjualan 
                left join menu_layanan on menu_layanan.id_menu=penjualan.id_menu 
                left join member on member.id_member=penjualan.id_member WHERE penjualan.periode = ? 
                ORDER BY id_penjualan ASC";
        $row = $this->db->prepare($sql);
        $row->execute(array($periode));
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Mengambil riwayat penjualan berdasarkan filter tanggal harian spesifik (YYYY-MM-DD)
     *
     * @param string $hari Format tanggal harian (contoh: 2026-06-28)
     * @return array
     */
    public function hari_jual($hari)
    {
        $ex = explode('-', $hari);
        $monthNum  = $ex[1];
        $monthName = date('F', mktime(0, 0, 0, (int)$monthNum, 10));
        if ((int)$ex[2] > 9) {
            $tgl = $ex[2];
        } else {
            $tgl1 = explode('0', $ex[2]);
            $tgl = $tgl1[1] ?? $ex[2];
        }
        $cek = $tgl.' '.$monthName.' '.$ex[0]; // Format tanggal tersimpan di db (contoh: 28 June 2026)
        $param = "%{$cek}%";
        $sql = "SELECT penjualan.*, penjualan.id_penjualan as id_nota, penjualan.id_menu as id_barang, 
                menu_layanan.nama as nama_barang,  menu_layanan.harga as harga_beli, member.id_member,
                member.nm_member from penjualan 
                left join menu_layanan on menu_layanan.id_menu=penjualan.id_menu 
                left join member on member.id_member=penjualan.id_member WHERE penjualan.tanggal_input LIKE ? 
                ORDER BY id_penjualan ASC";
        $row = $this->db->prepare($sql);
        $row->execute(array($param));
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Mengambil semua daftar menu belanja aktif yang ada di keranjang kasir (penjualan_temp)
     *
     * @return array
     */
    public function penjualan()
    {
        $sql = "SELECT penjualan_temp.*, penjualan_temp.id_penjualan_temp as id_penjualan, penjualan_temp.id_menu as id_barang,
                menu_layanan.nama as nama_barang, menu_layanan.tipe, menu_layanan.foto, member.id_member, member.nm_member 
                from penjualan_temp 
                left join menu_layanan on menu_layanan.id_menu=penjualan_temp.id_menu 
                left join member on member.id_member=penjualan_temp.id_member
                ORDER BY id_penjualan_temp";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Mengambil detail transaksi checkout tertentu dari riwayat penjualan berdasarkan nomor invoice
     *
     * @param string $invoice Nomor invoice penjualan
     * @return array
     */
    public function penjualan_invoice($invoice)
    {
        $sql = "SELECT penjualan.*, penjualan.id_menu as id_barang, 
                menu_layanan.nama as nama_barang, menu_layanan.tipe, menu_layanan.foto, member.id_member, member.nm_member 
                from penjualan 
                left join menu_layanan on menu_layanan.id_menu=penjualan.id_menu 
                left join member on member.id_member=penjualan.id_member
                where penjualan.no_invoice = ?
                ORDER BY penjualan.id_penjualan";
        $row = $this->db->prepare($sql);
        $row->execute(array($invoice));
        $hasil = $row->fetchAll();
        return $hasil;
    }

    /**
     * Menjumlahkan total harga keranjang belanja aktif
     *
     * @return array|false
     */
    public function jumlah()
    {
        $sql = "SELECT SUM(total) as bayar FROM penjualan_temp";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetch();
        return $hasil;
    }

    /**
     * Menjumlahkan total harga dari riwayat transaksi penjualan berdasarkan invoice
     *
     * @param string $invoice Nomor invoice
     * @return array|false
     */
    public function jumlah_invoice($invoice)
    {
        $sql = "SELECT SUM(total) as bayar FROM penjualan WHERE no_invoice = ?";
        $row = $this->db->prepare($sql);
        $row->execute(array($invoice));
        $hasil = $row->fetch();
        return $hasil;
    }

    /**
     * Menghitung total keseluruhan omset pendapatan sepanjang waktu
     *
     * @return array|false
     */
    public function jumlah_nota()
    {
        $sql = "SELECT SUM(total) as bayar FROM penjualan";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetch();
        return $hasil;
    }

    /**
     * Menjumlahkan total nilai stok terdaftar dikalikan harganya (kapital aset resto)
     *
     * @return array|false
     */
    public function jml()
    {
        $sql = "SELECT SUM(harga*stok) as byr FROM menu_layanan";
        $row = $this->db->prepare($sql);
        $row->execute();
        $hasil = $row->fetch();
        return $hasil;
    }
}
?>
