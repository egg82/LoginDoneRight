-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jul 04, 2016 at 05:22 AM
-- Server version: 10.1.13-MariaDB
-- PHP Version: 7.0.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `web`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(255) NOT NULL,
  `user` tinytext COLLATE latin7_general_cs NOT NULL,
  `email` tinytext COLLATE latin7_general_cs,
  `pass` longtext COLLATE latin7_general_cs NOT NULL,
  `key` tinytext COLLATE latin7_general_cs NOT NULL,
  `iv` tinytext COLLATE latin7_general_cs NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin7 COLLATE=latin7_general_cs;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user`, `email`, `pass`, `key`, `iv`) VALUES
(1, 'test', NULL, 'qcaw9AqODT/K5LrMlpMxGAxGXTpEqBfldgii/Fs5YqBZUXvVd1mncBjeTUocM1cH5Jy2fp8KfA7uYBoenSbK7w4l1V4iMoO3Y9wu9LINw3wUV27YIesv+AZSAAMdmFy08CIqLEovk4YPr0LVPrSkEZLgNBOnqmE091K8N4hhPdFGyiSEHqBJVxidQ/NE+LS4', 'NE[@UeE>M2i/0+]39b)w{3.4fm"o3`x*', 'e0zurHKlZucBE/ICDrXg187UykVbj1lOTh/fS6mUOD0=');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user` (`user`(255)),
  ADD KEY `email` (`email`(255));

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
