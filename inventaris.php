<?php
// Include sidebar.php di awal. Ini akan menangani session_start(), head, body, sidebar, dan topbar.
include 'sidebar.php'; 

// Pastikan file function.php Anda menginisialisasi koneksi database $conn.
include_once "function.php"; 

// --- Logika Paginasi ---

$limit = 5; // Batas data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

// Hitung total data untuk paginasi
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_inventaris) AS total FROM inventaris");
$total_data = mysqli_fetch_assoc($total_data_query)['total'];
$total_pages = ceil($total_data / $limit);

// Pastikan halaman tidak melebihi total halaman yang ada
if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
} elseif ($total_pages == 0) { // Jika tidak ada data sama sekali
    $page = 1;
}

$start = ($page - 1) * $limit;

// --- Akhir Logika Paginasi ---

// --- Tangani Operasi CRUD ---

// Tangani operasi Tambah Inventaris
if (isset($_POST['addInventaris'])) {
    $id_barang = htmlspecialchars($_POST['id_barang']);
    $id_ruangan = htmlspecialchars($_POST['id_ruangan']);
    $tanggal_inventaris = htmlspecialchars($_POST['tanggal_inventaris']);

    // Karena merk dan jumlah ada di tabel barang, kita tidak menambahkannya ke tabel inventaris.
    // Jika Anda ingin menyimpan merk dan jumlah *spesifik untuk entri inventaris ini*,
    // maka struktur tabel 'inventaris' di database Anda perlu diubah untuk memiliki kolom 'merk' dan 'jumlah'.
    // Untuk saat ini, kita hanya akan menampilkan informasi tersebut dari tabel 'barang'.

    $query_insert = "INSERT INTO inventaris (id_barang, id_ruangan, tanggal_inventaris) VALUES (?, ?, ?)";
    $stmt_insert = mysqli_prepare($conn, $query_insert);

    if ($stmt_insert) {
        mysqli_stmt_bind_param($stmt_insert, "iis", $id_barang, $id_ruangan, $tanggal_inventaris); // iis: integer, integer, string
        $result_insert = mysqli_stmt_execute($stmt_insert);

        if ($result_insert) {
            echo '<script>alert("Data Inventaris Berhasil Ditambahkan!"); window.location.href="inventaris.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menambahkan Data Inventaris: ' . mysqli_error($conn) . '"); window.location.href="inventaris.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt_insert);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Insert: ' . mysqli_error($conn) . '"); window.location.href="inventaris.php?page=' . $page . '";</script>';
    }
    exit();
}

// Tangani operasi Edit Inventaris
if (isset($_POST['SimpanEditInventaris'])) {
    $id_inventaris = htmlspecialchars($_POST['Edit_Id_Inventaris']);
    $id_barang_baru = htmlspecialchars($_POST['Edit_Id_Barang']);
    $id_ruangan_baru = htmlspecialchars($_POST['Edit_Id_Ruangan']);
    $tanggal_inventaris_baru = htmlspecialchars($_POST['Edit_Tanggal_Inventaris']);
    
    // Sama seperti penambahan, merk dan jumlah tidak disimpan di tabel inventaris.
    // Jadi tidak ada perubahan pada query UPDATE ini.
    $query_update = "UPDATE inventaris SET id_barang=?, id_ruangan=?, tanggal_inventaris=? WHERE id_inventaris=?";
    $stmt_update = mysqli_prepare($conn, $query_update);

    if ($stmt_update) {
        mysqli_stmt_bind_param($stmt_update, "iisi", $id_barang_baru, $id_ruangan_baru, $tanggal_inventaris_baru, $id_inventaris); // iisi: integer, integer, string, integer
        $result_update = mysqli_stmt_execute($stmt_update);

        if ($result_update) {
            echo '<script>alert("Data Inventaris Berhasil Diperbarui!"); window.location.href="inventaris.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Memperbarui Data Inventaris: ' . mysqli_error($conn) . '"); window.location.href="inventaris.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt_update);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Update: ' . mysqli_error($conn) . '"); window.location.href="inventaris.php?page=' . $page . '";</script>';
    }
    exit();
}

// Tangani operasi Hapus Inventaris
if (isset($_GET['hapus'])) {
    $id_inventaris_to_delete = htmlspecialchars($_GET['hapus']);

    $query_delete = "DELETE FROM inventaris WHERE id_inventaris=?";
    $stmt_delete = mysqli_prepare($conn, $query_delete);

    if ($stmt_delete) {
        mysqli_stmt_bind_param($stmt_delete, "i", $id_inventaris_to_delete);
        $result_delete = mysqli_stmt_execute($stmt_delete);

        if ($result_delete) {
            echo '<script>alert("Data Inventaris Berhasil Dihapus!"); window.location.href="inventaris.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menghapus Data Inventaris: ' . mysqli_error($conn) . '"); window.location.href="inventaris.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt_delete);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Delete: ' . mysqli_error($conn) . '"); window.location.href="inventaris.php?page=' . $page . '";</script>';
    }
    exit();
}

// Ambil semua data inventaris untuk halaman saat ini dengan JOIN ke tabel barang dan ruangan
$ambilsemuadatanya = mysqli_query($conn, "
    SELECT 
        i.id_inventaris,
        i.id_barang, 
        i.id_ruangan,
        i.tanggal_inventaris, 
        b.namabarang,
        b.merk,         -- Tambahkan kolom merk dari tabel barang
        b.jumlah,       -- Tambahkan kolom jumlah dari tabel barang
        r.nama_ruangan
    FROM 
        inventaris i
    LEFT JOIN 
        barang b ON i.id_barang = b.id_barang
    LEFT JOIN 
        ruangan r ON i.id_ruangan = r.id_ruangan
    ORDER BY 
        i.id_inventaris DESC 
    LIMIT $start, $limit
");

// Ambil data barang dan ruangan untuk dropdown di modal
$barang_options = mysqli_query($conn, "SELECT id_barang, namabarang, merk, jumlah FROM barang ORDER BY namabarang ASC"); // Ambil juga merk dan jumlah
$ruangan_options = mysqli_query($conn, "SELECT id_ruangan, nama_ruangan FROM ruangan ORDER BY nama_ruangan ASC");

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Inventaris Barang</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addInventarisModal">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari inventaris..." list="inventarisSuggestions">
                    <datalist id="inventarisSuggestions"></datalist>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="myTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Merk</th>         <th>Jumlah</th>       <th>Ruangan</th>
                            <th>Tanggal Inventaris</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $id_inventaris = $data['id_inventaris']; 
                                $id_barang = $data['id_barang']; 
                                $namabarang = $data['namabarang'];     // Dari JOIN
                                $merk = $data['merk'];                 // Dari JOIN
                                $jumlah = $data['jumlah'];             // Dari JOIN
                                $id_ruangan = $data['id_ruangan']; 
                                $nama_ruangan = $data['nama_ruangan']; // Dari JOIN
                                $tanggal_inventaris = $data['tanggal_inventaris'];
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= htmlspecialchars($namabarang ? $namabarang : 'N/A') ?></td>
                                    <td><?= htmlspecialchars($merk ? $merk : 'N/A') ?></td>        <td><?= htmlspecialchars($jumlah ? $jumlah : 'N/A') ?></td>      <td><?= htmlspecialchars($nama_ruangan ? $nama_ruangan : 'N/A') ?></td>
                                    <td><?= htmlspecialchars($tanggal_inventaris) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#editInventarisModal<?= $id_inventaris ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $id_inventaris ?>&page=<?= $page ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data inventaris ini?')" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editInventarisModal<?= $id_inventaris ?>" tabindex="-1" role="dialog" aria-labelledby="editInventarisModalLabel<?= $id_inventaris ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editInventarisModalLabel<?= $id_inventaris ?>">Edit Inventaris Barang</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="Edit_Id_Inventaris" value="<?= $id_inventaris ?>">
                                                    <div class="form-group">
                                                        <label for="editIdBarang<?= $id_inventaris ?>">Nama Barang:</label>
                                                        <select name="Edit_Id_Barang" class="form-control" id="editIdBarang<?= $id_inventaris ?>" required>
                                                            <option value="">-- Pilih Barang --</option>
                                                            <?php
                                                            mysqli_data_seek($barang_options, 0); // Reset pointer
                                                            while ($brg = mysqli_fetch_assoc($barang_options)) {
                                                                $selected = ($brg['id_barang'] == $id_barang) ? 'selected' : '';
                                                                echo '<option value="' . $brg['id_barang'] . '" ' . $selected . '>' . htmlspecialchars($brg['namabarang']) . ' (Merk: ' . htmlspecialchars($brg['merk']) . ', Jumlah: ' . htmlspecialchars($brg['jumlah']) . ')</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editIdRuangan<?= $id_inventaris ?>">Ruangan:</label>
                                                        <select name="Edit_Id_Ruangan" class="form-control" id="editIdRuangan<?= $id_inventaris ?>" required>
                                                            <option value="">-- Pilih Ruangan --</option>
                                                            <?php
                                                            mysqli_data_seek($ruangan_options, 0); // Reset pointer
                                                            while ($ru = mysqli_fetch_assoc($ruangan_options)) {
                                                                $selected = ($ru['id_ruangan'] == $id_ruangan) ? 'selected' : '';
                                                                echo '<option value="' . $ru['id_ruangan'] . '" ' . $selected . '>' . htmlspecialchars($ru['nama_ruangan']) . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editTanggalInventaris<?= $id_inventaris ?>">Tanggal Inventaris:</label>
                                                        <input type="date" name="Edit_Tanggal_Inventaris" value="<?= htmlspecialchars($tanggal_inventaris) ?>" class="form-control" id="editTanggalInventaris<?= $id_inventaris ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditInventaris" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            // Sesuaikan colspan karena sekarang ada 7 kolom yang ditampilkan (No, Nama Barang, Merk, Jumlah, Ruangan, Tgl Inventaris, Opsi)
                            echo '<tr><td colspan="7" class="text-center">Belum ada data inventaris barang.</td></tr>'; 
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

<div class="modal fade" id="addInventarisModal" tabindex="-1" role="dialog" aria-labelledby="addInventarisModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addInventarisModalLabel">Tambah Data Inventaris Barang Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="inventaris.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="id_barang">Nama Barang:</label>
                        <select name="id_barang" class="form-control" id="id_barang" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php
                            mysqli_data_seek($barang_options, 0); // Reset pointer
                            while ($brg = mysqli_fetch_assoc($barang_options)) {
                                echo '<option value="' . $brg['id_barang'] . '">' . htmlspecialchars($brg['namabarang']) . ' (Merk: ' . htmlspecialchars($brg['merk']) . ', Jumlah: ' . htmlspecialchars($brg['jumlah']) . ')</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_ruangan">Ruangan:</label>
                        <select name="id_ruangan" class="form-control" id="id_ruangan" required>
                            <option value="">-- Pilih Ruangan --</option>
                            <?php
                            mysqli_data_seek($ruangan_options, 0); // Reset pointer
                            while ($ru = mysqli_fetch_assoc($ruangan_options)) {
                                echo '<option value="' . $ru['id_ruangan'] . '">' . htmlspecialchars($ru['nama_ruangan']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tanggal_inventaris">Tanggal Inventaris:</label>
                        <input type="date" name="tanggal_inventaris" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="addInventaris">Tambah</button>
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
        var foundVisibleRow = false; 

        for (i = 0; i < tr.length; i++) {
            if (tr[i].parentNode.tagName === 'TBODY') {
                var foundInRow = false;
                // Kolom yang ingin dicari (indeks disesuaikan):
                // Nama Barang (1), Merk (2), Jumlah (3), Ruangan (4), Tanggal Inventaris (5)
                const searchColumns = [1, 2, 3, 4, 5]; 
                for (j = 0; j < searchColumns.length; j++) { 
                    td = tr[i].cells[searchColumns[j]];
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

        if (foundVisibleRow) {
            noDataMessage.style.display = "none"; 
        } else {
            noDataMessage.style.display = "block"; 
        }
    }

    document.getElementById("searchInput").addEventListener("keyup", filterTable);
    document.getElementById("searchInput").addEventListener("input", filterTable);

    let debounceTimer;
    document.getElementById("searchInput").addEventListener("input", function() {
        clearTimeout(debounceTimer);
        const inputVal = this.value;

        debounceTimer = setTimeout(() => {
            if (inputVal.length >= 2) {
                // Anda mungkin perlu membuat file 'inventaris_suggestions.php' untuk ini.
                fetch('inventaris_suggestions.php?query=' + encodeURIComponent(inputVal))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        var datalist = document.getElementById('inventarisSuggestions');
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
                document.getElementById('inventarisSuggestions').innerHTML = '';
            }
        }, 300);
    });

    document.addEventListener('DOMContentLoaded', function() {
        filterTable();
    });

    document.getElementById("searchInput").addEventListener("change", filterTable);
</script>

<?php
// Include footer.php di akhir. Ini akan menutup semua tag HTML yang dibuka di sidebar.php
// dan menyertakan skrip JavaScript global.
include 'footer.php';
?>