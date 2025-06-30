<?php
// FILE: report/ranggaran.php

// Pastikan file function.php Anda menginisialisasi koneksi database $conn.
include_once "../function.php";

// Mulai Sesi
session_start();
if (!isset($_SESSION['log']) || $_SESSION['log'] != "login") {
    header("location:../index.php");
    exit();
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
// Menggunakan date_default_timezone_set untuk memastikan waktu yang benar
date_default_timezone_set('Asia/Makassar'); // Zona Waktu Indonesia Tengah (WITA), sesuai Banjarbaru
setlocale(LC_TIME, 'id_ID', 'id_ID.UTF8', 'id_ID.utf8', 'indonesian');

// Tanggal saat ini di Banjarbaru, South Kalimantan, Indonesia
$tgl = strftime("%e %B %Y"); // Tanggal otomatis dari server

// Header untuk memberitahu browser bahwa ini adalah file HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Data Anggaran</title>
    <link href="../img/logo.png" rel="icon" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.dataTables.min.css">
    <style>
        /* Umum untuk Tampilan Layar */
        html {
            position: relative;
            min-height: 100%;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px 60px; /* Padding kiri kanan untuk kesan surat */
            position: relative;
            min-height: 100vh;
            box-sizing: border-box;
            color: #000;
            line-height: 1.6;
            padding-bottom: 250px;
        }
        .container-fluid {
            width: 100%;
            margin: auto;
        }
        h1, h2, h3, h4, h5, h6, p {
            margin: 0;
            padding: 0;
        }

        /* Kop Surat (Header Resmi) */
        table.kop-surat-table {
            width: 100%;
            margin: 0 auto 10px auto;
            border-collapse: collapse;
            border: none;
        }
        table.kop-surat-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        table.kop-surat-table td img {
            display: block;
            margin-right: 20px;
            height: 100px;
            width: auto;
        }
        table.kop-surat-table td.align-center {
            text-align: center;
            padding-top: 10px;
        }
        table.kop-surat-table h2, table.kop-surat-table h3 {
            margin-bottom: 3px;
        }
        table.kop-surat-table p.address {
            font-size: 0.85em;
            margin-bottom: 5px;
            line-height: 1.3;
        }
        .line-kop {
            border: 0;
            border-top: 2px solid #000;
            margin: 15px 0 25px 0;
        }

        /* Informasi Surat (Nomor, Perihal, Lampiran) */
        .info-surat {
            width: 100%;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .info-surat .kiri {
            float: left;
            width: 50%;
        }
        .info-surat .kanan {
            float: right;
            width: 40%;
            text-align: right;
        }
        .info-surat p {
            margin-bottom: 5px;
            line-height: 1.4;
        }
        .info-surat p strong {
            display: inline-block;
            width: 80px;
        }

        /* Tujuan Surat */
        .tujuan-surat {
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .tujuan-surat p {
            margin-bottom: 5px;
        }
        .tujuan-surat .indent {
            margin-left: 20px;
        }

        /* Paragraf Pembuka */
        .paragraf-pembuka {
            margin-bottom: 30px;
            text-align: justify;
        }

        /* Tabel Data */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 0.95em;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* Paragraf Penutup */
        .paragraf-penutup {
            margin-top: 30px;
            margin-bottom: 50px;
            text-align: justify;
        }

        /* Tanda Tangan */
        .signature-block {
            text-align: left;
            width: 350px;
            margin-top: 50px;
            float: right;
            margin-right: 0;
        }
        .signature-block .kota-tgl { margin-bottom: 10px; }
        .signature-block .jabatan { margin-bottom: 80px; }
        .signature-block .nama-pejabat {
            text-decoration: underline;
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 5px;
        }
        .signature-block .nip { font-size: 0.9em; margin-top: 0; }

        /* Clearfix untuk float */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* --- MEDIA QUERIES untuk CETAK --- */
        @media print {
            html, body {
                height: auto;
                margin: 0;
                padding: 20px 50px;
                font-size: 11pt;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            .container-fluid {
                width: 100%;
                margin: 0 auto;
            }

            /* Kop Surat untuk Cetak */
            table.kop-surat-table {
                width: 100%;
                margin-bottom: 10px;
            }
            table.kop-surat-table td img {
                height: 90px;
                margin-right: 15px;
            }
            table.kop-surat-table td.align-center {
                padding-top: 5px;
            }
            .line-kop {
                margin: 10px 0 20px 0;
            }

            /* Info Surat untuk Cetak */
            .info-surat .kiri, .info-surat .kanan {
                float: none;
                width: 100%;
                text-align: left;
            }
            .info-surat .kanan {
                text-align: right;
                margin-top: -30px;
                margin-bottom: 20px;
            }
            .info-surat p {
                line-height: 1.2;
                margin-bottom: 2px;
            }

            /* Tujuan Surat untuk Cetak */
            .tujuan-surat {
                margin-top: 20px;
                margin-bottom: 20px;
            }

            /* Paragraf Pembuka/Penutup untuk Cetak */
            .paragraf-pembuka, .paragraf-penutup {
                margin-bottom: 20px;
                margin-top: 20px;
            }

            /* Tabel untuk Cetak */
            table {
                font-size: 0.9em;
                margin-bottom: 15px;
            }
            table, th, td {
                border: 1px solid #000;
            }
            th, td {
                padding: 6px;
            }
            tfoot td {
                padding-top: 8px;
                padding-bottom: 8px;
            }

            /* Tanda Tangan untuk Cetak */
            .signature-block {
                float: right;
                margin-top: 30px;
                margin-right: 0;
                width: 350px;
            }
            .signature-block .jabatan { margin-bottom: 70px; }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <table border="0" class="kop-surat-table">
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
            </td>
        </tr>
    </table>
    <hr class="line-kop"> <div class="info-surat clearfix">
        <div class="kiri">
            <p>Nomor &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: 900/123/PRKM/2025</p>
            <p>Perihal &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: Pengajuan Pengadaan Barang</p>
            <p>Lampiran &nbsp;&nbsp;: -</p>
        </div>
        <div class="kanan">
            <p>Banjarmasin, <?php echo $tgl; ?></p>
        </div>
    </div>

    <div class="tujuan-surat">
        <p>Kepada Yth. Sekretariat Daerah Kota Banjarmasin</p>
        <p>Di -</p>
        <p class="indent">Banjarmasin</p>
    </div>

    <p>Dengan Hormat,</p>
    <p class="paragraf-pembuka">
        Sehubungan dengan akan dilaksanakannya kegiatan operasional di Dinas Perumahan Rakyat dan Kawasan Permukiman Kota Banjarmasin,
        bersama ini kami sampaikan rincian data anggaran yang diperlukan sebagai berikut:
    </p>

    <div class="table-responsive">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nomor Anggaran</th>
                    <th>Nama Barang (Pengajuan)</th>
                    <th>Jumlah (Pengajuan)</th>
                    <th>Total Anggaran</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Query untuk mengambil semua data anggaran dengan JOIN yang diperlukan
                // Pemilihan 'p.nama AS nama_pegawai' dihapus karena tabel 'p' tidak di-join dan tidak digunakan di HTML.
                $query = "
                    SELECT
                        a.id_anggaran,
                        a.nomor_anggaran,
                        a.total,
                        pj.nama_barang,
                        pj.jumlah AS jumlah_barang_diajukan
                    FROM
                        anggaran a
                    LEFT JOIN
                        pemesanan pm ON a.id_pemesanan = pm.id_pemesanan
                    LEFT JOIN
                        pengajuan pj ON pm.id_pengajuan = pj.id_pengajuan
                    ORDER BY
                        a.id_anggaran ASC
                ";
                $result = mysqli_query($conn, $query);

                $grand_total_anggaran = 0;

                if ($result && mysqli_num_rows($result) > 0) {
                    $nomor = 1;
                    while ($data = mysqli_fetch_array($result)) {
                        $total_anggaran_row = $data['total'];
                        $grand_total_anggaran += $total_anggaran_row;
                        ?>
                        <tr>
                            <td class="text-center"><?= htmlspecialchars($nomor++); ?></td>
                            <td><?= htmlspecialchars($data['nomor_anggaran']); ?></td>
                            <td><?= htmlspecialchars($data['nama_barang']); ?></td>
                            <td class="text-center"><?= htmlspecialchars($data['jumlah_barang_diajukan']); ?></td>
                            <td class="text-right">Rp. <?= ribuan($total_anggaran_row); ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="6" class="text-center">
                            <?php
                            if (!$result) {
                                echo "Gagal mengambil data anggaran dari database: " . mysqli_error($conn) . ".";
                            } else {
                                echo "Tidak ada data anggaran ditemukan.";
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right"><b>Grand Total Anggaran:</b></td>
                    <td class="text-right"><b>Rp. <?= ribuan($grand_total_anggaran); ?></b></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <p class="paragraf-penutup">
        Demikian permohonan pengadaan anggaran ini kami sampaikan, atas perhatian dan kerja sama Saudara, kami ucapkan terima kasih.
    </p>

    <div class="signature-block clearfix">
        <p class="kota-tgl">Banjarmasin, <?php echo $tgl; ?></p>
        <p class="jabatan">Hormat Kami,</p>
        <p class="nama-pejabat"><b>H. Chandra Iriandhy Wijaya, ST.MM</b></p>
        <p class="nip">NIP. 19710726 200003 1 004</p>
    </div>

</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>

<script>
    $(document).ready(function() {
        // DataTables tetap dinonaktifkan untuk tampilan surat
    });

    window.onload = function() {
        window.print();
    };
</script>

</body>
</html>