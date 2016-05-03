-- phpMyAdmin SQL Dump
-- version 4.5.0.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: May 03, 2016 at 02:03 AM
-- Server version: 10.0.17-MariaDB
-- PHP Version: 5.6.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `raspberry-spi`
--

-- --------------------------------------------------------

--
-- Table structure for table `appinfo`
--

CREATE TABLE `appinfo` (
  `Field` varchar(50) NOT NULL,
  `Value` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `appinfo`
--

INSERT INTO `appinfo` (`Field`, `Value`) VALUES
('registration_state', 'open');

-- --------------------------------------------------------

--
-- Table structure for table `commands`
--

CREATE TABLE `commands` (
  `DID` int(11) NOT NULL COMMENT 'The command name',
  `Command` varchar(100) NOT NULL COMMENT 'The device the command was issued for',
  `Data` text NOT NULL COMMENT 'The additional date/arguments to the command',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp of when issued'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `ID` int(11) NOT NULL COMMENT 'The Unique ID of the device',
  `Auth_Token` char(32) NOT NULL COMMENT 'The Auth Token for the specific device',
  `Name` varchar(100) NOT NULL COMMENT 'The name of the device',
  `Address` varchar(40) DEFAULT NULL COMMENT 'The IP address of the device (v4 or v6)'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `RID` int(11) NOT NULL COMMENT 'The ID of the report',
  `DID` int(11) NOT NULL COMMENT 'The ID of the device',
  `Content` text NOT NULL COMMENT 'The contents of the report',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The timestamp of when the report was received.'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appinfo`
--
ALTER TABLE `appinfo` ADD FULLTEXT KEY `Field` (`Field`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`ID`);
ALTER TABLE `devices` ADD FULLTEXT KEY `Name` (`Name`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`RID`),
  ADD KEY `DID` (`DID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'The Unique ID of the device', AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `RID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'The ID of the report', AUTO_INCREMENT=13;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
