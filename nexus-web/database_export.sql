-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql205.infinityfree.com
-- Generation Time: Jul 10, 2026 at 02:31 PM
-- Server version: 11.4.12-MariaDB
-- PHP Version: 7.2.22

USE mvvrym_sylphina;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_41646299_datacom_2026_1`
--

-- --------------------------------------------------------

--
-- Table structure for table `can_protocol`
--

CREATE TABLE `can_protocol` (
  `id` tinyint(4) NOT NULL DEFAULT 1,
  `velocidade` int(11) NOT NULL DEFAULT 0,
  `marcha` int(11) NOT NULL DEFAULT 0,
  `erro` int(11) NOT NULL DEFAULT 0,
  `horario` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `online` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `can_protocol`
--

INSERT INTO `can_protocol` (`id`, `velocidade`, `marcha`, `erro`, `horario`, `online`) VALUES
(1, 32, 0, 0, '2026-07-08 01:11:27', 1);

-- --------------------------------------------------------

--
-- Table structure for table `mqtt_protocol`
--

CREATE TABLE `mqtt_protocol` (
  `id` tinyint(4) NOT NULL DEFAULT 1,
  `temperatura` varchar(32) DEFAULT NULL,
  `estado` varchar(32) DEFAULT NULL,
  `horario` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `online` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `mqtt_protocol`
--

INSERT INTO `mqtt_protocol` (`id`, `temperatura`, `estado`, `horario`, `online`) VALUES
(1, '18.5', '---', '2026-07-08 01:11:27', 1);

-- --------------------------------------------------------

--
-- Table structure for table `profinet_protocol`
--

CREATE TABLE `profinet_protocol` (
  `id` tinyint(4) NOT NULL DEFAULT 1,
  `estado` varchar(50) DEFAULT NULL,
  `habilitar` tinyint(1) NOT NULL DEFAULT 0,
  `resetar` tinyint(1) NOT NULL DEFAULT 0,
  `frequencia` int(11) NOT NULL DEFAULT 0,
  `horario` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `online` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `profinet_protocol`
--

INSERT INTO `profinet_protocol` (`id`, `estado`, `habilitar`, `resetar`, `frequencia`, `horario`, `online`) VALUES
(1, '0', 0, 0, 0, '2026-07-08 01:11:27', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `can_protocol`
--
ALTER TABLE `can_protocol`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mqtt_protocol`
--
ALTER TABLE `mqtt_protocol`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profinet_protocol`
--
ALTER TABLE `profinet_protocol`
  ADD PRIMARY KEY (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
