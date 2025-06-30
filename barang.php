<?php
// Langkah 1: Panggil file koneksi. Ini harus menjadi baris pertama untuk memastikan $conn tersedia.
// Pastikan Anda sudah membuat file koneksi.php yang berisi koneksi ke database.
require_once 'function.php';

// Langkah 2: Logika Pemrosesan Form (CRUD) dan Paginasi
// Semua logika PHP diletakkan di sini, sebelum tag HTML apa pun.

// --- Logika Paginasi ---
$limit = 10; // Batas data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$start = ($page - 1) * $limit;

// --- Tangani Operasi CRUD ---

// Tangani operasi Edit Barang
if (isset($_POST['SimpanEditBarang'])) {
    $id_barang = htmlspecialchars($_POST['Edit_Id_Barang']);
    $namabarang = htmlspecialchars($_POST['Edit_Nama_Barang']);
    $merk = htmlspecialchars($_POST['Edit_Merk']);
    $jumlah = htmlspecialchars($_POST['Edit_Jumlah']);
    $satuan = htmlspecialchars($_POST['Edit_Satuan']);

    $query = "UPDATE barang SET namabarang=?, merk=?, jumlah=?, satuan=? WHERE id_barang=?";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssisi", $namabarang, $merk, $jumlah, $satuan, $id_barang);
        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("Data Barang Berhasil Diperbarui!"); window.location.href="barang.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Memperbarui Data: ' . mysqli_error($conn) . '"); window.location.href="barang.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Query Gagal Disiapkan: ' . mysqli_error($conn) . '"); window.location.href="barang.php?page=' . $page . '";</script>';
    }
    exit();
}

// Tangani operasi Hapus Barang
if (isset($_GET['hapus'])) {
    $id_to_delete = htmlspecialchars($_GET['hapus']);
    $query_delete = "DELETE FROM barang WHERE id_barang=?";
    $stmt_delete = mysqli_prepare($conn, $query_delete);
    if ($stmt_delete) {
        mysqli_stmt_bind_param($stmt_delete, "i", $id_to_delete);
        if (mysqli_stmt_execute($stmt_delete)) {
            echo '<script>alert("Data Barang Berhasil Dihapus!"); window.location.href="barang.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menghapus Data: ' . mysqli_error($conn) . '"); window.location.href="barang.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt_delete);
    } else {
        echo '<script>alert("Query Hapus Gagal Disiapkan: ' . mysqli_error($conn) . '"); window.location.href="barang.php?page=' . $page . '";</script>';
    }
    exit();
}

// Tangani operasi Tambah Barang
if (isset($_POST['addbarang'])) {
    $namabarang = htmlspecialchars($_POST['namabarang']);
    $merk = htmlspecialchars($_POST['merk']);
    $jumlah = htmlspecialchars($_POST['jumlah']);
    $satuan = htmlspecialchars($_POST['satuan']);

    $query = "INSERT INTO barang (namabarang, merk, jumlah, satuan) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssis", $namabarang, $merk, $jumlah, $satuan);
        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("Data Barang Berhasil Ditambahkan!"); window.location.href="barang.php";</script>';
        } else {
            echo '<script>alert("Gagal Menambahkan Data: ' . mysqli_error($conn) . '"); window.location.href="barang.php";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Query Tambah Gagal Disiapkan: ' . mysqli_error($conn) . '"); window.location.href="barang.php";</script>';
    }
    exit();
}

// Langkah 3: Ambil data setelah semua operasi CRUD selesai
$ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM barang ORDER BY id_barang DESC LIMIT $start, $limit");

// Hitung total data untuk paginasi
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_barang) AS total FROM barang");
$total_data = mysqli_fetch_assoc($total_data_query)['total'];
$total_pages = ceil($total_data / $limit);

// Langkah 4: Include sidebar setelah semua logika header selesai.
include 'sidebar.php';
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Barang</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addBarangModal">
                <i class="fas fa-plus"></i> Tambah Barang
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari data barang...">
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
                            <th>Satuan</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $id_barang = $data['id_barang'];
                                $namabarang = $data['namabarang'];
                                $merk = $data['merk'];
                                $jumlah = $data['jumlah'];
                                $satuan = $data['satuan'];
                        ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td><?= htmlspecialchars($namabarang); ?></td>
                                    <td><?= htmlspecialchars($merk); ?></td>
                                    <td><?= htmlspecialchars($jumlah); ?></td>
                                    <td><?= htmlspecialchars($satuan); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-warning btn-sm mb-1" data-toggle="modal" data-target="#editBarangModal<?= $id_barang ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $id_barang ?>&page=<?= $page ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')" class="btn btn-danger btn-sm mb-1">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                        <a href="cetak_barang.php?id=<?= $id_barang ?>" target="_blank" class="btn btn-info btn-sm mb-1">
                                            <i class="fa fa-print"></i> Cetak
                                        </a>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editBarangModal<?= $id_barang ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="post" action="barang.php?page=<?= $page; ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Data Barang</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="Edit_Id_Barang" value="<?= $id_barang ?>">
                                                    <div class="form-group">
                                                        <label>Nama Barang:</label>
                                                        <input type="text" name="Edit_Nama_Barang" value="<?= htmlspecialchars($namabarang) ?>" class="form-control" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Merk:</label>
                                                        <input type="text" name="Edit_Merk" value="<?= htmlspecialchars($merk) ?>" class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Jumlah:</label>
                                                        <input type="number" name="Edit_Jumlah" value="<?= htmlspecialchars($jumlah) ?>" class="form-control" required min="0">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Satuan:</label>
                                                        <input type="text" name="Edit_Satuan" value="<?= htmlspecialchars($satuan) ?>" class="form-control" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditBarang" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            } // Akhir loop
                        } else {
                            echo '<tr><td colspan="6" class="text-center">Belum ada data barang.</td></tr>';
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

<div class="modal fade" id="addBarangModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Barang Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Barang:</label>
                        <input type="text" name="namabarang" placeholder="Nama Barang" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Merk:</label>
                        <input type="text" name="merk" placeholder="Merk Barang (opsional)" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Jumlah:</label>
                        <input type="number" name="jumlah" placeholder="Jumlah Awal" class="form-control" required min="0">
                    </div>
                    <div class="form-group">
                        <label>Satuan:</label>
                        <input type="text" name="satuan" placeholder="Contoh: Unit, Buah, Pcs" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="addbarang">Tambah Barang</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php
// Langkah 6: Include footer dan script JS
include 'footer.php';
?>

<script>
    // Fungsi untuk filter tabel (pencarian), sama seperti di pengajuan.php
    function filterTable() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("myTable");
        tr = table.getElementsByTagName("tr");
        var noDataMessage = document.getElementById("noDataMessage");
        var foundVisibleRow = false;

        for (i = 0; i < tr.length; i++) {
            if (tr[i].parentNode.tagName === 'TBODY') {
                // Cari di semua kolom kecuali kolom No dan Opsi
                td = tr[i].getElementsByTagName("td");
                var foundInRow = false;
                for (var j = 1; j < td.length - 1; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
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
        noDataMessage.style.display = foundVisibleRow ? "none" : "block";
    }
    document.getElementById("searchInput").addEventListener("keyup", filterTable);
</script>

</body>
</html>