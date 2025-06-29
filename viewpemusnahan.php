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

// --- Tidak ada lagi Tangani Operasi CRUD di sini ---

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

// Data untuk dropdown (jika masih diperlukan untuk filter atau saran pencarian)
// $ambil_barang_rusak = mysqli_query($conn, "SELECT br.id_rusak, b.namabarang FROM barang_rusak br JOIN barang b ON br.id_barang = b.id_barang WHERE br.status = 'Rusak'");
// $ambil_ruangan = mysqli_query($conn, "SELECT id_ruangan, nama_ruangan FROM ruangan");

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Pemusnahan Barang</h6>
            <div>
                <a href="report/rpemusnahan.php" target="_blank" class="btn btn-info ml-2">
                    <i class="fas fa-print"></i> Cetak Laporan
                </a>
            </div>
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
                            </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $namabarang_rusak = $data['namabarang']; // Nama barang dari tabel barang
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
                                </tr>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            echo '<tr><td colspan="5" class="text-center">Belum ada data pemusnahan.</td></tr>'; // colspan disesuaikan
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
                const searchColumns = [1, 2, 4]; // Indeks disesuaikan setelah kolom 'Opsi' dihapus
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
    // Jika Anda tidak ingin menampilkan saran saat pencarian, bagian ini bisa dihapus.
    let debounceTimer;
    document.getElementById("searchInput").addEventListener("input", function() {
        clearTimeout(debounceTimer);
        const inputVal = this.value;

        debounceTimer = setTimeout(() => {
            if (inputVal.length >= 2) {
                // Asumsi pemusnahan_suggestions.php masih ada dan berfungsi
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