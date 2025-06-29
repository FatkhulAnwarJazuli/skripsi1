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
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_penerimaan) AS total FROM penerimaan");
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

// Tangani operasi Tambah Penerimaan
if (isset($_POST['addPenerimaan'])) {
    $id_pemesanan = htmlspecialchars($_POST['id_pemesanan']);
    $tanggal_terima = htmlspecialchars($_POST['tanggal_terima']);
    $id_pegawai = htmlspecialchars($_POST['id_pegawai']);
    $kondisi_barang = htmlspecialchars($_POST['kondisi_barang']);
    $id_pengajuan = null; // Inisialisasi null, akan diisi jika id_pemesanan ada

    // Jika id_pemesanan ada, ambil id_pengajuan dari tabel pemesanan
    if (!empty($id_pemesanan)) {
        $query_get_pengajuan_id = "SELECT id_pengajuan FROM pemesanan WHERE id_pemesanan = ?";
        $stmt_get_pengajuan_id = mysqli_prepare($conn, $query_get_pengajuan_id);
        if ($stmt_get_pengajuan_id) {
            mysqli_stmt_bind_param($stmt_get_pengajuan_id, "i", $id_pemesanan);
            mysqli_stmt_execute($stmt_get_pengajuan_id);
            mysqli_stmt_bind_result($stmt_get_pengajuan_id, $fetched_id_pengajuan);
            mysqli_stmt_fetch($stmt_get_pengajuan_id);
            $id_pengajuan = $fetched_id_pengajuan;
            mysqli_stmt_close($stmt_get_pengajuan_id);
        }
    }

    $query_insert = "INSERT INTO penerimaan (id_pemesanan, tanggal_terima, kondisi_barang, id_pegawai, id_pengajuan) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = mysqli_prepare($conn, $query_insert);

    if ($stmt_insert) {
        // Tipe parameter disesuaikan: "issii" (integer, string, string, integer, integer)
        mysqli_stmt_bind_param($stmt_insert, "issii", $id_pemesanan, $tanggal_terima, $kondisi_barang, $id_pegawai, $id_pengajuan);
        $result_insert = mysqli_stmt_execute($stmt_insert);

        if ($result_insert) {
            echo '<script>alert("Data Penerimaan Berhasil Ditambahkan!"); window.location.href="penerimaan.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menambahkan Data Penerimaan: ' . mysqli_error($conn) . '"); window.location.href="penerimaan.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt_insert);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Insert: ' . mysqli_error($conn) . '"); window.location.href="penerimaan.php?page=' . $page . '";</script>';
    }
    exit();
}

// Tangani operasi Edit Penerimaan
if (isset($_POST['SimpanEditPenerimaan'])) {
    $id_penerimaan = htmlspecialchars($_POST['Edit_Id_Penerimaan']);
    $id_pemesanan_baru = htmlspecialchars($_POST['Edit_Id_Pemesanan']);
    $tanggal_terima_baru = htmlspecialchars($_POST['Edit_Tanggal_Terima']);
    $id_pegawai_baru = htmlspecialchars($_POST['Edit_Id_Pegawai']);
    $kondisi_barang_baru = htmlspecialchars($_POST['Edit_Kondisi_Barang']);
    $id_pengajuan_baru = null; // Inisialisasi null, akan diisi jika id_pemesanan_baru ada

    // Jika id_pemesanan_baru ada, ambil id_pengajuan dari tabel pemesanan
    if (!empty($id_pemesanan_baru)) {
        $query_get_pengajuan_id = "SELECT id_pengajuan FROM pemesanan WHERE id_pemesanan = ?";
        $stmt_get_pengajuan_id = mysqli_prepare($conn, $query_get_pengajuan_id);
        if ($stmt_get_pengajuan_id) {
            mysqli_stmt_bind_param($stmt_get_pengajuan_id, "i", $id_pemesanan_baru);
            mysqli_stmt_execute($stmt_get_pengajuan_id);
            mysqli_stmt_bind_result($stmt_get_pengajuan_id, $fetched_id_pengajuan);
            mysqli_stmt_fetch($stmt_get_pengajuan_id);
            $id_pengajuan_baru = $fetched_id_pengajuan;
            mysqli_stmt_close($stmt_get_pengajuan_id);
        }
    }
    
    $query_update = "UPDATE penerimaan SET id_pemesanan=?, tanggal_terima=?, kondisi_barang=?, id_pegawai=?, id_pengajuan=? WHERE id_penerimaan=?";
    $stmt_update = mysqli_prepare($conn, $query_update);

    if ($stmt_update) {
        // Tipe parameter disesuaikan: "issiii" (integer, string, string, integer, integer, integer)
        mysqli_stmt_bind_param($stmt_update, "issiii", $id_pemesanan_baru, $tanggal_terima_baru, $kondisi_barang_baru, $id_pegawai_baru, $id_pengajuan_baru, $id_penerimaan);
        $result_update = mysqli_stmt_execute($stmt_update);

        if ($result_update) {
            echo '<script>alert("Data Penerimaan Berhasil Diperbarui!"); window.location.href="penerimaan.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Memperbarui Data Penerimaan: ' . mysqli_error($conn) . '"); window.location.href="penerimaan.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt_update);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Update: ' . mysqli_error($conn) . '"); window.location.href="penerimaan.php?page=' . $page . '";</script>';
    }
    exit();
}

// Tangani operasi Hapus Penerimaan
if (isset($_GET['hapus'])) {
    $id_penerimaan_to_delete = htmlspecialchars($_GET['hapus']);

    $query_delete = "DELETE FROM penerimaan WHERE id_penerimaan=?";
    $stmt_delete = mysqli_prepare($conn, $query_delete);

    if ($stmt_delete) {
        mysqli_stmt_bind_param($stmt_delete, "i", $id_penerimaan_to_delete);
        $result_delete = mysqli_stmt_execute($stmt_delete);

        if ($result_delete) {
            echo '<script>alert("Data Penerimaan Berhasil Dihapus!"); window.location.href="penerimaan.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menghapus Data Penerimaan: ' . mysqli_error($conn) . '"); window.location.href="penerimaan.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt_delete);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Delete: ' . mysqli_error($conn) . '"); window.location.href="penerimaan.php?page=' . $page . '";</script>';
    }
    exit();
}

// Ambil semua data penerimaan untuk halaman saat ini dengan JOIN ke tabel pemesanan, pengajuan dan pegawai
$ambilsemuadatanya = mysqli_query($conn, "
    SELECT 
        p.*, 
        pm.tanggal_pemesanan, 
        pm.status AS status_pemesanan,
        peg.nama AS nama_pegawai,
        pj.nama_barang
    FROM 
        penerimaan p
    LEFT JOIN 
        pemesanan pm ON p.id_pemesanan = pm.id_pemesanan
    LEFT JOIN
        pegawai peg ON p.id_pegawai = peg.id_pegawai
    LEFT JOIN
        pengajuan pj ON p.id_pengajuan = pj.id_pengajuan
    ORDER BY 
        p.id_penerimaan DESC 
    LIMIT $start, $limit
");

// Ambil data pemesanan dan pegawai untuk dropdown di modal
$pemesanan_options = mysqli_query($conn, "SELECT id_pemesanan, tanggal_pemesanan, id_pengajuan FROM pemesanan ORDER BY tanggal_pemesanan DESC");
$pegawai_options = mysqli_query($conn, "SELECT id_pegawai, nama FROM pegawai ORDER BY nama ASC");

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Penerimaan Barang</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPenerimaanModal">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari penerimaan..." list="penerimaanSuggestions">
                    <datalist id="penerimaanSuggestions"></datalist>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="myTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Pemesanan</th>
                            <th>Nama Barang</th> <th>Tanggal Terima</th>
                            <th>Kondisi Barang</th>
                            <th>Pegawai Penerima</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $id_penerimaan = $data['id_penerimaan']; 
                                $id_pemesanan = $data['id_pemesanan']; 
                                $tanggal_pemesanan = $data['tanggal_pemesanan']; 
                                $nama_barang = $data['nama_barang']; // Ambil nama_barang
                                $tanggal_terima = $data['tanggal_terima'];
                                $kondisi_barang = $data['kondisi_barang'];
                                $id_pegawai = $data['id_pegawai'];
                                $nama_pegawai = $data['nama_pegawai']; // Dari JOIN
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= htmlspecialchars($tanggal_pemesanan ? $tanggal_pemesanan : 'N/A') ?></td>
                                    <td><?= htmlspecialchars($nama_barang ? $nama_barang : 'N/A') ?></td> <td><?= htmlspecialchars($tanggal_terima) ?></td>
                                    <td><?= htmlspecialchars($kondisi_barang) ?></td>
                                    <td><?= htmlspecialchars($nama_pegawai ? $nama_pegawai : 'N/A') ?></td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#editPenerimaanModal<?= $id_penerimaan ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $id_penerimaan ?>&page=<?= $page ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data penerimaan ini?')" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editPenerimaanModal<?= $id_penerimaan ?>" tabindex="-1" role="dialog" aria-labelledby="editPenerimaanModalLabel<?= $id_penerimaan ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editPenerimaanModalLabel<?= $id_penerimaan ?>">Edit Penerimaan Barang</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="Edit_Id_Penerimaan" value="<?= $id_penerimaan ?>">
                                                    <div class="form-group">
                                                        <label for="editIdPemesanan<?= $id_penerimaan ?>">Pemesanan:</label>
                                                        <select name="Edit_Id_Pemesanan" class="form-control" id="editIdPemesanan<?= $id_penerimaan ?>">
                                                            <option value="">-- Pilih Pemesanan (Opsional) --</option>
                                                            <?php
                                                            mysqli_data_seek($pemesanan_options, 0); // Reset pointer
                                                            while ($pem = mysqli_fetch_assoc($pemesanan_options)) {
                                                                $selected = ($pem['id_pemesanan'] == $id_pemesanan) ? 'selected' : '';
                                                                // Kita perlu mendapatkan nama barang dari pengajuan yang terkait dengan pemesanan ini
                                                                $nama_barang_pemesanan = 'N/A';
                                                                if (!empty($pem['id_pengajuan'])) {
                                                                    $query_get_nama_barang = "SELECT nama_barang FROM pengajuan WHERE id_pengajuan = " . $pem['id_pengajuan'];
                                                                    $result_get_nama_barang = mysqli_query($conn, $query_get_nama_barang);
                                                                    if ($result_get_nama_barang && mysqli_num_rows($result_get_nama_barang) > 0) {
                                                                        $row_nama_barang = mysqli_fetch_assoc($result_get_nama_barang);
                                                                        $nama_barang_pemesanan = $row_nama_barang['nama_barang'];
                                                                    }
                                                                }
                                                                echo '<option value="' . $pem['id_pemesanan'] . '" ' . $selected . '>ID: ' . htmlspecialchars($pem['id_pemesanan']) . ' (Tgl: ' . htmlspecialchars($pem['tanggal_pemesanan']) . ') - Barang: ' . htmlspecialchars($nama_barang_pemesanan) . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editTanggalTerima<?= $id_penerimaan ?>">Tanggal Terima:</label>
                                                        <input type="date" name="Edit_Tanggal_Terima" value="<?= htmlspecialchars($tanggal_terima) ?>" class="form-control" id="editTanggalTerima<?= $id_penerimaan ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editIdPegawai<?= $id_penerimaan ?>">Pegawai Penerima:</label>
                                                        <select name="Edit_Id_Pegawai" class="form-control" id="editIdPegawai<?= $id_penerimaan ?>" required>
                                                            <option value="">-- Pilih Pegawai --</option>
                                                            <?php
                                                            mysqli_data_seek($pegawai_options, 0); // Reset pointer
                                                            while ($peg = mysqli_fetch_assoc($pegawai_options)) {
                                                                $selected = ($peg['id_pegawai'] == $id_pegawai) ? 'selected' : '';
                                                                echo '<option value="' . $peg['id_pegawai'] . '" ' . $selected . '>' . htmlspecialchars($peg['nama']) . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editKondisiBarang<?= $id_penerimaan ?>">Kondisi Barang:</label>
                                                        <textarea name="Edit_Kondisi_Barang" class="form-control" id="editKondisiBarang<?= $id_penerimaan ?>"><?= htmlspecialchars($kondisi_barang) ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditPenerimaan" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            // Sesuaikan colspan karena sekarang ada 6 kolom yang ditampilkan (No, Tgl Pemesanan, Nama Barang, Tgl Terima, Kondisi, Pegawai Penerima, Opsi)
                            echo '<tr><td colspan="6" class="text-center">Belum ada data penerimaan barang.</td></tr>'; 
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

<div class="modal fade" id="addPenerimaanModal" tabindex="-1" role="dialog" aria-labelledby="addPenerimaanModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPenerimaanModalLabel">Tambah Penerimaan Barang Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="penerimaan.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="id_pemesanan">Pemesanan:</label>
                        <select name="id_pemesanan" class="form-control" id="id_pemesanan">
                            <option value="">-- Pilih Pemesanan (Opsional) --</option>
                            <?php
                            mysqli_data_seek($pemesanan_options, 0); // Reset pointer
                            while ($pem = mysqli_fetch_assoc($pemesanan_options)) {
                                // Kita perlu mendapatkan nama barang dari pengajuan yang terkait dengan pemesanan ini
                                $nama_barang_pemesanan = 'N/A';
                                if (!empty($pem['id_pengajuan'])) {
                                    $query_get_nama_barang = "SELECT nama_barang FROM pengajuan WHERE id_pengajuan = " . $pem['id_pengajuan'];
                                    $result_get_nama_barang = mysqli_query($conn, $query_get_nama_barang);
                                    if ($result_get_nama_barang && mysqli_num_rows($result_get_nama_barang) > 0) {
                                        $row_nama_barang = mysqli_fetch_assoc($result_get_nama_barang);
                                        $nama_barang_pemesanan = $row_nama_barang['nama_barang'];
                                    }
                                }
                                echo '<option value="' . $pem['id_pemesanan'] . '">ID: ' . htmlspecialchars($pem['id_pemesanan']) . ' (Tgl: ' . htmlspecialchars($pem['tanggal_pemesanan']) . ') - Barang: ' . htmlspecialchars($nama_barang_pemesanan) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tanggal_terima">Tanggal Terima:</label>
                        <input type="date" name="tanggal_terima" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="id_pegawai">Pegawai Penerima:</label>
                        <select name="id_pegawai" class="form-control" id="id_pegawai" required>
                            <option value="">-- Pilih Pegawai --</option>
                            <?php
                            mysqli_data_seek($pegawai_options, 0); // Reset pointer
                            while ($peg = mysqli_fetch_assoc($pegawai_options)) {
                                echo '<option value="' . $peg['id_pegawai'] . '">' . htmlspecialchars($peg['nama']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="kondisi_barang">Kondisi Barang:</label>
                        <textarea name="kondisi_barang" class="form-control" placeholder="Kondisi barang yang diterima (misal: Baik, Rusak, Kurang)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="addPenerimaan">Tambah</button>
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
                // Tanggal Pemesanan (1), Nama Barang (2), Tanggal Terima (3), Kondisi Barang (4), Pegawai Penerima (5)
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
                // Pastikan Anda memiliki file 'penerimaan_suggestions.php' untuk ini.
                // Anda mungkin perlu memperbarui penerimaan_suggestions.php juga
                // agar menyertakan nama barang dalam suggestions.
                fetch('penerimaan_suggestions.php?query=' + encodeURIComponent(inputVal))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        var datalist = document.getElementById('penerimaanSuggestions');
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
                document.getElementById('penerimaanSuggestions').innerHTML = '';
            }
        }, 300);
    });

    document.addEventListener('DOMContentLoaded', function() {
        filterTable();
        
        if (typeof updatePemesananCount === 'function') {
            updatePemesananCount();
        }
    });

    document.getElementById("searchInput").addEventListener("change", filterTable);
</script>

<?php
// Include footer.php di akhir. Ini akan menutup semua tag HTML yang dibuka di sidebar.php
// dan menyertakan skrip JavaScript global.
include 'footer.php';
?>