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
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_peminjaman) AS total FROM peminjaman");
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

// Tangani operasi Tambah Peminjaman
if (isset($_POST['addPeminjaman'])) {
    $id_barang = htmlspecialchars($_POST['id_barang']);
    $id_pegawai = htmlspecialchars($_POST['id_pegawai']);
    // $kondisi = htmlspecialchars($_POST['kondisi']); // Dihapus: kolom kondisi
    $jumlah_pinjam = htmlspecialchars($_POST['jumlah_pinjam']); // Ditambahkan: input manual jumlah
    $tanggal_peminjaman = htmlspecialchars($_POST['tanggal_peminjaman']);
    $tanggal_pengembalian = !empty($_POST['tanggal_pengembalian']) ? htmlspecialchars($_POST['tanggal_pengembalian']) : NULL; // Bisa null

    // Perhatikan: query_insert dan bind_param TIDAK LAGI menyertakan 'kondisi'
    $query_insert = "INSERT INTO peminjaman (id_barang, id_pegawai, jumlah_pinjam, tanggal_peminjaman, tanggal_pengembalian) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = mysqli_prepare($conn, $query_insert);

    if ($stmt_insert) {
        // iisss: int (id_barang), int (id_pegawai), int (jumlah_pinjam), string (tanggal_peminjaman), string (tanggal_pengembalian)
        mysqli_stmt_bind_param($stmt_insert, "iisss", $id_barang, $id_pegawai, $jumlah_pinjam, $tanggal_peminjaman, $tanggal_pengembalian); 
        $result_insert = mysqli_stmt_execute($stmt_insert);

        if ($result_insert) {
            echo '<script>alert("Data Peminjaman Berhasil Ditambahkan!"); window.location.href="peminjaman.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menambahkan Data Peminjaman: ' . mysqli_error($conn) . '. Pastikan kolom \'jumlah_pinjam\' ada di tabel peminjaman Anda dan \'kondisi\' sudah dihapus."); window.location.href="peminjaman.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt_insert);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Insert: ' . mysqli_error($conn) . '"); window.location.href="peminjaman.php?page=' . $page . '";</script>';
    }
    exit();
}

// Tangani operasi Edit Peminjaman
if (isset($_POST['SimpanEditPeminjaman'])) {
    $id_peminjaman = htmlspecialchars($_POST['Edit_Id_Peminjaman']);
    $id_barang_baru = htmlspecialchars($_POST['Edit_Id_Barang']);
    $id_pegawai_baru = htmlspecialchars($_POST['Edit_Id_Pegawai']);
    // $kondisi_baru = htmlspecialchars($_POST['Edit_Kondisi']); // Dihapus: kolom kondisi
    $jumlah_pinjam_baru = htmlspecialchars($_POST['Edit_Jumlah_Pinjam']); // Ditambahkan: input manual jumlah
    $tanggal_peminjaman_baru = htmlspecialchars($_POST['Edit_Tanggal_Peminjaman']);
    $tanggal_pengembalian_baru = !empty($_POST['Edit_Tanggal_Pengembalian']) ? htmlspecialchars($_POST['Edit_Tanggal_Pengembalian']) : NULL;
    
    // Perhatikan: query_update dan bind_param TIDAK LAGI menyertakan 'kondisi'
    $query_update = "UPDATE peminjaman SET id_barang=?, id_pegawai=?, jumlah_pinjam=?, tanggal_peminjaman=?, tanggal_pengembalian=? WHERE id_peminjaman=?";
    $stmt_update = mysqli_prepare($conn, $query_update);

    if ($stmt_update) {
        // iisssi: int (id_barang), int (id_pegawai), int (jumlah_pinjam), string (tanggal_peminjaman), string (tanggal_pengembalian), int (id_peminjaman)
        mysqli_stmt_bind_param($stmt_update, "iisssi", $id_barang_baru, $id_pegawai_baru, $jumlah_pinjam_baru, $tanggal_peminjaman_baru, $tanggal_pengembalian_baru, $id_peminjaman); 
        $result_update = mysqli_stmt_execute($stmt_update);

        if ($result_update) {
            echo '<script>alert("Data Peminjaman Berhasil Diperbarui!"); window.location.href="peminjaman.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Memperbarui Data Peminjaman: ' . mysqli_error($conn) . '. Pastikan kolom \'jumlah_pinjam\' ada di tabel peminjaman Anda dan \'kondisi\' sudah dihapus."); window.location.href="peminjaman.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt_update);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Update: ' . mysqli_error($conn) . '"); window.location.href="peminjaman.php?page=' . $page . '";</script>';
    }
    exit();
}

// Tangani operasi Hapus Peminjaman
if (isset($_GET['hapus'])) {
    $id_peminjaman_to_delete = htmlspecialchars($_GET['hapus']);

    $query_delete = "DELETE FROM peminjaman WHERE id_peminjaman=?";
    $stmt_delete = mysqli_prepare($conn, $query_delete);

    if ($stmt_delete) {
        mysqli_stmt_bind_param($stmt_delete, "i", $id_peminjaman_to_delete);
        $result_delete = mysqli_stmt_execute($stmt_delete);

        if ($result_delete) {
            echo '<script>alert("Data Peminjaman Berhasil Dihapus!"); window.location.href="peminjaman.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menghapus Data Peminjaman: ' . mysqli_error($conn) . '"); window.location.href="peminjaman.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt_delete);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Delete: ' . mysqli_error($conn) . '"); window.location.href="peminjaman.php?page=' . $page . '";</script>';
    }
    exit();
}

// Ambil semua data peminjaman untuk halaman saat ini dengan JOIN ke tabel barang dan pegawai
// Mengambil jumlah_pinjam dari tabel peminjaman (setelah kolom ditambahkan)
// TIDAK LAGI MENGAMBIL b.jumlah atau p.kondisi
$ambilsemuadatanya = mysqli_query($conn, "
    SELECT 
        p.id_peminjaman,
        p.id_barang, 
        p.id_pegawai,
        p.jumlah_pinjam,         -- Mengambil jumlah_pinjam dari tabel peminjaman
        p.tanggal_peminjaman,
        p.tanggal_pengembalian,
        b.namabarang,
        b.merk,
        pg.nama AS nama_pegawai
    FROM 
        peminjaman p
    LEFT JOIN 
        barang b ON p.id_barang = b.id_barang
    LEFT JOIN 
        pegawai pg ON p.id_pegawai = pg.id_pegawai
    ORDER BY 
        p.id_peminjaman DESC 
    LIMIT $start, $limit
");

// Ambil data barang dan pegawai untuk dropdown di modal
$barang_options = mysqli_query($conn, "SELECT id_barang, namabarang, merk, jumlah FROM barang ORDER BY namabarang ASC");
$pegawai_options = mysqli_query($conn, "SELECT id_pegawai, nama FROM pegawai ORDER BY nama ASC");

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Peminjaman Barang</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPeminjamanModal">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari peminjaman..." list="peminjamanSuggestions">
                    <datalist id="peminjamanSuggestions"></datalist>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="myTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Merk</th>
                            <th>Jumlah Dipinjam</th> <th>Peminjam</th>
                            <th>Tanggal Peminjaman</th>
                            <th>Tanggal Pengembalian</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $id_peminjaman = $data['id_peminjaman']; 
                                $id_barang = $data['id_barang']; 
                                $namabarang = $data['namabarang'];     // Dari JOIN
                                $merk = $data['merk'];                 // Dari JOIN
                                // $jumlah = $data['jumlah'];             // Dihapus
                                $jumlah_pinjam = $data['jumlah_pinjam']; // Menggunakan kolom baru
                                $id_pegawai = $data['id_pegawai']; 
                                $nama_pegawai = $data['nama_pegawai']; // Dari JOIN
                                // $kondisi = $data['kondisi'];         // Dihapus
                                $tanggal_peminjaman = $data['tanggal_peminjaman'];
                                $tanggal_pengembalian = $data['tanggal_pengembalian'];
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= htmlspecialchars($namabarang ? $namabarang : 'N/A') ?></td>
                                    <td><?= htmlspecialchars($merk ? $merk : 'N/A') ?></td>
                                    <td><?= htmlspecialchars($jumlah_pinjam ? $jumlah_pinjam : 'N/A') ?></td> <td><?= htmlspecialchars($nama_pegawai ? $nama_pegawai : 'N/A') ?></td>
                                    <td><?= htmlspecialchars($tanggal_peminjaman) ?></td>
                                    <td><?= htmlspecialchars($tanggal_pengembalian ? $tanggal_pengembalian : 'Belum Dikembalikan') ?></td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#editPeminjamanModal<?= $id_peminjaman ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $id_peminjaman ?>&page=<?= $page ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data peminjaman ini?')" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editPeminjamanModal<?= $id_peminjaman ?>" tabindex="-1" role="dialog" aria-labelledby="editPeminjamanModalLabel<?= $id_peminjaman ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editPeminjamanModalLabel<?= $id_peminjaman ?>">Edit Peminjaman Barang</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="Edit_Id_Peminjaman" value="<?= $id_peminjaman ?>">
                                                    <div class="form-group">
                                                        <label for="editIdBarang<?= $id_peminjaman ?>">Nama Barang:</label>
                                                        <select name="Edit_Id_Barang" class="form-control" id="editIdBarang<?= $id_peminjaman ?>" required>
                                                            <option value="">-- Pilih Barang --</option>
                                                            <?php
                                                            mysqli_data_seek($barang_options, 0); // Reset pointer
                                                            while ($brg = mysqli_fetch_assoc($barang_options)) {
                                                                $selected = ($brg['id_barang'] == $id_barang) ? 'selected' : '';
                                                                echo '<option value="' . $brg['id_barang'] . '" ' . $selected . '>' . htmlspecialchars($brg['namabarang']) . ' (Merk: ' . htmlspecialchars($brg['merk']) . ', Total Stok: ' . htmlspecialchars($brg['jumlah']) . ')</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editIdPegawai<?= $id_peminjaman ?>">Peminjam (Pegawai):</label>
                                                        <select name="Edit_Id_Pegawai" class="form-control" id="editIdPegawai<?= $id_peminjaman ?>" required>
                                                            <option value="">-- Pilih Pegawai --</option>
                                                            <?php
                                                            mysqli_data_seek($pegawai_options, 0); // Reset pointer
                                                            while ($pgw = mysqli_fetch_assoc($pegawai_options)) {
                                                                $selected = ($pgw['id_pegawai'] == $id_pegawai) ? 'selected' : '';
                                                                echo '<option value="' . $pgw['id_pegawai'] . '" ' . $selected . '>' . htmlspecialchars($pgw['nama']) . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editJumlahPinjam<?= $id_peminjaman ?>">Jumlah Dipinjam:</label>
                                                        <input type="number" name="Edit_Jumlah_Pinjam" value="<?= htmlspecialchars($jumlah_pinjam) ?>" class="form-control" id="editJumlahPinjam<?= $id_peminjaman ?>" min="1" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editTanggalPeminjaman<?= $id_peminjaman ?>">Tanggal Peminjaman:</label>
                                                        <input type="date" name="Edit_Tanggal_Peminjaman" value="<?= htmlspecialchars($tanggal_peminjaman) ?>" class="form-control" id="editTanggalPeminjaman<?= $id_peminjaman ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editTanggalPengembalian<?= $id_peminjaman ?>">Tanggal Pengembalian (Opsional):</label>
                                                        <input type="date" name="Edit_Tanggal_Pengembalian" value="<?= htmlspecialchars($tanggal_pengembalian) ?>" class="form-control" id="editTanggalPengembalian<?= $id_peminjaman ?>">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditPeminjaman" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            // Sesuaikan colspan karena sekarang ada 7 kolom yang ditampilkan (No, Nama Barang, Merk, Jumlah Dipinjam, Peminjam, Tgl Peminjaman, Tgl Pengembalian, Opsi)
                            echo '<tr><td colspan="7" class="text-center">Belum ada data peminjaman barang.</td></tr>'; 
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

<div class="modal fade" id="addPeminjamanModal" tabindex="-1" role="dialog" aria-labelledby="addPeminjamanModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPeminjamanModalLabel">Tambah Data Peminjaman Barang Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="peminjaman.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="id_barang">Nama Barang:</label>
                        <select name="id_barang" class="form-control" id="id_barang" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php
                            mysqli_data_seek($barang_options, 0); // Reset pointer
                            while ($brg = mysqli_fetch_assoc($barang_options)) {
                                echo '<option value="' . $brg['id_barang'] . '">' . htmlspecialchars($brg['namabarang']) . ' (Merk: ' . htmlspecialchars($brg['merk']) . ', Total Stok: ' . htmlspecialchars($brg['jumlah']) . ')</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_pegawai">Peminjam (Pegawai):</label>
                        <select name="id_pegawai" class="form-control" id="id_pegawai" required>
                            <option value="">-- Pilih Pegawai --</option>
                            <?php
                            mysqli_data_seek($pegawai_options, 0); // Reset pointer
                            while ($pgw = mysqli_fetch_assoc($pegawai_options)) {
                                echo '<option value="' . $pgw['id_pegawai'] . '">' . htmlspecialchars($pgw['nama']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="jumlah_pinjam">Jumlah Dipinjam:</label>
                        <input type="number" name="jumlah_pinjam" class="form-control" min="1" placeholder="Masukkan jumlah barang yang dipinjam" required>
                    </div>
                    <div class="form-group">
                        <label for="tanggal_peminjaman">Tanggal Peminjaman:</label>
                        <input type="date" name="tanggal_peminjaman" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="tanggal_pengembalian">Tanggal Pengembalian (Opsional):</label>
                        <input type="date" name="tanggal_pengembalian" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="addPeminjaman">Tambah</button>
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
                // Nama Barang (1), Merk (2), Jumlah Dipinjam (3), Peminjam (4), Tanggal Peminjaman (5), Tanggal Pengembalian (6)
                // Indeks 5 (Kondisi) telah dihapus
                const searchColumns = [1, 2, 3, 4, 5, 6]; 
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
                // Anda mungkin perlu membuat file 'peminjaman_suggestions.php' untuk ini.
                fetch('peminjaman_suggestions.php?query=' + encodeURIComponent(inputVal))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        var datalist = document.getElementById('peminjamanSuggestions');
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
                document.getElementById('peminjamanSuggestions').innerHTML = '';
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