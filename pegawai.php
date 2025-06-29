<?php
// Include sidebar.php di awal. Ini akan menangani session_start(), head, body, sidebar, dan topbar.
include 'sidebar.php'; 

// Pastikan file function.php Anda menginisialisasi koneksi database $conn.
include_once "function.php"; 

// --- Logika Paginasi (Dipindahkan ke Awal, sebelum operasi CRUD) ---

$limit = 5; // Batas data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

// Hitung total data untuk paginasi
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_pegawai) AS total FROM pegawai");
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

// Tangani operasi Edit
if (isset($_POST['SimpanEditPegawai'])) {
    $idpegawai = htmlspecialchars($_POST['Edit_Id_Pegawai']);
    $nama = htmlspecialchars($_POST['Edit_Nama']);
    $no_telepon = htmlspecialchars($_POST['Edit_No_Telepon']);
    $alamat = htmlspecialchars($_POST['Edit_Alamat']);

    $query = "UPDATE pegawai SET nama=?, no_telepon=?, alamat=? WHERE id_pegawai=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssi", $nama, $no_telepon, $alamat, $idpegawai);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            // Penting: Sertakan parameter 'page' saat redirect
            echo '<script>alert("Data Pegawai Berhasil Diperbarui!"); window.location.href="pegawai.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Memperbarui Data Pegawai: ' . mysqli_error($conn) . '"); window.location.href="pegawai.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Update: ' . mysqli_error($conn) . '"); window.location.href="pegawai.php?page=' . $page . '";</script>';
    }
    exit(); // Tambahkan exit() setelah redirect
}

// Tangani operasi Hapus
if (isset($_GET['hapus'])) {
    $id_pegawai_to_delete = htmlspecialchars($_GET['hapus']);

    $query = "DELETE FROM pegawai WHERE id_pegawai=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_pegawai_to_delete);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            // Penting: Sertakan parameter 'page' saat redirect
            echo '<script>alert("Data Pegawai Berhasil Dihapus!"); window.location.href="pegawai.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menghapus Data Pegawai: ' . mysqli_error($conn) . '"); window.location.href="pegawai.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Delete: ' . mysqli_error($conn) . '"); window.location.href="pegawai.php?page=' . $page . '";</script>';
    }
    exit(); // Tambahkan exit() setelah redirect
}

// Tangani operasi Tambah Pegawai
if (isset($_POST['addpegawai'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $no_telepon = htmlspecialchars($_POST['no_telepon']);
    $alamat = htmlspecialchars($_POST['alamat']);

    $query = "INSERT INTO pegawai (nama, no_telepon, alamat) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $nama, $no_telepon, $alamat);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            // Penting: Sertakan parameter 'page' saat redirect
            echo '<script>alert("Data Pegawai Berhasil Ditambahkan!"); window.location.href="pegawai.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menambahkan Data Pegawai: ' . mysqli_error($conn) . '"); window.location.href="pegawai.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Tambah: ' . mysqli_error($conn) . '"); window.location.href="pegawai.php?page=' . $page . '";</script>';
    }
    exit(); // Tambahkan exit() setelah redirect
}

// Ambil semua data pegawai untuk halaman saat ini
// Query ini harus di bawah definisi $start
$ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM pegawai ORDER BY id_pegawai DESC LIMIT $start, $limit");

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Pegawai</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPegawaiModal">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari pegawai..." list="pegawaiSuggestions">
                    <datalist id="pegawaiSuggestions"></datalist>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="myTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>No. Telepon</th>
                            <th>Alamat</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $id_pegawai = $data['id_pegawai'];
                                $nama = $data['nama'];
                                $no_telepon = $data['no_telepon'];
                                $alamat = $data['alamat'];
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= htmlspecialchars($nama) ?></td>
                                    <td><?= htmlspecialchars($no_telepon) ?></td>
                                    <td><?= htmlspecialchars($alamat) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#editPegawaiModal<?= $id_pegawai ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $id_pegawai ?>&page=<?= $page ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data pegawai ini?')" class="btn btn-danger btn-sm mr-1">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                        </td>
                                </tr>

                                <div class="modal fade" id="editPegawaiModal<?= $id_pegawai ?>" tabindex="-1" role="dialog" aria-labelledby="editPegawaiModalLabel<?= $id_pegawai ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editPegawaiModalLabel<?= $id_pegawai ?>">Edit Pegawai</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="editNama<?= $id_pegawai ?>">Nama:</label>
                                                        <input type="hidden" name="Edit_Id_Pegawai" value="<?= $id_pegawai ?>">
                                                        <input type="text" name="Edit_Nama" value="<?= htmlspecialchars($nama) ?>" class="form-control" id="editNama<?= $id_pegawai ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editNoTelepon<?= $id_pegawai ?>">No. Telepon:</label>
                                                        <input type="text" name="Edit_No_Telepon" value="<?= htmlspecialchars($no_telepon) ?>" class="form-control" id="editNoTelepon<?= $id_pegawai ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editAlamat<?= $id_pegawai ?>">Alamat:</label>
                                                        <textarea name="Edit_Alamat" class="form-control" id="editAlamat<?= $id_pegawai ?>" required><?= htmlspecialchars($alamat) ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditPegawai" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            echo '<tr><td colspan="5" class="text-center">Belum ada data pegawai.</td></tr>';
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
// Modal Tambah Pegawai Baru
?>
<div class="modal fade" id="addPegawaiModal" tabindex="-1" role="dialog" aria-labelledby="addPegawaiModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPegawaiModalLabel">Tambah Pegawai Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="pegawai.php">
                <div class="modal-body">
                    <div class="form-group">
                        <input type="text" name="nama" placeholder="Nama Pegawai" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="no_telepon" placeholder="No. Telepon" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <textarea name="alamat" placeholder="Alamat" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="addpegawai">Tambah</button>
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
                // Loop melalui semua kolom yang relevan (Nama, No. Telepon, Alamat)
                // Dimulai dari indeks 1 (Nama) dan berhenti sebelum kolom Opsi (indeks 4)
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
                // Pastikan Anda memiliki file 'pegawai_suggestions.php'
                // File ini akan mengembalikan daftar saran dalam format JSON
                fetch('pegawai_suggestions.php?query=' + encodeURIComponent(inputVal))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        var datalist = document.getElementById('pegawaiSuggestions');
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
                document.getElementById('pegawaiSuggestions').innerHTML = '';
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