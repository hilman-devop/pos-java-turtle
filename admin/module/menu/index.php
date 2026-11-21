<?php
/**
 * -------------------------------------------------------------------------
 * Modul Pengelolaan Menu Makanan & Minuman - POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * Halaman ini menampilkan daftar seluruh menu makanan/minuman yang aktif
 * beserta detail kategori, harga jual, dan tipe. Khusus admin, disediakan
 * tombol tambah data menu baru (dengan upload foto) serta aksi edit dan hapus.
 */
        // Mengidentifikasi role pengguna login aktif
        $role = $_SESSION['admin']['role'] ?? 'kasir';
        
        // Membaca status notifikasi alert banner
        $successStokParam = filter_input(INPUT_GET, 'success-stok', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
        $showSuccessStok = is_string($successStokParam) && $successStokParam !== '';

        $successParam = filter_input(INPUT_GET, 'success', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
        $showSuccess = is_string($successParam) && $successParam !== '';

        $removeParam = filter_input(INPUT_GET, 'remove', FILTER_UNSAFE_RAW, ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]);
        $showRemove = is_string($removeParam) && $removeParam !== '';
?>
        <h4>Daftar Menu</h4>
        <br />
        
        <!-- Notifikasi Banner Interaktif -->
        <?php if($showSuccessStok){?>
        <div class="alert alert-success">
            <p>Tambah Stok Berhasil !</p>
        </div>
        <?php }?>
        <?php if($showSuccess){?>
        <div class="alert alert-success">
            <p>Tambah Data Berhasil !</p>
        </div>
        <?php }?>
        <?php if($showRemove){?>
        <div class="alert alert-danger">
            <p>Hapus Data Berhasil !</p>
        </div>
        <?php }?>

        <!-- Tombol Pemicu Tambah Menu Baru (Hanya Muncul untuk Admin) -->
        <?php if ($role === 'admin') { ?>
        <button type="button" class="btn btn-primary btn-md mr-2" data-toggle="modal" data-target="#myModal">
            <i class="fa fa-plus"></i> Tambah Menu Makanan/Minuman</button>
        <?php } ?>
        <a href="index.php?page=menu" class="btn btn-success btn-md">
            <i class="fa fa-refresh"></i> Refresh Halaman</a>
        <div class="clearfix"></div>
        <br />
        
        <!-- Tabel Tampilan Menu Makanan/Minuman -->
        <div class="card card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm" id="example1">
                    <thead>
                        <tr style="background:#285c64;color:#fff;">
                            <th>No.</th>
                            <th>Foto</th>
                            <th>Kode</th>
                            <th>Kategori</th>
                            <th>Nama Menu</th>
                            <th>Tipe</th>
                            <th>Harga</th>
                            <th>Satuan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
						$totalHarga = 0;
						$totalStok = 0;
                        $hasil = $lihat->barang();
						$no=1;
						foreach($hasil as $isi) {
					?>
                        <tr>
                            <td><?php echo $no;?></td>
                            <td>
                                <!-- Merender thumbnail foto menu -->
                                <?php if (!empty($isi['foto']) && is_file('assets/img/menu/' . $isi['foto'])) { ?>
                                    <img src="assets/img/menu/<?php echo htmlspecialchars($isi['foto'], ENT_QUOTES, 'UTF-8'); ?>" class="img-thumbnail" style="width:50px;height:50px;object-fit:cover;" alt="Foto Menu">
                                <?php } else { ?>
                                    <span class="text-muted"><i class="fa fa-cutlery" style="font-size:24px;"></i></span>
                                <?php } ?>
                            </td>
                            <td><?php echo htmlspecialchars($isi['id_barang'], ENT_QUOTES, 'UTF-8');?></td>
                            <td><?php echo htmlspecialchars($isi['nama_kategori'], ENT_QUOTES, 'UTF-8');?></td>
                            <td><b><?php echo htmlspecialchars($isi['nama_barang'], ENT_QUOTES, 'UTF-8');?></b></td>
                            <td><span class="badge badge-info"><?php echo htmlspecialchars(ucfirst($isi['tipe']), ENT_QUOTES, 'UTF-8');?></span></td>

                            <td>Rp.<?php echo number_format((float)$isi['harga_jual']);?>,-</td>
                            <td> / <?php echo htmlspecialchars($isi['satuan_barang'], ENT_QUOTES, 'UTF-8');?></td>
                            <td>
                                <a href="index.php?page=menu/details&menu=<?php echo urlencode($isi['id_barang']);?>" class="btn btn-primary btn-xs">Detail</a>
                                
                                <!-- Opsi Edit dan Hapus hanya untuk Admin -->
                                <?php if ($role === 'admin') { ?>
                                <a href="index.php?page=menu/edit&menu=<?php echo urlencode($isi['id_barang']);?>" class="btn btn-warning btn-xs">Edit</a>
                                <a href="fungsi/hapus/hapus.php?menu=hapus&id=<?php echo urlencode($isi['id_barang']);?>&csrf_token=<?php echo urlencode(csrf_get_token());?>"
                                    class="btn btn-danger btn-xs" onclick="javascript:return confirm('Hapus menu ini dari daftar ?');">Hapus</a>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php 
							$no++; 
							$totalHarga += (float)$isi['harga_jual']; 
							$totalStok += (int)$isi['stok'];
						}
					?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($role === 'admin') { ?>
        <!-- MODAL POPUP TAMBAH MENU BARU -->
        <div id="myModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content" style="border-radius:0px;">
                    <div class="modal-header" style="background:#285c64;color:#fff;">
                        <h5 class="modal-title"><i class="fa fa-plus"></i> Tambah Menu Baru</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                     <form action="fungsi/tambah/tambah.php?menu=tambah" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="modal-body">
                            <table class="table table-striped bordered">
                                <?php
                                    // Generate ID Menu unik secara otomatis
									$format = $lihat->barang_id();
								?>
                                <tr>
                                    <td>Kode Menu</td>
                                    <td><input type="text" readonly="readonly" required value="<?= htmlspecialchars($format, ENT_QUOTES, 'UTF-8'); ?>"
                                             class="form-control" name="id"></td>
                                </tr>
                                <tr>
                                    <td>Kategori</td>
                                    <td>
                                        <select name="kategori" class="form-control" required>
                                            <option value="">Pilih Kategori</option>
                                            <?php $kat = $lihat->kategori(); foreach($kat as $isi){ 	?>
                                            <option value="<?= htmlspecialchars($isi['id_kategori'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <?= htmlspecialchars($isi['nama_kategori'], ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php }?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Nama Menu Makanan/Minuman</td>
                                    <td><input type="text" placeholder="Contoh: Nasi Goreng Gila" required class="form-control"
                                             name="nama"></td>
                                </tr>
                                <tr>
                                    <td>Harga Jual (Rp)</td>
                                    <td><input type="number" placeholder="Contoh: 25000" required class="form-control"
                                             name="jual"></td>
                                </tr>
                                <!-- Set stok default 999 karena menu resto bisa selalu dibuat -->
                                <input type="hidden" name="stok" value="999">
                                <tr>
                                    <td>Foto Menu</td>
                                    <td><input type="file" class="form-control" name="foto" accept="image/*"></td>
                                </tr>
                                <tr>
                                    <td>Tanggal Input</td>
                                    <td><input type="text" required readonly="readonly" class="form-control"
                                             value="<?php echo date("j F Y, G:i");?>" name="tgl"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Tambah Data</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php } ?>
