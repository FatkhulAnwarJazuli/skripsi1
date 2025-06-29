<?php
// FILE: viewinventaris.php

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
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_inventaris) AS total FROM inventaris");
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

// --- Hapus semua logika CRUD di sini, kecuali jika ada kebutuhan khusus untuk "Read" yang berbeda dari paginasi utama. ---
// Bagian ini sekarang hanya akan mengambil data untuk ditampilkan.

// Ambil semua data inventaris untuk halaman saat ini dengan JOIN ke tabel barang dan ruangan
$ambilsemuadatanya = mysqli_query($conn, "
    SELECT
        i.id_inventaris,
        i.id_barang,
        i.id_ruangan,
        i.tanggal_inventaris,
        b.namabarang,
        b.merk,
        b.jumlah,
        r.nama_ruangan
    FROM
        inventaris i
    LEFT JOIN
        barang b ON i.id_barang = b.id_barang
    LEFT JOIN
        ruangan r ON i.id_ruangan = r.id_ruangan
    ORDER BY
        i.id_inventaris DESC
    LIMIT $start, $limit
");

// Ambil data barang dan ruangan untuk dropdown di modal (meskipun modalnya dihapus, query ini tetap ada jika sewaktu-waktu dibutuhkan)
// Anda bisa menghapus dua query di bawah ini jika yakin tidak ada penggunaan lain di halaman ini.
$barang_options = mysqli_query($conn, "SELECT id_barang, namabarang, merk, jumlah FROM barang ORDER BY namabarang ASC");
$ruangan_options = mysqli_query($conn, "SELECT id_ruangan, nama_ruangan FROM ruangan ORDER BY nama_ruangan ASC");

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Inventaris Barang</h6>
            <div>
                <a href="report/rinventaris.php" target="_blank" class="btn btn-info">
                    <i class="fas fa-print"></i> Cetak Data
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari inventaris..." list="inventarisSuggestions">
                    <datalist id="inventarisSuggestions"></datalist>
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
                            <th>Ruangan</th>
                            <th>Tanggal Inventaris</th>
                            </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $id_inventaris = $data['id_inventaris'];
                                $namabarang = $data['namabarang'];
                                $merk = $data['merk'];
                                $jumlah = $data['jumlah'];
                                $nama_ruangan = $data['nama_ruangan'];
                                $tanggal_inventaris = $data['tanggal_inventaris'];
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= htmlspecialchars($namabarang ? $namabarang : 'N/A') ?></td>
                                    <td><?= htmlspecialchars($merk ? $merk : 'N/A') ?></td>
                                    <td><?= htmlspecialchars($jumlah ? $jumlah : 'N/A') ?></td>
                                    <td><?= htmlspecialchars($nama_ruangan ? $nama_ruangan : 'N/A') ?></td>
                                    <td><?= htmlspecialchars($tanggal_inventaris) ?></td>
                                    </tr>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            echo '<tr><td colspan="6" class="text-center">Belum ada data inventaris barang.</td></tr>';
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
                // Nama Barang (1), Merk (2), Jumlah (3), Ruangan (4), Tanggal Inventaris (5)
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
                // Anda mungkin perlu membuat file 'inventaris_suggestions.php' untuk ini.
                fetch('inventaris_suggestions.php?query=' + encodeURIComponent(inputVal))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        var datalist = document.getElementById('inventarisSuggestions');
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
                document.getElementById('inventarisSuggestions').innerHTML = '';
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