<?php
// Include sidebar.php di awal. Ini akan menangani session_start(), head, body, sidebar, dan topbar.
include 'sidebar.php'; 

// Pastikan file function.php Anda menginisialisasi koneksi database $conn.
include_once "function.php"; 

// --- Logika Paginasi (Dipindahkan ke Awal) ---
// Ini harus dieksekusi sebelum operasi CRUD yang mungkin melakukan redirect/exit.

$limit = 5; // Batas data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

// Hitung total data untuk paginasi
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_ruangan) AS total FROM ruangan");
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


// --- Tangani Operasi CRUD untuk Ruangan ---

// Tangani operasi Edit Ruangan
if (isset($_POST['SimpanEditRuangan'])) {
    $idruangan = htmlspecialchars($_POST['Edit_Id_Ruangan']);
    $namaruangan = htmlspecialchars($_POST['Edit_Nama_Ruangan']);
    
    // Prepared statement untuk UPDATE
    $query = "UPDATE ruangan SET nama_ruangan=? WHERE id_ruangan=?"; 
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        // "si" -> string, integer
        mysqli_stmt_bind_param($stmt, "si", $namaruangan, $idruangan);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo '<script>alert("Data Ruangan Berhasil Diperbarui!"); window.location.href="ruangan.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Memperbarui Data Ruangan: ' . mysqli_error($conn) . '"); window.location.href="ruangan.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Update: ' . mysqli_error($conn) . '"); window.location.href="ruangan.php?page=' . $page . '";</script>';
    }
    exit(); // Penting: Hentikan eksekusi setelah redirect
}

// Tangani operasi Hapus Ruangan
if (isset($_GET['hapus'])) {
    $id_ruangan_to_delete = htmlspecialchars($_GET['hapus']);

    // Prepared statement untuk DELETE
    $query = "DELETE FROM ruangan WHERE id_ruangan=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        // "i" -> integer
        mysqli_stmt_bind_param($stmt, "i", $id_ruangan_to_delete);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo '<script>alert("Data Ruangan Berhasil Dihapus!"); window.location.href="ruangan.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menghapus Data Ruangan: ' . mysqli_error($conn) . '"); window.location.href="ruangan.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Delete: ' . mysqli_error($conn) . '"); window.location.href="ruangan.php?page=' . $page . '";</script>';
    }
    exit(); // Penting: Hentikan eksekusi setelah redirect
}

// Ambil semua data ruangan untuk halaman saat ini (setelah $start didefinisikan)
// Query disesuaikan untuk hanya mengambil id_ruangan dan nama_ruangan
$ambilsemuadatanya = mysqli_query($conn, "SELECT id_ruangan, nama_ruangan FROM ruangan ORDER BY id_ruangan DESC LIMIT $start, $limit");

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
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari ruangan..." list="ruanganSuggestions">
                    <datalist id="ruanganSuggestions"></datalist>
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
                                        <button type="button" class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#editRuanganModal<?= $id_ruangan ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $id_ruangan ?>&page=<?= $page ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ruangan ini?')" class="btn btn-danger btn-sm mr-1">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                        <form action="ppbruangan.php" method="POST" target="_blank" class="d-inline">
                                            <input type="hidden" name="id_ruangan_to_print" value="<?= $id_ruangan ?>">
                                            <button type="submit" class="btn btn-info btn-sm">
                                                <i class="fa fa-print"></i> Cetak
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editRuanganModal<?= $id_ruangan ?>" tabindex="-1" role="dialog" aria-labelledby="editRuanganModalLabel<?= $id_ruangan ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editRuanganModalLabel<?= $id_ruangan ?>">Edit Ruangan</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="editNamaRuangan<?= $id_ruangan ?>">Nama Ruangan:</label>
                                                        <input type="hidden" name="Edit_Id_Ruangan" value="<?= $id_ruangan ?>">
                                                        <input type="text" name="Edit_Nama_Ruangan" value="<?= htmlspecialchars($nama_ruangan) ?>" class="form-control" id="editNamaRuangan<?= $id_ruangan ?>" required>
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
                            } // Akhir dari loop while
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
// Modal Tambah Ruangan Baru
?>
<div class="modal fade" id="addRuanganModal" tabindex="-1" role="dialog" aria-labelledby="addRuanganModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRuanganModalLabel">Tambah Ruangan Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="function.php"> 
                <div class="modal-body">
                    <div class="form-group">
                        <input type="text" name="ruangan" placeholder="Nama Ruangan" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="addruangan">Tambah</button>
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
                // Hanya kolom Nama Ruangan (indeks 1) yang difilter
                for (j = 1; j < tr[i].cells.length - 1; j++) { 
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
                // Pastikan Anda memiliki file 'ruangan_suggestions.php'
                fetch('ruangan_suggestions.php?query=' + encodeURIComponent(inputVal))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        var datalist = document.getElementById('ruanganSuggestions');
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
                document.getElementById('ruanganSuggestions').innerHTML = '';
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