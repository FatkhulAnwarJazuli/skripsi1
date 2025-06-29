<?php
// Include sidebar.php di awal. Ini akan menangani session_start(), head, body, sidebar, dan topbar.
include 'sidebar.php'; 

// Pastikan file function.php Anda menginisialisasi koneksi database $conn.
include_once "function.php"; 

// --- Logika Paginasi ---
$limit = 5; // Batas data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Mendapatkan halaman saat ini dari GET request, default ke 1
if ($page < 1) { // Memastikan halaman tidak kurang dari 1
    $page = 1;
}

// Hitung total data untuk paginasi
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_rusak) AS total FROM barang_rusak");
$total_data = mysqli_fetch_assoc($total_data_query)['total'];
$total_pages = ceil($total_data / $limit); // Menghitung total halaman

// Pastikan halaman tidak melebihi total halaman yang ada
if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
} elseif ($total_pages == 0) { // Jika tidak ada data sama sekali
    $page = 1;
}

$start = ($page - 1) * $limit; // Menghitung offset awal untuk query

// --- Akhir Logika Paginasi ---

// --- Tangani Operasi CRUD ---

// Tangani operasi Edit Barang Rusak
if (isset($_POST['SimpanEditBarangRusak'])) {
    $idrusak = htmlspecialchars($_POST['Edit_Id_Rusak']);
    $idbarang = htmlspecialchars($_POST['Edit_Id_Barang_Rusak']); // ID barang yang terkait dengan barang rusak
    $jumlah = htmlspecialchars($_POST['Edit_Jumlah_Rusak']);
    $kondisi = htmlspecialchars($_POST['Edit_Kondisi_Rusak']);
    $status = htmlspecialchars($_POST['Edit_Status_Rusak']);

    // Mengambil jumlah lama dari barang rusak
    $query_old_jumlah = mysqli_query($conn, "SELECT id_barang, jumlah FROM barang_rusak WHERE id_rusak = '$idrusak'");
    $old_data = mysqli_fetch_assoc($query_old_jumlah);
    $old_id_barang = $old_data['id_barang'];
    $old_jumlah = $old_data['jumlah'];

    // Menangani upload foto baru jika ada
    $foto_lama = htmlspecialchars($_POST['Edit_Foto_Lama_Rusak']);
    $foto_baru = $_FILES['Edit_Foto_Rusak']['name'];
    $tmp_name_baru = $_FILES['Edit_Foto_Rusak']['tmp_name'];
    $upload_dir = 'foto/';
    $foto_to_save = $foto_lama; // Default menggunakan foto lama

    if (!empty($foto_baru)) {
        if (move_uploaded_file($tmp_name_baru, $upload_dir . $foto_baru)) {
            $foto_to_save = $foto_baru;
            // Hapus foto lama jika ada dan berbeda
            if (!empty($foto_lama) && file_exists($upload_dir . $foto_lama) && $foto_lama != $foto_baru) {
                unlink($upload_dir . $foto_lama);
            }
        } else {
            echo '<script>alert("Gagal mengupload foto baru."); window.location.href="barangrusak.php?page=' . $page . '";</script>';
            exit();
        }
    }

    // Update data di tabel barang_rusak
    $query = "UPDATE barang_rusak SET id_barang=?, jumlah=?, kondisi=?, foto=?, status=? WHERE id_rusak=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iisssi", $idbarang, $jumlah, $kondisi, $foto_to_save, $status, $idrusak);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            // Update jumlah barang di tabel barang
            // Hitung selisih jumlah
            $selisih_jumlah = $jumlah - $old_jumlah;

            // Ambil jumlah barang yang terkait di tabel barang
            $cekjumlahsekarang = mysqli_query($conn, "SELECT jumlah FROM barang WHERE id_barang='$idbarang'");
            $ambildatanya = mysqli_fetch_assoc($cekjumlahsekarang);
            $jumlah_barang_utama_sekarang = $ambildatanya['jumlah'];

            // Sesuaikan jumlah di tabel barang:
            // Jika id_barang berubah, kembalikan jumlah lama ke barang lama, dan kurangi dari barang baru.
            if ($idbarang != $old_id_barang) {
                // Kembalikan jumlah lama ke barang lama
                mysqli_query($conn, "UPDATE barang SET jumlah = jumlah + '$old_jumlah' WHERE id_barang='$old_id_barang'");
                // Kurangi jumlah baru dari barang baru
                $new_total_barang = $jumlah_barang_utama_sekarang - $jumlah;
                mysqli_query($conn, "UPDATE barang SET jumlah = '$new_total_barang' WHERE id_barang='$idbarang'");
            } else {
                // Jika id_barang sama, sesuaikan jumlah berdasarkan selisih
                $new_total_barang = $jumlah_barang_utama_sekarang - $selisih_jumlah;
                mysqli_query($conn, "UPDATE barang SET jumlah = '$new_total_barang' WHERE id_barang='$idbarang'");
            }
            
            echo '<script>alert("Data Barang Rusak Berhasil Diperbarui!"); window.location.href="barangrusak.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Memperbarui Data Barang Rusak: ' . mysqli_error($conn) . '"); window.location.href="barangrusak.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Update: ' . mysqli_error($conn) . '"); window.location.href="barangrusak.php?page=' . $page . '";</script>';
    }
    exit();
}

// Tangani operasi Hapus Barang Rusak
if (isset($_GET['hapus'])) {
    $id_rusak_to_delete = htmlspecialchars($_GET['hapus']);

    // Ambil id_barang dan jumlah dari barang rusak yang akan dihapus
    $query_get_rusak_data = mysqli_query($conn, "SELECT id_barang, jumlah, foto FROM barang_rusak WHERE id_rusak = '$id_rusak_to_delete'");
    $rusak_data = mysqli_fetch_assoc($query_get_rusak_data);
    $id_barang_terkait = $rusak_data['id_barang'];
    $jumlah_rusak_dihapus = $rusak_data['jumlah'];
    $foto_to_delete = $rusak_data['foto'];
    $upload_dir = 'foto/';

    // Hapus foto dari server
    if (!empty($foto_to_delete) && file_exists($upload_dir . $foto_to_delete)) {
        unlink($upload_dir . $foto_to_delete);
    }

    // Hapus data dari tabel barang_rusak
    $query = "DELETE FROM barang_rusak WHERE id_rusak=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_rusak_to_delete);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            // Kembalikan jumlah barang ke tabel barang
            mysqli_query($conn, "UPDATE barang SET jumlah = jumlah + '$jumlah_rusak_dihapus' WHERE id_barang='$id_barang_terkait'");
            echo '<script>alert("Data Barang Rusak Berhasil Dihapus!"); window.location.href="barangrusak.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menghapus Data Barang Rusak: ' . mysqli_error($conn) . '"); window.location.href="barangrusak.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Delete: ' . mysqli_error($conn) . '"); window.location.href="barangrusak.php?page=' . $page . '";</script>';
    }
    exit();
}

// Ambil semua data barang rusak untuk halaman saat ini dengan join ke tabel barang
$ambilsemuadatanya = mysqli_query($conn, "SELECT br.*, b.namabarang, b.merk 
                                            FROM barang_rusak br
                                            JOIN barang b ON br.id_barang = b.id_barang
                                            ORDER BY br.id_rusak DESC LIMIT $start, $limit");

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Barang Rusak</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addBarangRusakModal">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari barang rusak..." list="barangRusakSuggestions">
                    <datalist id="barangRusakSuggestions"></datalist>
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
                            <th>Kondisi</th>
                            <th>Foto</th>
                            <th>Status</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $id_rusak = $data['id_rusak'];
                                $id_barang_fk = $data['id_barang']; // Foreign key ke tabel barang
                                $namabarang = $data['namabarang'];
                                $merk = $data['merk'];
                                $jumlah_rusak = $data['jumlah'];
                                $kondisi = $data['kondisi'];
                                $foto = $data['foto'];
                                $status = $data['status'];
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= htmlspecialchars($namabarang) ?></td>
                                    <td><?= htmlspecialchars($merk) ?></td>
                                    <td><?= htmlspecialchars($jumlah_rusak) ?></td>
                                    <td><?= htmlspecialchars($kondisi) ?></td>
                                    <td>
                                        <?php if (!empty($foto)) : ?>
                                            <img src="foto/<?= htmlspecialchars($foto) ?>" alt="Foto Barang Rusak" style="width: 100px; height: auto;">
                                        <?php else : ?>
                                            Tidak ada foto
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($status) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#editBarangRusakModal<?= $id_rusak ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $id_rusak ?>&page=<?= $page ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data barang rusak ini? Jumlah barang terkait akan dikembalikan ke inventaris utama.')" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                         <a href="ppbrusak.php?id_rusak=<?= $id_rusak ?>" target="_blank" class="btn btn-info btn-sm mt-1">
                                             <i class="fa fa-print"></i> Cetak
                                         </a>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editBarangRusakModal<?= $id_rusak ?>" tabindex="-1" role="dialog" aria-labelledby="editBarangRusakModalLabel<?= $id_rusak ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="post" enctype="multipart/form-data">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editBarangRusakModalLabel<?= $id_rusak ?>">Edit Barang Rusak</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="Edit_Id_Rusak" value="<?= $id_rusak ?>">
                                                    <input type="hidden" name="Edit_Foto_Lama_Rusak" value="<?= htmlspecialchars($foto) ?>">

                                                    <div class="form-group">
                                                        <label for="editIdBarangRusak<?= $id_rusak ?>">Nama Barang:</label>
                                                        <select name="Edit_Id_Barang_Rusak" class="form-control" id="editIdBarangRusak<?= $id_rusak ?>" required>
                                                            <?php
                                                            // Ambil semua barang dari tabel barang untuk dropdown
                                                            $query_barang = mysqli_query($conn, "SELECT id_barang, namabarang, merk FROM barang ORDER BY namabarang ASC");
                                                            while ($b_data = mysqli_fetch_assoc($query_barang)) {
                                                                $selected = ($b_data['id_barang'] == $id_barang_fk) ? 'selected' : '';
                                                                echo '<option value="' . $b_data['id_barang'] . '" ' . $selected . '>' . htmlspecialchars($b_data['namabarang']) . ' (' . htmlspecialchars($b_data['merk']) . ')</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editJumlahRusak<?= $id_rusak ?>">Jumlah:</label>
                                                        <input type="number" name="Edit_Jumlah_Rusak" value="<?= htmlspecialchars($jumlah_rusak) ?>" class="form-control" id="editJumlahRusak<?= $id_rusak ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editKondisiRusak<?= $id_rusak ?>">Kondisi:</label>
                                                        <input type="text" name="Edit_Kondisi_Rusak" value="<?= htmlspecialchars($kondisi) ?>" class="form-control" id="editKondisiRusak<?= $id_rusak ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editStatusRusak<?= $id_rusak ?>">Status:</label>
                                                        <select name="Edit_Status_Rusak" class="form-control" id="editStatusRusak<?= $id_rusak ?>" required>
                                                            <option value="Rusak" <?= ($status == 'Rusak') ? 'selected' : ''; ?>>Rusak</option>
                                                            <option value="Perbaikan" <?= ($status == 'Perbaikan') ? 'selected' : ''; ?>>Perbaikan</option>
                                                            <option value="Selesai Perbaikan" <?= ($status == 'Selesai Perbaikan') ? 'selected' : ''; ?>>Selesai Perbaikan</option>
                                                            <option value="Dihapus" <?= ($status == 'Dihapus') ? 'selected' : ''; ?>>Dihapus</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editFotoRusak<?= $id_rusak ?>">Foto (opsional, biarkan kosong jika tidak ingin mengubah):</label>
                                                        <input type="file" name="Edit_Foto_Rusak" class="form-control-file" id="editFotoRusak<?= $id_rusak ?>" accept="image/*">
                                                        <?php if (!empty($foto)) : ?>
                                                            <small class="form-text text-muted mt-2">Foto saat ini: <img src="foto/<?= htmlspecialchars($foto) ?>" alt="Foto Barang Rusak" style="width: 50px; height: auto; vertical-align: middle;"></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditBarangRusak" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            echo '<tr><td colspan="8" class="text-center">Belum ada data barang rusak.</td></tr>';
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

<div class="modal fade" id="addBarangRusakModal" tabindex="-1" role="dialog" aria-labelledby="addBarangRusakModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBarangRusakModalLabel">Tambah Barang Rusak Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="function.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="barangSelect">Nama Barang:</label>
                        <select name="barangnya" class="form-control" id="barangSelect" required>
                            <option value="">Pilih Barang</option>
                            <?php
                            $ambilsemuadatabarang = mysqli_query($conn, "SELECT id_barang, namabarang, merk, jumlah FROM barang WHERE jumlah > 0 ORDER BY namabarang ASC");
                            while ($fetcharray = mysqli_fetch_array($ambilsemuadatabarang)) {
                                $id_barang_add = $fetcharray['id_barang'];
                                $namabarang_add = $fetcharray['namabarang'];
                                $merk_add = $fetcharray['merk'];
                                $jumlah_tersedia = $fetcharray['jumlah'];
                            ?>
                                <option value="<?= $id_barang_add ?>"><?= htmlspecialchars($namabarang_add) ?> (<?= htmlspecialchars($merk_add) ?>) - Stok: <?= $jumlah_tersedia ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="jumlahInput">Jumlah Rusak:</label>
                        <input type="number" name="jumlah" placeholder="Jumlah Rusak" class="form-control" id="jumlahInput" required min="1">
                        <small class="form-text text-muted" id="stokWarning"></small>
                    </div>
                    <div class="form-group">
                        <label for="kondisiInput">Kondisi:</label>
                        <input type="text" name="kondisinya" placeholder="Kondisi Kerusakan" class="form-control" id="kondisiInput" required>
                    </div>
                    <div class="form-group">
                        <label for="statusSelect">Status:</label>
                        <select name="status" class="form-control" id="statusSelect" required>
                            <option value="Rusak">Rusak</option>
                            <option value="Perbaikan">Perbaikan</option>
                            <option value="Selesai Perbaikan">Selesai Perbaikan</option>
                            <option value="Dihapus">Dihapus</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fotoInput">Foto (Opsional):</label>
                        <input type="file" name="foto" class="form-control-file" id="fotoInput" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="addbarangrusak">Tambah</button>
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
                // Kolom yang akan dicari: Nama Barang (idx 1), Merk (idx 2), Kondisi (idx 4), Status (idx 6)
                var searchColumns = [1, 2, 4, 6]; 
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

    // Event listener untuk mengambil saran dari database secara real-time (AJAX)
    let debounceTimer;
    document.getElementById("searchInput").addEventListener("input", function() {
        clearTimeout(debounceTimer);
        const inputVal = this.value;

        debounceTimer = setTimeout(() => {
            if (inputVal.length >= 2) {
                // Endpoint untuk saran barang rusak (perlu dibuat terpisah)
                fetch('barangrusak_suggestions.php?query=' + encodeURIComponent(inputVal))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        var datalist = document.getElementById('barangRusakSuggestions');
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
                document.getElementById('barangRusakSuggestions').innerHTML = '';
            }
        }, 300);
    });

    document.addEventListener('DOMContentLoaded', filterTable);
    document.getElementById("searchInput").addEventListener("change", filterTable);

    // Logika untuk menampilkan stok yang tersedia di modal tambah barang rusak
    document.getElementById('barangSelect').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var stokWarning = document.getElementById('stokWarning');
        var jumlahInput = document.getElementById('jumlahInput');

        if (selectedOption.value) {
            // Mengambil stok dari teks option (misal: "Nama Barang (Merk) - Stok: X")
            var optionText = selectedOption.textContent;
            var stokMatch = optionText.match(/Stok: (\d+)/);
            if (stokMatch && stokMatch[1]) {
                var availableStok = parseInt(stokMatch[1]);
                stokWarning.textContent = 'Stok tersedia: ' + availableStok;
                stokWarning.style.color = 'blue';
                jumlahInput.max = availableStok; // Set max attribute for input number
                if (parseInt(jumlahInput.value) > availableStok) {
                    jumlahInput.value = availableStok; // Reset jumlah jika melebihi stok
                }
            } else {
                stokWarning.textContent = 'Stok tidak ditemukan untuk barang ini.';
                stokWarning.style.color = 'red';
                jumlahInput.removeAttribute('max');
            }
        } else {
            stokWarning.textContent = '';
            jumlahInput.removeAttribute('max');
        }
    });

    // Pemicu awal untuk menampilkan stok jika ada pilihan default (misal dari edit)
    document.addEventListener('DOMContentLoaded', function() {
        var barangSelect = document.getElementById('barangSelect');
        if (barangSelect && barangSelect.value) {
            barangSelect.dispatchEvent(new Event('change'));
        }
    });
</script>

<?php
// Include footer.php di akhir. Ini akan menutup semua tag HTML yang dibuka di sidebar.php
// dan menyertakan skrip JavaScript global.
include 'footer.php';
?>