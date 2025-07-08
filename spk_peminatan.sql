-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 08, 2025 at 01:40 PM
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
-- Database: `spk_peminatan`
--

-- --------------------------------------------------------

--
-- Table structure for table `alternatif`
--

CREATE TABLE `alternatif` (
  `id` int(11) NOT NULL,
  `kode` varchar(5) NOT NULL,
  `nama_spesialisasi` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alternatif`
--

INSERT INTO `alternatif` (`id`, `kode`, `nama_spesialisasi`) VALUES
(1, 'A1', 'Jaringan Rekayasa Sistem'),
(2, 'A2', 'Komputasi Cerdas');

-- --------------------------------------------------------

--
-- Table structure for table `kriteria`
--

CREATE TABLE `kriteria` (
  `id` int(11) NOT NULL,
  `nama_kriteria` varchar(100) NOT NULL,
  `tipe_jrs` enum('benefit','cost') DEFAULT NULL,
  `bobot_jrs` float NOT NULL,
  `tipe_kc` enum('benefit','cost') DEFAULT NULL,
  `bobot_kc` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kriteria`
--

INSERT INTO `kriteria` (`id`, `nama_kriteria`, `tipe_jrs`, `bobot_jrs`, `tipe_kc`, `bobot_kc`) VALUES
(1, 'Kalkulus', 'cost', 3, 'benefit', 5),
(2, 'Sistem Digital dan Arsitektur', 'benefit', 12, 'cost', 3),
(3, 'Matematika Diskrit', 'benefit', 7, 'benefit', 7),
(4, 'Dasar Pemrograman', 'benefit', 5, 'benefit', 8),
(5, 'Aljabar Linear', 'cost', 2, 'benefit', 9),
(6, 'Algoritma dan Struktur Data', 'benefit', 10, 'benefit', 11),
(7, 'Basis Data', 'benefit', 6, 'cost', 4),
(8, 'Jaringan Komputer', 'benefit', 14, 'cost', 2),
(9, 'Pemrograman Web', 'benefit', 4, 'benefit', 4),
(10, 'Pemrograman Berorientasi Objek', 'benefit', 6, 'benefit', 6),
(11, 'Kompleksitas Algoritma', 'benefit', 5, 'benefit', 6),
(12, 'Statistika', 'cost', 3, 'benefit', 9),
(13, 'Rekayasa Perangkat Lunak', 'benefit', 8, 'cost', 3),
(14, 'Kecerdasan Buatan', 'cost', 1, 'benefit', 13),
(15, 'Mobile Programming', 'cost', 2, 'benefit', 4),
(16, 'Keamanan Komputer dan Jaringan', 'benefit', 10, 'cost', 2),
(17, 'Pemrograman Web Lanjut', 'benefit', 2, 'benefit', 4);

-- --------------------------------------------------------

--
-- Table structure for table `nilai_mahasiswa`
--

CREATE TABLE `nilai_mahasiswa` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nilai` text DEFAULT NULL,
  `rekomendasi` varchar(50) DEFAULT NULL,
  `waktu_input` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `nim` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','mahasiswa') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `reset_token` varchar(100) DEFAULT NULL,
  `khs_file` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `nama`, `nim`, `password`, `role`, `is_active`, `reset_token`, `khs_file`) VALUES
(464, 'admin', 'admin@umkt.ac.id', NULL, NULL, '$2y$10$UwiwbOleOllSNRVNCFLQeO7pPSsg/2CChgyiJKIxLyWLoDnY9UMZS', 'admin', 1, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alternatif`
--
ALTER TABLE `alternatif`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kriteria`
--
ALTER TABLE `kriteria`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nilai_mahasiswa`
--
ALTER TABLE `nilai_mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alternatif`
--
ALTER TABLE `alternatif`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `kriteria`
--
ALTER TABLE `kriteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `nilai_mahasiswa`
--
ALTER TABLE `nilai_mahasiswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=327;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=466;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `nilai_mahasiswa`
--
ALTER TABLE `nilai_mahasiswa`
  ADD CONSTRAINT `nilai_mahasiswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
