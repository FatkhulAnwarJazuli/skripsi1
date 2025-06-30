<?php
require_once 'function.php';

// --- Logika Paginasi ---
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

// --- Logika CRUD (sudah aman dengan transaksi dan prepared statements) ---
// (Tidak ada perubahan pada logika PHP di bagian atas ini, semua sudah baik)

// Logika Tambah Barang Rusak
if (isset($_POST['addbarangrusak'])) {
    $id_barang = htmlspecialchars($_POST['barangnya']);
    $jumlah = htmlspecialchars($_POST['jumlah']);
    $kondisi = htmlspecialchars($_POST['kondisinya']);
    $status = htmlspecialchars($_POST['status']);
    
    $foto_name = null;
    $upload_dir = 'foto/';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_name = 'rusak_' . uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $foto_name;
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
            echo '<script>alert("Gagal mengupload file foto."); history.go(-1);</script>';
            exit();
        }
    }

    mysqli_begin_transaction($conn);
    try {
        $stmt_update = mysqli_prepare($conn, "UPDATE barang SET jumlah = jumlah - ? WHERE id_barang = ? AND jumlah >= ?");
        mysqli_stmt_bind_param($stmt_update, "iii", $jumlah, $id_barang, $jumlah);
        mysqli_stmt_execute($stmt_update);

        if (mysqli_stmt_affected_rows($stmt_update) == 0) {
            throw new Exception("Stok tidak mencukupi untuk barang yang dipilih.");
        }
        mysqli_stmt_close($stmt_update);

        $stmt_insert = mysqli_prepare($conn, "INSERT INTO barang_rusak (id_barang, jumlah, kondisi, foto, status) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_insert, "iisss", $id_barang, $jumlah, $kondisi, $foto_name, $status);
        mysqli_stmt_execute($stmt_insert);
        mysqli_stmt_close($stmt_insert);
        
        mysqli_commit($conn);
        echo '<script>alert("Data barang rusak berhasil ditambahkan!"); window.location.href="barangrusak.php";</script>';
    } catch (Exception $e) {
        mysqli_rollback($conn);
        if ($foto_name && file_exists($upload_dir . $foto_name)) unlink($upload_dir . $foto_name);
        echo '<script>alert("Transaksi Gagal: ' . $e->getMessage() . '"); history.go(-1);</script>';
    }
    exit();
}

// Logika Edit Barang Rusak
if (isset($_POST['SimpanEditBarangRusak'])) {
    $id_rusak = htmlspecialchars($_POST['Edit_Id_Rusak']);
    $id_barang_baru = htmlspecialchars($_POST['Edit_Id_Barang_Rusak']);
    $jumlah_baru = htmlspecialchars($_POST['Edit_Jumlah_Rusak']);
    $kondisi = htmlspecialchars($_POST['Edit_Kondisi_Rusak']);
    $status = htmlspecialchars($_POST['Edit_Status_Rusak']);
    $foto_lama = htmlspecialchars($_POST['Edit_Foto_Lama_Rusak']);
    $foto_to_save = $foto_lama;
    
    mysqli_begin_transaction($conn);
    try {
        $stmt_old = mysqli_prepare($conn, "SELECT id_barang, jumlah FROM barang_rusak WHERE id_rusak = ?");
        mysqli_stmt_bind_param($stmt_old, "i", $id_rusak);
        mysqli_stmt_execute($stmt_old);
        $data_lama = mysqli_stmt_get_result($stmt_old)->fetch_assoc();
        mysqli_stmt_close($stmt_old);

        if ($data_lama) {
            $stmt_restore = mysqli_prepare($conn, "UPDATE barang SET jumlah = jumlah + ? WHERE id_barang = ?");
            mysqli_stmt_bind_param($stmt_restore, "ii", $data_lama['jumlah'], $data_lama['id_barang']);
            mysqli_stmt_execute($stmt_restore);
            mysqli_stmt_close($stmt_restore);

            $stmt_reduce = mysqli_prepare($conn, "UPDATE barang SET jumlah = jumlah - ? WHERE id_barang = ? AND jumlah >= ?");
            mysqli_stmt_bind_param($stmt_reduce, "iii", $jumlah_baru, $id_barang_baru, $jumlah_baru);
            mysqli_stmt_execute($stmt_reduce);
            if (mysqli_stmt_affected_rows($stmt_reduce) == 0) throw new Exception("Stok tidak mencukupi untuk barang baru yang dipilih.");
            mysqli_stmt_close($stmt_reduce);
        }

        if (isset($_FILES['Edit_Foto_Rusak']) && $_FILES['Edit_Foto_Rusak']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'foto/';
            $file_extension = pathinfo($_FILES['Edit_Foto_Rusak']['name'], PATHINFO_EXTENSION);
            $foto_to_save = 'rusak_edit_' . uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $foto_to_save;
            if (move_uploaded_file($_FILES['Edit_Foto_Rusak']['tmp_name'], $target_file)) {
                if ($foto_lama && file_exists($upload_dir . $foto_lama)) unlink($upload_dir . $foto_lama);
            } else {
                throw new Exception("Gagal mengupload foto baru.");
            }
        }
        
        $stmt_update = mysqli_prepare($conn, "UPDATE barang_rusak SET id_barang=?, jumlah=?, kondisi=?, foto=?, status=? WHERE id_rusak=?");
        mysqli_stmt_bind_param($stmt_update, "iisssi", $id_barang_baru, $jumlah_baru, $kondisi, $foto_to_save, $status, $id_rusak);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);
        
        mysqli_commit($conn);
        echo '<script>alert("Data Barang Rusak Berhasil Diperbarui!"); window.location.href="barangrusak.php?page=' . $page . '";</script>';
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo '<script>alert("Transaksi Gagal: ' . $e->getMessage() . '"); history.go(-1);</script>';
    }
    exit();
}

// Logika Hapus Barang Rusak
if (isset($_GET['hapus'])) {
    $id_rusak_to_delete = htmlspecialchars($_GET['hapus']);
    mysqli_begin_transaction($conn);
    try {
        $stmt_get = mysqli_prepare($conn, "SELECT id_barang, jumlah, foto FROM barang_rusak WHERE id_rusak = ?");
        mysqli_stmt_bind_param($stmt_get, "i", $id_rusak_to_delete);
        mysqli_stmt_execute($stmt_get);
        $rusak_data = mysqli_stmt_get_result($stmt_get)->fetch_assoc();
        mysqli_stmt_close($stmt_get);
        
        if ($rusak_data) {
            $stmt_delete = mysqli_prepare($conn, "DELETE FROM barang_rusak WHERE id_rusak=?");
            mysqli_stmt_bind_param($stmt_delete, "i", $id_rusak_to_delete);
            mysqli_stmt_execute($stmt_delete);
            mysqli_stmt_close($stmt_delete);
            
            $stmt_restore = mysqli_prepare($conn, "UPDATE barang SET jumlah = jumlah + ? WHERE id_barang = ?");
            mysqli_stmt_bind_param($stmt_restore, "ii", $rusak_data['jumlah'], $rusak_data['id_barang']);
            mysqli_stmt_execute($stmt_restore);
            mysqli_stmt_close($stmt_restore);

            if ($rusak_data['foto'] && file_exists('foto/' . $rusak_data['foto'])) {
                unlink('foto/' . $rusak_data['foto']);
            }
        }
        mysqli_commit($conn);
        echo '<script>alert("Data Barang Rusak Berhasil Dihapus!"); window.location.href="barangrusak.php?page=' . $page . '";</script>';
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo '<script>alert("Transaksi Hapus Gagal: ' . $e->getMessage() . '"); history.go(-1);</script>';
    }
    exit();
}

// Ambil data untuk ditampilkan
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_rusak) AS total FROM barang_rusak");
$total_data = mysqli_fetch_assoc($total_data_query)['total'];
$total_pages = ceil($total_data / $limit);
$ambilsemuadatanya = mysqli_query($conn, "SELECT br.*, b.namabarang, b.merk FROM barang_rusak br JOIN barang b ON br.id_barang = b.id_barang ORDER BY br.id_rusak DESC LIMIT $start, $limit");

include 'sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Barang Rusak</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addBarangRusakModal">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari barang rusak...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" id="myTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Merk</th>
                            <th>Jumlah</th>
                            <th>Kondisi</th>
                            <th>Foto</th>
                            <th>Status</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $id_rusak = $data['id_rusak'];
                                $id_barang_fk = $data['id_barang'];
                                $namabarang = $data['namabarang'];
                                $merk = $data['merk'];
                                $jumlah_rusak = $data['jumlah'];
                                $kondisi = $data['kondisi'];
                                $foto = $data['foto'];
                                $status = $data['status'];
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= htmlspecialchars($namabarang) ?></td>
                                    <td><?= htmlspecialchars($merk) ?></td>
                                    <td><?= htmlspecialchars($jumlah_rusak) ?></td>
                                    <td><?= htmlspecialchars($kondisi) ?></td>
                                    <td>
                                        <?php if (!empty($foto) && file_exists('foto/'.htmlspecialchars($foto))) : ?>
                                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewFotoModal<?= $id_rusak ?>">
                                                <i class="fa fa-eye"></i> Lihat
                                            </button>
                                        <?php else : ?>
                                            Tidak ada foto
                                        <?php endif; ?>
                                        </td>
                                    <td><?= htmlspecialchars($status) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-warning btn-sm mb-1" data-toggle="modal" data-target="#editBarangRusakModal<?= $id_rusak ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $id_rusak ?>&page=<?= $page ?>" onclick="return confirm('Anda yakin ingin menghapus data ini? Stok barang akan dikembalikan.')" class="btn btn-danger btn-sm mb-1">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                         <a href="ppbrusak.php?id_rusak=<?= $id_rusak ?>" target="_blank" class="btn btn-info btn-sm mb-1">
                                             <i class="fa fa-print"></i> Cetak
                                         </a>
                                    </td>
                                </tr>

                                <?php if (!empty($foto) && file_exists('foto/'.htmlspecialchars($foto))) : ?>
                                <div class="modal fade" id="viewFotoModal<?= $id_rusak ?>" tabindex="-1" role="dialog" aria-labelledby="viewFotoModalLabel<?= $id_rusak ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="viewFotoModalLabel<?= $id_rusak ?>">Foto: <?= htmlspecialchars($namabarang) ?></h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="foto/<?= htmlspecialchars($foto) ?>" class="img-fluid" alt="Foto Barang Rusak">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="modal fade" id="editBarangRusakModal<?= $id_rusak ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="post" action="barangrusak.php?page=<?= $page ?>" enctype="multipart/form-data">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Barang Rusak</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="Edit_Id_Rusak" value="<?= $id_rusak ?>">
                                                    <input type="hidden" name="Edit_Foto_Lama_Rusak" value="<?= htmlspecialchars($foto) ?>">
                                                    <div class="form-group">
                                                        <label>Nama Barang:</label>
                                                        <select name="Edit_Id_Barang_Rusak" class="form-control" required>
                                                            <?php
                                                            $query_barang_edit = mysqli_query($conn, "SELECT id_barang, namabarang, merk FROM barang ORDER BY namabarang ASC");
                                                            while ($b_data = mysqli_fetch_assoc($query_barang_edit)) {
                                                                $selected = ($b_data['id_barang'] == $id_barang_fk) ? 'selected' : '';
                                                                echo '<option value="' . $b_data['id_barang'] . '" ' . $selected . '>' . htmlspecialchars($b_data['namabarang']) . ' (' . htmlspecialchars($b_data['merk']) . ')</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Jumlah:</label>
                                                        <input type="number" name="Edit_Jumlah_Rusak" value="<?= htmlspecialchars($jumlah_rusak) ?>" class="form-control" required min="1">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Kondisi:</label>
                                                        <input type="text" name="Edit_Kondisi_Rusak" value="<?= htmlspecialchars($kondisi) ?>" class="form-control" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Status:</label>
                                                        <select name="Edit_Status_Rusak" class="form-control" required>
                                                            <option value="Rusak" <?= ($status == 'Rusak') ? 'selected' : ''; ?>>Rusak</option>
                                                            <option value="Perbaikan" <?= ($status == 'Perbaikan') ? 'selected' : ''; ?>>Perbaikan</option>
                                                            <option value="Selesai Perbaikan" <?= ($status == 'Selesai Perbaikan') ? 'selected' : ''; ?>>Selesai Perbaikan</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Foto (Biarkan kosong jika tidak diubah):</label>
                                                        <input type="file" name="Edit_Foto_Rusak" class="form-control-file" accept="image/*">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditBarangRusak" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            }
                        } else {
                            echo '<tr><td colspan="8" class="text-center">Belum ada data barang rusak.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
                <div id="noDataMessage" class="alert alert-warning text-center" style="display: none;">Data tidak ditemukan.</div>
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>"><a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a></li>
                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>"><a class="page-link" href="?page=<?= $page + 1 ?>">Next</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<div class="modal fade" id="addBarangRusakModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Barang Rusak Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form method="post" action="" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Barang:</label>
                        <select name="barangnya" class="form-control" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php
                            $query_barang_add = mysqli_query($conn, "SELECT id_barang, namabarang, merk, jumlah FROM barang WHERE jumlah > 0 ORDER BY namabarang ASC");
                            while ($fetcharray = mysqli_fetch_array($query_barang_add)) {
                                echo '<option value="'.$fetcharray['id_barang'].'">'.htmlspecialchars($fetcharray['namabarang']).' ('.htmlspecialchars($fetcharray['merk']).') - Stok: '.$fetcharray['jumlah'].'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jumlah Rusak:</label>
                        <input type="number" name="jumlah" placeholder="Jumlah Rusak" class="form-control" required min="1">
                    </div>
                    <div class="form-group">
                        <label>Kondisi:</label>
                        <input type="text" name="kondisinya" placeholder="Deskripsi Kerusakan" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Status:</label>
                        <select name="status" class="form-control" required>
                            <option value="Rusak">Rusak</option>
                            <option value="Perbaikan">Perbaikan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Foto (Opsional):</label>
                        <input type="file" name="foto" class="form-control-file" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="addbarangrusak">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>

<script>
    function filterTable() {
        var input = document.getElementById("searchInput");
        var filter = input.value.toUpperCase();
        var table = document.getElementById("myTable");
        var tr = table.getElementsByTagName("tr");
        var noDataMessage = document.getElementById("noDataMessage");
        var foundVisibleRow = false;

        for (var i = 1; i < tr.length; i++) {
            var displayRow = false;
            var tds = tr[i].getElementsByTagName("td");
            var searchIndexes = [1, 2, 4, 6];
            for (var j = 0; j < searchIndexes.length; j++) {
                var td = tds[searchIndexes[j]];
                if (td && td.textContent.toUpperCase().indexOf(filter) > -1) {
                    displayRow = true;
                    break;
                }
            }
            tr[i].style.display = displayRow ? "" : "none";
            if(displayRow) foundVisibleRow = true;
        }
        noDataMessage.style.display = foundVisibleRow ? "none" : "block";
    }
    document.getElementById("searchInput").addEventListener("keyup", filterTable);
</script>

</body>
</html>