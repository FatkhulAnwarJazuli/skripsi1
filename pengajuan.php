<?php
include 'sidebar.php'; // Memasukkan sidebar dan tag HTML pembuka
include_once "function.php"; // Memastikan koneksi database $conn tersedia

// Pastikan koneksi database sudah ada dan valid sebelum melanjutkan
if (!isset($conn) || !$conn) {
    die("Koneksi database gagal! Pastikan 'function.php' menginisialisasi \$conn dengan benar.");
}

// Set zona waktu untuk tanggal (Waktu Indonesia Tengah untuk Banjarmasin)
date_default_timezone_set('Asia/Makassar');

// --- Tangani Operasi CRUD untuk Pengajuan ---

// Tangani operasi Edit Pengajuan
if (isset($_POST['SimpanEditPengajuan'])) {
    // Validasi input
    $errors = [];
    if (empty($_POST['Edit_Id_Pengajuan'])) { $errors[] = "ID Pengajuan tidak boleh kosong."; }
    if (empty($_POST['Edit_Tanggal_Pengajuan'])) { $errors[] = "Tanggal Pengajuan tidak boleh kosong."; }
    if (empty($_POST['Edit_Pengaju'])) { $errors[] = "Pengaju tidak boleh kosong."; }
    if (empty($_POST['Edit_Nama_Barang'])) { $errors[] = "Nama Barang tidak boleh kosong."; }
    // Merk bisa kosong
    if (empty($_POST['Edit_Jumlah']) || !is_numeric($_POST['Edit_Jumlah']) || $_POST['Edit_Jumlah'] < 1) { $errors[] = "Jumlah harus angka positif."; }
    if (empty($_POST['Edit_Satuan'])) { $errors[] = "Satuan tidak boleh kosong."; }

    if (!empty($errors)) {
        echo '<script>alert("Error: ' . implode('\n', $errors) . '"); window.location.href="pengajuan.php";</script>';
    } else {
        $id_pengajuan = htmlspecialchars($_POST['Edit_Id_Pengajuan']);
        $tanggal_pengajuan = htmlspecialchars($_POST['Edit_Tanggal_Pengajuan']);
        $pengaju = htmlspecialchars($_POST['Edit_Pengaju']);
        $nama_barang = htmlspecialchars($_POST['Edit_Nama_Barang']);
        $merk = htmlspecialchars($_POST['Edit_Merk']);
        $jumlah = htmlspecialchars($_POST['Edit_Jumlah']);
        $satuan = htmlspecialchars($_POST['Edit_Satuan']);

        $query = "UPDATE pengajuan SET tanggal_pengajuan=?, pengaju=?, nama_barang=?, merk=?, jumlah=?, satuan=? WHERE id_pengajuan=?";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            // "ssssisi" -> string, string, string, string, integer, string, integer (id_pengajuan)
            mysqli_stmt_bind_param($stmt, "ssssisi", $tanggal_pengajuan, $pengaju, $nama_barang, $merk, $jumlah, $satuan, $id_pengajuan);
            $result = mysqli_stmt_execute($stmt);

            if ($result) {
                echo '<script>alert("Data Pengajuan Berhasil Diperbarui!"); window.location.href="pengajuan.php";</script>';
            } else {
                echo '<script>alert("Gagal Memperbarui Data Pengajuan: ' . mysqli_error($conn) . '"); window.location.href="pengajuan.php";</script>';
            }
            mysqli_stmt_close($stmt);
        } else {
            echo '<script>alert("Gagal Menyiapkan Query Update: ' . mysqli_error($conn) . '"); window.location.href="pengajuan.php";</script>';
        }
    }
}

// Tangani operasi Hapus Pengajuan
if (isset($_GET['hapus'])) {
    $id_pengajuan_to_delete = htmlspecialchars($_GET['hapus']);

    // Pastikan id yang dihapus adalah angka untuk mencegah SQL Injection (walaupun prepare statement sudah melindungi)
    if (!is_numeric($id_pengajuan_to_delete)) {
        echo '<script>alert("ID pengajuan tidak valid."); window.location.href="pengajuan.php";</script>';
        exit;
    }

    $query_delete = "DELETE FROM pengajuan WHERE id_pengajuan=?";
    $stmt_delete = mysqli_prepare($conn, $query_delete);

    if ($stmt_delete) {
        mysqli_stmt_bind_param($stmt_delete, "i", $id_pengajuan_to_delete);
        $result_delete = mysqli_stmt_execute($stmt_delete);

        if ($result_delete) {
            echo '<script>alert("Data Pengajuan Berhasil Dihapus!"); window.location.href="pengajuan.php";</script>';
        } else {
            echo '<script>alert("Gagal Menghapus Data Pengajuan: ' . mysqli_error($conn) . '"); window.location.href="pengajuan.php";</script>';
        }
        mysqli_stmt_close($stmt_delete);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Delete: ' . mysqli_error($conn) . '"); window.location.href="pengajuan.php";</script>';
    }
}

// --- Logika Paginasi ---
$limit = 10; // Batas data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
// Memastikan halaman tidak kurang dari 1
if ($page < 1) {
    $page = 1;
}

$start = ($page - 1) * $limit;

// Ambil data pengajuan untuk ditampilkan
$ambilsemuadatanya = mysqli_query($conn, "SELECT id_pengajuan, tanggal_pengajuan, pengaju, nama_barang, merk, jumlah, satuan FROM pengajuan ORDER BY tanggal_pengajuan DESC, id_pengajuan DESC LIMIT $start, $limit");

// Hitung total data untuk paginasi
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_pengajuan) AS total FROM pengajuan");
$total_data = mysqli_fetch_assoc($total_data_query)['total'];
$total_pages = ceil($total_data / $limit);

// Menyesuaikan halaman jika total_pages 0 atau page melebihi total_pages
if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
} elseif ($total_pages == 0) { // Jika tidak ada data sama sekali
    $page = 1;
}

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Pengajuan Barang</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPengajuanModal">
                <i class="fas fa-plus"></i> Tambah Pengajuan
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari pengajuan...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="myTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Pengaju</th>
                            <th>Nama Barang</th>
                            <th>Merk</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $id_pengajuan = htmlspecialchars($data['id_pengajuan']);
                                $tanggal_pengajuan = htmlspecialchars($data['tanggal_pengajuan']);
                                $pengaju = htmlspecialchars($data['pengaju']);
                                $nama_barang = htmlspecialchars($data['nama_barang']);
                                $merk = htmlspecialchars($data['merk']);
                                $jumlah = htmlspecialchars($data['jumlah']);
                                $satuan = htmlspecialchars($data['satuan']);
                        ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td><?= date('d F Y', strtotime($tanggal_pengajuan)); ?></td>
                                    <td><?= $pengaju; ?></td>
                                    <td><?= $nama_barang; ?></td>
                                    <td><?= $merk; ?></td>
                                    <td><?= $jumlah; ?></td>
                                    <td><?= $satuan; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-warning btn-sm mb-1" data-toggle="modal" data-target="#editPengajuanModal<?= $id_pengajuan ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $id_pengajuan ?>&page=<?= $page ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data pengajuan ini?')" class="btn btn-danger btn-sm mb-1">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                        <a href="cetak_pengajuan.php?id=<?= $id_pengajuan ?>" target="_blank" class="btn btn-info btn-sm mb-1">
                                            <i class="fa fa-print"></i> Cetak
                                        </a>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editPengajuanModal<?= $id_pengajuan ?>" tabindex="-1" role="dialog" aria-labelledby="editPengajuanModalLabel<?= $id_pengajuan ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editPengajuanModalLabel<?= $id_pengajuan ?>">Edit Pengajuan Barang</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="Edit_Id_Pengajuan" value="<?= $id_pengajuan ?>">
                                                    <div class="form-group">
                                                        <label for="editTanggalPengajuan<?= $id_pengajuan ?>">Tanggal Pengajuan:</label>
                                                        <input type="date" name="Edit_Tanggal_Pengajuan" value="<?= $tanggal_pengajuan ?>" class="form-control" id="editTanggalPengajuan<?= $id_pengajuan ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editPengaju<?= $id_pengajuan ?>">Pengaju:</label>
                                                        <input type="text" name="Edit_Pengaju" value="<?= $pengaju ?>" class="form-control" id="editPengaju<?= $id_pengajuan ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editNamaBarang<?= $id_pengajuan ?>">Nama Barang:</label>
                                                        <input type="text" name="Edit_Nama_Barang" value="<?= $nama_barang ?>" class="form-control" id="editNamaBarang<?= $id_pengajuan ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editMerk<?= $id_pengajuan ?>">Merk:</label>
                                                        <input type="text" name="Edit_Merk" value="<?= $merk ?>" class="form-control" id="editMerk<?= $id_pengajuan ?>">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editJumlah<?= $id_pengajuan ?>">Jumlah:</label>
                                                        <input type="number" name="Edit_Jumlah" value="<?= $jumlah ?>" class="form-control" id="editJumlah<?= $id_pengajuan ?>" required min="1">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editSatuan<?= $id_pengajuan ?>">Satuan:</label>
                                                        <input type="text" name="Edit_Satuan" value="<?= $satuan ?>" class="form-control" id="editSatuan<?= $id_pengajuan ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditPengajuan" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            echo '<tr><td colspan="8" class="text-center">Belum ada data pengajuan.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
                <div id="noDataMessage" class="alert alert-warning text-center" style="display: none;">
                    Data tidak ditemukan.
                </div>
            </div>

            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>" tabindex="-1">Previous</a>
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
</div>
<footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright &copy; Your Website 2021</span>
        </div>
    </div>
</footer>
</div>
</div>
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Siap untuk Keluar?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Pilih "Logout" di bawah jika Anda siap untuk mengakhiri sesi Anda saat ini.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                <a class="btn btn-primary" href="logout.php">Logout</a> </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addPengajuanModal" tabindex="-1" role="dialog" aria-labelledby="addPengajuanModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPengajuanModalLabel">Tambah Pengajuan Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="function.php"> <div class="modal-body">
                    <div class="form-group">
                        <label for="addPengaju">Pengaju:</label>
                        <input type="text" name="pengaju" placeholder="Nama Pengaju" class="form-control" id="addPengaju" required>
                    </div>
                    <div class="form-group">
                        <label for="addNamaBarang">Nama Barang:</label>
                        <input type="text" name="nama_barang" placeholder="Nama Barang yang Diajukan" class="form-control" id="addNamaBarang" required>
                    </div>
                    <div class="form-group">
                        <label for="addMerk">Merk (Opsional):</label>
                        <input type="text" name="merk" placeholder="Merk Barang" class="form-control" id="addMerk">
                    </div>
                    <div class="form-group">
                        <label for="addJumlah">Jumlah:</label>
                        <input type="number" name="jumlah" placeholder="Jumlah Barang" class="form-control" id="addJumlah" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="addSatuan">Satuan:</label>
                        <input type="text" name="satuan" placeholder="Satuan (contoh: unit, buah, pcs)" class="form-control" id="addSatuan" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="addpengajuan">Tambah Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

<script src="js/sb-admin-2.min.js"></script>

<script>
    // Fungsi untuk memfilter tabel di sisi klien
    function filterTable() {
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("myTable");
        tr = table.getElementsByTagName("tr");
        var noDataMessage = document.getElementById("noDataMessage");
        var foundVisibleRow = false;

        // Loop melalui semua baris tabel, dan sembunyikan yang tidak cocok
        for (i = 0; i < tr.length; i++) {
            if (tr[i].parentNode.tagName === 'TBODY') { // Pastikan hanya memproses baris data (bukan thead)
                var foundInRow = false;
                // Kolom untuk pencarian: Tanggal (1), Pengaju (2), Nama Barang (3), Merk (4), Satuan (6)
                var columnsToSearch = [1, 2, 3, 4, 6];
                for (j = 0; j < columnsToSearch.length; j++) {
                    td = tr[i].cells[columnsToSearch[j]];
                    if (td) {
                        txtValue = td.textContent || td.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            foundInRow = true;
                            break;
                        }
                    }
                }

                if (foundInRow) {
                    tr[i].style.display = "";
                    foundVisibleRow = true;
                } else {
                    tr[i].style.display = "none";
                }
            }
        }

        // Tampilkan/sembunyikan pesan "Data tidak ditemukan"
        if (foundVisibleRow) {
            noDataMessage.style.display = "none";
        } else {
            noDataMessage.style.display = "block";
        }
    }

    // Event listener untuk memicu filter saat input berubah
    document.getElementById("searchInput").addEventListener("keyup", filterTable);
    document.getElementById("searchInput").addEventListener("input", filterTable);

    // Panggil filterTable saat halaman dimuat untuk menyembunyikan pesan jika ada data
    document.addEventListener('DOMContentLoaded', filterTable);
</script>

</body>

</html>