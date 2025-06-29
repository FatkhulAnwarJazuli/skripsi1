<?php
// Include sidebar.php di awal. Ini akan menangani session_start(), head, body, sidebar, dan topbar.
include 'sidebar.php';

// Pastikan file function.php Anda menginisialisasi koneksi database $conn.
include_once "function.php";

// Pastikan koneksi database sudah ada dan valid sebelum melanjutkan
if (!isset($conn) || !$conn) {
    die("Koneksi database gagal! Pastikan 'function.php' menginisialisasi \$conn dengan benar.");
}

// Set zona waktu untuk tanggal (Waktu Indonesia Tengah untuk Banjarmasin)
date_default_timezone_set('Asia/Makassar');

// --- Logika Paginasi ---

$limit = 5; // Batas data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

// Hitung total data untuk paginasi
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_pemesanan) AS total FROM pemesanan");
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

// --- Tangani Operasi CRUD ---

// Tangani operasi Tambah Pemesanan
if (isset($_POST['addpemesanan'])) {
    $id_pengajuan = htmlspecialchars($_POST['id_pengajuan']);
    $id_supplier = htmlspecialchars($_POST['id_supplier']);
    $tanggal_pemesanan = date('Y-m-d'); // Tanggal pemesanan otomatis hari ini

    // Sanitasi input harga
    $harga_raw = str_replace('.', '', $_POST['harga']); // Menghilangkan titik sebagai pemisah ribuan
    $harga = filter_var($harga_raw, FILTER_VALIDATE_FLOAT);

    $status_pemesanan = htmlspecialchars($_POST['status_pemesanan']);

    // Validasi input harga
    if ($harga === false || $harga < 0) {
        echo '<script>alert("Error: Harga tidak valid atau negatif."); window.location.href="pemesanan.php?page=' . $page . '";</script>';
        exit();
    }

    // Pastikan pengajuan belum dipesan (misal: belum ada di tabel pemesanan)
    $cek_pengajuan_query = mysqli_query($conn, "SELECT id_pemesanan FROM pemesanan WHERE id_pengajuan = " . (int)$id_pengajuan);
    if (mysqli_num_rows($cek_pengajuan_query) > 0) {
        echo '<script>alert("Pengajuan ini sudah pernah dibuatkan pemesanan!"); window.location.href="pemesanan.php?page=' . $page . '";</script>';
        exit();
    }

    $query = "INSERT INTO pemesanan (id_pengajuan, id_supplier, tanggal_pemesanan, harga, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        // "iisds" -> integer (id_pengajuan), integer (id_supplier), string (tanggal_pemesanan),
        //           double/decimal (harga), string (status)
        mysqli_stmt_bind_param($stmt, "iisds", $id_pengajuan, $id_supplier, $tanggal_pemesanan, $harga, $status_pemesanan);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo '<script>alert("Pemesanan Berhasil Ditambahkan!"); window.location.href="pemesanan.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menambahkan Pemesanan: ' . mysqli_error($conn) . '"); window.location.href="pemesanan.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Tambah Pemesanan: ' . mysqli_error($conn) . '"); window.location.href="pemesanan.php?page=' . $page . '";</script>';
    }
    exit();
}

// Tangani operasi Edit Pemesanan
if (isset($_POST['SimpanEditPemesanan'])) {
    $idpemesanan_edit = htmlspecialchars($_POST['Edit_Id_Pemesanan']);
    $id_supplier_baru = htmlspecialchars($_POST['Edit_Id_Supplier']);

    // Sanitasi input harga baru
    $harga_baru_raw = str_replace('.', '', $_POST['Edit_Harga']);
    $harga_baru = filter_var($harga_baru_raw, FILTER_VALIDATE_FLOAT);

    $status_pemesanan_baru = htmlspecialchars($_POST['Edit_Status_Pemesanan']);

    // Validasi input harga_baru
    if ($harga_baru === false || $harga_baru < 0) {
        echo '<script>alert("Error: Harga tidak valid atau negatif."); window.location.href="pemesanan.php?page=' . $page . '";</script>';
        exit();
    }

    $query = "UPDATE pemesanan SET id_supplier=?, harga=?, status=? WHERE id_pemesanan=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        // "idsi" -> integer (id_supplier_baru), double/decimal (harga_baru),
        //           string (status_pemesanan_baru), integer (idpemesanan_edit)
        mysqli_stmt_bind_param($stmt, "idsi", $id_supplier_baru, $harga_baru, $status_pemesanan_baru, $idpemesanan_edit);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo '<script>alert("Pemesanan Berhasil Diperbarui!"); window.location.href="pemesanan.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Memperbarui Pemesanan: ' . mysqli_error($conn) . '"); window.location.href="pemesanan.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Update Pemesanan: ' . mysqli_error($conn) . '"); window.location.href="pemesanan.php?page=' . $page . '";</script>';
    }
    exit();
}

// Tangani operasi Hapus Pemesanan
if (isset($_GET['hapus'])) {
    $id_pemesanan_to_delete = htmlspecialchars($_GET['hapus']);

    // Pastikan id yang dihapus adalah angka untuk mencegah SQL Injection (walaupun prepare statement sudah melindungi)
    if (!is_numeric($id_pemesanan_to_delete)) {
        echo '<script>alert("ID pemesanan tidak valid."); window.location.href="pemesanan.php?page=' . $page . '";</script>';
        exit;
    }

    $query = "DELETE FROM pemesanan WHERE id_pemesanan=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_pemesanan_to_delete);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo '<script>alert("Pemesanan Berhasil Dihapus!"); window.location.href="pemesanan.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menghapus Pemesanan: ' . mysqli_error($conn) . '"); window.location.href="pemesanan.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Delete Pemesanan: ' . mysqli_error($conn) . '"); window.location.href="pemesanan.php?page=' . $page . '";</script>';
    }
    exit();
}

// Ambil semua data pemesanan untuk halaman saat ini dengan join ke tabel pengajuan dan supplier
$ambilsemuadatanya = mysqli_query($conn, "SELECT p.*,
                                                 aj.nama_barang, aj.merk AS merk_barang, aj.jumlah AS jumlah_diajukan, aj.satuan, aj.pengaju,
                                                 s.nama_supplier
                                          FROM pemesanan p
                                          LEFT JOIN pengajuan aj ON p.id_pengajuan = aj.id_pengajuan
                                          LEFT JOIN supplier s ON p.id_supplier = s.id_supplier
                                          ORDER BY p.tanggal_pemesanan DESC
                                          LIMIT $start, $limit");

// Ambil daftar pengajuan yang belum dibuatkan pemesanan
// Ini untuk mencegah satu pengajuan dibuatkan banyak pemesanan
$daftar_pengajuan_belum_dipesan = mysqli_query($conn, "SELECT pa.id_pengajuan, pa.nama_barang, pa.merk, pa.jumlah, pa.satuan, pa.pengaju
                                                       FROM pengajuan pa
                                                       LEFT JOIN pemesanan p ON pa.id_pengajuan = p.id_pengajuan
                                                       WHERE p.id_pemesanan IS NULL
                                                       ORDER BY pa.tanggal_pengajuan DESC");

// Ambil daftar supplier untuk dropdown
$daftar_supplier = mysqli_query($conn, "SELECT id_supplier, nama_supplier FROM supplier ORDER BY nama_supplier ASC");

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Pemesanan</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPemesananModal">
                <i class="fas fa-plus"></i> Tambah Pemesanan
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInputPemesanan" class="form-control" placeholder="Cari pemesanan..." list="pemesananSuggestions">
                    <datalist id="pemesananSuggestions"></datalist>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="myTablePemesanan" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Merk</th>
                            <th>Jumlah Diajukan</th>
                            <th>Satuan</th>
                            <th>Harga (Rp)</th> <th>Pengaju</th>
                            <th>Supplier</th>
                            <th>Tanggal Pemesanan</th>
                            <th>Status</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $id_pemesanan = $data['id_pemesanan'];
                                $id_pengajuan_data = $data['id_pengajuan'];
                                $id_supplier_data = $data['id_supplier'];
                                $nama_barang_diajukan = $data['nama_barang'];
                                $merk_barang_diajukan = $data['merk_barang'];
                                $jumlah_diajukan = $data['jumlah_diajukan'];
                                $satuan_diajukan = $data['satuan'];
                                $harga = $data['harga']; // Ambil harga dari DB
                                $pengaju = $data['pengaju'];
                                $nama_supplier = $data['nama_supplier'];
                                $tanggal_pemesanan = $data['tanggal_pemesanan'];
                                $status_pemesanan = $data['status'];
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= htmlspecialchars($nama_barang_diajukan) ?></td>
                                    <td><?= htmlspecialchars($merk_barang_diajukan) ?></td>
                                    <td><?= htmlspecialchars($jumlah_diajukan) ?></td>
                                    <td><?= htmlspecialchars($satuan_diajukan) ?></td>
                                    <td>Rp <?= htmlspecialchars(number_format($harga, 0, ',', '.')) ?></td> <td><?= htmlspecialchars($pengaju) ?></td>
                                    <td><?= htmlspecialchars($nama_supplier) ?></td>
                                    <td><?= htmlspecialchars($tanggal_pemesanan) ?></td>
                                    <td><?= htmlspecialchars($status_pemesanan) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#editPemesananModal<?= $id_pemesanan ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $id_pemesanan ?>&page=<?= $page ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus pemesanan ini? Ini tidak akan mengembalikan pengajuan.')" class="btn btn-danger btn-sm mr-1">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editPemesananModal<?= $id_pemesanan ?>" tabindex="-1" role="dialog" aria-labelledby="editPemesananModalLabel<?= $id_pemesanan ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editPemesananModalLabel<?= $id_pemesanan ?>">Edit Pemesanan</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="Edit_Id_Pemesanan" value="<?= $id_pemesanan ?>">
                                                    <div class="form-group">
                                                        <label>Pengajuan (Tidak bisa diubah):</label>
                                                        <input type="text" class="form-control" value="ID: <?= htmlspecialchars($id_pengajuan_data) ?> - <?= htmlspecialchars($nama_barang_diajukan) ?> (<?= htmlspecialchars($jumlah_diajukan) ?> <?= htmlspecialchars($satuan_diajukan) ?>)" readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editIdSupplier<?= $id_pemesanan ?>">Supplier:</label>
                                                        <select name="Edit_Id_Supplier" class="form-control" id="editIdSupplier<?= $id_pemesanan ?>" required>
                                                            <?php
                                                            // Reset pointer daftar_supplier untuk modal edit
                                                            mysqli_data_seek($daftar_supplier, 0);
                                                            while ($supplier_data_edit = mysqli_fetch_array($daftar_supplier)) {
                                                                $supplier_id_edit = $supplier_data_edit['id_supplier'];
                                                                $supplier_nama_edit = $supplier_data_edit['nama_supplier'];
                                                                $selected = ($supplier_id_edit == $id_supplier_data) ? 'selected' : '';
                                                                echo '<option value="' . $supplier_id_edit . '" ' . $selected . '>' . htmlspecialchars($supplier_nama_edit) . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editHarga<?= $id_pemesanan ?>">Harga (Rp):</label> <input type="number" name="Edit_Harga" value="<?= htmlspecialchars($harga) ?>" class="form-control" id="editHarga<?= $id_pemesanan ?>" required min="0" step="0.01">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editStatusPemesanan<?= $id_pemesanan ?>">Status:</label>
                                                        <select name="Edit_Status_Pemesanan" class="form-control" id="editStatusPemesanan<?= $id_pemesanan ?>" required>
                                                            <option value="Dipesan" <?= ($status_pemesanan == 'Dipesan') ? 'selected' : '' ?>>Dipesan</option>
                                                            <option value="Dikirim" <?= ($status_pemesanan == 'Dikirim') ? 'selected' : '' ?>>Dikirim</option>
                                                            <option value="Dibatalkan" <?= ($status_pemesanan == 'Dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditPemesanan" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            // Sesuaikan colspan di sini jika ada perubahan jumlah kolom (dari 12 menjadi 10)
                            echo '<tr><td colspan="11" class="text-center">Belum ada data pemesanan.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
                <div id="noDataMessagePemesanan" class="alert alert-warning text-center" style="display: none;">
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

<div class="modal fade" id="addPemesananModal" tabindex="-1" role="dialog" aria-labelledby="addPemesananModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPemesananModalLabel">Tambah Pemesanan Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="pemesanan.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="idPengajuanPemesanan">Pilih Pengajuan (Belum dipesan):</label>
                        <select name="id_pengajuan" class="form-control" id="idPengajuanPemesanan" required>
                            <option value="">-- Pilih Pengajuan --</option>
                            <?php
                            // Pastikan kursor hasil query direset jika sudah digunakan sebelumnya
                            mysqli_data_seek($daftar_pengajuan_belum_dipesan, 0);
                            if (mysqli_num_rows($daftar_pengajuan_belum_dipesan) > 0) {
                                while ($pengajuan_data = mysqli_fetch_array($daftar_pengajuan_belum_dipesan)) {
                                    $p_id = $pengajuan_data['id_pengajuan'];
                                    $p_nama = $pengajuan_data['nama_barang'];
                                    $p_merk = $pengajuan_data['merk'];
                                    $p_jumlah = $pengajuan_data['jumlah'];
                                    $p_satuan = $pengajuan_data['satuan'];
                                    $p_pengaju = $pengajuan_data['pengaju'];
                                    echo '<option value="' . $p_id . '">' . htmlspecialchars($p_nama) . ' (' . htmlspecialchars($p_merk) . ') - ' . htmlspecialchars($p_jumlah) . ' ' . htmlspecialchars($p_satuan) . ' [Diajukan oleh: ' . htmlspecialchars($p_pengaju) . ']</option>';
                                }
                            } else {
                                echo '<option value="" disabled>Tidak ada pengajuan yang belum dipesan.</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="idSupplierPemesanan">Pilih Supplier:</label>
                        <select name="id_supplier" class="form-control" id="idSupplierPemesanan" required>
                            <option value="">-- Pilih Supplier --</option>
                            <?php
                            // Reset pointer daftar_supplier untuk modal tambah
                            mysqli_data_seek($daftar_supplier, 0);
                            while ($supplier_data = mysqli_fetch_array($daftar_supplier)) {
                                $supplier_id = $supplier_data['id_supplier'];
                                $supplier_nama = $supplier_data['nama_supplier'];
                                echo '<option value="' . $supplier_id . '">' . htmlspecialchars($supplier_nama) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="addHarga">Harga (Rp):</label> <input type="number" name="harga" placeholder="Masukkan Harga" class="form-control" id="addHarga" required min="0" step="0.01">
                    </div>
                    <div class="form-group">
                        <label for="statusPemesanan">Status Pemesanan:</label>
                        <select name="status_pemesanan" class="form-control" id="statusPemesanan" required>
                            <option value="Dipesan">Dipesan</option>
                            <option value="Dikirim">Dikirim</option>
                            <option value="Dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="addpemesanan">Tambah</button>
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
    function filterTablePemesanan() {
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInputPemesanan");
        filter = input.value.toUpperCase();
        table = document.getElementById("myTablePemesanan");
        tr = table.getElementsByTagName("tr");
        var noDataMessage = document.getElementById("noDataMessagePemesanan");
        var foundVisibleRow = false;

        for (i = 0; i < tr.length; i++) {
            if (tr[i].parentNode.tagName === 'TBODY') {
                var foundInRow = false;
                // Kolom yang akan dicari: Nama Barang (indeks 1), Merk (indeks 2), Pengaju (indeks 6), Supplier (indeks 7), Status (indeks 9)
                // Sesuaikan indeks kolom ini jika tata letak berubah
                const searchColumns = [1, 2, 6, 7, 9]; // Indeks kolom baru
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

    document.getElementById("searchInputPemesanan").addEventListener("keyup", filterTablePemesanan);
    document.getElementById("searchInputPemesanan").addEventListener("input", filterTablePemesanan);

    // Event listener untuk mengambil saran dari database secara real-time (AJAX)
    // Anda perlu membuat file baru: pemesanan_suggestions.php
    let debounceTimerPemesanan;
    document.getElementById("searchInputPemesanan").addEventListener("input", function() {
        clearTimeout(debounceTimerPemesanan);
        const inputVal = this.value;

        debounceTimerPemesanan = setTimeout(() => {
            if (inputVal.length >= 2) {
                // Asumsikan ada file 'pemesanan_suggestions.php' di root project
                fetch('pemesanan_suggestions.php?query=' + encodeURIComponent(inputVal))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        var datalist = document.getElementById('pemesananSuggestions');
                        datalist.innerHTML = '';
                        if (data && Array.isArray(data)) {
                            data.forEach(item => {
                                var option = document.createElement('option');
                                option.value = item;
                                datalist.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching pemesanan suggestions:', error));
            } else {
                document.getElementById('pemesananSuggestions').innerHTML = '';
            }
        }, 300);
    });

    document.addEventListener('DOMContentLoaded', filterTablePemesanan);
    document.getElementById("searchInputPemesanan").addEventListener("change", filterTablePemesanan);
</script>

<?php
// Include footer.php di akhir. Ini akan menutup semua tag HTML yang dibuka di sidebar.php
// dan menyertakan skrip JavaScript global.
include 'footer.php';
?>