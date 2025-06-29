-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2025 at 03:33 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventaris`
--

-- --------------------------------------------------------

--
-- Table structure for table `anggaran`
--

CREATE TABLE `anggaran` (
  `id_anggaran` int(11) NOT NULL,
  `id_pemesanan` int(11) DEFAULT NULL,
  `nomor_anggaran` varchar(50) DEFAULT NULL,
  `total` decimal(18,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `anggaran`
--

INSERT INTO `anggaran` (`id_anggaran`, `id_pemesanan`, `nomor_anggaran`, `total`) VALUES
(4, 7, '900/123/PRKM/2025', 1000000.00),
(5, 8, '900/123/PRKM/2025', 10000000.00);

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` int(11) NOT NULL,
  `kode_barang` varchar(20) DEFAULT NULL,
  `namabarang` varchar(50) DEFAULT NULL,
  `merk` varchar(50) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `kode_barang`, `namabarang`, `merk`, `jumlah`, `satuan`) VALUES
(25, NULL, 'Kursi', 'Matrix', 52, NULL),
(26, NULL, 'Lampu ', 'Philips', 10, NULL),
(27, NULL, 'Keyboard', 'Logitech', 1, NULL),
(28, NULL, 'Mouse', 'Robot', 30, NULL);

--
-- Triggers `barang`
--
DELIMITER $$
CREATE TRIGGER `buat_kode_barang_otomatis` AFTER INSERT ON `barang` FOR EACH ROW BEGIN
    UPDATE barang
    SET kode_barang = CONCAT('BRG', LPAD(NEW.id_barang, 5, '0'))
    WHERE id_barang = NEW.id_barang;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `barang_rusak`
--

CREATE TABLE `barang_rusak` (
  `id_rusak` int(11) NOT NULL,
  `id_barang` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `kondisi` varchar(50) DEFAULT NULL,
  `foto` varchar(50) DEFAULT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang_rusak`
--

INSERT INTO `barang_rusak` (`id_rusak`, `id_barang`, `jumlah`, `kondisi`, `foto`, `status`) VALUES
(30, 27, 1, 'Pecah', 'foto_685817998809c.png', 'Rusak'),
(31, 27, 1, 'Mati', 'foto_6861132b4cdd2.png', 'Rusak');

-- --------------------------------------------------------

--
-- Table structure for table `inventaris`
--

CREATE TABLE `inventaris` (
  `id_inventaris` int(11) NOT NULL,
  `id_ruangan` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `tanggal_inventaris` date NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventaris`
--

INSERT INTO `inventaris` (`id_inventaris`, `id_ruangan`, `id_barang`, `tanggal_inventaris`, `jumlah`) VALUES
(1, 3, 27, '2025-06-22', 0);

-- --------------------------------------------------------

--
-- Table structure for table `mutasi`
--

CREATE TABLE `mutasi` (
  `id_mutasi` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `id_ruangan_asal` int(11) NOT NULL,
  `id_ruangan` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pegawai`
--

CREATE TABLE `pegawai` (
  `id_pegawai` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pegawai`
--

INSERT INTO `pegawai` (`id_pegawai`, `nama`, `no_telepon`, `alamat`) VALUES
(1, 'a', '1', 'a');

-- --------------------------------------------------------

--
-- Table structure for table `pemeliharaan_barang`
--

CREATE TABLE `pemeliharaan_barang` (
  `id_pemeliharaan` int(11) NOT NULL,
  `id_barang` int(11) DEFAULT NULL,
  `id_rusak` int(11) DEFAULT NULL,
  `keterangan` varchar(500) DEFAULT NULL,
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemeliharaan_barang`
--

INSERT INTO `pemeliharaan_barang` (`id_pemeliharaan`, `id_barang`, `id_rusak`, `keterangan`, `tanggal`) VALUES
(16, 27, NULL, 'p', '2025-06-22');

-- --------------------------------------------------------

--
-- Table structure for table `pemesanan`
--

CREATE TABLE `pemesanan` (
  `id_pemesanan` int(11) NOT NULL,
  `id_pengajuan` int(11) DEFAULT NULL,
  `id_supplier` int(11) DEFAULT NULL,
  `tanggal_pemesanan` date NOT NULL,
  `harga` int(50) NOT NULL,
  `status` enum('Dipesan','Dikirim','Dibatalkan') DEFAULT 'Dipesan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemesanan`
--

INSERT INTO `pemesanan` (`id_pemesanan`, `id_pengajuan`, `id_supplier`, `tanggal_pemesanan`, `harga`, `status`) VALUES
(7, 2, 10, '2025-06-22', 1000000, 'Dipesan'),
(8, 3, 10, '2025-06-29', 10000000, 'Dipesan');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_peminjaman` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `id_pegawai` int(11) DEFAULT NULL,
  `jumlah_pinjam` int(11) NOT NULL,
  `tanggal_peminjaman` date NOT NULL,
  `tanggal_pengembalian` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id_peminjaman`, `id_barang`, `id_pegawai`, `jumlah_pinjam`, `tanggal_peminjaman`, `tanggal_pengembalian`) VALUES
(2, 27, 1, 1, '2025-06-22', '2025-06-30');

-- --------------------------------------------------------

--
-- Table structure for table `pemusnahan`
--

CREATE TABLE `pemusnahan` (
  `id_pemusnahan` int(11) NOT NULL,
  `id_rusak` int(11) NOT NULL,
  `id_ruangan` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `alasan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemusnahan`
--

INSERT INTO `pemusnahan` (`id_pemusnahan`, `id_rusak`, `id_ruangan`, `jumlah`, `alasan`) VALUES
(1, 30, 3, 1, 'a');

-- --------------------------------------------------------

--
-- Table structure for table `penerimaan`
--

CREATE TABLE `penerimaan` (
  `id_penerimaan` int(11) NOT NULL,
  `id_pemesanan` int(11) DEFAULT NULL,
  `tanggal_terima` date NOT NULL,
  `kondisi_barang` text DEFAULT NULL,
  `id_pegawai` int(11) DEFAULT NULL,
  `id_pengajuan` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penerimaan`
--

INSERT INTO `penerimaan` (`id_penerimaan`, `id_pemesanan`, `tanggal_terima`, `kondisi_barang`, `id_pegawai`, `id_pengajuan`) VALUES
(3, 7, '2025-06-22', 'a', 1, 2),
(4, 8, '2025-06-29', 'Baik', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan`
--

CREATE TABLE `pengajuan` (
  `id_pengajuan` int(11) NOT NULL,
  `tanggal_pengajuan` date NOT NULL,
  `pengaju` varchar(100) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `merk` varchar(100) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `satuan` varchar(50) NOT NULL,
  `id_pegawai` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengajuan`
--

INSERT INTO `pengajuan` (`id_pengajuan`, `tanggal_pengajuan`, `pengaju`, `nama_barang`, `merk`, `jumlah`, `satuan`, `id_pegawai`) VALUES
(2, '2025-06-22', 'Anwar', 'Kertas', 'SIDU', 10, 'Pak', NULL),
(3, '2025-06-29', 'aan', 'Monitor', 'LG', 10, 'Unit', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ruangan`
--

CREATE TABLE `ruangan` (
  `id_ruangan` int(11) NOT NULL,
  `nama_ruangan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ruangan`
--

INSERT INTO `ruangan` (`id_ruangan`, `nama_ruangan`) VALUES
(3, 'Ruangan EF'),
(4, 'Ruangan GH'),
(5, 'Ruangan IJ'),
(11, 'a'),
(12, 'c');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` int(11) NOT NULL,
  `nama_supplier` varchar(50) DEFAULT NULL,
  `no_telp` varchar(50) DEFAULT NULL,
  `alamat` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `nama_supplier`, `no_telp`, `alamat`) VALUES
(10, 'Udin', '08162376523', 'Kotabaru');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(50) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `nama`, `username`, `password`, `role`) VALUES
(2, 'anwar', 'admin', '$2y$10$NUsjyyy8.UP2Uj3OkUtQGepwoCeMb8BKUEoo5lnkeqNItpYUEeqt.', 'Admin'),
(3, 'Aan', 'aan', '$2y$10$PKV0aYCPpGkCNl8R3Zz/m.RBB.T1MkbceXdv/wvgiIT2u5mgxeLaS', 'Pegawai');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anggaran`
--
ALTER TABLE `anggaran`
  ADD PRIMARY KEY (`id_anggaran`),
  ADD KEY `id_pemesanan` (`id_pemesanan`);

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`);

--
-- Indexes for table `barang_rusak`
--
ALTER TABLE `barang_rusak`
  ADD PRIMARY KEY (`id_rusak`),
  ADD KEY `barang_rusak_ibfk_1` (`id_barang`);

--
-- Indexes for table `inventaris`
--
ALTER TABLE `inventaris`
  ADD PRIMARY KEY (`id_inventaris`),
  ADD KEY `id_ruangan` (`id_ruangan`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `mutasi`
--
ALTER TABLE `mutasi`
  ADD PRIMARY KEY (`id_mutasi`),
  ADD KEY `id_barang` (`id_barang`),
  ADD KEY `id_ruangan` (`id_ruangan`),
  ADD KEY `fk_mutasi_ruangan_asal` (`id_ruangan_asal`);

--
-- Indexes for table `pegawai`
--
ALTER TABLE `pegawai`
  ADD PRIMARY KEY (`id_pegawai`);

--
-- Indexes for table `pemeliharaan_barang`
--
ALTER TABLE `pemeliharaan_barang`
  ADD PRIMARY KEY (`id_pemeliharaan`),
  ADD KEY `pemeliharaan_barang_ibfk_1` (`id_barang`),
  ADD KEY `pemeliharaan_barang_ibfk_2` (`id_rusak`);

--
-- Indexes for table `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD PRIMARY KEY (`id_pemesanan`),
  ADD KEY `fk_pengajuan` (`id_pengajuan`),
  ADD KEY `fk_pemesanan_supplier` (`id_supplier`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `id_barang` (`id_barang`),
  ADD KEY `id_pegawai` (`id_pegawai`);

--
-- Indexes for table `pemusnahan`
--
ALTER TABLE `pemusnahan`
  ADD PRIMARY KEY (`id_pemusnahan`),
  ADD KEY `id_rusak` (`id_rusak`),
  ADD KEY `id_ruangan` (`id_ruangan`);

--
-- Indexes for table `penerimaan`
--
ALTER TABLE `penerimaan`
  ADD PRIMARY KEY (`id_penerimaan`),
  ADD KEY `fk_pemesanan_penerimaan` (`id_pemesanan`),
  ADD KEY `fk_penerimaan_pegawai` (`id_pegawai`),
  ADD KEY `fk_penerimaan_pengajuan` (`id_pengajuan`);

--
-- Indexes for table `pengajuan`
--
ALTER TABLE `pengajuan`
  ADD PRIMARY KEY (`id_pengajuan`),
  ADD KEY `fk_pengajuan_pegawai` (`id_pegawai`);

--
-- Indexes for table `ruangan`
--
ALTER TABLE `ruangan`
  ADD PRIMARY KEY (`id_ruangan`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anggaran`
--
ALTER TABLE `anggaran`
  MODIFY `id_anggaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `barang_rusak`
--
ALTER TABLE `barang_rusak`
  MODIFY `id_rusak` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `inventaris`
--
ALTER TABLE `inventaris`
  MODIFY `id_inventaris` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mutasi`
--
ALTER TABLE `mutasi`
  MODIFY `id_mutasi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pegawai`
--
ALTER TABLE `pegawai`
  MODIFY `id_pegawai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pemeliharaan_barang`
--
ALTER TABLE `pemeliharaan_barang`
  MODIFY `id_pemeliharaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `pemesanan`
--
ALTER TABLE `pemesanan`
  MODIFY `id_pemesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_peminjaman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pemusnahan`
--
ALTER TABLE `pemusnahan`
  MODIFY `id_pemusnahan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `penerimaan`
--
ALTER TABLE `penerimaan`
  MODIFY `id_penerimaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pengajuan`
--
ALTER TABLE `pengajuan`
  MODIFY `id_pengajuan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ruangan`
--
ALTER TABLE `ruangan`
  MODIFY `id_ruangan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anggaran`
--
ALTER TABLE `anggaran`
  ADD CONSTRAINT `anggaran_ibfk_1` FOREIGN KEY (`id_pemesanan`) REFERENCES `pemesanan` (`id_pemesanan`);

--
-- Constraints for table `barang_rusak`
--
ALTER TABLE `barang_rusak`
  ADD CONSTRAINT `barang_rusak_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `inventaris`
--
ALTER TABLE `inventaris`
  ADD CONSTRAINT `inventaris_ibfk_1` FOREIGN KEY (`id_ruangan`) REFERENCES `ruangan` (`id_ruangan`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventaris_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE;

--
-- Constraints for table `mutasi`
--
ALTER TABLE `mutasi`
  ADD CONSTRAINT `fk_mutasi_ruangan_asal` FOREIGN KEY (`id_ruangan_asal`) REFERENCES `ruangan` (`id_ruangan`) ON DELETE CASCADE,
  ADD CONSTRAINT `mutasi_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE,
  ADD CONSTRAINT `mutasi_ibfk_2` FOREIGN KEY (`id_ruangan`) REFERENCES `ruangan` (`id_ruangan`) ON DELETE CASCADE;

--
-- Constraints for table `pemeliharaan_barang`
--
ALTER TABLE `pemeliharaan_barang`
  ADD CONSTRAINT `pemeliharaan_barang_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `pemeliharaan_barang_ibfk_2` FOREIGN KEY (`id_rusak`) REFERENCES `barang_rusak` (`id_rusak`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD CONSTRAINT `fk_pemesanan_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pengajuan` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan` (`id_pengajuan`) ON DELETE SET NULL;

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id_pegawai`) ON DELETE SET NULL;

--
-- Constraints for table `pemusnahan`
--
ALTER TABLE `pemusnahan`
  ADD CONSTRAINT `pemusnahan_ibfk_1` FOREIGN KEY (`id_rusak`) REFERENCES `barang_rusak` (`id_rusak`) ON DELETE CASCADE,
  ADD CONSTRAINT `pemusnahan_ibfk_2` FOREIGN KEY (`id_ruangan`) REFERENCES `ruangan` (`id_ruangan`) ON DELETE CASCADE;

--
-- Constraints for table `penerimaan`
--
ALTER TABLE `penerimaan`
  ADD CONSTRAINT `fk_pemesanan_penerimaan` FOREIGN KEY (`id_pemesanan`) REFERENCES `pemesanan` (`id_pemesanan`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_penerimaan_pegawai` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id_pegawai`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_penerimaan_pengajuan` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan` (`id_pengajuan`) ON DELETE SET NULL;

--
-- Constraints for table `pengajuan`
--
ALTER TABLE `pengajuan`
  ADD CONSTRAINT `fk_pengajuan_pegawai` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id_pegawai`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
