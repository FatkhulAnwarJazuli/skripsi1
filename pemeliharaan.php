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
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_pemeliharaan) AS total FROM pemeliharaan_barang");
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

// Tangani operasi Tambah Pemeliharaan
if (isset($_POST['addPemeliharaan'])) {
    $id_barang = !empty($_POST['id_barang']) ? htmlspecialchars($_POST['id_barang']) : null;
    $id_rusak = !empty($_POST['id_rusak']) ? htmlspecialchars($_POST['id_rusak']) : null;
    $keterangan = htmlspecialchars($_POST['keterangan']);
    $tanggal = htmlspecialchars($_POST['tanggal']);

    // Validasi minimal salah satu id_barang atau id_rusak harus diisi
    if (empty($id_barang) && empty($id_rusak)) {
        echo '<script>alert("Anda harus memilih Barang (Normal) atau Barang Rusak!"); window.location.href="pemeliharaan.php?page=' . $page . '";</script>';
        exit();
    }

    $query = "INSERT INTO pemeliharaan_barang (id_barang, id_rusak, keterangan, tanggal) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        // Bind parameter, perhatikan tipe data 'i' untuk integer, 's' untuk string. Null perlu ditangani.
        // Jika id_barang atau id_rusak bisa null, ini sedikit lebih kompleks dengan mysqli_stmt_bind_param
        // Contoh ini mengasumsikan int atau null, dan MySQL akan mengkonversi NULL string ke NULL.
        // Namun, jika kolom NULLable, lebih baik gunakan 's' dan kirimkan '' jika null, atau tangani khusus.
        // Untuk kasus ini, karena kolomnya INT, kita bisa kirim null jika PHP NULL
        $barang_param = $id_barang === null ? null : (int)$id_barang;
        $rusak_param = $id_rusak === null ? null : (int)$id_rusak;

        mysqli_stmt_bind_param($stmt, "iiss", $barang_param, $rusak_param, $keterangan, $tanggal);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            // Update status di barang_rusak jika id_rusak tidak null
            if ($id_rusak !== null) {
                $query_update_rusak = "UPDATE barang_rusak SET status = 'Selesai Pemeliharaan' WHERE id_rusak = ?";
                $stmt_update_rusak = mysqli_prepare($conn, $query_update_rusak);
                if ($stmt_update_rusak) {
                    mysqli_stmt_bind_param($stmt_update_rusak, "i", $rusak_param);
                    mysqli_stmt_execute($stmt_update_rusak);
                    mysqli_stmt_close($stmt_update_rusak);
                }
            }
            echo '<script>alert("Data Pemeliharaan Berhasil Ditambahkan!"); window.location.href="pemeliharaan.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menambahkan Data Pemeliharaan: ' . mysqli_error($conn) . '"); window.location.href="pemeliharaan.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Tambah: ' . mysqli_error($conn) . '"); window.location.href="pemeliharaan.php?page=' . $page . '";</script>';
    }
    exit(); 
}

// Tangani operasi Edit Pemeliharaan
if (isset($_POST['SimpanEditPemeliharaan'])) {
    $id_pemeliharaan = htmlspecialchars($_POST['Edit_Id_Pemeliharaan']);
    $id_barang = !empty($_POST['Edit_Id_Barang']) ? htmlspecialchars($_POST['Edit_Id_Barang']) : null;
    $id_rusak = !empty($_POST['Edit_Id_Rusak']) ? htmlspecialchars($_POST['Edit_Id_Rusak']) : null;
    $keterangan = htmlspecialchars($_POST['Edit_Keterangan']);
    $tanggal = htmlspecialchars($_POST['Edit_Tanggal']);

    // Validasi minimal salah satu id_barang atau id_rusak harus diisi
    if (empty($id_barang) && empty($id_rusak)) {
        echo '<script>alert("Anda harus memilih Barang (Normal) atau Barang Rusak!"); window.location.href="pemeliharaan.php?page=' . $page . '";</script>';
        exit();
    }

    // Ambil id_rusak_lama untuk cek perubahan status
    $query_old_rusak = "SELECT id_rusak FROM pemeliharaan_barang WHERE id_pemeliharaan = ?";
    $stmt_old_rusak = mysqli_prepare($conn, $query_old_rusak);
    mysqli_stmt_bind_param($stmt_old_rusak, "i", $id_pemeliharaan);
    mysqli_stmt_execute($stmt_old_rusak);
    mysqli_stmt_bind_result($stmt_old_rusak, $old_id_rusak);
    mysqli_stmt_fetch($stmt_old_rusak);
    mysqli_stmt_close($stmt_old_rusak);

    $query = "UPDATE pemeliharaan_barang SET id_barang=?, id_rusak=?, keterangan=?, tanggal=? WHERE id_pemeliharaan=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        $barang_param = $id_barang === null ? null : (int)$id_barang;
        $rusak_param = $id_rusak === null ? null : (int)$id_rusak;

        mysqli_stmt_bind_param($stmt, "iissi", $barang_param, $rusak_param, $keterangan, $tanggal, $id_pemeliharaan);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            // Logika update status barang_rusak
            if ($old_id_rusak !== null && $old_id_rusak != $id_rusak) {
                // Jika id_rusak berubah atau dihapus, kembalikan status barang_rusak lama ke 'Rusak'
                $query_revert_status = "UPDATE barang_rusak SET status = 'Rusak' WHERE id_rusak = ?";
                $stmt_revert = mysqli_prepare($conn, $query_revert_status);
                if ($stmt_revert) {
                    mysqli_stmt_bind_param($stmt_revert, "i", $old_id_rusak);
                    mysqli_stmt_execute($stmt_revert);
                    mysqli_stmt_close($stmt_revert);
                }
            }
            if ($id_rusak !== null) {
                // Set status barang_rusak baru ke 'Selesai Pemeliharaan'
                $query_update_new_rusak = "UPDATE barang_rusak SET status = 'Selesai Pemeliharaan' WHERE id_rusak = ?";
                $stmt_update_new = mysqli_prepare($conn, $query_update_new_rusak);
                if ($stmt_update_new) {
                    mysqli_stmt_bind_param($stmt_update_new, "i", $rusak_param);
                    mysqli_stmt_execute($stmt_update_new);
                    mysqli_stmt_close($stmt_update_new);
                }
            }

            echo '<script>alert("Data Pemeliharaan Berhasil Diperbarui!"); window.location.href="pemeliharaan.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Memperbarui Data Pemeliharaan: ' . mysqli_error($conn) . '"); window.location.href="pemeliharaan.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Update: ' . mysqli_error($conn) . '"); window.location.href="pemeliharaan.php?page=' . $page . '";</script>';
    }
    exit(); 
}

// Tangani operasi Hapus Pemeliharaan
if (isset($_GET['hapus'])) {
    $id_pemeliharaan_to_delete = htmlspecialchars($_GET['hapus']);

    // Ambil id_rusak terkait sebelum menghapus pemeliharaan
    $query_get_rusak_id = "SELECT id_rusak FROM pemeliharaan_barang WHERE id_pemeliharaan = ?";
    $stmt_get_rusak_id = mysqli_prepare($conn, $query_get_rusak_id);
    mysqli_stmt_bind_param($stmt_get_rusak_id, "i", $id_pemeliharaan_to_delete);
    mysqli_stmt_execute($stmt_get_rusak_id);
    mysqli_stmt_bind_result($stmt_get_rusak_id, $rusak_id_to_revert);
    mysqli_stmt_fetch($stmt_get_rusak_id);
    mysqli_stmt_close($stmt_get_rusak_id);

    $query = "DELETE FROM pemeliharaan_barang WHERE id_pemeliharaan=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_pemeliharaan_to_delete);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            // Kembalikan status barang_rusak ke 'Rusak' jika ada id_rusak terkait
            if ($rusak_id_to_revert !== null) {
                $query_revert_status = "UPDATE barang_rusak SET status = 'Rusak' WHERE id_rusak = ?";
                $stmt_revert = mysqli_prepare($conn, $query_revert_status);
                if ($stmt_revert) {
                    mysqli_stmt_bind_param($stmt_revert, "i", $rusak_id_to_revert);
                    mysqli_stmt_execute($stmt_revert);
                    mysqli_stmt_close($stmt_revert);
                }
            }
            echo '<script>alert("Data Pemeliharaan Berhasil Dihapus!"); window.location.href="pemeliharaan.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menghapus Data Pemeliharaan: ' . mysqli_error($conn) . '"); window.location.href="pemeliharaan.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Delete: ' . mysqli_error($conn) . '"); window.location.href="pemeliharaan.php?page=' . $page . '";</script>';
    }
    exit(); 
}

// Ambil data untuk dropdown Barang (normal)
$barang_data = [];
$sql_barang = "SELECT id_barang, namabarang, merk FROM barang ORDER BY namabarang ASC";
$result_barang = mysqli_query($conn, $sql_barang);
if ($result_barang && mysqli_num_rows($result_barang) > 0) {
    while($row = mysqli_fetch_assoc($result_barang)) {
        $barang_data[] = $row;
    }
}

// Ambil data untuk dropdown Barang Rusak (hanya yang statusnya 'Rusak')
$barang_rusak_data = [];
$sql_barang_rusak = "SELECT br.id_rusak, b.namabarang, br.kondisi, br.jumlah 
                     FROM barang_rusak br 
                     JOIN barang b ON br.id_barang = b.id_barang
                     WHERE br.status = 'Rusak' ORDER BY b.namabarang ASC"; // Hanya yang statusnya 'Rusak'
$result_barang_rusak = mysqli_query($conn, $sql_barang_rusak);
if ($result_barang_rusak && mysqli_num_rows($result_barang_rusak) > 0) {
    while($row = mysqli_fetch_assoc($result_barang_rusak)) {
        $barang_rusak_data[] = $row;
    }
}

// Ambil semua data pemeliharaan untuk halaman saat ini dengan JOIN
$ambilsemuadatanya = mysqli_query($conn, "
    SELECT 
        pb.id_pemeliharaan, 
        pb.id_barang, 
        pb.id_rusak,
        b_normal.namabarang AS nama_barang_normal, 
        b_rusak.namabarang AS nama_barang_rusak_tipe,
        br.kondisi AS kondisi_barang_rusak,
        pb.keterangan, 
        pb.tanggal
    FROM pemeliharaan_barang pb
    LEFT JOIN barang b_normal ON pb.id_barang = b_normal.id_barang
    LEFT JOIN barang_rusak br ON pb.id_rusak = br.id_rusak
    LEFT JOIN barang b_rusak ON br.id_barang = b_rusak.id_barang
    ORDER BY pb.tanggal DESC 
    LIMIT $start, $limit
");

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Pemeliharaan Barang</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPemeliharaanModal">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari pemeliharaan..." list="pemeliharaanSuggestions">
                    <datalist id="pemeliharaanSuggestions"></datalist>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="myTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Barang Normal</th>
                            <th>Barang Rusak (Kondisi)</th>
                            <th>Keterangan</th>
                            <th>Tanggal</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $id_pemeliharaan = $data['id_pemeliharaan'];
                                $id_barang_normal = $data['id_barang'];
                                $id_barang_rusak = $data['id_rusak'];
                                $nama_barang_normal = $data['nama_barang_normal'];
                                $nama_barang_rusak_tipe = $data['nama_barang_rusak_tipe'];
                                $kondisi_barang_rusak = $data['kondisi_barang_rusak'];
                                $keterangan = $data['keterangan'];
                                $tanggal = $data['tanggal'];
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= htmlspecialchars($nama_barang_normal ?? '-') ?></td>
                                    <td>
                                        <?php 
                                            echo htmlspecialchars($nama_barang_rusak_tipe ? $nama_barang_rusak_tipe . ' (' . $kondisi_barang_rusak . ')' : '-'); 
                                        ?>
                                    </td>
                                    <td><?= htmlspecialchars($keterangan) ?></td>
                                    <td><?= htmlspecialchars($tanggal) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#editPemeliharaanModal<?= $id_pemeliharaan ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $id_pemeliharaan ?>&page=<?= $page ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data pemeliharaan ini?')" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editPemeliharaanModal<?= $id_pemeliharaan ?>" tabindex="-1" role="dialog" aria-labelledby="editPemeliharaanModalLabel<?= $id_pemeliharaan ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editPemeliharaanModalLabel<?= $id_pemeliharaan ?>">Edit Pemeliharaan</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="Edit_Id_Pemeliharaan" value="<?= $id_pemeliharaan ?>">
                                                    <div class="form-group">
                                                        <label for="editIdBarangNormal<?= $id_pemeliharaan ?>">Barang (Normal):</label>
                                                        <select name="Edit_Id_Barang" class="form-control" id="editIdBarangNormal<?= $id_pemeliharaan ?>">
                                                            <option value="">-- Pilih Barang Normal --</option>
                                                            <?php foreach ($barang_data as $barang) : ?>
                                                                <option value="<?= $barang['id_barang'] ?>" <?= ($barang['id_barang'] == $id_barang_normal) ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($barang['namabarang'] . " (" . $barang['merk'] . ")"); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editIdBarangRusak<?= $id_pemeliharaan ?>">Barang Rusak:</label>
                                                        <select name="Edit_Id_Rusak" class="form-control" id="editIdBarangRusak<?= $id_pemeliharaan ?>">
                                                            <option value="">-- Pilih Barang Rusak --</option>
                                                            <?php 
                                                            // Untuk edit, tampilkan juga barang rusak yang mungkin sudah selesai dipelihara (statusnya sudah diubah)
                                                            // atau yang sedang dipelihara saat ini.
                                                            // Query ulang data barang_rusak untuk modal edit
                                                            $current_barang_rusak_edit = [];
                                                            $sql_current_rusak = "SELECT br.id_rusak, b.namabarang, br.kondisi, br.jumlah, br.status
                                                                                  FROM barang_rusak br 
                                                                                  JOIN barang b ON br.id_barang = b.id_barang
                                                                                  WHERE br.status = 'Rusak' OR br.id_rusak = ?";
                                                            $stmt_current_rusak = mysqli_prepare($conn, $sql_current_rusak);
                                                            mysqli_stmt_bind_param($stmt_current_rusak, "i", $id_barang_rusak);
                                                            mysqli_stmt_execute($stmt_current_rusak);
                                                            $result_current_rusak = mysqli_stmt_get_result($stmt_current_rusak);
                                                            if ($result_current_rusak && mysqli_num_rows($result_current_rusak) > 0) {
                                                                while($row_rusak_edit = mysqli_fetch_assoc($result_current_rusak)) {
                                                                    $current_barang_rusak_edit[] = $row_rusak_edit;
                                                                }
                                                            }
                                                            mysqli_stmt_close($stmt_current_rusak);
                                                            
                                                            foreach ($current_barang_rusak_edit as $rusak_edit) : ?>
                                                                <option value="<?= $rusak_edit['id_rusak'] ?>" <?= ($rusak_edit['id_rusak'] == $id_barang_rusak) ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($rusak_edit['namabarang'] . " (" . $rusak_edit['kondisi'] . " - " . $rusak_edit['jumlah'] . " unit) [" . $rusak_edit['status'] . "]"); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editKeterangan<?= $id_pemeliharaan ?>">Keterangan:</label>
                                                        <textarea name="Edit_Keterangan" class="form-control" id="editKeterangan<?= $id_pemeliharaan ?>" rows="3" required><?= htmlspecialchars($keterangan) ?></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editTanggal<?= $id_pemeliharaan ?>">Tanggal:</label>
                                                        <input type="date" name="Edit_Tanggal" value="<?= htmlspecialchars($tanggal) ?>" class="form-control" id="editTanggal<?= $id_pemeliharaan ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditPemeliharaan" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            echo '<tr><td colspan="6" class="text-center">Belum ada data pemeliharaan.</td></tr>';
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
// Modal Tambah Pemeliharaan Baru 
?>
<div class="modal fade" id="addPemeliharaanModal" tabindex="-1" role="dialog" aria-labelledby="addPemeliharaanModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPemeliharaanModalLabel">Tambah Pemeliharaan Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="pemeliharaan.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="id_barang_add">Barang (Normal):</label>
                        <select name="id_barang" class="form-control" id="id_barang_add">
                            <option value="">-- Pilih Barang Normal --</option>
                            <?php foreach ($barang_data as $barang) : ?>
                                <option value="<?= $barang['id_barang'] ?>"><?= htmlspecialchars($barang['namabarang'] . " (" . $barang['merk'] . ")"); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_rusak_add">Barang Rusak:</label>
                        <select name="id_rusak" class="form-control" id="id_rusak_add">
                            <option value="">-- Pilih Barang Rusak --</option>
                            <?php foreach ($barang_rusak_data as $rusak) : ?>
                                <option value="<?= $rusak['id_rusak'] ?>"><?= htmlspecialchars($rusak['namabarang'] . " (" . $rusak['kondisi'] . " - " . $rusak['jumlah'] . " unit)"); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="keterangan_add">Keterangan:</label>
                        <textarea name="keterangan" placeholder="Keterangan Pemeliharaan" class="form-control" id="keterangan_add" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="tanggal_add">Tanggal:</label>
                        <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" class="form-control" id="tanggal_add" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="addPemeliharaan">Tambah</button>
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
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("myTable");
        tr = table.getElementsByTagName("tr");
        var noDataMessage = document.getElementById("noDataMessage");
        var foundVisibleRow = false; 

        for (i = 0; i < tr.length; i++) {
            if (tr[i].parentNode.tagName === 'TBODY') {
                var foundInRow = false;
                // Kolom yang relevan untuk pencarian: Barang Normal (1), Barang Rusak (2), Keterangan (3)
                for (j = 1; j <= 3; j++) { 
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

    // Event listener untuk mengambil saran dari database secara real-time (AJAX)
    let debounceTimerPemeliharaan;
    document.getElementById("searchInput").addEventListener("input", function() {
        clearTimeout(debounceTimerPemeliharaan);
        const inputVal = this.value;

        debounceTimerPemeliharaan = setTimeout(() => {
            if (inputVal.length >= 2) {
                // Pastikan Anda memiliki file 'pemeliharaan_suggestions.php'
                fetch('pemeliharaan_suggestions.php?query=' + encodeURIComponent(inputVal))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        var datalist = document.getElementById('pemeliharaanSuggestions');
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
                document.getElementById('pemeliharaanSuggestions').innerHTML = '';
            }
        }, 300);
    });

    document.addEventListener('DOMContentLoaded', filterTable);
    document.getElementById("searchInput").addEventListener("change", filterTable);
</script>

<?php
// Include footer.php di akhir.
include 'footer.php';
?>