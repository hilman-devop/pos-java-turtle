    </div>
    <!-- /.container-fluid -->

    </div>
    <!-- Akhir dari Main Content -->
    
    <!-- Bagian Kaki Halaman (Footer) -->
    <footer class="sticky-footer bg-white shadow-sm mt-4">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>
                    &copy; <?php echo date('Y');?> - Sistem Kasir Java Turtle Lodge<b></b>
                </span>
            </div>
        </div>
    </footer>
    <!-- Akhir dari Footer -->

    </div>
    <!-- Akhir dari Content Wrapper -->

    </div>
    <!-- Akhir dari Page Wrapper -->

    <!-- Tombol pintas gulir kembali ke atas (Scroll to Top) -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    
    <!-- Memuat Javascript untuk sb-admin-2 template, DataTables, dan konfigurasinya -->
    <script src="sb-admin/js/sb-admin-2.min.js"></script>
    <script src="sb-admin/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="sb-admin/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    
    <!-- Inisialisasi DataTables pada tabel dengan ID example1 dan example2 -->
    <script type="text/javascript">
    $(function() {
        $("#example1").DataTable();
        $('#example2').DataTable();
    });
   </script>
   
   <?php
        // Cek stok menu di database, jika ada yang menipis (<=10), tampilkan notifikasi peringatan
        $sql = "select * from menu_layanan where stok <= 10 LIMIT 1";
        $row = $config->prepare($sql);
        $row->execute();
        $q = $row->fetch();
        if ($q && (int)$q['stok'] <= 10) {	
   ?>
   <!-- Gritter Notification Peringatan Stok Tipis -->
   <script type="text/javascript">
    $(document).ready(function() {
        $.gritter.add({
            title: 'Peringatan Stok Menu!',
            text: 'Persediaan porsi menu makanan/minuman ada yang kurang dari 10! Silakan cek menu.',
            image: 'assets/img/seru.png',
            sticky: true,
            time: '',
            class_name: 'my-sticky-class'
        });
        return false;
    });
   </script>
   <?php } ?>
   
   <!-- Animasi Tampil & Sembunyikan Alert Bootstrap secara otomatis dalam waktu 5 detik -->
   <script type="application/javascript">
    $(document).ready(function() {
        // Tampilkan alert error secara perlahan
        setTimeout(function() {
            $(".alert-danger").fadeIn('slow');
        }, 500);
    });
    setTimeout(function() {
        $(".alert-danger").fadeOut('slow');
    }, 5000);

    $(document).ready(function() {
        // Tampilkan alert sukses secara perlahan
        setTimeout(function() {
            $(".alert-success").fadeIn('slow');
        }, 500);
    });
    setTimeout(function() {
        $(".alert-success").fadeOut('slow');
    }, 5000);

    $(document).ready(function() {
        // Tampilkan alert peringatan secara perlahan
        setTimeout(function() {
            $(".alert-warning").fadeIn('slow');
        }, 500);
    });
    setTimeout(function() {
        $(".alert-success").fadeOut('slow');
    }, 5000);
   </script>
   
   <!-- Fungsi Pengendali Modal Popup Pembayaran & Input Transaksi -->
   <script>
    // Menyembunyikan overlay bayangan dan modal saat halaman pertama dimuat
    $(".modal-create").hide();
    $(".bg-shadow").hide();

    // Fungsi menampilkan modal popup
    function clickModals() {
        $(".bg-shadow").fadeIn();
        $(".modal-create").fadeIn();
    }

    // Fungsi menyembunyikan modal popup dan menampilkan kembali detail nota
    function cancelModals() {
        $('.modal-view').fadeIn();
        $(".modal-create").hide();
        $(".bg-shadow").hide();
    }
   </script>

   </body>

   </html>