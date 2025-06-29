<?php

// Detail koneksi database utama (untuk inventaris dan lainnya)
$host = "localhost";
$user = "root";
$pass = "";
$db = "inventaris"; // Menggunakan nama database "inventaris"

// Buat koneksi utama
$conn = mysqli_connect($host, $user, $pass, $db);

// Periksa koneksi utama
if (!$conn) {
    die("Koneksi database utama gagal: " . mysqli_connect_error());
}

// Set zona waktu (opsional, tapi disarankan)
date_default_timezone_set('Asia/Makassar'); // WITA (Waktu Indonesia Tengah)

// --- Logika Penanganan POST/GET ---

// Logika untuk Menambah Pengajuan Baru
if (isset($_POST['addpengajuan'])) {
    $tanggal_pengajuan = date('Y-m-d'); // Tanggal otomatis saat ini
    $pengaju = htmlspecialchars($_POST['pengaju']);
    $nama_barang = htmlspecialchars($_POST['nama_barang']);
    $merk = htmlspecialchars($_POST['merk']);
    $jumlah = htmlspecialchars($_POST['jumlah']);
    $satuan = htmlspecialchars($_POST['satuan']);

    // Prepared statement untuk INSERT data pengajuan
    // Menggunakan $conn utama untuk operasi ini
    $stmt = mysqli_prepare($conn, "INSERT INTO pengajuan (tanggal_pengajuan, pengaju, nama_barang, merk, jumlah, satuan) VALUES (?, ?, ?, ?, ?, ?)");

    if ($stmt) {
        // "ssssisi" -> string (tanggal), string (pengaju), string (nama_barang), string (merk), integer (jumlah), string (satuan), integer (estimasi_harga)
        mysqli_stmt_bind_param($stmt, "ssssis", $tanggal_pengajuan, $pengaju, $nama_barang, $merk, $jumlah, $satuan);

        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("Data Pengajuan Berhasil Ditambahkan!"); window.location.href="pengajuan.php";</script>';
        } else {
            echo '<script>alert("Gagal Menambahkan Data Pengajuan: ' . mysqli_error($conn) . '"); window.location.href="pengajuan.php";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Insert: ' . mysqli_error($conn) . '"); window.location.href="pengajuan.php";</script>';
    }
    exit(); // Penting untuk menghentikan eksekusi setelah redirect
}

// Logika untuk Menambah Barang Baru
if (isset($_POST['addbarang'])) {
    $namabarang = htmlspecialchars($_POST['namabarang']); // Tambahkan htmlspecialchars
    $merk = htmlspecialchars($_POST['merk']); // Tambahkan htmlspecialchars
    $jumlah = htmlspecialchars($_POST['jumlah']); // Tambahkan htmlspecialchars

    // Gunakan prepared statement untuk keamanan
    $stmt = mysqli_prepare($conn, "INSERT INTO barang (namabarang, merk, jumlah) VALUES (?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssi", $namabarang, $merk, $jumlah);
        if (mysqli_stmt_execute($stmt)) {
            header('location:barang.php');
        } else {
            echo 'Gagal menambahkan barang: ' . mysqli_error($conn); // Pesan error lebih detail
            // header('location:barang.php'); // Hindari redirect jika ada error, agar pesan terlihat
        }
        mysqli_stmt_close($stmt);
    } else {
        echo 'Gagal menyiapkan statement untuk barang: ' . mysqli_error($conn);
    }
    exit();
}

// Logika untuk Menambah Data Ruangan
if (isset($_POST['addruangan'])) {
    $ruangan = htmlspecialchars($_POST['ruangan']); // Tambahkan htmlspecialchars

    $stmt = mysqli_prepare($conn, "INSERT INTO ruangan (nama_ruangan) VALUES (?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $ruangan);
        if (mysqli_stmt_execute($stmt)) {
            header('location:ruangan.php');
        } else {
            echo 'Gagal menambahkan ruangan: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo 'Gagal menyiapkan statement untuk ruangan: ' . mysqli_error($conn);
    }
    exit();
}

// Logika untuk Menambah Ruangan 1
if (isset($_POST['addruangan1'])) {
    $ruangan1 = htmlspecialchars($_POST['ruangan1']); // Tambahkan htmlspecialchars

    $stmt = mysqli_prepare($conn, "INSERT INTO ruangan1 (nama_ruangan) VALUES (?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $ruangan1);
        if (mysqli_stmt_execute($stmt)) {
            header('location:ruangan1.php');
        } else {
            echo 'Gagal menambahkan ruangan1: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo 'Gagal menyiapkan statement untuk ruangan1: ' . mysqli_error($conn);
    }
    exit();
}

// Logika untuk Menambah Supplier
if (isset($_POST['addsupplier'])) {
    $namasupplier = htmlspecialchars($_POST['namasupplier']); // Tambahkan htmlspecialchars
    $notelp = htmlspecialchars($_POST['notelp']); // Tambahkan htmlspecialchars
    $alamat = htmlspecialchars($_POST['alamat']); // Tambahkan htmlspecialchars

    $stmt = mysqli_prepare($conn, "INSERT INTO supplier (nama_supplier, no_telp, alamat) VALUES (?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $namasupplier, $notelp, $alamat);
        if (mysqli_stmt_execute($stmt)) {
            header('location:supplier.php');
        } else {
            echo 'Gagal menambahkan supplier: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo 'Gagal menyiapkan statement untuk supplier: ' . mysqli_error($conn);
    }
    exit();
}

// Logika untuk Menambah Pengadaan Barang
if (isset($_POST['addpengadaan'])) {
    $barangnya = htmlspecialchars($_POST['Tambah_Nama_Barang']);
    $merknya = htmlspecialchars($_POST['Tambah_Merk']); // Merk ini tidak digunakan di INSERT pengadaan_barang, hanya ada di form. Mungkin bisa dihapus dari sini atau ditambahkan ke tabel jika relevan
    $suppliernya = htmlspecialchars($_POST['Tambah_Nama_supplier']);
    $ruangannya = htmlspecialchars($_POST['Tambah_Ruangan']);
    $jumlah = htmlspecialchars($_POST['jumlah']);
    $tanggalmasuk = htmlspecialchars($_POST['tanggal_masuk']);

    // Mulai transaksi untuk memastikan konsistensi data
    mysqli_begin_transaction($conn);

    try {
        // Ambil jumlah barang saat ini
        $stmt_select_barang = mysqli_prepare($conn, "SELECT jumlah FROM barang WHERE id_barang=?");
        mysqli_stmt_bind_param($stmt_select_barang, "i", $barangnya);
        mysqli_stmt_execute($stmt_select_barang);
        $result_select_barang = mysqli_stmt_get_result($stmt_select_barang);
        $ambildatanya = mysqli_fetch_array($result_select_barang);
        $jumlahsekarang = $ambildatanya['jumlah'];
        mysqli_stmt_close($stmt_select_barang);

        $tambahjumlahsekarangdenganyangbaru = $jumlahsekarang + $jumlah;

        // Insert ke tabel pengadaan_barang
        $stmt_add_pengadaan = mysqli_prepare($conn, "INSERT INTO pengadaan_barang (id_barang, id_supplier, id_ruangan, jumlah, tanggal_masuk) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_add_pengadaan, "iiiis", $barangnya, $suppliernya, $ruangannya, $jumlah, $tanggalmasuk);
        mysqli_stmt_execute($stmt_add_pengadaan);
        mysqli_stmt_close($stmt_add_pengadaan);

        // Update jumlah di tabel barang
        $stmt_update_barang = mysqli_prepare($conn, "UPDATE barang SET jumlah=? WHERE id_barang=?");
        mysqli_stmt_bind_param($stmt_update_barang, "ii", $tambahjumlahsekarangdenganyangbaru, $barangnya);
        mysqli_stmt_execute($stmt_update_barang);
        mysqli_stmt_close($stmt_update_barang);

        mysqli_commit($conn); // Komit transaksi jika semua berhasil
        header('location:pengadaan.php');
    } catch (mysqli_sql_exception $e) {
        mysqli_rollback($conn); // Rollback transaksi jika ada error
        echo 'Gagal: ' . $e->getMessage();
        // header('location:pengadaan.php'); // Hindari redirect agar pesan error terlihat
    }
    exit();
}

// Logika untuk Menambah Inventaris Barang
if (isset($_POST['addinventaris'])) {
    $ruangannya = htmlspecialchars($_POST['Tambah_Nama_Ruangan']);
    $barangnya = htmlspecialchars($_POST['Tambah_Nama_Barang']);
    $tanggalnya = htmlspecialchars($_POST['Tambah_Tanggal_Pengadaan']); // Ini id_pengadaan
    $tanggal = htmlspecialchars($_POST['tanggal']);

    $stmt = mysqli_prepare($conn, "INSERT INTO inventaris_barang (id_ruangan, id_barang, id_pengadaan, tanggal_inventaris) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iiis", $ruangannya, $barangnya, $tanggalnya, $tanggal);
        if (mysqli_stmt_execute($stmt)) {
            header('location:inventaris.php');
        } else {
            echo 'Gagal menambahkan inventaris: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo 'Gagal menyiapkan statement untuk inventaris: ' . mysqli_error($conn);
    }
    exit();
}

// Logika untuk Menambah User
if (isset($_POST['TambahUser'])) {
    $nama = htmlspecialchars($_POST['Tambah_Nama']);
    $username = htmlspecialchars($_POST['Tambah_Username']);
    $password = password_hash($_POST['Tambah_Password'], PASSWORD_DEFAULT);
    $role = htmlspecialchars($_POST['Tambah_Role']);

    $stmt_check = mysqli_prepare($conn, "SELECT COUNT(*) FROM user WHERE username=?");
    mysqli_stmt_bind_param($stmt_check, "s", $username);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_bind_result($stmt_check, $user_count);
    mysqli_stmt_fetch($stmt_check);
    mysqli_stmt_close($stmt_check);

    if ($user_count > 0) {
        echo '<script>alert("Maaf! Username sudah ada");history.go(-1);</script>';
    } else {
        $stmt_insert = mysqli_prepare($conn, "INSERT INTO user (nama, username, password, role) VALUES (?, ?, ?, ?)");
        if ($stmt_insert) {
            mysqli_stmt_bind_param($stmt_insert, "ssss", $nama, $username, $password, $role);
            if (mysqli_stmt_execute($stmt_insert)) {
                header('location:pengaturan.php');
            } else {
                echo '<script>alert("Gagal Menambahkan Data User: ' . mysqli_error($conn) . '");history.go(-1);</script>';
            }
            mysqli_stmt_close($stmt_insert);
        } else {
            echo '<script>alert("Gagal Menyiapkan Query Insert User: ' . mysqli_error($conn) . '");history.go(-1);</script>';
        }
    }
    exit();
}

// Logika untuk Menambah Barang Rusak
if (isset($_POST['addbarangrusak'])) {
    $barangnya = htmlspecialchars($_POST['barangnya']);
    $jumlahnya = htmlspecialchars($_POST['jumlah']);
    $kondisinya = htmlspecialchars($_POST['kondisinya']);
    $statusnya = htmlspecialchars($_POST['status']);

    // Menangani upload foto
    $foto_name = $_FILES['foto']['name'];
    $foto_tmp_name = $_FILES['foto']['tmp_name'];
    $foto_error = $_FILES['foto']['error'];
    $upload_dir = 'foto/'; // Pastikan direktori ini ada dan writable

    if ($foto_error === UPLOAD_ERR_OK) {
        $file_extension = pathinfo($foto_name, PATHINFO_EXTENSION);
        $new_file_name = uniqid('foto_') . '.' . $file_extension; // Nama file unik untuk menghindari tabrakan
        $target_file = $upload_dir . $new_file_name;

        if (move_uploaded_file($foto_tmp_name, $target_file)) {
            // Mulai transaksi
            mysqli_begin_transaction($conn);
            try {
                // Insert ke barang_rusak
                $stmt_rusak = mysqli_prepare($conn, "INSERT INTO barang_rusak (id_barang, jumlah, kondisi, foto, status) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt_rusak, "iisss", $barangnya, $jumlahnya, $kondisinya, $new_file_name, $statusnya);
                mysqli_stmt_execute($stmt_rusak);
                mysqli_stmt_close($stmt_rusak);

                // Ambil jumlah barang saat ini
                $stmt_select_barang = mysqli_prepare($conn, "SELECT jumlah FROM barang WHERE id_barang=?");
                mysqli_stmt_bind_param($stmt_select_barang, "i", $barangnya);
                mysqli_stmt_execute($stmt_select_barang);
                $result_select_barang = mysqli_stmt_get_result($stmt_select_barang);
                $ambildatanya = mysqli_fetch_array($result_select_barang);
                $jumlahsekarang = $ambildatanya['jumlah'];
                mysqli_stmt_close($stmt_select_barang);

                // Update jumlah di tabel barang
                $tambahjumlahsekarangdenganyangbaru = $jumlahsekarang - $jumlahnya;
                $stmt_update_barang = mysqli_prepare($conn, "UPDATE barang SET jumlah=? WHERE id_barang=?");
                mysqli_stmt_bind_param($stmt_update_barang, "ii", $tambahjumlahsekarangdenganyangbaru, $barangnya);
                mysqli_stmt_execute($stmt_update_barang);
                mysqli_stmt_close($stmt_update_barang);

                mysqli_commit($conn); // Komit transaksi
                header('location:barangrusak.php');
            } catch (mysqli_sql_exception $e) {
                mysqli_rollback($conn); // Rollback jika ada error
                echo 'Gagal: ' . $e->getMessage();
                // Hapus file yang sudah terupload jika transaksi gagal
                if (file_exists($target_file)) {
                    unlink($target_file);
                }
            }
        } else {
            echo 'Gagal mengupload foto.';
        }
    } else {
        echo 'Error upload foto: ' . $_FILES['foto']['error'];
    }
    exit();
}

// Logika untuk Menambah Pemeliharaan
if (isset($_POST['addpemeliharaan'])) {
    $id_barang = htmlspecialchars($_POST['Tambah_Nama_Barang']); // Ini seharusnya id_barang
    $id_rusak = htmlspecialchars($_POST['Tambah_Jumlah']); // Ini seharusnya id_rusak
    $keterangan = htmlspecialchars($_POST['keterangan']);
    $tanggal_pemeliharaan = htmlspecialchars($_POST['tanggal_pemeliharaan']);

    $stmt = mysqli_prepare($conn, "INSERT INTO pemeliharaan_barang (id_barang, id_rusak, keterangan, tanggal) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iiss", $id_barang, $id_rusak, $keterangan, $tanggal_pemeliharaan);
        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("Data Pemeliharaan berhasil ditambahkan!"); history.go(-1);</script>';
        } else {
            echo '<script>alert("Gagal Tambah Data Pemeliharaan: ' . mysqli_error($conn) . '"); history.go(-1);</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Pemeliharaan: ' . mysqli_error($conn) . '"); history.go(-1);</script>';
    }
    exit();
}

// Logika untuk Menambah Mutasi Barang
if (isset($_POST['addmutasi'])) {
    $id_barang = htmlspecialchars($_POST['Tambah_Id_Barang']);
    $id_ruangan = htmlspecialchars($_POST['Tambah_Id_Ruangan']);
    $id_ruangan1 = htmlspecialchars($_POST['Tambah_Id_Ruangan1']);
    $id_rusak = htmlspecialchars($_POST['Tambah_Id_Rusak']);

    $stmt = mysqli_prepare($conn, "INSERT INTO mutasi_barang (id_barang, id_ruangan, id_ruangan1, id_rusak) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iiii", $id_barang, $id_ruangan, $id_ruangan1, $id_rusak);
        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("Data Mutasi berhasil ditambahkan!"); window.location.href="mutasi.php";</script>';
        } else {
            echo '<script>alert("Gagal menambahkan data mutasi: ' . mysqli_error($conn) . '"); window.location.href="mutasi.php";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Mutasi: ' . mysqli_error($conn) . '"); window.location.href="mutasi.php";</script>';
    }
    exit();
}

// --- Logika untuk Pegawai ---
// Logika untuk Menambah Pegawai Baru
if (isset($_POST['addpegawai'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $no_telepon = htmlspecialchars($_POST['no_telepon']);
    $alamat = htmlspecialchars($_POST['alamat']);

    $stmt = mysqli_prepare($conn, "INSERT INTO pegawai (nama, no_telepon, alamat) VALUES (?, ?, ?)");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $nama, $no_telepon, $alamat);

        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("Data Pegawai Berhasil Ditambahkan!"); window.location.href="pegawai.php";</script>';
        } else {
            echo '<script>alert("Gagal Menambahkan Data Pegawai: ' . mysqli_error($conn) . '"); window.location.href="pegawai.php";</script>';
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Gagal Menyiapkan Query Tambah Pegawai: ' . mysqli_error($conn) . '"); window.location.href="pegawai.php";</script>';
    }
    exit();
}