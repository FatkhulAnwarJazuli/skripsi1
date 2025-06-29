<?php
// FILE: viewbarang.php

// Sertakan sidebar.php. Ini akan menangani session_start(), head, body, sidebar, dan topbar.
// Asumsi sidebar.php ada di root project.
include 'sidebar.php';

// Pastikan file function.php Anda menginisialisasi koneksi database $conn.
// Asumsi function.php ada di root project.
include_once "function.php";

// Pastikan koneksi database $conn sudah tersedia dari function.php
if (!isset($conn) || !$conn) {
    die("Koneksi database tidak tersedia. Pastikan function.php sudah benar dan menginisialisasi \$conn.");
}

// --- Logika Paginasi ---

$limit = 5; // Batas data per halaman
// Ambil nomor halaman dari URL, pastikan itu integer dan minimal 1
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Hitung total data untuk paginasi
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_barang) AS total FROM barang");
if ($total_data_query) {
    $total_data = mysqli_fetch_assoc($total_data_query)['total'];
} else {
    $total_data = 0; // Jika query gagal, anggap tidak ada data
    error_log("Error counting total barang: " . mysqli_error($conn)); // Log error
}
$total_pages = ceil($total_data / $limit);
$start_from = ($page - 1) * $limit;

// Query untuk mengambil data barang dengan paginasi
// Hanya ambil kolom yang relevan: id_barang, namabarang, merk, jumlah
$query = "SELECT id_barang, namabarang, merk, jumlah FROM barang ORDER BY id_barang DESC LIMIT $start_from, $limit";
$result = mysqli_query($conn, $query);

// Cek apakah query berhasil
if (!$result) {
    die("Query data barang gagal: " . mysqli_error($conn));
}
?>

<div class="container-fluid">

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Laporan Data Barang</h6>
            <form action="report/rbarang.php" method="POST" target="_blank">
                <button type="submit" class="btn btn-info">
                    <i class="fa fa-print"></i> Cetak Semua Data
                </button>
            </form>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="Cari barang..." list="barangSuggestions">
                <datalist id="barangSuggestions"></datalist>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" id="myTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Merk</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $nomor = $start_from + 1;
                        if (mysqli_num_rows($result) > 0) {
                            while ($data = mysqli_fetch_array($result)) {
                                ?>
                                <tr>
                                    <td><?= $nomor++; ?></td>
                                    <td><?= htmlspecialchars($data['namabarang']); ?></td>
                                    <td><?= htmlspecialchars($data['merk']); ?></td>
                                    <td><?= htmlspecialchars($data['jumlah']); ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data barang ditemukan.</td>
                            </tr>
                            <?php
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
                        <a class="page-link" href="?page=<?= $page - 1; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?= $page + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

</div>
<?php
// Sertakan footer.php di akhir. Ini akan menutup semua tag HTML yang dibuka di sidebar.php.
// Asumsi footer.php ada di root project.
include 'footer.php';
?>

<script>
    // Fungsi untuk memfilter tabel di sisi klien
    function filterTable() {
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("myTable"); // Pastikan ID tabel sama
        tr = table.getElementsByTagName("tr");
        var noDataMessage = document.getElementById("noDataMessage");
        var foundVisibleRow = false;

        // Loop melalui semua baris tabel, dan sembunyikan yang tidak cocok dengan kueri pencarian
        for (i = 0; i < tr.length; i++) {
            // Lewati baris header dan footer (thead, tfoot)
            if (tr[i].parentNode.tagName === 'TBODY') {
                var foundInRow = false;
                // Loop melalui semua kolom yang relevan (Nama Barang, Merk, Jumlah)
                // Dimulai dari indeks 1 (Nama Barang) dan berhenti sebelum kolom Opsi
                // Kolom di viewbarang.php sekarang: No (0), Nama Barang (1), Merk (2), Jumlah (3)
                for (j = 1; j < tr[i].cells.length; j++) { // Kolom 1, 2, 3
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

        // Tampilkan atau sembunyikan pesan "Data tidak ada"
        if (foundVisibleRow) {
            noDataMessage.style.display = "none";
        } else {
            noDataMessage.style.display = "block";
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
                // Pastikan Anda memiliki file 'barang_suggestions.php'
                // File ini akan mengembalikan daftar saran dalam format JSON
                fetch('barang_suggestions.php?query=' + encodeURIComponent(inputVal))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        var datalist = document.getElementById('barangSuggestions');
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
                document.getElementById('barangSuggestions').innerHTML = '';
            }
        }, 300);
    });

    // Panggil filterTable() saat halaman dimuat pertama kali untuk menangani kasus kosong awal
    document.addEventListener('DOMContentLoaded', filterTable);

    // Opsional: Jika Anda ingin memicu filter ulang ketika memilih saran dari datalist
    document.getElementById("searchInput").addEventListener("change", filterTable);
</script>
</body>
</html>