<?php
// Include sidebar.php di awal. Ini akan menangani session_start(), head, body, sidebar, dan topbar.
include 'sidebar.php'; 

// Pastikan file function.php Anda menginisialisasi koneksi database $conn.
// Jika koneksi sudah di function.php, ini sudah benar.
include_once "function.php"; 

// --- Logika Paginasi (Dipindahkan ke Awal, sebelum operasi CRUD) ---

$limit = 5; // Batas data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

// Hitung total data untuk paginasi
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_barang) AS total FROM barang");
$total_data = mysqli_fetch_assoc($total_data_query)['total'];
$total_pages = ceil($total_data / $limit);

// Pastikan halaman tidak melebihi total halaman yang ada
if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
} elseif ($total_pages == 0) { // Jika tidak ada data sama sekali
    $page = 1;
}

$start = ($page - 1) * $limit; // Sekarang $start akan selalu terdefinisi

// --- Akhir Logika Paginasi ---

// --- Tangani Operasi CRUD (dengan prepared statements untuk keamanan) ---
// ... (Logika CRUD Anda di sini, tidak ada perubahan) ...

// Tangani operasi Edit
if (isset($_POST['SimpanEditBarang'])) {
    $idbarang = htmlspecialchars($_POST['Edit_Id_Barang']);
    $namabarang = htmlspecialchars($_POST['Edit_Nama_Barang']);
    $merk = htmlspecialchars($_POST['Edit_Merk']);
    $jumlah = htmlspecialchars($_POST['Edit_Jumlah']);

    $query = "UPDATE barang SET namabarang=?, merk=?, jumlah=? WHERE id_barang=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssii", $namabarang, $merk, $jumlah, $idbarang);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            // Penting: Sertakan parameter 'page' saat redirect
            echo '<script>alert("Data Barang Berhasil Diperbarui!"); window.location.href="barang.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Memperbarui Data Barang: ' . mysqli_error($conn) . '"); window.location.href="barang.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Update: ' . mysqli_error($conn) . '"); window.location.href="barang.php?page=' . $page . '";</script>';
    }
    exit(); // Tambahkan exit() setelah redirect
}

// Tangani operasi Hapus
if (isset($_GET['hapus'])) {
    $id_barang_to_delete = htmlspecialchars($_GET['hapus']);

    $query = "DELETE FROM barang WHERE id_barang=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_barang_to_delete);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            // Penting: Sertakan parameter 'page' saat redirect
            echo '<script>alert("Data Barang Berhasil Dihapus!"); window.location.href="barang.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menghapus Data Barang: ' . mysqli_error($conn) . '"); window.location.href="barang.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Delete: ' . mysqli_error($conn) . '"); window.location.href="barang.php?page=' . $page . '";</script>';
    }
    exit(); // Tambahkan exit() setelah redirect
}

// Tangani operasi Tambah Barang
if (isset($_POST['addbarang'])) {
    $namabarang = htmlspecialchars($_POST['namabarang']);
    $merk = htmlspecialchars($_POST['merk']);
    $jumlah = htmlspecialchars($_POST['jumlah']);

    $query = "INSERT INTO barang (namabarang, merk, jumlah) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssi", $namabarang, $merk, $jumlah);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            // Penting: Sertakan parameter 'page' saat redirect
            echo '<script>alert("Data Barang Berhasil Ditambahkan!"); window.location.href="barang.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menambahkan Data Barang: ' . mysqli_error($conn) . '"); window.location.href="barang.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Tambah: ' . mysqli_error($conn) . '"); window.location.href="barang.php?page=' . $page . '";</script>';
    }
    exit(); // Tambahkan exit() setelah redirect
}

// Ambil semua data barang untuk halaman saat ini
// Query ini harus di bawah definisi $start
$ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM barang ORDER BY id_barang DESC LIMIT $start, $limit");

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Barang</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addBarangModal">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari barang..." list="barangSuggestions">
                    <datalist id="barangSuggestions"></datalist>
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
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= htmlspecialchars($namabarang) ?></td>
                                    <td><?= htmlspecialchars($merk) ?></td>
                                    <td><?= htmlspecialchars($jumlah) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#editBarangModal<?= $id_barang ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $id_barang ?>&page=<?= $page ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data barang ini?')" class="btn btn-danger btn-sm mr-1">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                        <form action="ppb1.php" method="POST" target="_blank" class="d-inline">
                                            <input type="hidden" name="id_barang_to_print" value="<?= $id_barang ?>">
                                            <button type="submit" class="btn btn-info btn-sm">
                                                <i class="fa fa-print"></i> Cetak
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editBarangModal<?= $id_barang ?>" tabindex="-1" role="dialog" aria-labelledby="editBarangModalLabel<?= $id_barang ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editBarangModalLabel<?= $id_barang ?>">Edit Barang</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="editNamaBarang<?= $id_barang ?>">Nama Barang:</label>
                                                        <input type="hidden" name="Edit_Id_Barang" value="<?= $id_barang ?>">
                                                        <input type="text" name="Edit_Nama_Barang" value="<?= htmlspecialchars($namabarang) ?>" class="form-control" id="editNamaBarang<?= $id_barang ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editMerk<?= $id_barang ?>">Merk:</label>
                                                        <input type="text" name="Edit_Merk" value="<?= htmlspecialchars($merk) ?>" class="form-control" id="editMerk<?= $id_barang ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editJumlah<?= $id_barang ?>">Jumlah:</label>
                                                        <input type="number" name="Edit_Jumlah" value="<?= htmlspecialchars($jumlah) ?>" class="form-control" id="editJumlah<?= $id_barang ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditBarang" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            echo '<tr><td colspan="5" class="text-center">Belum ada data barang.</td></tr>';
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
// Modal Tambah Barang Baru (Ini diletakkan di sini jika hanya spesifik untuk halaman ini)
// Jika Anda ingin modal ini bisa diakses dari halaman lain, pindahkan ke footer.php
?>
<div class="modal fade" id="addBarangModal" tabindex="-1" role="dialog" aria-labelledby="addBarangModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBarangModalLabel">Tambah Barang Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="barang.php">
                <div class="modal-body">
                    <div class="form-group">
                        <input type="text" name="namabarang" placeholder="Nama Barang" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="merk" placeholder="Merk" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <input type="number" name="jumlah" placeholder="Jumlah" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="addbarang">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Skrip JavaScript yang spesifik untuk halaman ini
// Skrip filterTable() dan AJAX datalist harus tetap di sini
// karena mereka berinteraksi langsung dengan elemen di halaman barang.php
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
                // Loop melalui semua kolom yang relevan (Nama Barang, Merk, Jumlah)
                // Dimulai dari indeks 1 (Nama Barang) dan berhenti sebelum kolom Opsi (indeks 4)
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
                // Pastikan Anda memiliki file 'barang_suggestions.php'
                // File ini akan mengembalikan daftar saran dalam format JSON
                fetch('barang_suggestions.php?query=' + encodeURIComponent(inputVal))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        var datalist = document.getElementById('barangSuggestions');
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
                document.getElementById('barangSuggestions').innerHTML = '';
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