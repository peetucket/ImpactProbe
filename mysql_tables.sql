-- phpMyAdmin SQL Dump
-- version 3.3.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 13, 2010 at 11:38 AM
-- Server version: 5.1.37
-- PHP Version: 5.2.10-2ubuntu6.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `project_aware`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_sources`
--

CREATE TABLE IF NOT EXISTS `api_sources` (
  `api_id` smallint(5) NOT NULL AUTO_INCREMENT,
  `api_name` varchar(60) NOT NULL,
  `source_url` varchar(140) NOT NULL,
  `gather_method_name` varchar(40) NOT NULL,
  PRIMARY KEY (`api_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT INTO `api_sources` (`api_id`, `api_name`, `source_url`, `gather_method_name`) VALUES
(1, 'Twitter Search', 'http://search.twitter.com/search.json', 'twitter_search');

-- --------------------------------------------------------

--
-- Table structure for table `cached_text`
--

CREATE TABLE IF NOT EXISTS `cached_text` (
  `meta_id` int(10) NOT NULL,
  `text` mediumtext NOT NULL,
  PRIMARY KEY (`meta_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cluster_log`
--

CREATE TABLE IF NOT EXISTS `cluster_log` (
  `project_id` int(10) unsigned NOT NULL,
  `threshold` float(3,3) NOT NULL DEFAULT '0.250',
  `order` varchar(35) NOT NULL DEFAULT 'arbitrarily',
  `num_docs` int(15) NOT NULL DEFAULT '0',
  `date_clustered` int(10) unsigned NOT NULL,
  PRIMARY KEY (`project_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `doc_clusters`
--

CREATE TABLE IF NOT EXISTS `doc_clusters` (
  `meta_id` int(10) unsigned NOT NULL,
  `cluster_id` int(10) unsigned NOT NULL,
  `score` float(6,6) NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`meta_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `gather_log`
--

CREATE TABLE IF NOT EXISTS `gather_log` (
  `search_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned NOT NULL,
  `search_query` varchar(600) NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `results_gathered` mediumint(6) unsigned NOT NULL,
  `error` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`search_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `keywords_phrases`
--

CREATE TABLE IF NOT EXISTS `keywords_phrases` (
  `keyword_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned NOT NULL,
  `keyword_phrase` varchar(250) NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `exact_phrase` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `rank` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `date_added` int(10) NOT NULL,
  PRIMARY KEY (`keyword_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `keyword_metadata`
--

CREATE TABLE IF NOT EXISTS `keyword_metadata` (
  `meta_id` int(10) unsigned NOT NULL,
  `keyword_id` int(10) unsigned NOT NULL,
  `num_occurrences` int(8) unsigned NOT NULL,
  KEY `meta_id` (`meta_id`,`keyword_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `metadata`
--

CREATE TABLE IF NOT EXISTS `metadata` (
  `meta_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(10) unsigned NOT NULL,
  `url_id` int(10) unsigned NOT NULL,
  `api_id` smallint(5) unsigned NOT NULL,
  `date_published` int(10) unsigned NOT NULL,
  `date_retrieved` int(10) unsigned NOT NULL,
  `lang` varchar(50) DEFAULT NULL,
  `total_words` int(8) unsigned NOT NULL,
  `geolocation` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`meta_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `metadata_urls`
--

CREATE TABLE IF NOT EXISTS `metadata_urls` (
  `url_id` int(10) NOT NULL AUTO_INCREMENT,
  `url` varchar(600) NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`url_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE IF NOT EXISTS `projects` (
  `project_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `project_title` varchar(120) NOT NULL,
  `date_created` int(10) NOT NULL,
  `gather_interval` varchar(25) NOT NULL DEFAULT 'daily',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`project_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
