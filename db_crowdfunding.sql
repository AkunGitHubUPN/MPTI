-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 19, 2025 at 01:32 PM
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
-- Database: `db_crowdfunding`
--

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `target_donasi` decimal(15,2) NOT NULL,
  `dana_terkumpul` decimal(15,2) DEFAULT 0.00,
  `batas_waktu` date NOT NULL,
  `gambar_url` varchar(255) DEFAULT 'default.jpg',
  `rejection_reason` text DEFAULT NULL,
  `ktp_file` varchar(255) DEFAULT NULL,
  `kk_file` varchar(255) DEFAULT NULL,
  `surat_polisi_file` varchar(255) DEFAULT NULL,
  `foto_diri_file` varchar(255) DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `status` enum('pending','active','rejected','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campaigns`
--

INSERT INTO `campaigns` (`id`, `user_id`, `judul`, `deskripsi`, `target_donasi`, `dana_terkumpul`, `batas_waktu`, `gambar_url`, `rejection_reason`, `ktp_file`, `kk_file`, `surat_polisi_file`, `foto_diri_file`, `admin_notes`, `status`, `created_at`) VALUES
(4, 7, 'Sumbangan Dana Untuk Himasisfo', 'Bergabunglah dalam kampanye donasi untuk Himasisfo! Setiap sumbangan Anda akan mendukung kegiatan dan pengembangan mahasiswa, memperkuat komunitas, dan menciptakan peluang belajar yang lebih baik. Mari bersama-sama wujudkan perubahan positif!', 20000000.00, 2100000.00, '2026-02-28', 'default.jpg', NULL, 'ktp_7_1766080369.png', 'kk_7_1766080369.png', 'surat_7_1766080369.png', 'foto_7_1766080369.png', NULL, 'active', '2025-12-18 17:52:49');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama_donatur` varchar(100) DEFAULT 'Hamba Allah',
  `jumlah_kotor` decimal(15,2) NOT NULL,
  `biaya_admin` decimal(15,2) NOT NULL,
  `jumlah_bersih` decimal(15,2) NOT NULL,
  `pesan_dukungan` text DEFAULT NULL,
  `status_pembayaran` enum('pending','paid') DEFAULT 'paid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama_lengkap`, `email`, `password`, `no_hp`, `role`, `created_at`) VALUES
(5, 'Stefano Garrent Khristiawan', 'stefano.garrentk@gmail.com', '$2y$10$xI31hL40rjyHGyLh4cpd0uiV6K9dnX401aiMmryA0fNGqzlGXaQZi', '081386296745', 'admin', '2025-12-18 17:27:46'),
(7, 'Nobel', 'bel@bel.bel', '$2y$10$XOgYWvB7byz0c4kFJTxGwuEYc8AOut6YI1XNEsbbLQlWUVkvLCjk6', '081386290000', 'user', '2025-12-18 17:48:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campaign_id` (`campaign_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `campaigns_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
