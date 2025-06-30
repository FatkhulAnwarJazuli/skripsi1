<?php
// Langkah 1: Ganti panggilan ke function.php dengan koneksi.php
require_once 'function.php'; 

// --- Logika Paginasi ---
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

// --- Tangani Operasi CRUD untuk Ruangan ---

// BARU: Logika untuk menangani penambahan ruangan, dipindahkan dari function.php
if (isset($_POST['addruangan'])) {
    $namaruangan = htmlspecialchars($_POST['ruangan']);

    $stmt = mysqli_prepare($conn, "INSERT INTO ruangan (nama_ruangan) VALUES (?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $namaruangan);
        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("Ruangan baru berhasil ditambahkan!"); window.location.href="ruangan.php";</script>';
        } else {
            echo '<script>alert("Gagal menambahkan ruangan: ' . mysqli_error($conn) . '"); window.location.href="ruangan.php";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal menyiapkan statement: ' . mysqli_error($conn) . '"); window.location.href="ruangan.php";</script>';
    }
    exit(); // Hentikan eksekusi setelah redirect
}


// Logika untuk Edit Ruangan (Sudah Benar)
if (isset($_POST['SimpanEditRuangan'])) {
    $idruangan = htmlspecialchars($_POST['Edit_Id_Ruangan']);
    $namaruangan = htmlspecialchars($_POST['Edit_Nama_Ruangan']);
    
    $query = "UPDATE ruangan SET nama_ruangan=? WHERE id_ruangan=?"; 
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $namaruangan, $idruangan);
        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("Data Ruangan Berhasil Diperbarui!"); window.location.href="ruangan.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Memperbarui Data: ' . mysqli_error($conn) . '"); window.location.href="ruangan.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Update: ' . mysqli_error($conn) . '"); window.location.href="ruangan.php?page=' . $page . '";</script>';
    }
    exit();
}

// Logika untuk Hapus Ruangan (Sudah Benar)
if (isset($_GET['hapus'])) {
    $id_ruangan_to_delete = htmlspecialchars($_GET['hapus']);
    $query = "DELETE FROM ruangan WHERE id_ruangan=?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_ruangan_to_delete);
        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("Data Ruangan Berhasil Dihapus!"); window.location.href="ruangan.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menghapus Data: ' . mysqli_error($conn) . '"); window.location.href="ruangan.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Delete: ' . mysqli_error($conn) . '"); window.location.href="ruangan.php?page=' . $page . '";</script>';
    }
    exit();
}

// --- Ambil Data untuk Tampilan ---
// Hitung total data untuk paginasi
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_ruangan) AS total FROM ruangan");
$total_data = mysqli_fetch_assoc($total_data_query)['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data ruangan untuk halaman saat ini
$ambilsemuadatanya = mysqli_query($conn, "SELECT id_ruangan, nama_ruangan FROM ruangan ORDER BY id_ruangan DESC LIMIT $start, $limit");

// Sertakan sidebar setelah semua logika PHP selesai
include 'sidebar.php'; 
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Ruangan</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addRuanganModal">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari ruangan...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="myTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Ruangan</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $id_ruangan = $data['id_ruangan'];
                                $nama_ruangan = $data['nama_ruangan'];
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= htmlspecialchars($nama_ruangan) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-warning btn-sm mb-1" data-toggle="modal" data-target="#editRuanganModal<?= $id_ruangan ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $id_ruangan ?>&page=<?= $page ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ruangan ini?')" class="btn btn-danger btn-sm mb-1">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                        <a href="cetak_ruangan.php?id=<?= $id_ruangan ?>" target="_blank" class="btn btn-info btn-sm mb-1">
                                            <i class="fa fa-print"></i> Cetak
                                        </a>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editRuanganModal<?= $id_ruangan ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="post" action="ruangan.php?page=<?= $page; ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Ruangan</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>Nama Ruangan:</label>
                                                        <input type="hidden" name="Edit_Id_Ruangan" value="<?= $id_ruangan ?>">
                                                        <input type="text" name="Edit_Nama_Ruangan" value="<?= htmlspecialchars($nama_ruangan) ?>" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditRuangan" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            }
                        } else {
                            echo '<tr><td colspan="3" class="text-center">Belum ada data ruangan.</td></tr>'; 
                        }
                        ?>
                    </tbody>
                </table>
                 <div id="noDataMessage" class="alert alert-warning text-center" style="display: none;">
                    Data tidak ditemukan.
                </div>
            </div>

            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>


<div class="modal fade" id="addRuanganModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Ruangan Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action=""> 
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Ruangan:</label>
                        <input type="text" name="ruangan" placeholder="Masukkan Nama Ruangan" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss-modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="addruangan">Tambah</button>
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
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("myTable");
        tr = table.getElementsByTagName("tr");
        var noDataMessage = document.getElementById("noDataMessage");
        var foundVisibleRow = false;
        
        for (i = 1; i < tr.length; i++) {
            if (tr[i].parentNode.tagName === 'TBODY') {
                td = tr[i].getElementsByTagName("td")[1];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        foundVisibleRow = true;
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
        noDataMessage.style.display = foundVisibleRow ? "none" : "block";
    }
    document.getElementById("searchInput").addEventListener("keyup", filterTable);
</script>

</body>
</html>