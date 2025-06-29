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

// --- Ambil Data Peminjaman ---
// Ambil semua data peminjaman untuk halaman saat ini dengan JOIN ke tabel barang dan pegawai
$ambilsemuadatanya = mysqli_query($conn, "
    SELECT 
        p.id_peminjaman,
        p.id_barang, 
        p.id_pegawai,
        p.jumlah_pinjam, 
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

// Query untuk dropdown (meskipun tidak digunakan lagi di halaman ini setelah CRUD dihapus, 
// tapi mungkin relevan untuk cetak atau keperluan lain di masa depan)
$barang_options = mysqli_query($conn, "SELECT id_barang, namabarang, merk, jumlah FROM barang ORDER BY namabarang ASC");
$pegawai_options = mysqli_query($conn, "SELECT id_pegawai, nama FROM pegawai ORDER BY nama ASC");

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Peminjaman Barang</h6>
            <div>
                <a href="report/rpeminjaman.php" target="_blank" class="btn btn-info">
                    <i class="fas fa-print"></i> Cetak Data
                </a>
            </div>
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
                            <th>Jumlah Dipinjam</th> 
                            <th>Peminjam</th>
                            <th>Tanggal Peminjaman</th>
                            <th>Tanggal Pengembalian</th>
                            </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $namabarang = $data['namabarang']; 
                                $merk = $data['merk']; 
                                $jumlah_pinjam = $data['jumlah_pinjam'];
                                $nama_pegawai = $data['nama_pegawai'];
                                $tanggal_peminjaman = $data['tanggal_peminjaman'];
                                $tanggal_pengembalian = $data['tanggal_pengembalian'];
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= htmlspecialchars($namabarang ? $namabarang : 'N/A') ?></td>
                                    <td><?= htmlspecialchars($merk ? $merk : 'N/A') ?></td>
                                    <td><?= htmlspecialchars($jumlah_pinjam ? $jumlah_pinjam : 'N/A') ?></td> 
                                    <td><?= htmlspecialchars($nama_pegawai ? $nama_pegawai : 'N/A') ?></td>
                                    <td><?= htmlspecialchars($tanggal_peminjaman) ?></td>
                                    <td><?= htmlspecialchars($tanggal_pengembalian ? $tanggal_pengembalian : 'Belum Dikembalikan') ?></td>
                                    </tr>
                        <?php
                            } 
                        } else {
                            // Sesuaikan colspan karena sekarang ada 7 kolom yang ditampilkan
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