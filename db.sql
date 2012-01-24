-- phpMyAdmin SQL Dump
-- version 3.3.2deb1ubuntu1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 24, 2012 at 08:46 PM
-- Server version: 5.1.41
-- PHP Version: 5.3.2-1ubuntu4.11

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `dundef`
--

-- --------------------------------------------------------

--
-- Table structure for table `layouts`
--

CREATE TABLE IF NOT EXISTS `layouts` (
  `layoutid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Internal Layout ID',
  `publicid` varchar(20) NOT NULL COMMENT 'Public Layout ID',
  `parentid` varchar(20) DEFAULT NULL COMMENT 'Parent Layout Public ID',
  `ownerid` int(11) DEFAULT NULL COMMENT 'Owner of this layout',
  `rating` int(11) NOT NULL COMMENT 'Rating for this layout',
  `units` int(11) NOT NULL COMMENT 'Defense units allowed in this layout',
  `level` int(11) NOT NULL COMMENT 'Level this layout is for',
  `notes` text NOT NULL COMMENT 'Notes for this layout',
  `classes` set('apprentice','squire','monk','huntress') NOT NULL COMMENT 'Classes required for this layout',
  PRIMARY KEY (`layoutid`),
  UNIQUE KEY `publicid` (`publicid`),
  FOREIGN KEY (`parentid`) REFERENCES layouts(`publicid`) ON DELETE SET NULL
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `towers`
--

CREATE TABLE IF NOT EXISTS `towers` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Tower ID',
  `layoutid` int(11) NOT NULL COMMENT 'Layout ID',
  `type` varchar(20) NOT NULL COMMENT 'Tower Type',
  `top` decimal(10,0) NOT NULL COMMENT 'Top Position',
  `left` decimal(10,0) NOT NULL COMMENT 'Left Position',
  `rotation` decimal(10,0) NOT NULL COMMENT 'Tower Rotation',
  `cost` tinyint(4) NOT NULL COMMENT 'Tower Cost',
  `scale` decimal(10,0) NOT NULL COMMENT 'Tower Scale',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`layoutid`) REFERENCES layouts(`layoutid`) ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
