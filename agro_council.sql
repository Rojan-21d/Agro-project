-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2025 at 03:50 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `agro_council`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `adminname` varchar(30) NOT NULL,
  `adminpassword` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `adminname`, `adminpassword`) VALUES
(1, 'superadmin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `counsellor`
--

CREATE TABLE `counsellor` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `address` varchar(50) NOT NULL,
  `mobile` bigint(20) NOT NULL,
  `email` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_otp_hash` varchar(64) DEFAULT NULL,
  `reset_otp_expires_at` datetime DEFAULT NULL,
  `status` varchar(255) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `counsellor`
--

INSERT INTO `counsellor` (`id`, `name`, `address`, `mobile`, `email`, `password`, `reset_otp_hash`, `reset_otp_expires_at`, `status`) VALUES
(301, 'Ram Water', 'Bhaktapur', 9800001001, 'ram.water@agri.com', '$2y$10$ceMorStuo/Fwa7aJtdn0F.gaH.Ogh7lSnBjVPjSvFM4YmAREKlIr6', NULL, NULL, 'Approved'),
(302, 'Mina Livestock', 'Bhaktapur', 9800001002, 'mina.livestock@vet.com', '$2y$10$wsSHMt9t8cWOdWV7D7Pb8u3i4D4Kq2fbf53SgnhNfHbNHbSSBUkHK', NULL, NULL, 'Approved'),
(303, 'Sita Crop', 'Kathmandu', 9800001003, 'sita.crop@plant.com', '$2y$10$hlsA0yalh8RokKZQzwpiZepcoCVVipyczCH6V16Rnxx12WwHRisUO', NULL, NULL, 'Approved'),
(304, 'cousellro parsad', 'Bhaktapur', 9877777777, 'parsad@ex.com', '$2y$10$dbGEaPU5sANBa/hOrZvxM.BDhURH9ec2XxrtrciEA47Y06jn8hsmG', NULL, NULL, 'Approved');

-- --------------------------------------------------------

--
-- Table structure for table `farm`
--

CREATE TABLE `farm` (
  `fid` int(11) NOT NULL,
  `farm_area` varchar(50) NOT NULL,
  `farm_unit` varchar(20) NOT NULL,
  `farm_type` varchar(50) NOT NULL,
  `farmer_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farm`
--

INSERT INTO `farm` (`fid`, `farm_area`, `farm_unit`, `farm_type`, `farmer_id`) VALUES
(51, '5', 'acre', 'livestock', 201),
(52, '15', 'acre', 'crops', 202),
(53, '20', 'acre', 'mixed', 203),
(54, '15', 'acre', 'livestock', 204);

-- --------------------------------------------------------

--
-- Table structure for table `farmer`
--

CREATE TABLE `farmer` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `address` varchar(50) NOT NULL,
  `mobile` bigint(20) NOT NULL,
  `email` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_otp_hash` varchar(64) DEFAULT NULL,
  `reset_otp_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmer`
--

INSERT INTO `farmer` (`id`, `name`, `address`, `mobile`, `email`, `password`, `reset_otp_hash`, `reset_otp_expires_at`) VALUES
(201, 'Farmer One', 'Bhaktapur, Nepal', 9800000001, 'farmer1@example.com', '$2y$10$Z5SCrM87inXoeC0ijRVaMOSHvEHqUi2cXTwuo7LXK30/SlMtGuYoq', NULL, NULL),
(202, 'Farmer Two', 'Pokhara, Kaski', 9800000002, 'farmer2@example.com', '$2y$10$yELUx8yGM3NfToVQ8Ehbu.HLoHzXvO5Qz8wsNSUul2md9vzZ7fFxa', NULL, NULL),
(203, 'Farmer Three', 'Biratnagar, Morang', 9800000003, 'farmer3@example.com', '$2y$10$xE1N3LufAS5uJMiqv6Xcd.irZGrDxKD1SlrH23y1z64ZV/bO5.6YG', NULL, NULL),
(204, 'Ram Thapa Farmer', 'Bhaktapur', 9874444454, 'ramthapa@ex.com', '$2y$10$UTM4exrFqQv.1NUcoSBmT.Erqlc/3yXfx3wBjMUJz4ox6TbmzzKyW', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `guidelines`
--

CREATE TABLE `guidelines` (
  `gid` int(11) NOT NULL,
  `title` varchar(30) NOT NULL,
  `predicament_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `counsellor_id` int(11) NOT NULL,
  `submitted_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guidelines`
--

INSERT INTO `guidelines` (`gid`, `title`, `predicament_id`, `description`, `counsellor_id`, `submitted_date`) VALUES
(601, 'Controlling armyworm in maize', 403, 'Use pheromone traps, neem spray, and scouting.', 303, '2025-11-24 17:02:22'),
(602, 'Treating common goat diseases', 401, 'Sanitation and appropriate antibiotics.', 302, '2025-11-24 17:02:22'),
(603, 'Do a physical checkups', 405, 'Do a physical checkup through vet of goats and goat farm', 304, '2025-11-24 19:42:21'),
(604, 'Mixing new and old feeds to fe', 402, 'Store the feed properly in shelter that would protect from environments. Feed the mix of old and new stock as 50-50 first and is there are no improvements reduce quantity of old feeds', 304, '2025-11-24 19:44:10'),
(605, 'second guide line', 405, 'Do a physical checkup', 303, '2025-11-24 20:15:23');

-- --------------------------------------------------------

--
-- Table structure for table `predicament`
--

CREATE TABLE `predicament` (
  `pid` int(11) NOT NULL,
  `farmer_id` int(11) NOT NULL,
  `title` varchar(30) NOT NULL,
  `description` text NOT NULL,
  `submitted_date` datetime NOT NULL DEFAULT current_timestamp(),
  `priority_score` int(11) DEFAULT NULL,
  `recommended_counsellor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `predicament`
--

INSERT INTO `predicament` (`pid`, `farmer_id`, `title`, `description`, `submitted_date`, `priority_score`, `recommended_counsellor_id`) VALUES
(401, 201, 'Goat coughing and fever', 'Many goats showing fever, coughing and weakness. Might be infection.', '2025-11-24 17:02:22', NULL, NULL),
(402, 201, 'Goat feed quality issue', 'Feed looks old, animals not eating properly.', '2025-11-24 17:02:22', NULL, NULL),
(403, 202, 'Armyworm infestation in maize', 'Leaves eaten, larvae found, infestation spreading.', '2025-11-24 17:02:22', NULL, NULL),
(404, 202, 'Soil pH imbalance', 'Acidic soil harming maize seedlings.', '2025-11-24 17:02:22', NULL, NULL),
(405, 204, 'Goat coughing and fever', 'Many goats showing fever, coughing and weakness. Might be infection.', '2025-11-24 19:35:54', NULL, NULL),
(406, 204, 'Feed quality question', 'Feed looks old, animals not eating properly.', '2025-11-24 19:36:24', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `counsellor`
--
ALTER TABLE `counsellor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `reset_otp_hash` (`reset_otp_hash`);

--
-- Indexes for table `farm`
--
ALTER TABLE `farm`
  ADD PRIMARY KEY (`fid`),
  ADD KEY `farm_ibfk_1` (`farmer_id`);

--
-- Indexes for table `farmer`
--
ALTER TABLE `farmer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `reset_otp_hash` (`reset_otp_hash`);

--
-- Indexes for table `guidelines`
--
ALTER TABLE `guidelines`
  ADD PRIMARY KEY (`gid`),
  ADD KEY `counselor_id` (`counsellor_id`),
  ADD KEY `predicament_id` (`predicament_id`);

--
-- Indexes for table `predicament`
--
ALTER TABLE `predicament`
  ADD PRIMARY KEY (`pid`),
  ADD KEY `predicament_ibfk_1` (`farmer_id`),
  ADD KEY `fk_pred_rec_counsellor` (`recommended_counsellor_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `counsellor`
--
ALTER TABLE `counsellor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=305;

--
-- AUTO_INCREMENT for table `farm`
--
ALTER TABLE `farm`
  MODIFY `fid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `farmer`
--
ALTER TABLE `farmer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=205;

--
-- AUTO_INCREMENT for table `guidelines`
--
ALTER TABLE `guidelines`
  MODIFY `gid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=606;

--
-- AUTO_INCREMENT for table `predicament`
--
ALTER TABLE `predicament`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=407;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `farm`
--
ALTER TABLE `farm`
  ADD CONSTRAINT `farm_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `guidelines`
--
ALTER TABLE `guidelines`
  ADD CONSTRAINT `guidelines_ibfk_1` FOREIGN KEY (`counsellor_id`) REFERENCES `counsellor` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `guidelines_ibfk_2` FOREIGN KEY (`predicament_id`) REFERENCES `predicament` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `predicament`
--
ALTER TABLE `predicament`
  ADD CONSTRAINT `fk_pred_rec_counsellor` FOREIGN KEY (`recommended_counsellor_id`) REFERENCES `counsellor` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `predicament_ibfk_1` FOREIGN KEY (`farmer_id`) REFERENCES `farmer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
