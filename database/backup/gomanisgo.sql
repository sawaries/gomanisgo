-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 22, 2026 at 01:03 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gomanisgo`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `buat_transaksi` (IN `p_id_user` INT, IN `p_id_keranjang` INT)   BEGIN
                INSERT INTO transaksi (id_user, id_keranjang, total_harga, status)
                VALUES (p_id_user, p_id_keranjang, 0, 'diproses');
            END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `tambah_produk` (IN `p_id_kategori` INT, IN `p_nama_produk` VARCHAR(150), IN `p_harga` DECIMAL(12,2), IN `p_stok` INT)   BEGIN
    INSERT INTO produk (
        id_kategori,
        nama_produk,
        harga,
        stok
    )
    VALUES (
        p_id_kategori,
        p_nama_produk,
        p_harga,
        p_stok
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `tambah_user` (IN `p_nama` VARCHAR(100), IN `p_no_telepon` VARCHAR(20), IN `p_email` VARCHAR(100))   BEGIN
    INSERT INTO users (
        nama_user,
        no_telepon,
        email
    )
    VALUES (
        p_nama,
        p_no_telepon,
        p_email
    );
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `hitung_subtotal` (`p_id_produk` INT, `p_jumlah` INT) RETURNS DECIMAL(12,2) DETERMINISTIC BEGIN
    DECLARE v_harga DECIMAL(12,2);

    SELECT harga
    INTO v_harga
    FROM produk
    WHERE id_produk = p_id_produk;

    RETURN v_harga * p_jumlah;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `hitung_total_transaksi` (`p_id_transaksi` INT) RETURNS DECIMAL(12,2) DETERMINISTIC BEGIN
    DECLARE v_total DECIMAL(12,2);

    SELECT COALESCE(SUM(subtotal), 0)
    INTO v_total
    FROM detail_transaksi
    WHERE id_transaksi = p_id_transaksi;

    RETURN v_total;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `detail_keranjang`
--

CREATE TABLE `detail_keranjang` (
  `id_keranjang` int NOT NULL,
  `id_produk` int NOT NULL,
  `jumlah` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_keranjang`
--

INSERT INTO `detail_keranjang` (`id_keranjang`, `id_produk`, `jumlah`) VALUES
(1, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail_transaksi` int NOT NULL,
  `id_transaksi` int NOT NULL,
  `id_produk` int NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `jumlah` int NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail_transaksi`, `id_transaksi`, `id_produk`, `harga_satuan`, `jumlah`, `subtotal`) VALUES
(1, 1, 2, 1122333.00, 1, 1122333.00),
(2, 2, 3, 10000.00, 5, 50000.00),
(3, 3, 5, 10000000.00, 1, 10000000.00);

--
-- Triggers `detail_transaksi`
--
DELIMITER $$
CREATE TRIGGER `trigger_kurangi_stok` AFTER INSERT ON `detail_transaksi` FOR EACH ROW BEGIN
    UPDATE produk
    SET stok = stok - NEW.jumlah
    WHERE id_produk = NEW.id_produk;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trigger_update_total` AFTER INSERT ON `detail_transaksi` FOR EACH ROW BEGIN
    UPDATE transaksi
    SET total_harga = (
        SELECT SUM(subtotal)
        FROM detail_transaksi
        WHERE id_transaksi = NEW.id_transaksi
    )
    WHERE id_transaksi = NEW.id_transaksi;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `gambar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `deskripsi`, `gambar`) VALUES
(1, 'gula', 'gula gula', NULL),
(2, 'pesawat', 'bisa terbang', NULL),
(3, 'full frame', 'fulll frame', NULL),
(4, 'drone', 'bisa terbang cuy', NULL),
(5, 'stabilizer', 'biar stabil', NULL),
(6, 'lensa', 'buat kamera sih', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `keranjang`
--

CREATE TABLE `keranjang` (
  `id_keranjang` int NOT NULL,
  `id_user` int NOT NULL,
  `tanggal_dibuat` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `keranjang`
--

INSERT INTO `keranjang` (`id_keranjang`, `id_user`, `tanggal_dibuat`) VALUES
(1, 7, '2026-10-04 23:01:57'),
(2, 9, '2026-10-20 15:16:51');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_09_22_123331_add_role_to_users_table', 1),
(5, '2026_10_04_065158_add_detail_to_produk_dan_kategori', 2),
(6, '2026_10_05_000001_ubah_status_transaksi_dan_prosedur', 3);

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int NOT NULL,
  `id_transaksi` int NOT NULL,
  `metode_pembayaran` enum('COD') COLLATE utf8mb4_general_ci DEFAULT 'COD',
  `total_bayar` decimal(12,2) NOT NULL,
  `status_pembayaran` enum('Belum Dibayar','Lunas') COLLATE utf8mb4_general_ci DEFAULT 'Belum Dibayar'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_transaksi`, `metode_pembayaran`, `total_bayar`, `status_pembayaran`) VALUES
(1, 1, 'COD', 1122333.00, 'Lunas'),
(2, 2, 'COD', 50000.00, 'Lunas'),
(3, 3, 'COD', 10000000.00, 'Lunas');

--
-- Triggers `pembayaran`
--
DELIMITER $$
CREATE TRIGGER `trigger_update_status` AFTER UPDATE ON `pembayaran` FOR EACH ROW BEGIN
    IF NEW.status_pembayaran = 'Lunas' THEN
        UPDATE transaksi
        SET status = 'Selesai'
        WHERE id_transaksi = NEW.id_transaksi;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int NOT NULL,
  `id_kategori` int NOT NULL,
  `nama_produk` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `stok` int NOT NULL DEFAULT '0',
  `sku` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `satuan` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `gambar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `id_kategori`, `nama_produk`, `harga`, `stok`, `sku`, `satuan`, `deskripsi`, `gambar`, `status`) VALUES
(1, 1, 'gulaku', 50000.00, 5, 'aaaa', 'Pcs', 'gulaaa', 'produk/aEL32AYogKI15SlvnGqH8vUtDF4mocllO5joTbLr.png', 1),
(2, 1, 'gulaum', 1122333.00, 5, 'aSAd', 'Dus', 'efegrgrw', 'produk/zVei9pEWSe8fIYeiP691pWQgAsF8cI1Anhi0vn1J.png', 1),
(3, 2, 'airbus a330', 10000.00, 95, 'aaab', 'Pcs', 'bisa terbang', 'produk/pw5JOa7JLqb3tTIFsRB82wuNHvG1FXMwZaRXlIhE.jpg', 1),
(4, 3, 'sony a74', 1000000.00, 1, NULL, 'Karton', 'kamera spek kencang', 'produk/nNUNZWADW4Y5wEwbf13W5c2qo4OI0xVOnu8mJIcF.jpg', 1),
(5, 4, 'mavic 4', 10000000.00, 0, NULL, 'Karton', 'asdfewgfwerg', 'produk/XRESDILSOKoDEcAMceWedqpx9vL7Bb0JMCjpTA5v.png', 1);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int NOT NULL,
  `id_user` int NOT NULL,
  `id_keranjang` int NOT NULL,
  `total_harga` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` enum('diproses','disiapkan','siap_diambil','selesai','dibatalkan') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'diproses',
  `tanggal_transaksi` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_user`, `id_keranjang`, `total_harga`, `status`, `tanggal_transaksi`) VALUES
(1, 7, 1, 1122333.00, 'selesai', '2026-10-04 23:02:06'),
(2, 9, 2, 50000.00, 'selesai', '2026-10-20 15:17:05'),
(3, 9, 2, 10000000.00, 'selesai', '2026-10-20 15:44:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `nama_user` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `no_telepon` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama_user`, `no_telepon`, `email`, `password`, `role`) VALUES
(6, 'Admin GoManisGo', '081234567890', 'admin@gomanisgo.com', '$2y$12$rbsn5zZ8F.JDE87dE300TuYHpF.H3IWWyNRKO0PmJSkWDM2hUvbYm', 'admin'),
(7, 'wicak', '08888888888', 'sudrajatwicaksono99@gmail.com', '$2y$12$Db3bIHDnqmqUerRFLVBGA.fOhzgVXZrBPE9CEZTfezRT1nSIX4C/.', 'user'),
(8, 'rafa', '088888888889', 'intern@gmail.com', '$2y$12$xRbrZ2kuuiaCou3T/wGF.uaoSUasvXpHRcdJtS0C2K6ysISwDnW4W', 'user'),
(9, 'wicak', '08888888888', 'humas@um.ac.id', '$2y$12$lpnR8QzSEwF4kpEjZlHS6OSKTZu/S4u3jxQLJrlgtmNjW6KvKF2z.', 'user');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_detail_transaksi`
-- (See below for the actual view)
--
CREATE TABLE `view_detail_transaksi` (
`id_transaksi` int
,`nama_user` varchar(100)
,`nama_produk` varchar(150)
,`jumlah` int
,`harga_satuan` decimal(12,2)
,`subtotal` decimal(12,2)
,`total_harga` decimal(12,2)
,`status_transaksi` enum('diproses','disiapkan','siap_diambil','selesai','dibatalkan')
,`metode_pembayaran` enum('COD')
,`status_pembayaran` enum('Belum Dibayar','Lunas')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_keranjang_user`
-- (See below for the actual view)
--
CREATE TABLE `view_keranjang_user` (
`id_keranjang` int
,`nama_user` varchar(100)
,`nama_produk` varchar(150)
,`jumlah` int
,`harga` decimal(12,2)
,`subtotal` decimal(22,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_produk_kategori`
-- (See below for the actual view)
--
CREATE TABLE `view_produk_kategori` (
`id_produk` int
,`nama_produk` varchar(150)
,`nama_kategori` varchar(100)
,`harga` decimal(12,2)
,`stok` int
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_keranjang`
--
ALTER TABLE `detail_keranjang`
  ADD PRIMARY KEY (`id_keranjang`,`id_produk`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail_transaksi`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD UNIQUE KEY `id_transaksi` (`id_transaksi`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD UNIQUE KEY `produk_sku_unique` (`sku`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_keranjang` (`id_keranjang`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail_transaksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

-- --------------------------------------------------------

--
-- Structure for view `view_detail_transaksi`
--
DROP TABLE IF EXISTS `view_detail_transaksi`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_detail_transaksi`  AS SELECT `t`.`id_transaksi` AS `id_transaksi`, `u`.`nama_user` AS `nama_user`, `p`.`nama_produk` AS `nama_produk`, `dt`.`jumlah` AS `jumlah`, `dt`.`harga_satuan` AS `harga_satuan`, `dt`.`subtotal` AS `subtotal`, `t`.`total_harga` AS `total_harga`, `t`.`status` AS `status_transaksi`, `pb`.`metode_pembayaran` AS `metode_pembayaran`, `pb`.`status_pembayaran` AS `status_pembayaran` FROM ((((`transaksi` `t` join `users` `u` on((`t`.`id_user` = `u`.`id_user`))) join `detail_transaksi` `dt` on((`t`.`id_transaksi` = `dt`.`id_transaksi`))) join `produk` `p` on((`dt`.`id_produk` = `p`.`id_produk`))) join `pembayaran` `pb` on((`t`.`id_transaksi` = `pb`.`id_transaksi`))) ;

-- --------------------------------------------------------

--
-- Structure for view `view_keranjang_user`
--
DROP TABLE IF EXISTS `view_keranjang_user`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_keranjang_user`  AS SELECT `k`.`id_keranjang` AS `id_keranjang`, `u`.`nama_user` AS `nama_user`, `p`.`nama_produk` AS `nama_produk`, `dk`.`jumlah` AS `jumlah`, `p`.`harga` AS `harga`, (`dk`.`jumlah` * `p`.`harga`) AS `subtotal` FROM (((`keranjang` `k` join `users` `u` on((`k`.`id_user` = `u`.`id_user`))) join `detail_keranjang` `dk` on((`k`.`id_keranjang` = `dk`.`id_keranjang`))) join `produk` `p` on((`dk`.`id_produk` = `p`.`id_produk`))) ;

-- --------------------------------------------------------

--
-- Structure for view `view_produk_kategori`
--
DROP TABLE IF EXISTS `view_produk_kategori`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_produk_kategori`  AS SELECT `p`.`id_produk` AS `id_produk`, `p`.`nama_produk` AS `nama_produk`, `k`.`nama_kategori` AS `nama_kategori`, `p`.`harga` AS `harga`, `p`.`stok` AS `stok` FROM (`produk` `p` join `kategori` `k` on((`p`.`id_kategori` = `k`.`id_kategori`))) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_keranjang`
--
ALTER TABLE `detail_keranjang`
  ADD CONSTRAINT `detail_keranjang_ibfk_1` FOREIGN KEY (`id_keranjang`) REFERENCES `keranjang` (`id_keranjang`),
  ADD CONSTRAINT `detail_keranjang_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`),
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Constraints for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`);

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_keranjang`) REFERENCES `keranjang` (`id_keranjang`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
