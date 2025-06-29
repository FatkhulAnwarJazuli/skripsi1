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

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Penerimaan Barang</h6>
            <a href="report/rpenerimaan.php" target="_blank" class="btn btn-info">
                <i class="fas fa-print"></i> Cetak Laporan
            </a>
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
                            <th>Nama Barang</th> 
                            <th>Tanggal Terima</th>
                            <th>Kondisi Barang</th>
                            <th>Pegawai Penerima</th>
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
                                $nama_barang = $data['nama_barang']; 
                                $tanggal_terima = $data['tanggal_terima'];
                                $kondisi_barang = $data['kondisi_barang'];
                                $id_pegawai = $data['id_pegawai'];
                                $nama_pegawai = $data['nama_pegawai']; 
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= htmlspecialchars($tanggal_pemesanan ? $tanggal_pemesanan : 'N/A') ?></td>
                                    <td><?= htmlspecialchars($nama_barang ? $nama_barang : 'N/A') ?></td> 
                                    <td><?= htmlspecialchars($tanggal_terima) ?></td>
                                    <td><?= htmlspecialchars($kondisi_barang) ?></td>
                                    <td><?= htmlspecialchars($nama_pegawai ? $nama_pegawai : 'N/A') ?></td>
                                </tr>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            // Sesuaikan colspan karena sekarang ada 5 kolom yang ditampilkan (No, Tgl Pemesanan, Nama Barang, Tgl Terima, Kondisi, Pegawai Penerima)
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