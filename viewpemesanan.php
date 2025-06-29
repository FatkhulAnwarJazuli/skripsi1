<?php
// FILE: viewpemesanan.php

// Sertakan sidebar.php. Ini akan menangani session_start(), head, body, sidebar, dan topbar.
include 'sidebar.php';

// Pastikan file function.php Anda menginisialisasi koneksi database $conn.
include_once "function.php";

// Pastikan koneksi database $conn sudah tersedia dari function.php
if (!isset($conn) || !$conn) {
    die("Koneksi database tidak tersedia. Pastikan function.php sudah benar dan menginisialisasi \$conn.");
}

// Set zona waktu untuk tanggal (Waktu Indonesia Tengah untuk Banjarmasin)
date_default_timezone_set('Asia/Makassar');

// --- Logika Paginasi ---

$limit = 5; // Batas data per halaman
// Ambil nomor halaman dari URL, pastikan itu integer dan minimal 1
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Hitung total data untuk paginasi dari tabel `pemesanan`
$total_data_query = mysqli_query($conn, "SELECT COUNT(id_pemesanan) AS total FROM pemesanan");
if ($total_data_query) {
    $total_data = mysqli_fetch_assoc($total_data_query)['total'];
} else {
    $total_data = 0; // Jika query gagal, anggap tidak ada data
    error_log("Error counting total pemesanan: " . mysqli_error($conn)); // Log error
}
$total_pages = ceil($total_data / $limit);
$start_from = ($page - 1) * $limit;

// Pastikan halaman tidak melebihi total halaman yang ada (untuk kasus data dihapus dan halaman jadi kosong)
if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
    $start_from = ($page - 1) * $limit; // Hitung ulang start_from
} elseif ($total_pages == 0) { // Jika tidak ada data sama sekali
    $page = 1;
    $start_from = 0;
}


// Ambil semua data pemesanan untuk halaman saat ini dengan join ke tabel pengajuan dan supplier
$ambilsemuadatanya = mysqli_query($conn, "SELECT p.*,
                                                 aj.nama_barang, aj.merk AS merk_barang, aj.jumlah AS jumlah_diajukan, aj.satuan, aj.pengaju,
                                                 s.nama_supplier
                                          FROM pemesanan p
                                          LEFT JOIN pengajuan aj ON p.id_pengajuan = aj.id_pengajuan
                                          LEFT JOIN supplier s ON p.id_supplier = s.id_supplier
                                          ORDER BY p.tanggal_pemesanan DESC
                                          LIMIT $start_from, $limit");

// Tidak perlu mengambil daftar pengajuan atau supplier karena form tambah/edit dihapus
// $daftar_pengajuan_belum_dipesan = mysqli_query($conn, "SELECT pa.id_pengajuan, pa.nama_barang, pa.merk, pa.jumlah, pa.satuan, pa.pengaju
//                                                        FROM pengajuan pa
//                                                        LEFT JOIN pemesanan p ON pa.id_pengajuan = p.id_pengajuan
//                                                        WHERE p.id_pemesanan IS NULL
//                                                        ORDER BY pa.tanggal_pengajuan DESC");

// $daftar_supplier = mysqli_query($conn, "SELECT id_supplier, nama_supplier FROM supplier ORDER BY nama_supplier ASC");


// Cek apakah query berhasil
if (!$ambilsemuadatanya) {
    die("Query data pemesanan gagal: " . mysqli_error($conn));
}
?>

<div class="container-fluid">

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Tabel Data Pemesanan</h6>
            <div>
                <form action="report/rpemesanan.php" method="POST" target="_blank" class="d-inline">
                    <button type="submit" class="btn btn-info">
                        <i class="fa fa-print"></i> Cetak Semua Data
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="searchInputPemesanan" class="form-control" placeholder="Cari pemesanan..." list="pemesananSuggestions">
                    <datalist id="pemesananSuggestions"></datalist>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered" id="myTablePemesanan" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Merk</th>
                            <th>Jumlah Diajukan</th>
                            <th>Satuan</th>
                            <th>Harga (Rp)</th>
                            <th>Pengaju</th>
                            <th>Supplier</th>
                            <th>Tanggal Pemesanan</th>
                            <th>Status</th>
                            </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($ambilsemuadatanya && mysqli_num_rows($ambilsemuadatanya) > 0) {
                            $i = $start_from + 1;
                            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                                // Data-data ini masih diambil untuk ditampilkan
                                $id_pemesanan = $data['id_pemesanan'];
                                $id_pengajuan_data = $data['id_pengajuan'];
                                $id_supplier_data = $data['id_supplier'];
                                $nama_barang_diajukan = $data['nama_barang'];
                                $merk_barang_diajukan = $data['merk_barang'];
                                $jumlah_diajukan = $data['jumlah_diajukan'];
                                $satuan_diajukan = $data['satuan'];
                                $harga = $data['harga'];
                                $pengaju = $data['pengaju'];
                                $nama_supplier = $data['nama_supplier'];
                                $tanggal_pemesanan = $data['tanggal_pemesanan'];
                                $status_pemesanan = $data['status'];
                        ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?= htmlspecialchars($nama_barang_diajukan) ?></td>
                                    <td><?= htmlspecialchars($merk_barang_diajukan) ?></td>
                                    <td><?= htmlspecialchars($jumlah_diajukan) ?></td>
                                    <td><?= htmlspecialchars($satuan_diajukan) ?></td>
                                    <td>Rp <?= htmlspecialchars(number_format($harga, 0, ',', '.')) ?></td>
                                    <td><?= htmlspecialchars($pengaju) ?></td>
                                    <td><?= htmlspecialchars($nama_supplier) ?></td>
                                    <td><?= htmlspecialchars($tanggal_pemesanan) ?></td>
                                    <td><?= htmlspecialchars($status_pemesanan) ?></td>
                                    </tr>

                                <?php
                                // Modal edit di sini juga dihapus
                                // <div class="modal fade" id="editPemesananModal<?= $id_pemesanan ?>
                        <?php
                            } // Akhir dari loop while
                        } else {
                            // Sesuaikan colspan jika kolom "Opsi" dihapus (sebelumnya 11, sekarang 10)
                            echo '<tr><td colspan="10" class="text-center" id="noDataMessagePemesanan">Belum ada data pemesanan.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
                <div id="noDataMessagePemesanan" class="alert alert-warning text-center" style="display: none;">
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
// Modal tambah di sini juga dihapus
// <div class="modal fade" id="addPemesananModal" tabindex="-1" role="dialog" aria-labelledby="addPemesananModalLabel" aria-hidden="true">
// ... (isi modal tambah) ...
// </div>
?>

<?php
// Include footer.php di akhir. Ini akan menutup semua tag HTML yang dibuka di sidebar.php
// dan menyertakan skrip JavaScript global.
include 'footer.php';
?>

<script>
    // Fungsi untuk memfilter tabel di sisi klien
    function filterTablePemesanan() {
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInputPemesanan");
        filter = input.value.toUpperCase();
        table = document.getElementById("myTablePemesanan");
        tr = table.getElementsByTagName("tr");
        var noDataMessage = document.getElementById("noDataMessagePemesanan");
        var foundVisibleRow = false;

        for (i = 0; i < tr.length; i++) {
            if (tr[i].parentNode.tagName === 'TBODY') {
                var foundInRow = false;
                // Kolom yang akan dicari: Nama Barang (indeks 1), Merk (indeks 2), Pengaju (indeks 6), Supplier (indeks 7), Status (indeks 9)
                // Sesuaikan indeks kolom ini jika tata letak berubah (tetap sama karena hanya kolom opsi yang dihapus)
                const searchColumns = [1, 2, 6, 7, 9];
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

        // Handle "No Data" message
        if (foundVisibleRow) {
            noDataMessage.style.display = "none";
        } else {
            // Check if "Belum ada data pemesanan." row exists initially
            // Sesuaikan colspan menjadi 10
            let initialNoDataRow = document.querySelector("#myTablePemesanan tbody td[colspan='10']");
            if (initialNoDataRow && initialNoDataRow.textContent.includes("Belum ada data pemesanan.")) {
                 initialNoDataRow.parentNode.style.display = ""; // Show initial message
                 noDataMessage.style.display = "none"; // Hide dynamic message
            } else {
                 noDataMessage.style.display = "block"; // Show dynamic message if no initial data row or it's hidden
            }
        }
    }

    document.getElementById("searchInputPemesanan").addEventListener("keyup", filterTablePemesanan);
    document.getElementById("searchInputPemesanan").addEventListener("input", filterTablePemesanan);

    // Event listener untuk mengambil saran dari database secara real-time (AJAX)
    // Anda perlu membuat file baru: pemesanan_suggestions.php
    let debounceTimerPemesanan;
    document.getElementById("searchInputPemesanan").addEventListener("input", function() {
        clearTimeout(debounceTimerPemesanan);
        const inputVal = this.value;

        debounceTimerPemesanan = setTimeout(() => {
            if (inputVal.length >= 2) {
                // Asumsikan ada file 'pemesanan_suggestions.php' di root project
                fetch('pemesanan_suggestions.php?query=' + encodeURIComponent(inputVal))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        var datalist = document.getElementById('pemesananSuggestions');
                        datalist.innerHTML = '';
                        if (data && Array.isArray(data)) {
                            data.forEach(item => {
                                var option = document.createElement('option');
                                option.value = item;
                                datalist.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching pemesanan suggestions:', error));
            } else {
                document.getElementById('pemesananSuggestions').innerHTML = '';
            }
        }, 300);
    });

    document.addEventListener('DOMContentLoaded', filterTablePemesanan);
    document.getElementById("searchInputPemesanan").addEventListener("change", filterTablePemesanan);
</script>