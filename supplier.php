<?php
// Include sidebar.php di awal. Ini akan menangani session_start(), head, body, sidebar, dan topbar.
include 'sidebar.php'; 

// Pastikan file function.php Anda menginisialisasi koneksi database $conn.
include_once "function.php"; 

// --- Tangani Operasi CRUD untuk Supplier (dengan prepared statements untuk keamanan) ---

// Tangani operasi Edit Supplier
if (isset($_POST['SimpanEditSupplier'])) {
    $idsupplier = htmlspecialchars($_POST['Edit_Id_Supplier']);
    $namasupplier = htmlspecialchars($_POST['Edit_Nama_Supplier']);
    $notelp = htmlspecialchars($_POST['Edit_No_Telp']);
    $alamat = htmlspecialchars($_POST['Edit_Alamat']);

    // Prepared statement untuk UPDATE
    $query = "UPDATE supplier SET nama_supplier=?, no_telp=?, alamat=? WHERE id_supplier=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        // "sssi" -> string, string, string, integer (sesuaikan dengan tipe data kolom Anda)
        mysqli_stmt_bind_param($stmt, "sssi", $namasupplier, $notelp, $alamat, $idsupplier);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo '<script>alert("Data Supplier Berhasil Diperbarui!"); window.location.href="supplier.php";</script>';
        } else {
            echo '<script>alert("Gagal Memperbarui Data Supplier: ' . mysqli_error($conn) . '"); window.location.href="supplier.php";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Update: ' . mysqli_error($conn) . '"); window.location.href="supplier.php";</script>';
    }
}

// Tangani operasi Hapus Supplier
if (isset($_GET['hapus'])) {
    $id_supplier_to_delete = htmlspecialchars($_GET['hapus']);

    // Prepared statement untuk DELETE
    $query = "DELETE FROM supplier WHERE id_supplier=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        // "i" -> integer (sesuai dengan tipe data id_supplier)
        mysqli_stmt_bind_param($stmt, "i", $id_supplier_to_delete);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo '<script>alert("Data Supplier Berhasil Dihapus!"); window.location.href="supplier.php";</script>';
        } else {
            echo '<script>alert("Gagal Menghapus Data Supplier: ' . mysqli_error($conn) . '"); window.location.href="supplier.php";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Delete: ' . mysqli_error($conn) . '"); window.location.href="supplier.php";</script>';
    }
}

// Tangani operasi Tambah Supplier
if (isset($_POST['addsupplier'])) {
    $namasupplier = htmlspecialchars($_POST['namasupplier']);
    $notelp = htmlspecialchars($_POST['notelp']);
    $alamat = htmlspecialchars($_POST['alamat']);

    // Menggunakan prepared statement untuk INSERT
    $query = "INSERT INTO supplier (nama_supplier, no_telp, alamat) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $namasupplier, $notelp, $alamat); // string, string, string
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo '<script>alert("Data Supplier Berhasil Ditambahkan!"); window.location.href="supplier.php";</script>';
        } else {
            echo '<script>alert("Gagal Menambahkan Data Supplier: ' . mysqli_error($conn) . '"); window.location.href="supplier.php";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Tambah: ' . mysqli_error($conn) . '"); window.location.href="supplier.php";</script>';
    }
}

// --- Logika Paginasi ---
$limit = 5; // Batas data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

// Ambil semua data supplier untuk halaman saat ini
$start = ($page - 1) * $limit;
$ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM supplier ORDER BY id_supplier DESC LIMIT $start, $limit");

// Hitung total data untuk paginasi
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_supplier) AS total FROM supplier");
$total_data = mysqli_fetch_assoc($total_data_query)['total'];
$total_pages = ceil($total_data / $limit);

if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
} elseif ($total_pages == 0) {
    $page = 1;
}

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Supplier</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSupplierModal">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari supplier..." list="supplierSuggestions">
                    <datalist id="supplierSuggestions"></datalist>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="myTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Supplier</th>
                            <th>Nomor Telepon/WA</th>
                            <th>Alamat</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $id_supplier = $data['id_supplier'];
                                $nama_supplier = $data['nama_supplier'];
                                $no_telp = $data['no_telp'];
                                $alamat = $data['alamat'];
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= htmlspecialchars($nama_supplier) ?></td>
                                    <td><?= htmlspecialchars($no_telp) ?></td>
                                    <td><?= htmlspecialchars($alamat) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#editSupplierModal<?= $id_supplier ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $id_supplier ?>&page=<?= $page ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data supplier ini?')" class="btn btn-danger btn-sm mr-1">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                        <form action="ppbsupplier.php" method="POST" target="_blank" class="d-inline">
                                            <input type="hidden" name="id_supplier_to_print" value="<?= $id_supplier ?>">
                                            <button type="submit" class="btn btn-info btn-sm">
                                                <i class="fa fa-print"></i> Cetak
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editSupplierModal<?= $id_supplier ?>" tabindex="-1" role="dialog" aria-labelledby="editSupplierModalLabel<?= $id_supplier ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editSupplierModalLabel<?= $id_supplier ?>">Edit Supplier</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="editNamaSupplier<?= $id_supplier ?>">Nama Supplier:</label>
                                                        <input type="hidden" name="Edit_Id_Supplier" value="<?= $id_supplier ?>">
                                                        <input type="text" name="Edit_Nama_Supplier" value="<?= htmlspecialchars($nama_supplier) ?>" class="form-control" id="editNamaSupplier<?= $id_supplier ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editNoTelp<?= $id_supplier ?>">Nomor Telepon/WA:</label>
                                                        <input type="text" name="Edit_No_Telp" value="<?= htmlspecialchars($no_telp) ?>" class="form-control" id="editNoTelp<?= $id_supplier ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editAlamat<?= $id_supplier ?>">Alamat:</label>
                                                        <textarea name="Edit_Alamat" class="form-control" id="editAlamat<?= $id_supplier ?>" rows="3" required><?= htmlspecialchars($alamat) ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditSupplier" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            echo '<tr><td colspan="5" class="text-center">Belum ada data supplier.</td></tr>';
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

<?php
// Modal Tambah Supplier Baru
?>
<div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSupplierModalLabel">Tambah Supplier Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="supplier.php">
                <div class="modal-body">
                    <div class="form-group">
                        <input type="text" name="namasupplier" placeholder="Nama Supplier" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="notelp" placeholder="Nomor Telepon/WA" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <textarea name="alamat" placeholder="Alamat" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="addsupplier">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Skrip JavaScript yang spesifik untuk halaman ini
?>
<script>
    // Fungsi untuk memfilter tabel di sisi klien
    function filterTable() {
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("myTable");
        tr = table.getElementsByTagName("tr");
        var noDataMessage = document.getElementById("noDataMessage");
        var foundVisibleRow = false; // Flag untuk melacak apakah ada baris yang terlihat

        // Loop melalui semua baris tabel, dan sembunyikan yang tidak cocok dengan kueri pencarian
        for (i = 0; i < tr.length; i++) {
            // Lewati baris header dan footer (thead, tfoot)
            if (tr[i].parentNode.tagName === 'TBODY') {
                var foundInRow = false;
                // Loop melalui semua kolom yang relevan (Nama Supplier, Nomor Telepon/WA, Alamat)
                // Dimulai dari indeks 1 (Nama Supplier) dan berhenti sebelum kolom Opsi (indeks 4)
                for (j = 1; j < tr[i].cells.length - 1; j++) { // Kolom 1, 2, 3
                    td = tr[i].cells[j];
                    if (td) {
                        txtValue = td.textContent || td.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            foundInRow = true;
                            break;
                        }
                    }
                }
                if (foundInRow) {
                    tr[i].style.display = ""; // Tampilkan baris
                    foundVisibleRow = true; // Set flag karena ada baris yang terlihat
                } else {
                    tr[i].style.display = "none"; // Sembunyikan baris
                }
            }
        }

        // Tampilkan atau sembunyikan pesan "Data tidak ada"
        if (foundVisibleRow) {
            noDataMessage.style.display = "none"; // Sembunyikan pesan jika ada data
        } else {
            noDataMessage.style.display = "block"; // Tampilkan pesan jika tidak ada data
        }
    }

    // Event listener untuk input pencarian (memfilter tabel secara real-time)
    document.getElementById("searchInput").addEventListener("keyup", filterTable);
    document.getElementById("searchInput").addEventListener("input", filterTable); // Juga untuk event input

    // Event listener untuk mengambil saran dari database secara real-time (AJAX)
    let debounceTimer;
    document.getElementById("searchInput").addEventListener("input", function() {
        clearTimeout(debounceTimer);
        const inputVal = this.value;

        debounceTimer = setTimeout(() => {
            if (inputVal.length >= 2) {
                // Pastikan Anda memiliki file 'supplier_suggestions.php'
                // File ini akan mengembalikan daftar saran dalam format JSON
                fetch('supplier_suggestions.php?query=' + encodeURIComponent(inputVal))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        var datalist = document.getElementById('supplierSuggestions');
                        datalist.innerHTML = '';
                        if (data && Array.isArray(data)) {
                            data.forEach(item => {
                                var option = document.createElement('option');
                                option.value = item;
                                datalist.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching suggestions:', error));
            } else {
                document.getElementById('supplierSuggestions').innerHTML = '';
            }
        }, 300);
    });

    // Panggil filterTable() saat halaman dimuat pertama kali untuk menangani kasus kosong awal
    document.addEventListener('DOMContentLoaded', filterTable);

    // Opsional: Jika Anda ingin memicu filter ulang ketika memilih saran dari datalist
    document.getElementById("searchInput").addEventListener("change", filterTable);
</script>

<?php
// Include footer.php di akhir. Ini akan menutup semua tag HTML yang dibuka di sidebar.php
// dan menyertakan skrip JavaScript global.
include 'footer.php';
?>