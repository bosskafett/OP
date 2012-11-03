-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 25, 2012 at 05:40 PM
-- Server version: 5.1.44
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `CPPSR`
--
CREATE DATABASE `CPPSR` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `CPPSR`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(300) NOT NULL,
  `crumbs` text NOT NULL,
  `head` int(11) NOT NULL,
  `body` int(11) NOT NULL,
  `face` int(11) NOT NULL,
  `neck` int(11) NOT NULL,
  `feet` int(11) NOT NULL,
  `hand` int(11) NOT NULL,
  `color` int(11) NOT NULL,
  `coins` int(11) NOT NULL,
  `inventory` text NOT NULL,
  `photo` int(11) NOT NULL,
  `flag` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `crumbs`, `head`, `body`, `face`, `neck`, `feet`, `hand`, `color`, `coins`, `inventory`, `photo`, `flag`) VALUES
(1, 'Sauron', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', '', 485, 775, 0, 0, 0, 321, 23, 0, '1/2/3/4/5/6/7/8/9/10/11/12/13/14/15/413/23/4039/4035/4034/401\r/403/413/4033/569/322\r/44/413/403/492/415/416/417/418/294/415/416/417/421\r/422/19072/14205/626/1175/467/430\r/430/19072/19069/11166/5014/321/775/1043/677/485/458/408', 19069, 626),
(2, 'Test', '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8', '', 0, 0, 0, 0, 0, 0, 0, 0, '', 0, 0);
