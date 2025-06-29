<?php
// FILE: report/rpemesanan.php

// Pastikan file function.php Anda menginisialisasi koneksi database $conn.
include_once "../function.php"; // Menggunakan path relatif yang benar

// Mulai Sesi
session_start();
if (!isset($_SESSION['log']) || $_SESSION['log'] != "login") {
    header("location:../index.php"); // Sesuaikan path jika index.php ada di root project
    exit(); // Penting untuk menghentikan eksekusi setelah redirect
}

// Pastikan koneksi database $conn sudah tersedia dari function.php
if (!isset($conn) || !$conn) {
    die("Koneksi database tidak tersedia. Pastikan function.php sudah benar dan menginisialisasi \$conn.");
}

function ribuan($nilai)
{
    return number_format($nilai, 0, ',', '.');
}

// Set locale ke Bahasa Indonesia
setlocale(LC_TIME, 'id_ID', 'id_ID.UTF8', 'id_ID.utf8', 'indonesian');

// Format tanggal menggunakan strftime untuk nama bulan dalam Bahasa Indonesia
// Menggunakan tanggal saat ini
$tgl = strftime("%e %B %Y", time());

// Set header untuk memberitahu browser bahwa ini adalah file HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Data Pemesanan</title>
    <link href="../img/logo.png" rel="icon" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Umum untuk Tampilan Layar */
        html {
            position: relative; /* Menetapkan konteks positioning untuk body */
            min-height: 100%; /* Memastikan html setidaknya menutupi tinggi viewport */
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0; /* Reset default body margin */
            padding: 20px; /* Add padding for content */
            position: relative; /* Establish positioning context for absolute children (like signature) */
            min-height: 100vh; /* Ensure body covers full viewport height */
            box-sizing: border-box; /* Include padding in element's total width and height */
            color: #333; /* Warna teks standar */
            /* Padding bawah untuk memberi ruang tanda tangan di layar */
            padding-bottom: 250px; /* **PENTING**: Memberi ruang cukup di bawah untuk tanda tangan */
        }
        .container-fluid {
            width: 100%;
            margin: auto;
        }
        h1, h2, h3 {
            text-align: center;
            color: #000; /* Warna teks hitam untuk judul */
        }
        h2.main-title { margin-bottom: 0; }
        h3.sub-title { margin-top: 5px; margin-bottom: 5px; }
        p.address {
            font-size: 0.9em;
            margin-bottom: 5px;
            line-height: 1.4;
        }
        .line {
            border: 0;
            border-top: 2px solid #000; /* Garis hitam tebal */
            margin: 20px 0;
        }

        /* Tabel Data */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #000; /* Garis tabel hitam */
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #e9e9e9; /* Sedikit lebih gelap dari f2f2f2 */
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }

        /* Header (Kop Surat) */
        table.header-table {
            width: 90%; /* Sedikit lebih kecil agar ada margin di samping */
            margin: 0 auto 30px auto;
            border: none;
        }
        table.header-table td {
            border: none;
            padding: 0;
            vertical-align: top; /* Pastikan gambar dan teks sejajar di atas */
        }
        table.header-table td img {
            display: block;
            margin-right: 20px; /* Jarak antara logo dan teks kop */
        }
        table.header-table td.align-center {
            text-align: center;
        }

        /* Tanda Tangan (di layar) */
        .signature-block {
            text-align: center;
            width: 300px;
            position: absolute; /* Position relative to the body */
            bottom: 50px; /* Distance from bottom of the body (after padding) */
            right: 50px; /* Distance from right of the body */
            margin: 0; /* Pastikan tidak ada margin tambahan */
        }
        /* Penyesuaian untuk spasi di dalam signature-block */
        .signature-block .date { margin-bottom: 10px; }
        .signature-block .title { margin-bottom: 80px; } /* Memberikan ruang untuk tanda tangan */
        .signature-block .name {
            text-decoration: underline;
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 5px;
        }
        .signature-block .nip { font-size: 0.9em; margin-top: 0; }


        /* --- MEDIA QUERIES untuk CETAK --- */
        @media print {
            html, body {
                height: 100%; /* Pastikan elemen root dan body memiliki tinggi penuh halaman */
                margin: 0;
                padding: 0;
            }
            body {
                position: relative; /* Penting untuk positioning signature */
                padding-bottom: 280px; /* **KRUSIAL**: Memberi ruang di bawah untuk tanda tangan saat dicetak */
                box-sizing: border-box; /* Pastikan padding dihitung dalam total tinggi */
            }
            .container-fluid {
                width: 95%; /* Sesuaikan lebar untuk cetak */
                margin: 0 auto;
                /* Padding bawah diatur di body untuk cetak */
            }

            /* Penyesuaian header untuk cetak */
            table.header-table {
                width: 95%; /* Lebih lebar sedikit untuk cetak */
                margin-top: 0;
                margin-left: auto;
                margin-right: auto;
            }
            .line {
                margin-top: 15px; /* Sedikit naik garisnya */
                margin-bottom: 15px;
            }
            h2.main-title, h3.sub-title, p.address {
                line-height: 1.2; /* Kerapatan baris lebih baik untuk cetak */
            }

            /* Penyesuaian tabel untuk cetak */
            #example1 {
                width: 95%; /* Sesuaikan lebar tabel */
                margin: 20px auto 0 auto; /* Margin atas dan otomatis di tengah */
            }
            #example1 th, #example1 td {
                border: 1px solid #000;
                padding: 6px; /* Padding sedikit lebih kecil */
                font-size: 0.85em; /* Ukuran font lebih kecil untuk cetak */
            }

            /* Tanda Tangan untuk CETAK - Posisi di ujung kanan bawah */
            .signature-block {
                position: absolute; /* Absolut ke body (yang sekarang 100% tinggi halaman) */
                bottom: 80px; /* Jarak dari bawah kertas, dipindah lebih ke bawah */
                right: 50px; /* Jarak dari kanan kertas */
                width: 300px; /* Lebar tetap */
                margin: 0; /* Pastikan tidak ada margin yang mengganggu */
                padding: 0; /* Hapus padding yang mungkin ada */
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <table border="0" class="header-table">
        <tr>
            <td width="100px">
                <img src="../img/logo.png" width="120">
            </td>
            <td class="align-center">
                <h2 class="main-title"><b>PEMERINTAH KOTA BANJARMASIN</b></h2>
                <h3 class="sub-title"><b>DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN</b></h3>
                <p class="address">
                Jalan R.E Martadinata No. 1 Blok B Lantai 2 Kec. Banjarmasin Tengah, Kota Banjarmasin Kalimantan Selatan - 70111<br>
                E-mail : ampihkumuh@gmail.com | Telp./Fax. (0511) 3365592<br></p>
                <hr class="line">
                <h3 style="text-align: center;">LAPORAN DATA PEMESANAN BARANG</h3>
            </td>
        </tr>
    </table>

    <div class="table-responsive">
        <table class="table table-bordered" id="example1">
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
                $ambilsemuadatanya = mysqli_query($conn, "SELECT p.*,
                                                                 aj.nama_barang,
                                                                 aj.merk AS merk_barang,
                                                                 aj.jumlah AS jumlah_diajukan,
                                                                 aj.satuan,
                                                                 aj.pengaju,
                                                                 s.nama_supplier
                                                          FROM pemesanan p
                                                          LEFT JOIN pengajuan aj ON p.id_pengajuan = aj.id_pengajuan
                                                          LEFT JOIN supplier s ON p.id_supplier = s.id_supplier
                                                          ORDER BY p.tanggal_pemesanan DESC");

                if ($ambilsemuadatanya) {
                    if (mysqli_num_rows($ambilsemuadatanya) > 0) {
                        $i = 1;
                        while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
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
                                <td>Rp <?= htmlspecialchars(ribuan($harga)) ?></td>
                                <td><?= htmlspecialchars($pengaju) ?></td>
                                <td><?= htmlspecialchars($nama_supplier) ?></td>
                                <td><?= htmlspecialchars($tanggal_pemesanan) ?></td>
                                <td><?= htmlspecialchars($status_pemesanan) ?></td>
                            </tr>
                <?php
                        }
                    } else {
                ?>
                        <tr>
                            <td colspan="10" class="text-center">Tidak ada data pemesanan ditemukan.</td>
                        </tr>
                <?php
                    }
                } else {
                ?>
                    <tr>
                        <td colspan="10" class="text-center">
                            Error dalam mengambil data: <?php echo mysqli_error($conn); ?>.
                            <?php
                            if (mysqli_error($conn)) {
                                echo "Pesan error SQL: " . mysqli_error($conn) . ".";
                            } else {
                                echo "Tidak ada data pemesanan ditemukan.";
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>

</div>

<div class="signature-block">
    <div class="date">Banjarmasin, <?php echo $tgl; ?></div>
    <div class="title">KEPALA DINAS</div>
    <div class="name"><b>H. Chandra Iriandhy Wijaya, ST.MM</b></div>
    <div class="nip">NIP. 19710726 200003 1 004</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>

<script>
    $(document).ready(function() {
        $('#example1').DataTable({
            searching: false, // Nonaktifkan search bar
            paging: false,    // Nonaktifkan pagination
            info: false       // Nonaktifkan info footer
        });
        window.print(); // Memanggil fungsi print otomatis saat halaman dimuat
    });
</script>
</body>
</html>