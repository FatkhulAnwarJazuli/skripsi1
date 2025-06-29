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

// Ambil data untuk dropdown Barang (normal) - Tetap diperlukan untuk tampilan
$barang_data = [];
$sql_barang = "SELECT id_barang, namabarang, merk FROM barang ORDER BY namabarang ASC";
$result_barang = mysqli_query($conn, $sql_barang);
if ($result_barang && mysqli_num_rows($result_barang) > 0) {
    while($row = mysqli_fetch_assoc($result_barang)) {
        $barang_data[] = $row;
    }
}

// Ambil data untuk dropdown Barang Rusak (hanya yang statusnya 'Rusak') - Tetap diperlukan untuk tampilan
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
            <div>
                <a href="report/rpemeliharaan.php" target="_blank" class="btn btn-info">
                    <i class="fas fa-print"></i> Cetak Laporan
                </a>
            </div>
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
                                </tr>
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
// Modal Tambah Pemeliharaan Baru telah dihapus
?>

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