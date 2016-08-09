-- phpMyAdmin SQL Dump
-- version 4.1.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 09, 2016 at 01:44 AM
-- Server version: 5.6.22
-- PHP Version: 5.5.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `db_fbpostmon`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `del_flg` tinyint(4) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `fb_post_id` int(11) NOT NULL,
  `comment_uid` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `attachment` text,
  `comment_reply` varchar(255) DEFAULT NULL,
  `is_hidden` tinyint(4) NOT NULL DEFAULT '0',
  `from_uid` varchar(255) NOT NULL,
  `from_name` varchar(255) NOT NULL,
  `created_time` datetime NOT NULL,
  `send_flg` tinyint(4) NOT NULL DEFAULT '0',
  `del_flg` tinyint(4) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `indx2` (`comment_uid`),
  KEY `indx3` (`fb_post_id`),
  KEY `indx4` (`created_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fb_page`
--

CREATE TABLE `fb_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manager_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `page_uid` varchar(255) DEFAULT NULL,
  `page_url` varchar(255) DEFAULT NULL,
  `monitor_flg` tinyint(4) NOT NULL DEFAULT '0',
  `monitor_range` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:最新投稿5件, 2:過去すべて',
  `monitor_config_draft` text COMMENT '担当者ドラフト',
  `all_post_crawled` tinyint(4) NOT NULL DEFAULT '0' COMMENT '過去すべてデータを取得したことがある',
  `is_hidden` tinyint(4) NOT NULL DEFAULT '0',
  `del_flg` tinyint(4) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `indx2` (`page_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fb_page_admin`
--

CREATE TABLE `fb_page_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fb_page_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `del_flg` tinyint(4) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `fb_post`
--

CREATE TABLE `fb_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fb_page_id` int(11) NOT NULL,
  `post_uid` varchar(255) NOT NULL,
  `post_url` varchar(255) NOT NULL,
  `message` text,
  `created_time` datetime NOT NULL,
  `del_flg` tinyint(4) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `indx3` (`post_uid`),
  KEY `indx2` (`fb_page_id`),
  KEY `indx4` (`created_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `manager`
--

CREATE TABLE `manager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `facebook_uid` varchar(20) DEFAULT NULL,
  `access_token` varchar(255) DEFAULT NULL COMMENT 'long live access token',
  `expire_date` datetime DEFAULT NULL COMMENT 'access token expire date',
  `del_flg` tinyint(4) DEFAULT '0',
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `indx2` (`facebook_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
