<?php
// Pastikan file 'function.php' sudah di-include dan memiliki koneksi database $conn
include_once 'function.php'; // Menggunakan include_once untuk menghindari duplikasi

// Pastikan koneksi database sudah ada dan valid sebelum melanjutkan
if (!isset($conn) || !$conn) {
    die("Koneksi database gagal! Pastikan 'function.php' menginisialisasi \$conn dengan benar.");
}

// Mengambil tanggal saat ini untuk laporan
date_default_timezone_set('Asia/Makassar'); // Sesuaikan zona waktu ke WITA (Banjarmasin)
$tanggal_sekarang_format_bulan_tahun = date("m/Y"); // Contoh: 06/2025
$tanggal_sekarang_lengkap = date("d F Y"); // Contoh: 19 June 2025 (WITA - Banjarmasin)

// --- Ambil ID Barang Rusak untuk Dicetak ---
$id_rusak_to_print = null;
// Gunakan $_GET karena tombol cetak di barangrusak.php mengirimkan ID melalui GET
if (isset($_GET['id_rusak'])) { // Perhatikan perubahan dari 'id' menjadi 'id_rusak'
    // Sanitasi input untuk mencegah XSS dan SQL Injection
    $id_rusak_to_print = (int)$_GET['id_rusak']; // Pastikan ini adalah integer
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Halaman Cetak Laporan Data Barang Rusak Inventaris">
    <meta name="author" content="Your Name">

    <title>CETAK DATA BARANG RUSAK</title>

    <link href="img/logo.png" rel="icon">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
        /* CSS untuk Kop Surat */
        .kop-surat {
            text-align: center;
            margin-bottom: 20px;
            overflow: hidden; /* Clear float */
        }
        .kop-surat img {
            float: left;
            margin-right: 20px;
            height: 90px; /* Sesuaikan tinggi logo */
        }
        .kop-surat h2, .kop-surat h3, .kop-surat p {
            margin: 0;
            line-height: 1.2;
        }
        .kop-surat hr {
            border: 1px solid black; /* Garis pemisah kop surat */
            margin-top: 10px;
            margin-bottom: 10px;
            clear: both; /* Pastikan garis tidak terganggu oleh float */
        }

        /* CSS untuk Tabel Data Barang */
        #printTable {
            width: 90%; /* Sesuaikan lebar tabel agar terlihat rapi pada cetakan */
            margin: 0 auto;
            border-collapse: collapse; /* Pastikan border cell menyatu */
        }
        #printTable th,
        #printTable td {
            text-align: center;
            padding: 8px;
            border: 1px solid #dee2e6; /* Border standar Bootstrap */
            font-size: 14px; /* Ukuran font untuk konten tabel */
            vertical-align: middle; /* Agar konten di tengah secara vertikal */
        }
        #printTable thead th {
            background-color: #f2f2f2; /* Warna latar belakang header tabel */
        }
        .img-table-print {
            max-width: 80px; /* Ukuran thumbnail foto di cetakan */
            height: auto;
            display: block; /* Agar margin auto bekerja */
            margin: 0 auto; /* Tengah gambar */
        }

        /* CSS untuk Tanda Tangan */
        .signature {
            text-align: center;
            margin-top: 50px; /* Jarak antara tabel dan tanda tangan */
            margin-right: 5%; /* Dorong ke kanan sedikit agar sejajar dengan posisi cetak */
        }
        .signature .date,
        .signature .title,
        .signature .name,
        .signature .nip {
            font-size: 14px; /* Sedikit lebih kecil agar rapi */
            margin-bottom: 5px;
        }

        /* Aturan Cetak (Print Media Queries) */
        @media print {
            body {
                -webkit-print-color-adjust: exact; /* Untuk memastikan warna latar belakang tercetak */
                font-family: Arial, sans-serif;
                margin: 0; /* Atur ulang margin body untuk cetak */
                padding: 0;
            }
            .kop-surat, #printTable, .signature {
                page-break-inside: avoid; /* Hindari pemisahan elemen ini antar halaman */
            }
            /* Sesuaikan margin halaman untuk pencetakan */
            @page {
                margin: 1.5cm; /* Contoh margin standar untuk dokumen */
            }
            /* Sembunyikan tombol print jika ada di halaman */
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="kop-surat">
        <img src="img/logo.png" alt="Logo Kota Banjarmasin">
        <h2><b>PEMERINTAH KOTA BANJARMASIN</b></h2>
        <h3><b>DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN</b></h3>
        <p>
            Jalan R.E Martadinata No. 1 Blok B Lantai 2 Kec. Banjarmasin Tengah, Kota Banjarmasin Kalimantan Selatan - 70111<br>
            E-mail : ampihkumuh@gmail.com | Telp./Fax. (0511) 3365592<br>
        </p>
        <hr class="line">
        <h3 style="text-align: center; margin-top: 20px;">LAPORAN DATA BARANG RUSAK</h3>
    </div>

    <table id="printTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Merk</th>
                <th>Jumlah Rusak</th>
                <th>Kondisi</th>
                <th>Foto</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Mengambil data barang rusak berdasarkan ID yang diterima
            $query = "SELECT br.id_rusak, br.jumlah, br.kondisi, br.foto, br.status, b.namabarang, b.merk 
                      FROM barang_rusak br 
                      JOIN barang b ON br.id_barang = b.id_barang 
                      WHERE br.id_rusak = ?";
            $stmt = mysqli_prepare($conn, $query);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $id_rusak_to_print); // "i" untuk integer
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                $i = 1; // Nomor urut untuk tabel
                if (mysqli_num_rows($result) > 0) {
                    while ($data = mysqli_fetch_array($result)) {
                        $namabarang = htmlspecialchars($data['namabarang']);
                        $merk = htmlspecialchars($data['merk']);
                        $jumlah = htmlspecialchars($data['jumlah']);
                        $kondisi = htmlspecialchars($data['kondisi']);
                        $foto = htmlspecialchars($data['foto']);
                        $status = htmlspecialchars($data['status']);

                        $foto_path = !empty($foto) ? 'foto/' . $foto : 'img/no_image.png';
            ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo $namabarang; ?></td>
                            <td><?php echo $merk; ?></td>
                            <td><?php echo $jumlah; ?></td>
                            <td><?php echo $kondisi; ?></td>
                            <td>
                                <?php if (!empty($foto) && file_exists('foto/' . $data['foto'])) : ?>
                                    <img src="<?= $foto_path ?>" class="img-table-print" alt="Foto Barang Rusak">
                                <?php else : ?>
                                    <img src="img/no_image.png" class="img-table-print" alt="Tidak ada foto">
                                <?php endif; ?>
                            </td>
                            <td><?php echo $status; ?></td>
                        </tr>
            <?php
                    }
                } else {
                    echo '<tr><td colspan="7" class="text-center">Data barang rusak tidak ditemukan untuk ID ini.</td></tr>';
                }
                mysqli_stmt_close($stmt); // Tutup statement
            } else {
                echo '<tr><td colspan="7" class="text-center">Error saat menyiapkan query: ' . mysqli_error($conn) . '</td></tr>';
            }
            ?>
        </tbody>
    </table>

    <table width="100%">
        <tr>
            <td width="70%"></td>
            <td align="center">
                <div class="signature">
                    <div class="date">Banjarmasin, <?php echo $tanggal_sekarang_lengkap; ?></div>
                    <div class="title">KEPALA DINAS</div>
                    <br><br><br><br><br><br>
                    <div class="name"><b>H. Chandra Iriandhy Wijaya, ST.MM</b></div>
                    <div class="nip">NIP. 19710726 200003 1 004</div>
                </div>
            </td>
        </tr>
    </table>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            // Skrip untuk memulai print secara otomatis setelah halaman dimuat
            window.print();
        });
    </script>

</body>

</html>