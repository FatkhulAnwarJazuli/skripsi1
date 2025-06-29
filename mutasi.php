<?php
session_start();
include 'sidebar.php'; // Termasuk head, body, sidebar
include_once "function.php"; // Pastikan file ini berisi koneksi $conn

// Logika Paginasi (opsional, jika Anda ingin paginasi di mutasi juga)
// Untuk saat ini, kita tidak menerapkan paginasi di sini untuk menjaga fokus pada mutasi
// $limit = 10;
// $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
// ... (Logika paginasi seperti di barang.php jika diperlukan) ...

// --- Tangani Operasi CRUD ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        $id_barang = htmlspecialchars($_POST['id_barang']);
        $id_ruangan_asal = htmlspecialchars($_POST['id_ruangan_asal']);
        $id_ruangan_tujuan = htmlspecialchars($_POST['id_ruangan_tujuan']);
        $jumlah = htmlspecialchars($_POST['jumlah']);

        // Validasi: Ruangan asal dan tujuan tidak boleh sama
        if ($id_ruangan_asal == $id_ruangan_tujuan) {
            echo '<script>alert("Ruangan Asal dan Ruangan Tujuan tidak boleh sama!"); window.location.href="mutasi.php";</script>';
            exit();
        }

        // Cek stok barang di ruangan asal
        $sql_cek_stok_asal = "SELECT jumlah FROM inventaris WHERE id_barang = '$id_barang' AND id_ruangan = '$id_ruangan_asal'";
        $res_cek_stok_asal = mysqli_query($conn, $sql_cek_stok_asal);

        if (mysqli_num_rows($res_cek_stok_asal) > 0) {
            $row_stok_asal = mysqli_fetch_assoc($res_cek_stok_asal);
            $stok_saat_ini_asal = $row_stok_asal['jumlah'];

            if ($stok_saat_ini_asal >= $jumlah) {
                // Update stok di ruangan asal (kurangi)
                $sql_update_asal = "UPDATE inventaris SET jumlah = jumlah - '$jumlah' WHERE id_barang = '$id_barang' AND id_ruangan = '$id_ruangan_asal'";
                mysqli_query($conn, $sql_update_asal);

                // Tambahkan atau update stok di ruangan tujuan
                $sql_cek_stok_tujuan = "SELECT jumlah FROM inventaris WHERE id_barang = '$id_barang' AND id_ruangan = '$id_ruangan_tujuan'";
                $res_cek_stok_tujuan = mysqli_query($conn, $sql_cek_stok_tujuan);

                if (mysqli_num_rows($res_cek_stok_tujuan) > 0) {
                    // Jika barang sudah ada di ruangan tujuan, update jumlah
                    $sql_update_tujuan = "UPDATE inventaris SET jumlah = jumlah + '$jumlah' WHERE id_barang = '$id_barang' AND id_ruangan = '$id_ruangan_tujuan'";
                    mysqli_query($conn, $sql_update_tujuan);
                } else {
                    // Jika barang belum ada di ruangan tujuan, tambahkan baru
                    // Perhatikan: tanggal_inventaris akan menggunakan CURRENT_DATE
                    $sql_insert_tujuan = "INSERT INTO inventaris (id_barang, id_ruangan, jumlah, tanggal_inventaris) VALUES ('$id_barang', '$id_ruangan_tujuan', '$jumlah', CURRENT_DATE())";
                    mysqli_query($conn, $sql_insert_tujuan);
                }

                // Tambahkan catatan mutasi
                // Asumsi ada kolom tanggal_mutasi di tabel mutasi jika Anda ingin merekam waktu mutasi
                $sql = "INSERT INTO mutasi (id_barang, id_ruangan_asal, id_ruangan, jumlah) VALUES ('$id_barang', '$id_ruangan_asal', '$id_ruangan_tujuan', '$jumlah')";
                if (mysqli_query($conn, $sql)) {
                    echo '<script>alert("Mutasi berhasil ditambahkan."); window.location.href="mutasi.php";</script>';
                } else {
                    echo '<script>alert("Error: ' . mysqli_error($conn) . '"); window.location.href="mutasi.php";</script>';
                }
            } else {
                echo '<script>alert("Jumlah barang di ruangan asal tidak mencukupi. Stok tersedia: ' . $stok_saat_ini_asal . '"); window.location.href="mutasi.php";</script>';
            }
        } else {
            echo '<script>alert("Barang tidak ditemukan di ruangan asal atau stok 0."); window.location.href="mutasi.php";</script>';
        }
        exit();
    } elseif (isset($_POST['edit'])) {
        $id_mutasi = htmlspecialchars($_POST['id_mutasi']);
        $id_barang_new = htmlspecialchars($_POST['id_barang']);
        $id_ruangan_asal_new = htmlspecialchars($_POST['id_ruangan_asal']);
        $id_ruangan_tujuan_new = htmlspecialchars($_POST['id_ruangan_tujuan']);
        $jumlah_new = htmlspecialchars($_POST['jumlah']);

        // Validasi: Ruangan asal dan tujuan tidak boleh sama
        if ($id_ruangan_asal_new == $id_ruangan_tujuan_new) {
            echo '<script>alert("Ruangan Asal dan Ruangan Tujuan tidak boleh sama!"); window.location.href="mutasi.php";</script>';
            exit();
        }

        // Ambil data mutasi lama untuk mengembalikan stok
        $sql_old_mutasi = "SELECT id_barang, id_ruangan_asal, id_ruangan, jumlah FROM mutasi WHERE id_mutasi = '$id_mutasi'";
        $res_old_mutasi = mysqli_query($conn, $sql_old_mutasi);
        $old_mutasi = mysqli_fetch_assoc($res_old_mutasi);

        $old_id_barang = $old_mutasi['id_barang'];
        $old_id_ruangan_asal = $old_mutasi['id_ruangan_asal'];
        $old_id_ruangan_tujuan = $old_mutasi['id_ruangan'];
        $old_jumlah = $old_mutasi['jumlah'];

        // --- Proses pengembalian stok lama (sebelum perubahan) ---
        // Kembalikan jumlah ke ruangan asal lama
        $sql_kembalikan_asal = "UPDATE inventaris SET jumlah = jumlah + '$old_jumlah' WHERE id_barang = '$old_id_barang' AND id_ruangan = '$old_id_ruangan_asal'";
        mysqli_query($conn, $sql_kembalikan_asal);

        // Kurangi jumlah dari ruangan tujuan lama
        $sql_kurangi_tujuan = "UPDATE inventaris SET jumlah = jumlah - '$old_jumlah' WHERE id_barang = '$old_id_barang' AND id_ruangan = '$old_id_ruangan_tujuan'";
        mysqli_query($conn, $sql_kurangi_tujuan);
        // --- Akhir proses pengembalian stok lama ---

        // --- Proses penyesuaian stok baru ---
        // Cek stok di ruangan asal baru
        $sql_cek_stok_asal_new = "SELECT jumlah FROM inventaris WHERE id_barang = '$id_barang_new' AND id_ruangan = '$id_ruangan_asal_new'";
        $res_cek_stok_asal_new = mysqli_query($conn, $sql_cek_stok_asal_new);

        if (mysqli_num_rows($res_cek_stok_asal_new) > 0) {
            $row_stok_asal_new = mysqli_fetch_assoc($res_cek_stok_asal_new);
            $stok_saat_ini_asal_new = $row_stok_asal_new['jumlah'];

            // Jika barang baru dan ruangan asal baru sama dengan yang lama, tidak perlu validasi stok tambahan
            // Cukup pastikan jumlah baru tidak melebihi stok yang ada setelah pengembalian lama
            if ($id_barang_new == $old_id_barang && $id_ruangan_asal_new == $old_id_ruangan_asal) {
                // Stok yang tersedia di ruangan asal adalah stok saat ini_asal_new (yang sudah ditambah old_jumlah)
                // Jadi, cek apakah stok saat ini (setelah dikurangi old_jumlah) cukup untuk jumlah_new
                // atau lebih sederhana, cek jika (stok saat ini asal_new + old_jumlah - jumlah_new) >= 0
                if (($stok_saat_ini_asal_new - $jumlah_new) < 0) { // Jika setelah dikurangi jumlah baru jadi minus
                    echo '<script>alert("Jumlah barang di ruangan asal yang baru tidak mencukupi (setelah pengembalian stok lama)."); window.location.href="mutasi.php";</script>';
                    exit();
                }
            } elseif ($stok_saat_ini_asal_new < $jumlah_new) { // Jika barang/ruangan asal berubah
                echo '<script>alert("Jumlah barang di ruangan asal yang baru tidak mencukupi. Stok tersedia: ' . $stok_saat_ini_asal_new . '"); window.location.href="mutasi.php";</script>';
                exit();
            }

            // Update stok di ruangan asal baru (kurangi)
            $sql_update_asal_new = "UPDATE inventaris SET jumlah = jumlah - '$jumlah_new' WHERE id_barang = '$id_barang_new' AND id_ruangan = '$id_ruangan_asal_new'";
            mysqli_query($conn, $sql_update_asal_new);

            // Tambahkan atau update stok di ruangan tujuan baru
            $sql_cek_stok_tujuan_new = "SELECT jumlah FROM inventaris WHERE id_barang = '$id_barang_new' AND id_ruangan = '$id_ruangan_tujuan_new'";
            $res_cek_stok_tujuan_new = mysqli_query($conn, $sql_cek_stok_tujuan_new);

            if (mysqli_num_rows($res_cek_stok_tujuan_new) > 0) {
                // Jika barang sudah ada di ruangan tujuan baru, update jumlah
                $sql_update_tujuan_new = "UPDATE inventaris SET jumlah = jumlah + '$jumlah_new' WHERE id_barang = '$id_barang_new' AND id_ruangan = '$id_ruangan_tujuan_new'";
                mysqli_query($conn, $sql_update_tujuan_new);
            } else {
                // Jika barang belum ada di ruangan tujuan baru, tambahkan baru
                $sql_insert_tujuan_new = "INSERT INTO inventaris (id_barang, id_ruangan, jumlah, tanggal_inventaris) VALUES ('$id_barang_new', '$id_ruangan_tujuan_new', '$jumlah_new', CURRENT_DATE())";
                mysqli_query($conn, $sql_insert_tujuan_new);
            }

            // Update catatan mutasi
            $sql = "UPDATE mutasi SET id_barang='$id_barang_new', id_ruangan_asal='$id_ruangan_asal_new', id_ruangan='$id_ruangan_tujuan_new', jumlah='$jumlah_new' WHERE id_mutasi='$id_mutasi'";
            if (mysqli_query($conn, $sql)) {
                echo '<script>alert("Mutasi berhasil diupdate."); window.location.href="mutasi.php";</script>';
            } else {
                echo '<script>alert("Error: ' . mysqli_error($conn) . '"); window.location.href="mutasi.php";</script>';
            }
        } else {
            echo '<script>alert("Barang tidak ditemukan di ruangan asal yang baru."); window.location.href="mutasi.php";</script>';
        }
        exit();

    } elseif (isset($_POST['delete'])) {
        $id_mutasi = htmlspecialchars($_POST['id_mutasi']);

        // Ambil data mutasi yang akan dihapus untuk mengembalikan stok
        $sql_get_mutasi = "SELECT id_barang, id_ruangan_asal, id_ruangan, jumlah FROM mutasi WHERE id_mutasi = '$id_mutasi'";
        $result_get_mutasi = mysqli_query($conn, $sql_get_mutasi);
        $row_mutasi = mysqli_fetch_assoc($result_get_mutasi);

        $id_barang_del = $row_mutasi['id_barang'];
        $id_ruangan_asal_del = $row_mutasi['id_ruangan_asal'];
        $id_ruangan_tujuan_del = $row_mutasi['id_ruangan'];
        $jumlah_del = $row_mutasi['jumlah'];

        // Kembalikan jumlah ke ruangan asal (tambah kembali)
        $sql_update_stok_asal_del = "UPDATE inventaris SET jumlah = jumlah + '$jumlah_del' WHERE id_barang = '$id_barang_del' AND id_ruangan = '$id_ruangan_asal_del'";
        mysqli_query($conn, $sql_update_stok_asal_del);

        // Kurangi jumlah dari ruangan tujuan (kurangi kembali)
        $sql_update_stok_tujuan_del = "UPDATE inventaris SET jumlah = jumlah - '$jumlah_del' WHERE id_barang = '$id_barang_del' AND id_ruangan = '$id_ruangan_tujuan_del'";
        mysqli_query($conn, $sql_update_stok_tujuan_del);

        // Hapus mutasi
        $sql = "DELETE FROM mutasi WHERE id_mutasi='$id_mutasi'";
        if (mysqli_query($conn, $sql)) {
            echo '<script>alert("Mutasi berhasil dihapus."); window.location.href="mutasi.php";</script>';
        } else {
            echo '<script>alert("Error: ' . mysqli_error($conn) . '"); window.location.href="mutasi.php";</script>';
        }
        exit();
    }
}

// Fetch data for display
$sql = "SELECT m.id_mutasi, b.namabarang,
            r_asal.nama_ruangan AS nama_ruangan_asal,
            r_tujuan.nama_ruangan AS nama_ruangan_tujuan,
            m.jumlah,
            m.id_barang,
            m.id_ruangan_asal,
            m.id_ruangan AS id_ruangan_tujuan -- Alias untuk id_ruangan yang adalah tujuan
        FROM mutasi m
        JOIN barang b ON m.id_barang = b.id_barang
        JOIN ruangan r_tujuan ON m.id_ruangan = r_tujuan.id_ruangan
        JOIN ruangan r_asal ON m.id_ruangan_asal = r_asal.id_ruangan";

// Apply search filter
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $sql .= " WHERE b.namabarang LIKE '%$search%'
              OR r_asal.nama_ruangan LIKE '%$search%'
              OR r_tujuan.nama_ruangan LIKE '%$search%'";
}
$sql .= " ORDER BY m.id_mutasi DESC";
$result = mysqli_query($conn, $sql);

// Fetch barang and ruangan for dropdowns (ensure these queries are fresh for each modal)
$barang_query = mysqli_query($conn, "SELECT id_barang, namabarang FROM barang ORDER BY namabarang ASC");
$ruangan_query = mysqli_query($conn, "SELECT id_ruangan, nama_ruangan FROM ruangan ORDER BY nama_ruangan ASC");
?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Mutasi Barang</h6>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addMutasiModal">
                <i class="fas fa-plus"></i> Tambah Data
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari Mutasi..." list="mutasiSuggestions">
                    <datalist id="mutasiSuggestions"></datalist>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="myTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Ruangan Asal</th>
                            <th>Ruangan Tujuan</th>
                            <th>Jumlah</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && mysqli_num_rows($result) > 0) {
                            $i = 1; // Mulai nomor dari 1
                            while ($data = mysqli_fetch_assoc($result)) {
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= htmlspecialchars($data['namabarang']) ?></td>
                                    <td><?= htmlspecialchars($data['nama_ruangan_asal']) ?></td>
                                    <td><?= htmlspecialchars($data['nama_ruangan_tujuan']) ?></td>
                                    <td><?= htmlspecialchars($data['jumlah']) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm mr-1 editBtn"
                                                data-id_mutasi="<?= $data['id_mutasi'] ?>"
                                                data-id_barang="<?= $data['id_barang'] ?>"
                                                data-id_ruangan_asal="<?= $data['id_ruangan_asal'] ?>"
                                                data-id_ruangan_tujuan="<?= $data['id_ruangan_tujuan'] ?>"
                                                data-jumlah="<?= $data['jumlah'] ?>"
                                                data-toggle="modal" data-target="#editMutasiModal">
                                            <i class="fa fa-edit"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm deleteBtn"
                                                data-id_mutasi="<?= $data['id_mutasi'] ?>"
                                                data-toggle="modal" data-target="#deleteMutasiModal">
                                            <i class="fa fa-trash"></i> Hapus
                                        </button>
                                    </td>
                                </tr>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            echo '<tr><td colspan="6" class="text-center">Belum ada data mutasi.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
                <div id="noDataMessage" class="alert alert-warning text-center" style="display: none;">
                    Data tidak ditemukan.
                </div>
            </div>
            </div>
    </div>
</div>

<?php
// Modals
?>

<div class="modal fade" id="addMutasiModal" tabindex="-1" role="dialog" aria-labelledby="addMutasiModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMutasiModalLabel">Tambah Mutasi Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="id_barang">Nama Barang:</label>
                        <select class="form-control" id="id_barang" name="id_barang" required>
                            <option value="">Pilih Barang</option>
                            <?php
                            // Reset pointer atau fetch ulang jika diperlukan
                            mysqli_data_seek($barang_query, 0); // Kembali ke awal hasil query
                            while ($b = mysqli_fetch_assoc($barang_query)) {
                                echo "<option value='{$b['id_barang']}'>{$b['namabarang']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_ruangan_asal">Ruangan Asal:</label>
                        <select class="form-control" id="id_ruangan_asal" name="id_ruangan_asal" required>
                            <option value="">Pilih Ruangan Asal</option>
                            <?php
                            mysqli_data_seek($ruangan_query, 0);
                            while ($r = mysqli_fetch_assoc($ruangan_query)) {
                                echo "<option value='{$r['id_ruangan']}'>{$r['nama_ruangan']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_ruangan_tujuan">Ruangan Tujuan:</label>
                        <select class="form-control" id="id_ruangan_tujuan" name="id_ruangan_tujuan" required>
                            <option value="">Pilih Ruangan Tujuan</option>
                            <?php
                            mysqli_data_seek($ruangan_query, 0);
                            while ($r = mysqli_fetch_assoc($ruangan_query)) {
                                echo "<option value='{$r['id_ruangan']}'>{$r['nama_ruangan']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="jumlah">Jumlah:</label>
                        <input type="number" name="jumlah" placeholder="Jumlah" class="form-control" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="add" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editMutasiModal" tabindex="-1" role="dialog" aria-labelledby="editMutasiModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMutasiModalLabel">Edit Data Mutasi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_id_mutasi" name="id_mutasi">
                    <div class="form-group">
                        <label for="edit_id_barang">Nama Barang:</label>
                        <select class="form-control" id="edit_id_barang" name="id_barang" required>
                            <option value="">Pilih Barang</option>
                            <?php
                            mysqli_data_seek($barang_query, 0);
                            while ($b = mysqli_fetch_assoc($barang_query)) {
                                echo "<option value='{$b['id_barang']}'>{$b['namabarang']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_id_ruangan_asal">Ruangan Asal:</label>
                        <select class="form-control" id="edit_id_ruangan_asal" name="id_ruangan_asal" required>
                            <option value="">Pilih Ruangan Asal</option>
                            <?php
                            mysqli_data_seek($ruangan_query, 0);
                            while ($r = mysqli_fetch_assoc($ruangan_query)) {
                                echo "<option value='{$r['id_ruangan']}'>{$r['nama_ruangan']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_id_ruangan_tujuan">Ruangan Tujuan:</label>
                        <select class="form-control" id="edit_id_ruangan_tujuan" name="id_ruangan_tujuan" required>
                            <option value="">Pilih Ruangan Tujuan</option>
                            <?php
                            mysqli_data_seek($ruangan_query, 0);
                            while ($r = mysqli_fetch_assoc($ruangan_query)) {
                                echo "<option value='{$r['id_ruangan']}'>{$r['nama_ruangan']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_jumlah">Jumlah:</label>
                        <input type="number" name="jumlah" placeholder="Jumlah" class="form-control" id="edit_jumlah" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="edit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteMutasiModal" tabindex="-1" role="dialog" aria-labelledby="deleteMutasiModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteMutasiModalLabel">Hapus Data Mutasi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="delete_id_mutasi" name="id_mutasi">
                    <p>Apakah Anda yakin ingin menghapus data mutasi ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" name="delete" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Skrip JavaScript yang spesifik untuk halaman ini
?>
<script src="js/jquery-3.7.1.min.js"></script> <script src="js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        // Edit button click handler
        $('.editBtn').on('click', function() {
            $('#edit_id_mutasi').val($(this).data('id_mutasi'));
            $('#edit_id_barang').val($(this).data('id_barang'));
            $('#edit_id_ruangan_asal').val($(this).data('id_ruangan_asal'));
            $('#edit_id_ruangan_tujuan').val($(this).data('id_ruangan_tujuan'));
            $('#edit_jumlah').val($(this).data('jumlah'));
        });

        // Delete button click handler
        $('.deleteBtn').on('click', function() {
            $('#delete_id_mutasi').val($(this).data('id_mutasi'));
        });

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
                    // Loop melalui semua kolom yang relevan (Nama Barang, Ruangan Asal, Ruangan Tujuan)
                    // Dimulai dari indeks 1 (Nama Barang) hingga indeks 3 (Ruangan Tujuan)
                    for (j = 1; j <= 3; j++) { // Kolom 1 (Barang), 2 (Asal), 3 (Tujuan)
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
        document.getElementById("searchInput").addEventListener("input", filterTable);

        // Event listener untuk mengambil saran dari database secara real-time (AJAX)
        let debounceTimer;
        document.getElementById("searchInput").addEventListener("input", function() {
            clearTimeout(debounceTimer);
            const inputVal = this.value;

            debounceTimer = setTimeout(() => {
                if (inputVal.length >= 2) {
                    // Pastikan Anda memiliki file 'mutasi_suggestions.php'
                    fetch('mutasi_suggestions.php?query=' + encodeURIComponent(inputVal))
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            var datalist = $('#mutasiSuggestions');
                            datalist.empty();
                            $.each(data, function(index, value) {
                                datalist.append($('<option>').attr('value', value));
                            });
                        })
                        .catch(error => console.error('Error fetching suggestions:', error));
                } else {
                    $('#mutasiSuggestions').empty();
                }
            }, 300);
        });

        // Panggil filterTable() saat halaman dimuat pertama kali
        document.addEventListener('DOMContentLoaded', filterTable);
        document.getElementById("searchInput").addEventListener("change", filterTable);
    });
</script>

<?php
include 'footer.php'; // Tutup semua tag HTML yang dibuka di sidebar.php
?>