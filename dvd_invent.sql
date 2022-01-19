-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Värd: 127.0.0.1
-- Tid vid skapande: 19 jan 2022 kl 02:55
-- Serverversion: 10.4.18-MariaDB
-- PHP-version: 7.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databas: `dvd_invent`
--
CREATE DATABASE IF NOT EXISTS `dvd_invent` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `dvd_invent`;

-- --------------------------------------------------------

--
-- Tabellstruktur `case`
--

CREATE TABLE `case` (
  `id` int(11) NOT NULL,
  `c_short_name` varchar(11) DEFAULT NULL,
  `location` char(2) NOT NULL,
  `insert_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='For DVD cases in boxes';

-- --------------------------------------------------------

--
-- Tabellstruktur `case_film`
--

CREATE TABLE `case_film` (
  `case_id` int(11) NOT NULL,
  `film_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellstruktur `film`
--

CREATE TABLE `film` (
  `id` int(11) NOT NULL,
  `f_short_name` varchar(11) DEFAULT NULL,
  `insert_date` datetime NOT NULL DEFAULT current_timestamp(),
  `imdb_code` varchar(55) DEFAULT NULL COMMENT 'To link to imdb site'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='For a film, typically a DVD';

-- --------------------------------------------------------

--
-- Tabellstruktur `film_title`
--

CREATE TABLE `film_title` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `film_id` int(11) NOT NULL,
  `insert_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='titles of film in certain language';

-- --------------------------------------------------------

--
-- Tabellstruktur `inventory_info`
--

CREATE TABLE `inventory_info` (
  `created_date` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Time for insert into table just',
  `inventory_location` varchar(100) NOT NULL,
  `inventory_image_file` varchar(111) NOT NULL,
  `images_as_base64` int(11) NOT NULL DEFAULT 1 COMMENT '0=no, 1=yes',
  `inventory_name` varchar(100) NOT NULL,
  `inventory_contact_info` varchar(200) NOT NULL COMMENT 'name, phone etc.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Index för dumpade tabeller
--

--
-- Index för tabell `case`
--
ALTER TABLE `case`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `case_film`
--
ALTER TABLE `case_film`
  ADD UNIQUE KEY `case_id` (`case_id`,`film_id`),
  ADD KEY `film_id` (`film_id`);

--
-- Index för tabell `film`
--
ALTER TABLE `film`
  ADD PRIMARY KEY (`id`);

--
-- Index för tabell `film_title`
--
ALTER TABLE `film_title`
  ADD PRIMARY KEY (`id`),
  ADD KEY `film_title_film` (`film_id`);

--
-- AUTO_INCREMENT för dumpade tabeller
--

--
-- AUTO_INCREMENT för tabell `case`
--
ALTER TABLE `case`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT för tabell `film`
--
ALTER TABLE `film`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT för tabell `film_title`
--
ALTER TABLE `film_title`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restriktioner för dumpade tabeller
--

--
-- Restriktioner för tabell `case_film`
--
ALTER TABLE `case_film`
  ADD CONSTRAINT `case_film_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `case` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_film_ibfk_2` FOREIGN KEY (`film_id`) REFERENCES `film` (`id`);

--
-- Restriktioner för tabell `film_title`
--
ALTER TABLE `film_title`
  ADD CONSTRAINT `film_title_film` FOREIGN KEY (`film_id`) REFERENCES `film` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
