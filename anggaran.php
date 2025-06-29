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
// Query untuk total data harus sama dengan query utama (JOINs) agar paginasi akurat
$total_data_query = mysqli_query($conn, "
    SELECT COUNT(a.id_anggaran) AS total 
    FROM anggaran a
    LEFT JOIN pemesanan pm ON a.id_pemesanan = pm.id_pemesanan
    LEFT JOIN pengajuan pj ON pm.id_pengajuan = pj.id_pengajuan
");
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

// Tangani operasi Tambah Anggaran
if (isset($_POST['addAnggaran'])) {
    $id_pemesanan = htmlspecialchars($_POST['id_pemesanan']);
    $nomor_anggaran = htmlspecialchars($_POST['nomor_anggaran']);
    $total = htmlspecialchars($_POST['total']);

    $query = "INSERT INTO anggaran (id_pemesanan, nomor_anggaran, total) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        // 'isd' untuk integer, string, decimal (sesuai total decimal(18,2) di DB)
        mysqli_stmt_bind_param($stmt, "isd", $id_pemesanan, $nomor_anggaran, $total); 
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo '<script>alert("Data Anggaran Berhasil Ditambahkan!"); window.location.href="anggaran.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menambahkan Data Anggaran: ' . mysqli_error($conn) . '"); window.location.href="anggaran.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Tambah: ' . mysqli_error($conn) . '"); window.location.href="anggaran.php?page=' . $page . '";</script>';
    }
    exit();
}

// Tangani operasi Edit Anggaran
if (isset($_POST['SimpanEditAnggaran'])) {
    $id_anggaran = htmlspecialchars($_POST['Edit_Id_Anggaran']);
    $id_pemesanan = htmlspecialchars($_POST['Edit_Id_Pemesanan']);
    $nomor_anggaran = htmlspecialchars($_POST['Edit_Nomor_Anggaran']);
    $total = htmlspecialchars($_POST['Edit_Total']);

    $query = "UPDATE anggaran SET id_pemesanan=?, nomor_anggaran=?, total=? WHERE id_anggaran=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        // 'isdi' untuk integer, string, decimal, integer (sesuai total decimal(18,2) di DB)
        mysqli_stmt_bind_param($stmt, "isdi", $id_pemesanan, $nomor_anggaran, $total, $id_anggaran); 
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo '<script>alert("Data Anggaran Berhasil Diperbarui!"); window.location.href="anggaran.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Memperbarui Data Anggaran: ' . mysqli_error($conn) . '"); window.location.href="anggaran.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Update: ' . mysqli_error($conn) . '"); window.location.href="anggaran.php?page=' . $page . '";</script>';
    }
    exit();
}

// Tangani operasi Hapus Anggaran
if (isset($_GET['hapus'])) {
    $id_anggaran_to_delete = htmlspecialchars($_GET['hapus']);

    $query = "DELETE FROM anggaran WHERE id_anggaran=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_anggaran_to_delete);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo '<script>alert("Data Anggaran Berhasil Dihapus!"); window.location.href="anggaran.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menghapus Data Anggaran: ' . mysqli_error($conn) . '"); window.location.href="anggaran.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Delete: ' . mysqli_error($conn) . '"); window.location.href="anggaran.php?page=' . $page . '";</script>';
    }
    exit();
}

// Ambil data anggaran dengan JOIN ke tabel pemesanan dan pengajuan
$ambilsemuadatanya = mysqli_query($conn, "
    SELECT 
        a.id_anggaran,
        a.nomor_anggaran,
        a.total,
        pj.nama_barang,
        pj.jumlah AS jumlah_barang_diajukan,
        pm.id_pemesanan -- Tambahkan ini untuk dropdown edit
    FROM 
        anggaran a
    LEFT JOIN 
        pemesanan pm ON a.id_pemesanan = pm.id_pemesanan
    LEFT JOIN 
        pengajuan pj ON pm.id_pengajuan = pj.id_pengajuan
    ORDER BY 
        a.id_anggaran DESC 
    LIMIT $start, $limit
");

// Ambil data untuk dropdown di modal (Pemesanan)
$pemesanan_options = mysqli_query($conn, "SELECT id_pemesanan, tanggal_pemesanan FROM pemesanan ORDER BY tanggal_pemesanan DESC");

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Anggaran</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addAnggaranModal">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari anggaran..." list="anggaranSuggestions">
                    <datalist id="anggaranSuggestions"></datalist>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="myTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Anggaran</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Total Anggaran</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $id_anggaran = $data['id_anggaran'];
                                $nomor_anggaran = htmlspecialchars($data['nomor_anggaran']);
                                $nama_barang = htmlspecialchars($data['nama_barang']);
                                $jumlah_barang_diajukan = htmlspecialchars($data['jumlah_barang_diajukan']);
                                // MODIFIKASI INI: number_format dengan 0 digit desimal
                                $total = number_format($data['total'], 0, ',', '.'); 
                                
                                // Ambil nilai ID untuk modal edit
                                $current_id_pemesanan = $data['id_pemesanan'];
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= $nomor_anggaran ?></td>
                                    <td><?= $nama_barang ?></td>
                                    <td><?= $jumlah_barang_diajukan ?></td>
                                    <td>Rp. <?= $total ?></td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#editAnggaranModal<?= $id_anggaran ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $id_anggaran ?>&page=<?= $page ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data anggaran ini?')" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editAnggaranModal<?= $id_anggaran ?>" tabindex="-1" role="dialog" aria-labelledby="editAnggaranModalLabel<?= $id_anggaran ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editAnggaranModalLabel<?= $id_anggaran ?>">Edit Anggaran</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="Edit_Id_Anggaran" value="<?= $id_anggaran ?>">
                                                    <div class="form-group">
                                                        <label for="editNomorAnggaran<?= $id_anggaran ?>">Nomor Anggaran:</label>
                                                        <input type="text" name="Edit_Nomor_Anggaran" value="<?= $nomor_anggaran ?>" class="form-control" id="editNomorAnggaran<?= $id_anggaran ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editIdPemesanan<?= $id_anggaran ?>">Pemesanan:</label>
                                                        <select name="Edit_Id_Pemesanan" class="form-control" id="editIdPemesanan<?= $id_anggaran ?>" required>
                                                            <?php
                                                            // Reset pointer untuk pemesanan_options
                                                            mysqli_data_seek($pemesanan_options, 0); 
                                                            while ($pm_option = mysqli_fetch_array($pemesanan_options)) {
                                                                $selected = ($pm_option['id_pemesanan'] == $current_id_pemesanan) ? 'selected' : '';
                                                                echo '<option value="' . $pm_option['id_pemesanan'] . '" ' . $selected . '>ID Pemesanan: ' . $pm_option['id_pemesanan'] . ' (Tanggal: ' . $pm_option['tanggal_pemesanan'] . ')</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editTotal<?= $id_anggaran ?>">Total Anggaran:</label>
                                                        <input type="number" name="Edit_Total" value="<?= $data['total'] ?>" class="form-control" id="editTotal<?= $id_anggaran ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditAnggaran" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            echo '<tr><td colspan="6" class="text-center">Belum ada data anggaran.</td></tr>';
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

<div class="modal fade" id="addAnggaranModal" tabindex="-1" role="dialog" aria-labelledby="addAnggaranModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAnggaranModalLabel">Tambah Anggaran Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nomorAnggaran">Nomor Anggaran:</label>
                        <input type="text" name="nomor_anggaran" placeholder="Nomor Anggaran" class="form-control" id="nomorAnggaran" required>
                    </div>
                    <div class="form-group">
                        <label for="idPemesanan">Pemesanan:</label>
                        <select name="id_pemesanan" class="form-control" id="idPemesanan" required>
                            <option value="">-- Pilih Pemesanan --</option>
                            <?php
                            mysqli_data_seek($pemesanan_options, 0); // Reset pointer lagi untuk modal tambah
                            while ($pm_option = mysqli_fetch_array($pemesanan_options)) {
                                echo '<option value="' . $pm_option['id_pemesanan'] . '">ID Pemesanan: ' . $pm_option['id_pemesanan'] . ' (Tanggal: ' . $pm_option['tanggal_pemesanan'] . ')</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="totalAnggaran">Total Anggaran:</label>
                        <input type="number" name="total" placeholder="Total Anggaran" class="form-control" id="totalAnggaran" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="addAnggaran">Tambah</button>
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
                // Kolom yang relevan untuk pencarian:
                // 1: Nomor Anggaran
                // 2: Nama Barang
                // 3: Jumlah
                // 4: Total Anggaran (tanpa "Rp. ")
                for (j = 1; j <= 4; j++) { 
                    td = tr[i].cells[j];
                    if (td) {
                        txtValue = td.textContent || td.innerText;
                        // Khusus untuk kolom Total Anggaran, hapus "Rp. " agar pencarian angka tetap berfungsi
                        if (j === 4) {
                            txtValue = txtValue.replace('Rp. ', '');
                        }
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
    // Anda perlu membuat file baru: anggaran_suggestions.php
    let debounceTimer;
    document.getElementById("searchInput").addEventListener("input", function() {
        clearTimeout(debounceTimer);
        const inputVal = this.value;

        debounceTimer = setTimeout(() => {
            if (inputVal.length >= 2) {
                // Buat file 'anggaran_suggestions.php' yang serupa dengan 'barang_suggestions.php'
                // Namun, query-nya harus mengambil saran dari tabel anggaran, pemesanan, pengajuan
                fetch('anggaran_suggestions.php?query=' + encodeURIComponent(inputVal))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        var datalist = document.getElementById('anggaranSuggestions');
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
                document.getElementById('anggaranSuggestions').innerHTML = '';
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