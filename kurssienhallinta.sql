-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2025 at 12:56 PM
-- Server version: 10.4.6-MariaDB
-- PHP Version: 7.3.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kurssienhallinta`
--

-- --------------------------------------------------------

--
-- Table structure for table `kurssikirjautumiset`
--

CREATE TABLE `kurssikirjautumiset` (
  `kirjautumis_ID` int(10) NOT NULL,
  `opiskelija_ID` int(10) NOT NULL,
  `kurssi_ID` int(10) NOT NULL,
  `kirjautumisaika` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `kurssikirjautumiset`
--

INSERT INTO `kurssikirjautumiset` (`kirjautumis_ID`, `opiskelija_ID`, `kurssi_ID`, `kirjautumisaika`) VALUES
(1, 1, 1, '2025-10-09 11:31:29');

-- --------------------------------------------------------

--
-- Table structure for table `kurssit`
--

CREATE TABLE `kurssit` (
  `kurssi_ID` int(10) NOT NULL,
  `nimi` varchar(50) NOT NULL,
  `kuvaus` text DEFAULT NULL,
  `alkupaiva` date NOT NULL,
  `loppupaiva` date NOT NULL,
  `opettaja_ID` int(11) DEFAULT NULL,
  `tila_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `kurssit`
--

INSERT INTO `kurssit` (`kurssi_ID`, `nimi`, `kuvaus`, `alkupaiva`, `loppupaiva`, `opettaja_ID`, `tila_ID`) VALUES
(1, 'Matematiikka', 'matematiikan kurssi2', '2025-10-08', '2025-10-16', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `opettajat`
--

CREATE TABLE `opettajat` (
  `opettaja_ID` int(11) NOT NULL,
  `etunimi` varchar(50) NOT NULL,
  `sukunimi` varchar(50) NOT NULL,
  `aine` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `opettajat`
--

INSERT INTO `opettajat` (`opettaja_ID`, `etunimi`, `sukunimi`, `aine`) VALUES
(1, 'Bogdan', 'Udrescu', 'Matematiikka');

-- --------------------------------------------------------

--
-- Table structure for table `opiskelijat`
--

CREATE TABLE `opiskelijat` (
  `opiskelijat_ID` int(11) NOT NULL,
  `etunimi` varchar(50) DEFAULT NULL,
  `sukunimi` varchar(50) DEFAULT NULL,
  `syntymapaiva` date DEFAULT NULL,
  `vuosikurssi` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `opiskelijat`
--

INSERT INTO `opiskelijat` (`opiskelijat_ID`, `etunimi`, `sukunimi`, `syntymapaiva`, `vuosikurssi`) VALUES
(1, 'Jonttu', 'Aalto', '2025-01-24', 3);

-- --------------------------------------------------------

--
-- Table structure for table `tilat`
--

CREATE TABLE `tilat` (
  `tila_ID` int(11) NOT NULL,
  `nimi` varchar(50) NOT NULL,
  `kapasiteetti` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tilat`
--

INSERT INTO `tilat` (`tila_ID`, `nimi`, `kapasiteetti`) VALUES
(1, 'A195', 25);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kurssikirjautumiset`
--
ALTER TABLE `kurssikirjautumiset`
  ADD PRIMARY KEY (`kirjautumis_ID`),
  ADD KEY `opiskelija_ID` (`opiskelija_ID`),
  ADD KEY `kurssi_ID` (`kurssi_ID`);

--
-- Indexes for table `kurssit`
--
ALTER TABLE `kurssit`
  ADD PRIMARY KEY (`kurssi_ID`),
  ADD KEY `fk_opettaja` (`opettaja_ID`),
  ADD KEY `fk_kurssit_tilat` (`tila_ID`);

--
-- Indexes for table `opettajat`
--
ALTER TABLE `opettajat`
  ADD PRIMARY KEY (`opettaja_ID`);

--
-- Indexes for table `opiskelijat`
--
ALTER TABLE `opiskelijat`
  ADD PRIMARY KEY (`opiskelijat_ID`);

--
-- Indexes for table `tilat`
--
ALTER TABLE `tilat`
  ADD PRIMARY KEY (`tila_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kurssikirjautumiset`
--
ALTER TABLE `kurssikirjautumiset`
  MODIFY `kirjautumis_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kurssit`
--
ALTER TABLE `kurssit`
  MODIFY `kurssi_ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `opettajat`
--
ALTER TABLE `opettajat`
  MODIFY `opettaja_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tilat`
--
ALTER TABLE `tilat`
  MODIFY `tila_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kurssit`
--
ALTER TABLE `kurssit`
  ADD CONSTRAINT `fk_kurssit_opettajat` FOREIGN KEY (`opettaja_ID`) REFERENCES `opettajat` (`opettaja_ID`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
