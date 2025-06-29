<?php
include "function.php";
// Mulai Sesi 
session_start();
function ribuan($nilai)
{
    return number_format($nilai, 0, ',', '.');
}

// KONEKSI DB 
include "function.php";
$tanggal = date("m/Y");
$tgl = date("d F Y");
?>
<html>

<head>
    <title>CETAK DATA BARANG</title>
    <link href="img/logo.png" rel="icon" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.dataTables.min.css">
    <link href="img/logo.png" rel="icon" />
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
</head>

<body>
    <table border="0" align="left" width="90%">
        <tr>
        <td width="100px" style="vertical-align: top;"> <!-- Menambahkan style untuk vertikal align -->
            <img src="img/logo.png" width="90">
        </td>
            <td align="center">
                <h2><b>PEMERINTAH KOTA BANJARMASIN</b></h2>
                <h3><b>DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN</b></h3>
                <p>
                Jalan R.E Martadinata No. 1 Blok B Lantai 2 Kec. Banjarmasin Tengah, Kota Banjarmasin Kalimantan Selatan - 70111<br>
                E-mail : ampihkumuh@gmail.com | Telp./Fax. (0511) 3365592<br></p>

                
                <hr class="line">
                <h3 style="text-align: center;">LAPORAN BARANG</h3>
            </td>
        </tr>
    </table>

    <style>
    /* CSS untuk merapikan tabel */
    #example1 {
        width: 99%; /* Atur lebar tabel */
        margin: 0 auto; /* Pusatkan tabel */
    }
    th, td {
        text-align: center; /* Pusatkan teks dalam sel */
        padding: 10px; /* Tambahkan padding untuk ruang di dalam sel */
    }
    </style>

    <table border="1" id="example1" class="table table-bordered table-striped">
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
            $ambilsemuadatanya = mysqli_query($conn, "SELECT * FROM barang");
            $i = 1;
            while ($data = mysqli_fetch_array($ambilsemuadatanya)) {
                $namabarang = htmlspecialchars($data['namabarang']);
                $merk = htmlspecialchars($data['merk']);
                $jumlah = htmlspecialchars($data['jumlah']);
            ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $namabarang; ?></td>
                    <td><?php echo $merk; ?></td>
                    <td><?php echo $jumlah; ?></td>
                </tr>
            <?php
            }
            ?>
        </tbody>
    </table>

    <script>
        $(document).ready(function() {
            $('#example1').DataTable({
                searching: false, // Nonaktifkan search bar
                paging: false,    // Nonaktifkan pagination
                info: false       // Nonaktifkan info footer
            });
            window.print(); // Panggil print hanya sekali
        });
    </script>

    <table width="100%">
        <tr>
            <td width="70%"></td>
            <td align="center">
                <div class="signature">
                    <div class="date">Banjarmasin, <?php echo $tgl; ?></div>
                    <div class="title">KEPALA DINAS</div>
                    <br><br><br><br><br><br>
                    <div class="name"><b>H. Chandra Iriandhy Wijaya, ST.MM</b></div>
                    <div class="nip">NIP. 19710726 200003 1 004</div>
                </div>
            </td>
        </tr>
    </table>

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.print.min.js"></script>
</body>

</html>