-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 25, 2026 at 08:16 PM
-- Server version: 5.7.36
-- PHP Version: 8.1.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `adherents`
--

CREATE TABLE `adherents` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `adresse` varchar(255) NOT NULL,
  `date_paiement` date DEFAULT NULL,
  `information` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `adherents`
--

INSERT INTO `adherents` (`id`, `nom`, `prenom`, `email`, `adresse`, `date_paiement`, `information`) VALUES
(1, 'wail', 'hoa', NULL, 'tipaza', '2026-01-25', NULL),
(3, 'qs', 'sf', NULL, 'qsf', '2026-01-16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `auteurs`
--

CREATE TABLE `auteurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `auteurs`
--

INSERT INTO `auteurs` (`id`, `nom`, `prenom`) VALUES
(1, 'wail', 'hoa');

-- --------------------------------------------------------

--
-- Table structure for table `emprunts`
--

CREATE TABLE `emprunts` (
  `id` int(11) NOT NULL,
  `exemplaire_id` int(11) NOT NULL,
  `adherent_id` int(11) NOT NULL,
  `date_emprunt` date NOT NULL,
  `date_retour` date DEFAULT NULL,
  `date_retour_reelle` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `emprunts`
--

INSERT INTO `emprunts` (`id`, `exemplaire_id`, `adherent_id`, `date_emprunt`, `date_retour`, `date_retour_reelle`) VALUES
(1, 1, 1, '2026-01-25', NULL, '2026-01-25'),
(2, 1, 1, '2022-05-18', NULL, '2026-01-25'),
(3, 2, 1, '2026-01-25', NULL, '2026-01-25'),
(4, 3, 1, '2026-01-25', NULL, '2026-01-25'),
(5, 2, 1, '2026-01-25', NULL, NULL),
(6, 5, 3, '2026-01-25', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `exemplaires`
--

CREATE TABLE `exemplaires` (
  `id` int(11) NOT NULL,
  `oeuvre_id` int(11) NOT NULL,
  `etat` varchar(50) DEFAULT 'Bon',
  `prix` decimal(10,2) NOT NULL,
  `date_achat` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `exemplaires`
--

INSERT INTO `exemplaires` (`id`, `oeuvre_id`, `etat`, `prix`, `date_achat`) VALUES
(1, 1, 'Neuf', '20.00', '2026-01-25'),
(2, 1, 'Bon', '20.00', '2026-01-25'),
(3, 1, 'Usé', '10.00', '2026-01-25'),
(4, 1, 'Abîmé', '5.00', '2026-01-25'),
(5, 2, 'Neuf', '120.00', '2026-01-25');

-- --------------------------------------------------------

--
-- Table structure for table `oeuvres`
--

CREATE TABLE `oeuvres` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `date_parution` int(4) DEFAULT NULL,
  `auteur_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `oeuvres`
--

INSERT INTO `oeuvres` (`id`, `titre`, `date_parution`, `auteur_id`) VALUES
(1, 'harry', 2007, 1),
(2, 'Night', 2021, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adherents`
--
ALTER TABLE `adherents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `auteurs`
--
ALTER TABLE `auteurs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `emprunts`
--
ALTER TABLE `emprunts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exemplaire_id` (`exemplaire_id`),
  ADD KEY `adherent_id` (`adherent_id`);

--
-- Indexes for table `exemplaires`
--
ALTER TABLE `exemplaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oeuvre_id` (`oeuvre_id`);

--
-- Indexes for table `oeuvres`
--
ALTER TABLE `oeuvres`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auteur_id` (`auteur_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adherents`
--
ALTER TABLE `adherents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `auteurs`
--
ALTER TABLE `auteurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `emprunts`
--
ALTER TABLE `emprunts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `exemplaires`
--
ALTER TABLE `exemplaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `oeuvres`
--
ALTER TABLE `oeuvres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `emprunts`
--
ALTER TABLE `emprunts`
  ADD CONSTRAINT `fk_adherent` FOREIGN KEY (`adherent_id`) REFERENCES `adherents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exemplaire` FOREIGN KEY (`exemplaire_id`) REFERENCES `exemplaires` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exemplaires`
--
ALTER TABLE `exemplaires`
  ADD CONSTRAINT `fk_oeuvre` FOREIGN KEY (`oeuvre_id`) REFERENCES `oeuvres` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `oeuvres`
--
ALTER TABLE `oeuvres`
  ADD CONSTRAINT `fk_auteur` FOREIGN KEY (`auteur_id`) REFERENCES `auteurs` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
