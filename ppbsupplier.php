<?php
// Pastikan file 'function.php' sudah di-include dan memiliki koneksi database $conn
include_once 'function.php'; // Menggunakan include_once untuk menghindari duplikasi

// Pastikan koneksi database sudah ada dan valid sebelum melanjutkan
if (!isset($conn) || !$conn) {
    die("Koneksi database gagal! Pastikan 'function.php' menginisialisasi \$conn dengan benar.");
}

// Mengambil tanggal saat ini untuk laporan (WITA - Banjarmasin)
$tanggal_sekarang_lengkap = date("d F Y"); // Contoh: 19 June 2025

// --- Ambil ID Supplier untuk Dicetak ---
$id_supplier_to_print = null;
if (isset($_POST['id_supplier_to_print'])) {
    // Sanitasi input untuk mencegah XSS
    $id_supplier_to_print = htmlspecialchars($_POST['id_supplier_to_print']);
} else {
    // Hentikan eksekusi jika ID supplier tidak ditemukan
    die("ID Supplier tidak ditemukan. Tidak dapat mencetak laporan supplier.");
}

// --- Ambil Detail Lengkap Supplier dari tabel 'supplier' ---
$data_supplier_cetak = null; // Inisialisasi variabel untuk menyimpan data supplier
// Menggunakan kolom 'id_supplier', 'nama_supplier', 'no_telp', 'alamat' dari tabel 'supplier'
$query_detail_supplier = "SELECT id_supplier, nama_supplier, no_telp, alamat FROM supplier WHERE id_supplier = ?";
$stmt_detail_supplier = mysqli_prepare($conn, $query_detail_supplier);

if ($stmt_detail_supplier) {
    mysqli_stmt_bind_param($stmt_detail_supplier, "i", $id_supplier_to_print);
    mysqli_stmt_execute($stmt_detail_supplier);
    $result_detail_supplier = mysqli_stmt_get_result($stmt_detail_supplier);

    if (mysqli_num_rows($result_detail_supplier) > 0) {
        $data_supplier_cetak = mysqli_fetch_array($result_detail_supplier);
    }
    mysqli_stmt_close($stmt_detail_supplier);
} else {
    // Catat kesalahan jika persiapan query detail supplier gagal
    error_log("Error preparing detail supplier query: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Halaman Cetak Data Detail Supplier">
    <meta name="author" content="Your Name">

    <title>CETAK DATA SUPPLIER</title>

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

        /* CSS untuk Tabel Data Supplier (Mengikuti Gaya supplier.php) */
        #printTable {
            width: 90%; /* Sesuaikan lebar tabel agar terlihat rapi pada cetakan */
            margin: 0 auto;
            border-collapse: collapse; /* Pastikan border cell menyatu */
            margin-top: 20px; /* Jarak antara judul laporan dan tabel */
        }
        #printTable th,
        #printTable td {
            text-align: center; /* Sesuaikan dengan supplier.php */
            padding: 8px;
            border: 1px solid #dee2e6; /* Border standar Bootstrap */
            font-size: 14px; /* Ukuran font untuk konten tabel */
        }
        #printTable thead th {
            background-color: #f2f2f2; /* Warna latar belakang header tabel */
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
        <h3 style="text-align: center; margin-top: 20px;">DETAIL DATA SUPPLIER</h3>
    </div>

    <table id="printTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Supplier</th>
                <th>Nomor Telepon/WA</th>
                <th>Alamat</th>
                </tr>
        </thead>
        <tbody>
            <?php if ($data_supplier_cetak) : ?>
                <tr>
                    <td>1</td> <td><?= htmlspecialchars($data_supplier_cetak['nama_supplier']) ?></td>
                    <td><?= htmlspecialchars($data_supplier_cetak['no_telp']) ?></td>
                    <td><?= nl2br(htmlspecialchars($data_supplier_cetak['alamat'])) ?></td>
                </tr>
            <?php else : ?>
                <tr>
                    <td colspan="4" class="text-center">Data supplier tidak ditemukan untuk ID ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table width="100%">
        <tr>
            <td width="70%"></td> <td align="center">
                <div class="signature">
                    <div class="date">Banjarmasin, <?php echo $tanggal_sekarang_lengkap; ?></div>
                    <div class="title">KEPALA DINAS</div>
                    <br><br><br><br><br><br> <div class="name"><b>H. Chandra Iriandhy Wijaya, ST.MM</b></div>
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