-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Oct 27, 2025 at 07:13 PM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bts1aurlom`
--

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `ID_Client` int NOT NULL,
  `Identifiant` varchar(50) NOT NULL,
  `MDP` varchar(50) NOT NULL,
  `Prenom` varchar(50) DEFAULT NULL,
  `Nom` varchar(50) NOT NULL,
  `Mail` varchar(50) DEFAULT NULL,
  `Tel` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`ID_Client`, `Identifiant`, `MDP`, `Prenom`, `Nom`, `Mail`, `Tel`) VALUES
(1, 'Steph', 'Stephane1000', 'Stephane', 'Halimi', 'sha852716@gmail.com', '06 55 02 69 60'),
(2, 'manu', 'bigmac08', 'Emmanuel', 'Macron', 'bigmac@elysee.fr', '00 00 00 00 80'),
(4, 'Alexandre', 'Narutodu8303?', '', 'Alexandre', 'alexklsy@proton.me', '01 23 45 67 89'),
(8, 'Bouhz', 'Azerty123', '', 'Bouhz', 'ishbiqbhsx@gmail.com', '');

-- --------------------------------------------------------

--
-- Table structure for table `marque`
--

CREATE TABLE `marque` (
  `ID_Marque` int NOT NULL,
  `Nom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `marque`
--

INSERT INTO `marque` (`ID_Marque`, `Nom`) VALUES
(1, 'ASUS'),
(2, 'MSI'),
(3, 'ACER'),
(4, 'LENOVO'),
(5, 'DELL'),
(6, 'HP'),
(7, 'TOSHIBA'),
(8, 'APPLE');

-- --------------------------------------------------------

--
-- Table structure for table `panier`
--

CREATE TABLE `panier` (
  `ID_Client` int NOT NULL,
  `ID_PRO` int NOT NULL,
  `Qte` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `panier`
--

INSERT INTO `panier` (`ID_Client`, `ID_PRO`, `Qte`) VALUES
(4, 3, 4);

-- --------------------------------------------------------

--
-- Table structure for table `produit`
--

CREATE TABLE `produit` (
  `ID_PRO` int NOT NULL,
  `Nom` varchar(50) NOT NULL,
  `Description` mediumtext,
  `Prix` decimal(15,3) NOT NULL,
  `ID_Marque` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produit`
--

INSERT INTO `produit` (`ID_PRO`, `Nom`, `Description`, `Prix`, `ID_Marque`) VALUES
(1, 'HP 22-dg0000nf', '21,5\" FHD - Intel N100 - RAM 8Go - Stockage 256Go - Windows 11 - Clavier & souris filaires', 449.000, 6),
(2, 'CSL Computer', 'AMD Ryzen 5 5500 - Radeon RX 6400 - 16 Go RAM - 500 Go SSD', 556.000, 2),
(3, 'BEASTCOM Q3', 'Vibox I-100 PC Gamer - 22\" Écran Pack - Quad Core AMD Ryzen 3200G - Radeon Vega 8 - 16Go RAM - 500Go NVMe SSD - Linux - WiFi', 180.000, NULL),
(4, 'Chromebook 314 ', 'PC Portable ACER Chromebook 314 CB314-2H-K04F - 14\" FHD - MTK MT8183 - RAM 4Go - Stockage 32Go - Chrome OS - AZERTY', 470.000, 3),
(5, 'PC portable 16\"-Intel', 'Core i5-8210Y(Jusqu\'à 3,6 GHz)-16Go RAM DDR3 512Go SSD-Windows 11PRO-RJ45-Ordinateur Portable-AZERTY', 349.000, NULL),
(6, 'PC HP Compaq Pro 6300 SFF', 'PC HP Compaq Pro 6300 SFF (Slim) - Intel Pentium Dual-Core G630 2.7 Ghz (3 Mo) - 16 Go DDR3 - Disque Dur 3,5\" 2 To - Graveur DVD - Clef Wifi USB - Windows 10 - Livré avec Ecran 22\" + clavier + souris (Marques et modèles variables) - Garantie 1 an.', 349.000, 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`ID_Client`);

--
-- Indexes for table `marque`
--
ALTER TABLE `marque`
  ADD PRIMARY KEY (`ID_Marque`);

--
-- Indexes for table `panier`
--
ALTER TABLE `panier`
  ADD PRIMARY KEY (`ID_Client`,`ID_PRO`),
  ADD KEY `Panier_Produit0_FK` (`ID_PRO`);

--
-- Indexes for table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`ID_PRO`),
  ADD KEY `Produit_Marque_FK` (`ID_Marque`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `ID_Client` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `marque`
--
ALTER TABLE `marque`
  MODIFY `ID_Marque` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `produit`
--
ALTER TABLE `produit`
  MODIFY `ID_PRO` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `panier`
--
ALTER TABLE `panier`
  ADD CONSTRAINT `Panier_Client_FK` FOREIGN KEY (`ID_Client`) REFERENCES `client` (`ID_Client`),
  ADD CONSTRAINT `Panier_Produit0_FK` FOREIGN KEY (`ID_PRO`) REFERENCES `produit` (`ID_PRO`);

--
-- Constraints for table `produit`
--
ALTER TABLE `produit`
  ADD CONSTRAINT `Produit_Marque_FK` FOREIGN KEY (`ID_Marque`) REFERENCES `marque` (`ID_Marque`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
