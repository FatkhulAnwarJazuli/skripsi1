<?php
// FILE: report/rmutasi.php

// Pastikan file function.php Anda menginisialisasi koneksi database $conn.
// Asumsi function.php ada di root project (naik satu direktori dari 'report').
include_once "../function.php"; // Menggunakan path relatif yang benar

// Mulai Sesi
session_start();
if (!isset($_SESSION['log']) || $_SESSION['log'] != "login") {
    // Sesuaikan path jika index.php ada di root project.
    // Jika BASE_URL sudah didefinisikan secara global, bisa digunakan.
    // Atau bisa juga redirect langsung ke login.php: header("location:../login.php");
    header("location:../index.php"); 
    exit(); // Penting untuk menghentikan eksekusi setelah redirect
}

// Pastikan koneksi database $conn sudah tersedia dari function.php
if (!isset($conn) || !$conn) {
    die("Koneksi database tidak tersedia. Pastikan function.php sudah benar dan menginisialisasi \\$conn.");
}

function ribuan($nilai)
{
    return number_format($nilai, 0, ',', '.');
}

// Set locale ke Bahasa Indonesia
setlocale(LC_TIME, 'id_ID', 'id_ID.UTF8', 'id_ID.utf8', 'indonesian');

// Format tanggal menggunakan strftime untuk nama bulan dalam Bahasa Indonesia
$tgl = strftime('%e %B %Y', strtotime(date('Y-m-d')));

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
                                    ORDER BY mb.tanggal_mutasi ASC, mb.timestamp ASC"); // Urutkan sesuai tanggal dan waktu mutasi

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Mutasi Barang</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container-fluid {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }
        .header-kop {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header-kop img {
            max-width: 80px; /* Sesuaikan ukuran logo */
            float: left;
            margin-right: 15px;
        }
        .header-kop h2, .header-kop h4 {
            margin: 0;
            line-height: 1.2;
        }
        .header-kop p {
            margin: 0;
            font-size: 0.9em;
        }
        .title {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 1.2em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
            vertical-align: top;
            font-size: 0.9em;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .signature-block {
            margin-top: 50px;
            text-align: right;
            width: 300px;
            margin-left: auto;
            margin-right: 0;
        }
        .signature-block .date {
            margin-bottom: 5px;
        }
        .signature-block .title {
            margin-top: 20px;
            margin-bottom: 70px; /* Ruang untuk tanda tangan */
            font-weight: normal; /* Override .title global */
            font-size: 1em;
        }
        .signature-block .name {
            font-weight: bold;
            text-decoration: underline;
        }
        .signature-block .nip {
            margin-top: 5px;
        }
        /* Media query for printing */
        @media print {
            body {
                margin: 0;
            }
            .container-fluid {
                width: auto;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="header-kop">
        <h2>NAMA INSTANSI ANDA</h2>
        <h4>DINAS/BAGIAN INVENTARIS</h4>
        <p>Alamat Lengkap Instansi, Kota, Kode Pos</p>
        <p>Telepon: (XXX) XXXX-XXXX | Email: info@instansi.co.id</p>
    </div>

    <div class="title">LAPORAN RIWAYAT MUTASI BARANG</div>

    <button onclick="window.print()" class="btn btn-primary no-print" style="margin-bottom: 20px;">Cetak Laporan</button>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal Mutasi</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Dari Ruangan</th>
                    <th>Ke Ruangan</th>
                    <th>Petugas Mutasi</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                if (mysqli_num_rows($query_mutasi) > 0) {
                    while ($data = mysqli_fetch_assoc($query_mutasi)) {
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $no++; ?></td>
                            <td><?php echo date('d-m-Y', strtotime($data['tanggal_mutasi'])); ?></td>
                            <td><?php echo htmlspecialchars($data['kode_barang']); ?></td>
                            <td><?php echo htmlspecialchars($data['nama_barang']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($data['jumlah_mutasi']); ?></td>
                            <td><?php echo htmlspecialchars($data['nama_ruangan_asal'] ? $data['nama_ruangan_asal'] : 'Data Asal Terhapus'); ?></td>
                            <td><?php echo htmlspecialchars($data['nama_ruangan_tujuan']); ?></td>
                            <td><?php echo htmlspecialchars($data['nama_pegawai_mutasi'] ? $data['nama_pegawai_mutasi'] : 'Data Petugas Terhapus'); ?></td>
                            <td><?php echo htmlspecialchars($data['keterangan']); ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>Tidak ada data mutasi yang tercatat.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="signature-block">
        <div class="date">Banjarmasin, <?php echo $tgl; ?></div>
        <div class="title">KEPALA DINAS</div>
        <div class="name"><b>H. Chandra Iriandhy Wijaya, ST.MM</b></div>
        <div class="nip">NIP. 19710726 200003 1 004</div>
    </div>

</div>

<script>
    // Opsional: Untuk otomatis print saat halaman dimuat (jika diperlukan)
    // window.onload = function() {
    //     window.print();
    // };
</script>
</body>
</html>