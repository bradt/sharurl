-- phpMyAdmin SQL Dump
-- version 2.10.0.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Aug 09, 2012 at 08:47 PM
-- Server version: 5.0.92
-- PHP Version: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

-- 
-- Table structure for table `coupons`
-- 

CREATE TABLE `coupons` (
  `code` varchar(16) NOT NULL,
  `discount` int(10) NOT NULL,
  `limit` int(10) NOT NULL,
  `expires` datetime NOT NULL,
  `notes` varchar(255) NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `downloads`
-- 

CREATE TABLE `downloads` (
  `package_id` int(11) NOT NULL,
  `day` date NOT NULL,
  `count` int(11) NOT NULL,
  `bytes` bigint(20) NOT NULL,
  PRIMARY KEY  (`package_id`,`day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `files`
-- 

CREATE TABLE `files` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `filename` varchar(128) NOT NULL,
  `type` varchar(32) NOT NULL,
  `size` bigint(20) NOT NULL,
  `package_id` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1750 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `invites`
-- 

CREATE TABLE `invites` (
  `code` varchar(16) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `count` int(10) NOT NULL,
  `notes` varchar(255) NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `ipn`
-- 

CREATE TABLE `ipn` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `txn_id` varchar(255) NOT NULL,
  `mc_currency` varchar(255) NOT NULL,
  `mc_gross` varchar(255) NOT NULL,
  `payment_status` varchar(255) NOT NULL,
  `item_number` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `txn_type` varchar(255) NOT NULL,
  `receiver_email` varchar(255) NOT NULL,
  `payer_email` varchar(255) NOT NULL,
  `custom` varchar(255) NOT NULL,
  `memo` text NOT NULL,
  `raw_data` text NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `packages`
-- 

CREATE TABLE `packages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `alias` varchar(32) default NULL,
  `token` varchar(128) NOT NULL,
  `server_id` int(10) unsigned NOT NULL,
  `file_count` int(10) unsigned NOT NULL,
  `size` bigint(20) unsigned NOT NULL default '0',
  `expires` datetime NOT NULL,
  `options` text NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `status` enum('init','uploading','complete','expired','deleted') NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `token` (`token`),
  UNIQUE KEY `alias` (`alias`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=384 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `payments`
-- 

CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL,
  `plan_id` int(10) unsigned NOT NULL,
  `ipn_id` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `plans`
-- 

CREATE TABLE `plans` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL,
  `slug` varchar(64) NOT NULL,
  `desc` varchar(255) NOT NULL,
  `cost` float NOT NULL,
  `storage` int(11) NOT NULL,
  `bandwidth` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

-- 
-- Dumping data for table `plans`
-- 

INSERT INTO `plans` VALUES (1, 'Small', 'small', 'Personal use', 5, 5, 50, 1, '2010-03-27 19:34:41');
INSERT INTO `plans` VALUES (2, 'Regular', 'regular', 'Professional use', 8, 10, 100, 1, '2010-03-27 19:34:41');
INSERT INTO `plans` VALUES (3, 'Large', 'large', 'Business use', 16, 20, 200, 1, '2010-03-27 19:35:22');
INSERT INTO `plans` VALUES (4, 'Free', 'free', '', 0, 2, 5, 1, '2010-03-27 20:09:22');

-- --------------------------------------------------------

-- 
-- Table structure for table `reports`
-- 

CREATE TABLE `reports` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(128) NOT NULL,
  `query` text NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- 
-- Dumping data for table `reports`
-- 

INSERT INTO `reports` VALUES (1, 'Completed Uploads by User', 'SELECT u.email, COUNT(p.id) as package_count, MAX(p.created) as last_upload\r\nFROM users u\r\nINNER JOIN packages p ON p.user_id = u.id\r\nWHERE p.status = ''complete''\r\nGROUP BY u.email\r\nORDER BY COUNT(p.id) DESC', '2010-04-17 00:00:00', '2010-04-17 00:00:00');
INSERT INTO `reports` VALUES (2, 'Usage by User', 'SELECT u.email, COUNT(p.id) as packages, FORMAT( ( SUM(p.size) / 1048576 ), 2 ) as space_used\r\nFROM users u\r\nINNER JOIN packages p ON p.user_id = u.id\r\nWHERE p.status = ''complete''\r\nGROUP BY u.email\r\nORDER BY SUM(p.size) DESC', '2010-04-17 00:00:00', '2010-04-17 00:00:00');
INSERT INTO `reports` VALUES (3, 'Package Detail by User', 'SELECT u.id as user_id, u.email, p.alias as package_alias, COUNT(f.id) as file_count, FORMAT( ( SUM(f.size) / 1048576 ), 2 ) as space_used\r\nFROM users u\r\nINNER JOIN packages p ON p.user_id = u.id\r\nINNER JOIN files f ON f.package_id = p.id\r\nWHERE p.status = ''complete''\r\nGROUP BY u.id, u.email, p.alias\r\nORDER BY SUM(p.size) DESC', '2010-04-17 00:00:00', '2010-04-17 00:00:00');
INSERT INTO `reports` VALUES (4, 'Bandwidth by User', 'SELECT u.id as user_id, u.email, p.alias as package_alias, DATE_FORMAT(`day`, ''%Y-%m'') as `date`, SUM(`count`) as `count`, FORMAT( ( SUM( d.bytes ) / 1048576 ), 2 ) as megabytes\r\nFROM users u\r\nINNER JOIN packages p ON p.user_id = u.id\r\nINNER JOIN downloads d ON p.id = d.package_id\r\nWHERE p.status = ''complete''\r\nGROUP BY u.id, u.email, p.alias, CONCAT(YEAR(`day`), ''-'',  MONTH(`day`))\r\nORDER BY DATE_FORMAT(`day`, ''%Y-%m'') DESC, SUM(d.bytes) DESC', '2010-04-17 00:00:00', '2010-04-17 00:00:00');
INSERT INTO `reports` VALUES (5, 'New Users in Last 30 Days', '\r\nSELECT id, email, created\r\nFROM users u\r\nWHERE created > date_sub(now(),interval 30 day)\r\nORDER BY created DESC', '2010-04-17 00:00:00', '2010-04-17 00:00:00');
INSERT INTO `reports` VALUES (6, 'Latest User Signups', 'SELECT id, fname, lname, email, created\r\nFROM users u\r\nORDER BY created DESC', '2010-04-17 00:00:00', '2010-04-17 00:00:00');

-- --------------------------------------------------------

-- 
-- Table structure for table `servers`
-- 

CREATE TABLE `servers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `hostname` varchar(32) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `servers`
-- 

INSERT INTO `servers` VALUES (1, 'dl.sharurl.com', '2008-11-08 00:00:00');

-- --------------------------------------------------------

-- 
-- Table structure for table `settings`
-- 

CREATE TABLE `settings` (
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `updated` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `settings`
-- 

INSERT INTO `settings` VALUES ('log_seek', '701013', '2012-08-10 00:10:01');
INSERT INTO `settings` VALUES ('log_last_date', '1344557057', '2012-08-10 00:10:01');
INSERT INTO `settings` VALUES ('paypal_business', 'support@sharurl.com', '2010-03-28 09:41:28');
INSERT INTO `settings` VALUES ('paypal_hostname', 'www.paypal.com', '2010-03-28 09:41:28');

-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `email` varchar(128) NOT NULL,
  `password` varchar(40) NOT NULL,
  `token` varchar(40) NOT NULL,
  `invite_code` varchar(16) NOT NULL,
  `coupon_code` varchar(16) NOT NULL,
  `activation` varchar(32) NOT NULL,
  `reset_password` varchar(32) NOT NULL,
  `plan_id` int(10) unsigned NOT NULL,
  `plan_expires` datetime NOT NULL,
  `is_admin` tinyint(4) NOT NULL,
  `ip_address` varchar(128) NOT NULL,
  `updated` datetime NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=752 ;
