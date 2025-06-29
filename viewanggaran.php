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
// Query untuk total data harus sama dengan query utama (JOINs) agar paginasi akurat
$total_data_query = mysqli_query($conn, "
    SELECT COUNT(a.id_anggaran) AS total
    FROM anggaran a
    LEFT JOIN pemesanan pm ON a.id_pemesanan = pm.id_pemesanan
    LEFT JOIN pengajuan pj ON pm.id_pengajuan = pj.id_pengajuan
");
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

// Ambil data anggaran dengan JOIN ke tabel pemesanan dan pengajuan
$ambilsemuadatanya = mysqli_query($conn, "
    SELECT
        a.id_anggaran,
        a.nomor_anggaran,
        a.total,
        pj.nama_barang,
        pj.jumlah AS jumlah_barang_diajukan,
        pm.id_pemesanan
    FROM
        anggaran a
    LEFT JOIN
        pemesanan pm ON a.id_pemesanan = pm.id_pemesanan
    LEFT JOIN
        pengajuan pj ON pm.id_pengajuan = pj.id_pengajuan
    ORDER BY
        a.id_anggaran DESC
    LIMIT $start, $limit
");

// Ambil data untuk dropdown di modal (Pemesanan) - ini tetap ada jika diperlukan untuk tampilan data yang terkait meskipun CRUD dihapus
$pemesanan_options = mysqli_query($conn, "SELECT id_pemesanan, tanggal_pemesanan FROM pemesanan ORDER BY tanggal_pemesanan DESC");

?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Anggaran</h6>
            <div>
                <button type="button" class="btn btn-info" onclick="window.open('report/ranggaran.php', '_blank');">
                    <i class="fas fa-print"></i> Cetak Data
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari anggaran..." list="anggaranSuggestions">
                    <datalist id="anggaranSuggestions"></datalist>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="myTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor Anggaran</th>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Total Anggaran</th>
                            </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                $id_anggaran = $data['id_anggaran'];
                                $nomor_anggaran = htmlspecialchars($data['nomor_anggaran']);
                                $nama_barang = htmlspecialchars($data['nama_barang']);
                                $jumlah_barang_diajukan = htmlspecialchars($data['jumlah_barang_diajukan']);
                                $total = number_format($data['total'], 0, ',', '.');
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= $nomor_anggaran ?></td>
                                    <td><?= $nama_barang ?></td>
                                    <td><?= $jumlah_barang_diajukan ?></td>
                                    <td>Rp. <?= $total ?></td>
                                    </tr>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            echo '<tr><td colspan="5" class="text-center">Belum ada data anggaran.</td></tr>';
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
// Modal Tambah Anggaran dihapus sepenuhnya.
// Modal Edit Anggaran juga dihapus dari dalam loop.
?>

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
        var foundVisibleRow = false; // Flag untuk melacak apakah ada baris yang terlihat

        // Loop melalui semua baris tabel, dan sembunyikan yang tidak cocok dengan kueri pencarian
        for (i = 0; i < tr.length; i++) {
            // Lewati baris header dan footer (thead, tfoot)
            if (tr[i].parentNode.tagName === 'TBODY') {
                var foundInRow = false;
                // Kolom yang relevan untuk pencarian:
                // 1: Nomor Anggaran
                // 2: Nama Barang
                // 3: Jumlah
                // 4: Total Anggaran (tanpa "Rp. ")
                for (j = 1; j <= 4; j++) {
                    td = tr[i].cells[j];
                    if (td) {
                        txtValue = td.textContent || td.innerText;
                        // Khusus untuk kolom Total Anggaran, hapus "Rp. " agar pencarian angka tetap berfungsi
                        if (j === 4) {
                            txtValue = txtValue.replace('Rp. ', '');
                        }
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
    document.getElementById("searchInput").addEventListener("input", filterTable); // Juga untuk event input

    // Event listener untuk mengambil saran dari database secara real-time (AJAX)
    // Anda perlu membuat file baru: anggaran_suggestions.php
    let debounceTimer;
    document.getElementById("searchInput").addEventListener("input", function() {
        clearTimeout(debounceTimer);
        const inputVal = this.value;

        debounceTimer = setTimeout(() => {
            if (inputVal.length >= 2) {
                // Buat file 'anggaran_suggestions.php' yang serupa dengan 'barang_suggestions.php'
                // Namun, query-nya harus mengambil saran dari tabel anggaran, pemesanan, pengajuan
                fetch('anggaran_suggestions.php?query=' + encodeURIComponent(inputVal))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        var datalist = document.getElementById('anggaranSuggestions');
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
                document.getElementById('anggaranSuggestions').innerHTML = '';
            }
        }, 300);
    });

    // Panggil filterTable() saat halaman dimuat pertama kali untuk menangani kasus kosong awal
    document.addEventListener('DOMContentLoaded', filterTable);

    // Opsional: Jika Anda ingin memicu filter ulang ketika memilih saran dari datalist
    document.getElementById("searchInput").addEventListener("change", filterTable);
</script>

<?php
// Include footer.php di akhir. Ini akan menutup semua tag HTML yang dibuka di sidebar.php
// dan menyertakan skrip JavaScript global.
include 'footer.php';
?>