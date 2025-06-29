<?php
include 'sidebar.php'; // Sertakan sidebar
include_once "function.php"; // Pastikan function.php berisi koneksi $conn

// --- Logika Paginasi (Bisa disesuaikan dengan kebutuhan Anda) ---
$limit = 10; // Batas data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

$total_data_query = mysqli_query($conn, "SELECT COUNT(id_mutasi) AS total FROM mutasi_barang");
$total_data = mysqli_fetch_assoc($total_data_query)['total'];
$total_pages = ceil($total_data / $limit);

if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
} elseif ($total_pages == 0) {
    $page = 1;
}

$start = ($page - 1) * $limit;
// --- Akhir Logika Paginasi ---

// Ambil data mutasi barang dengan join ke tabel barang, ruangan, dan user
$query_mutasi = mysqli_query($conn, "SELECT mb.*, b.kode_barang, b.nama_barang, 
                                        ra.nama_ruangan AS nama_ruangan_asal, 
                                        rt.nama_ruangan AS nama_ruangan_tujuan,
                                        u.nama AS nama_pegawai_mutasi
                                    FROM mutasi_barang mb
                                    LEFT JOIN barang b ON mb.id_barang = b.id_barang
                                    LEFT JOIN ruangan ra ON mb.id_ruangan_asal = ra.id_ruangan
                                    LEFT JOIN ruangan rt ON mb.id_ruangan_tujuan = rt.id_ruangan
                                    LEFT JOIN user u ON mb.id_pegawai = u.id_user
                                    ORDER BY mb.tanggal_mutasi DESC, mb.timestamp DESC
                                    LIMIT $start, $limit");

?>

<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Riwayat Mutasi Barang</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Mutasi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal Mutasi</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Jumlah Mutasi</th>
                            <th>Ruangan Asal</th>
                            <th>Ruangan Tujuan</th>
                            <th>Petugas</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = $start + 1;
                        if (mysqli_num_rows($query_mutasi) > 0) {
                            while ($data = mysqli_fetch_assoc($query_mutasi)) {
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo date('d-m-Y', strtotime($data['tanggal_mutasi'])); ?></td>
                                    <td><?php echo htmlspecialchars($data['kode_barang']); ?></td>
                                    <td><?php echo htmlspecialchars($data['nama_barang']); ?></td>
                                    <td><?php echo htmlspecialchars($data['jumlah_mutasi']); ?></td>
                                    <td><?php echo htmlspecialchars($data['nama_ruangan_asal'] ? $data['nama_ruangan_asal'] : 'Tidak Diketahui'); ?></td>
                                    <td><?php echo htmlspecialchars($data['nama_ruangan_tujuan']); ?></td>
                                    <td><?php echo htmlspecialchars($data['nama_pegawai_mutasi'] ? $data['nama_pegawai_mutasi'] : 'Admin'); ?></td>
                                    <td><?php echo htmlspecialchars($data['keterangan']); ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='9' class='text-center'>Tidak ada data mutasi.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <nav aria-label="Page navigation example">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php if($page <= 1){ echo 'disabled'; } ?>">
                        <a class="page-link" href="?page=<?php echo $page-1; ?>" tabindex="-1">Previous</a>
                    </li>
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php if($page == $i){ echo 'active'; } ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php if($page >= $total_pages){ echo 'disabled'; } ?>">
                        <a class="page-link" href="?page=<?php echo $page+1; ?>">Next</a>
                    </li>
                </ul>
            </nav>

        </div>
    </div>

</div>
<?php include 'footer.php'; // Sertakan footer ?>