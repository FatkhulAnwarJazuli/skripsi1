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
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_pemusnahan) AS total FROM pemusnahan");
$total_data = mysqli_fetch_assoc($total_data_query)['total'];
$total_pages = ceil($total_data / $limit);

// Pastikan halaman tidak melebihi total halaman yang ada
if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
} elseif ($total_pages == 0) { // Jika tidak ada data sama sekali
    $page = 1;
}

$start = ($page - 1) * $limit; // Sekarang $start akan selalu terdefinisi

// --- Tangani Operasi CRUD ---

// Tangani operasi Edit
if (isset($_POST['SimpanEditPemusnahan'])) {
    $idpemusnahan = htmlspecialchars($_POST['Edit_Id_Pemusnahan']);
    $idrusak = htmlspecialchars($_POST['Edit_Id_Rusak']); // Tetap ada di form untuk pengiriman data
    $idruangan = htmlspecialchars($_POST['Edit_Id_Ruangan']);
    $jumlah = htmlspecialchars($_POST['Edit_Jumlah']);
    $alasan = htmlspecialchars($_POST['Edit_Alasan']);

    $query = "UPDATE pemusnahan SET id_rusak=?, id_ruangan=?, jumlah=?, alasan=? WHERE id_pemusnahan=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iiisi", $idrusak, $idruangan, $jumlah, $alasan, $idpemusnahan);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo '<script>alert("Data Pemusnahan Berhasil Diperbarui!"); window.location.href="pemusnahan.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Memperbarui Data Pemusnahan: ' . mysqli_error($conn) . '"); window.location.href="pemusnahan.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Update: ' . mysqli_error($conn) . '"); window.location.href="pemusnahan.php?page=' . $page . '";</script>';
    }
    exit();
}

// Tangani operasi Hapus
if (isset($_GET['hapus'])) {
    $id_pemusnahan_to_delete = htmlspecialchars($_GET['hapus']);

    $query = "DELETE FROM pemusnahan WHERE id_pemusnahan=?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_pemusnahan_to_delete);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo '<script>alert("Data Pemusnahan Berhasil Dihapus!"); window.location.href="pemusnahan.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menghapus Data Pemusnahan: ' . mysqli_error($conn) . '"); window.location.href="pemusnahan.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Delete: ' . mysqli_error($conn) . '"); window.location.href="pemusnahan.php?page=' . $page . '";</script>';
    }
    exit();
}

// Tangani operasi Tambah Pemusnahan
if (isset($_POST['addpemusnahan'])) {
    $idrusak = htmlspecialchars($_POST['idrusak']); // Tetap ada di form untuk pengiriman data
    $idruangan = htmlspecialchars($_POST['idruangan']);
    $jumlah = htmlspecialchars($_POST['jumlah']);
    $alasan = htmlspecialchars($_POST['alasan']);

    $query = "INSERT INTO pemusnahan (id_rusak, id_ruangan, jumlah, alasan) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iiis", $idrusak, $idruangan, $jumlah, $alasan);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo '<script>alert("Data Pemusnahan Berhasil Ditambahkan!"); window.location.href="pemusnahan.php?page=' . $page . '";</script>';
        } else {
            echo '<script>alert("Gagal Menambahkan Data Pemusnahan: ' . mysqli_error($conn) . '"); window.location.href="pemusnahan.php?page=' . $page . '";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Tambah: ' . mysqli_error($conn) . '"); window.location.href="pemusnahan.php?page=' . $page . '";</script>';
    }
    exit();
}

// Ambil semua data pemusnahan untuk halaman saat ini dengan JOIN
$ambilsemuadatanya = mysqli_query($conn, "
    SELECT
        p.id_pemusnahan,
        p.jumlah,
        p.alasan,
        br.id_rusak,
        b.namabarang,
        r.id_ruangan,
        r.nama_ruangan
    FROM
        pemusnahan p
    JOIN
        barang_rusak br ON p.id_rusak = br.id_rusak
    JOIN
        barang b ON br.id_barang = b.id_barang
    JOIN
        ruangan r ON p.id_ruangan = r.id_ruangan
    ORDER BY
        p.id_pemusnahan DESC
    LIMIT $start, $limit
");

// Ambil data untuk dropdown di modal (barang rusak dan ruangan)
$ambil_barang_rusak = mysqli_query($conn, "SELECT br.id_rusak, b.namabarang FROM barang_rusak br JOIN barang b ON br.id_barang = b.id_barang WHERE br.status = 'Rusak'");
$ambil_ruangan = mysqli_query($conn, "SELECT id_ruangan, nama_ruangan FROM ruangan");

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Pemusnahan Barang</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPemusnahanModal">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari pemusnahan..." list="pemusnahanSuggestions">
                    <datalist id="pemusnahanSuggestions"></datalist>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="myTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang Rusak</th>
                            <th>Ruangan</th>
                            <th>Jumlah</th>
                            <th>Alasan</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $id_pemusnahan = $data['id_pemusnahan'];
                                $id_rusak = $data['id_rusak']; // Data ini tetap diambil untuk modal edit dan kebutuhan internal
                                $namabarang_rusak = $data['namabarang']; // Nama barang dari tabel barang
                                $id_ruangan = $data['id_ruangan'];
                                $nama_ruangan = $data['nama_ruangan'];
                                $jumlah = $data['jumlah'];
                                $alasan = $data['alasan'];
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= htmlspecialchars($namabarang_rusak) ?></td>
                                    <td><?= htmlspecialchars($nama_ruangan) ?></td>
                                    <td><?= htmlspecialchars($jumlah) ?></td>
                                    <td><?= htmlspecialchars($alasan) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm mr-1" data-toggle="modal" data-target="#editPemusnahanModal<?= $id_pemusnahan ?>">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <a href="?hapus=<?= $id_pemusnahan ?>&page=<?= $page ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data pemusnahan ini?')" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>

                                <div class="modal fade" id="editPemusnahanModal<?= $id_pemusnahan ?>" tabindex="-1" role="dialog" aria-labelledby="editPemusnahanModalLabel<?= $id_pemusnahan ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="post">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editPemusnahanModalLabel<?= $id_pemusnahan ?>">Edit Data Pemusnahan</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="Edit_Id_Pemusnahan" value="<?= $id_pemusnahan ?>">
                                                    <div class="form-group">
                                                        <label for="editIdRusak<?= $id_pemusnahan ?>">Barang Rusak:</label>
                                                        <select name="Edit_Id_Rusak" class="form-control" id="editIdRusak<?= $id_pemusnahan ?>" required>
                                                            <?php
                                                            // Ulangi query untuk setiap modal edit agar pilihan tetap tersedia
                                                            $barang_rusak_options_edit = mysqli_query($conn, "SELECT br.id_rusak, b.namabarang FROM barang_rusak br JOIN barang b ON br.id_barang = b.id_barang WHERE br.status = 'Rusak'");
                                                            while ($br_option = mysqli_fetch_assoc($barang_rusak_options_edit)) {
                                                                $selected = ($br_option['id_rusak'] == $id_rusak) ? 'selected' : '';
                                                                echo '<option value="' . $br_option['id_rusak'] . '" ' . $selected . '>' . htmlspecialchars($br_option['namabarang']) . ' (ID: ' . $br_option['id_rusak'] . ')</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editIdRuangan<?= $id_pemusnahan ?>">Ruangan:</label>
                                                        <select name="Edit_Id_Ruangan" class="form-control" id="editIdRuangan<?= $id_pemusnahan ?>" required>
                                                            <?php
                                                            // Ulangi query untuk setiap modal edit
                                                            $ruangan_options_edit = mysqli_query($conn, "SELECT id_ruangan, nama_ruangan FROM ruangan");
                                                            while ($r_option = mysqli_fetch_assoc($ruangan_options_edit)) {
                                                                $selected = ($r_option['id_ruangan'] == $id_ruangan) ? 'selected' : '';
                                                                echo '<option value="' . $r_option['id_ruangan'] . '" ' . $selected . '>' . htmlspecialchars($r_option['nama_ruangan']) . '</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editJumlah<?= $id_pemusnahan ?>">Jumlah:</label>
                                                        <input type="number" name="Edit_Jumlah" value="<?= htmlspecialchars($jumlah) ?>" class="form-control" id="editJumlah<?= $id_pemusnahan ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editAlasan<?= $id_pemusnahan ?>">Alasan:</label>
                                                        <textarea name="Edit_Alasan" class="form-control" id="editAlasan<?= $id_pemusnahan ?>" rows="3"><?= htmlspecialchars($alasan) ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" name="SimpanEditPemusnahan" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            echo '<tr><td colspan="6" class="text-center">Belum ada data pemusnahan.</td></tr>'; // colspan disesuaikan
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

<div class="modal fade" id="addPemusnahanModal" tabindex="-1" role="dialog" aria-labelledby="addPemusnahanModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPemusnahanModalLabel">Tambah Data Pemusnahan Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="pemusnahan.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="idrusak">Barang Rusak yang Dimusnahkan:</label>
                        <select name="idrusak" class="form-control" id="idrusak" required>
                            <option value="">-- Pilih Barang Rusak --</option>
                            <?php
                            mysqli_data_seek($ambil_barang_rusak, 0); // Reset pointer
                            while ($br_option = mysqli_fetch_assoc($ambil_barang_rusak)) {
                                echo '<option value="' . $br_option['id_rusak'] . '">' . htmlspecialchars($br_option['namabarang']) . ' (ID: ' . $br_option['id_rusak'] . ')</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="idruangan">Ruangan:</label>
                        <select name="idruangan" class="form-control" id="idruangan" required>
                            <option value="">-- Pilih Ruangan --</option>
                            <?php
                            mysqli_data_seek($ambil_ruangan, 0); // Reset pointer
                            while ($r_option = mysqli_fetch_assoc($ambil_ruangan)) {
                                echo '<option value="' . $r_option['id_ruangan'] . '">' . htmlspecialchars($r_option['nama_ruangan']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="jumlah">Jumlah:</label>
                        <input type="number" name="jumlah" placeholder="Jumlah Barang" class="form-control" id="jumlah" required>
                    </div>
                    <div class="form-group">
                        <label for="alasan">Alasan:</label>
                        <textarea name="alasan" placeholder="Alasan Pemusnahan" class="form-control" id="alasan" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="addpemusnahan">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Skrip JavaScript yang spesifik untuk halaman ini
?>
<script>
    function filterTable() {
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("myTable");
        tr = table.getElementsByTagName("tr");
        var noDataMessage = document.getElementById("noDataMessage");
        var foundVisibleRow = false;

        // Dimulai dari i=1 untuk melewati thead
        for (i = 0; i < tr.length; i++) {
            if (tr[i].parentNode.tagName === 'TBODY') {
                var foundInRow = false;
                // Kolom yang dicari: Nama Barang Rusak (indeks 1), Ruangan (indeks 2), Alasan (indeks 4)
                // Indeks berubah karena kolom ID Rusak dihilangkan
                const searchColumns = [1, 2, 4];
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
                fetch('pemusnahan_suggestions.php?query=' + encodeURIComponent(inputVal))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        var datalist = document.getElementById('pemusnahanSuggestions');
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
                document.getElementById('pemusnahanSuggestions').innerHTML = '';
            }
        }, 300);
    });

    document.addEventListener('DOMContentLoaded', filterTable);
    document.getElementById("searchInput").addEventListener("change", filterTable);
</script>

<?php
// Include footer.php di akhir. Ini akan menutup semua tag HTML yang dibuka di sidebar.php
include 'footer.php';
?>