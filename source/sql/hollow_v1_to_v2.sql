-- phpMyAdmin SQL Dump
-- version 3.3.8.1
-- http://www.phpmyadmin.net
--
-- 主机: w.rdc.sae.sina.com.cn:3307
-- 生成日期: 2013 年 04 月 27 日 10:10
-- 服务器版本: 5.5.23
-- PHP 版本: 5.2.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- --------------------------------------------------------
-- 数据库: `app_hollow`
USE `app_hollow`;

-- --------------------------------------------------------
-- 表的结构 `renren_oauth`
CREATE TABLE IF NOT EXISTS `renren_oauth` (
  `user_id` int(10) unsigned NOT NULL,
  `access_token` varchar(255) DEFAULT NULL,
  `refresh_token` varchar(255) DEFAULT NULL,
  `expires_time` int(10) unsigned DEFAULT NULL,
  `access_scope` varchar(255) DEFAULT NULL,
  `last_error_time` int(10) unsigned DEFAULT '0',
  `last_error_code` int(10) unsigned DEFAULT '0',
  `last_error_msg` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
-- --------------------------------------------------------
-- 转存表中的数据 `renren_oauth`
INSERT INTO `renren_oauth`(`user_id`, `access_token`, `refresh_token`, `expires_time`, `access_scope`) 
SELECT DISTINCT RIGHT(`access_token`,9) AS `user_id`, `access_token`, `refresh_token`, 0, 'read_user_album read_user_feed admin_page' 
FROM `page` 
WHERE (SELECT COUNT(*) FROM `renren_oauth` WHERE RIGHT(`page`.`access_token`,9)=`renren_oauth`.`user_id`)=0;

-- --------------------------------------------------------
-- 表的结构 `hollow`
CREATE TABLE IF NOT EXISTS `hollow` (
  `hollow_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hollow_sid` varchar(255) DEFAULT NULL,
  `hollow_sname` varchar(255) DEFAULT NULL,
  `expire_num` int(10) unsigned DEFAULT '10',
  `expire_time` int(10) unsigned DEFAULT '0',
  `hollow_state` int(11) DEFAULT '0',
  `hollow_title` varchar(255) NOT NULL DEFAULT '树洞秘密',
  `hollow_subtitle` VARCHAR( 255 ) NOT NULL DEFAULT  '放在树洞里的秘密'
  `hollow_count` int(11) NOT NULL DEFAULT '0',
  `data_prefix` varchar(255) DEFAULT NULL,
  `data_suffix` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`hollow_id`),
  UNIQUE KEY `u_hollow_sid` (`hollow_sid`),
  UNIQUE KEY `u_hollow_sname` (`hollow_sname`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
-- --------------------------------------------------------
-- 转存表中的数据 `hollow`
INSERT INTO `hollow`(`hollow_sid`, `expire_num`, `expire_time`, `hollow_state`, `hollow_title`, `hollow_count`, `data_prefix`, `data_suffix`) 
SELECT CONCAT('RR',`sid`) AS `sid`, `expire_num`, `expire_time`, `hollow_state`, `hollow_title`, `hollow_count`, `data_prefix`, `data_suffix` 
FROM `page` 
WHERE (SELECT COUNT(*) FROM `hollow` WHERE CONCAT('RR',`page`.`sid`)=`hollow`.`hollow_sid`)=0;

UPDATE `hollow` SET `hollow_sname` =  'CQU'  WHERE `hollow_sid` = 'RR{68ADE40A-C01A-449B-9F16-BB1EEBDF8825}';
UPDATE `hollow` SET `hollow_sname` =  '348'  WHERE `hollow_sid` = 'RR{7132C09A-ABEF-22D9-296A-AD826856A3BE}';
UPDATE `hollow` SET `hollow_sname` =  'NJAU' WHERE `hollow_sid` = 'RR{E61E231D-13FA-BF16-A06D-27CEF4451332}';
UPDATE `hollow` SET `hollow_sname` =  'ZYM'  WHERE `hollow_sid` = 'RR{6BA8E15E-3BE4-4D40-8674-BF436A2CC59B}';
UPDATE `hollow` SET `hollow_sname` =  'BUCM' WHERE `hollow_sid` = 'RR{09DFDA5D-FFA0-D111-1A8C-613B311692A3}';
UPDATE `hollow` SET `hollow_sname` =  'RRSD' WHERE `hollow_sid` = 'RR{AB15A6BA-0F0D-F6BA-6386-87D399552CF9}';
UPDATE `hollow` SET `hollow_sname` =  'CQNU' WHERE `hollow_sid` = 'RR{50015724-8CDC-BAB4-2129-BE66A202486F}';
UPDATE `hollow` SET `hollow_sname` =  'SDMM' WHERE `hollow_sid` = 'RR{C4AC263B-A3F1-EF65-07AA-19484FDCECBC}';
UPDATE `hollow` SET `hollow_sname` =  'SHSD' WHERE `hollow_sid` = 'RR{004205CD-2642-ACFC-D3B0-69221D81829E}';
UPDATE `hollow` SET `hollow_sname` =  'CQSD' WHERE `hollow_sid` = 'RR{0C9676FF-B195-3142-A466-D6ADBF896DF2}';
UPDATE `hollow` SET `hollow_sname` =  'NJSD' WHERE `hollow_sid` = 'RR{36103CCA-49BE-4664-07E4-7C9AD35520C5}';
UPDATE `hollow` SET `hollow_sname` =  'HFSD' WHERE `hollow_sid` = 'RR{FC63C455-1A18-01A4-0A8A-5EA1D134003B}';
UPDATE `hollow` SET `hollow_sname` =  'ECSI' WHERE `hollow_sid` = 'RR{3A245503-1137-C971-3B09-0037F5A79E46}';
UPDATE `hollow` SET `hollow_sname` =  'SISU' WHERE `hollow_sid` = 'RR{8F322329-0639-F662-3EE8-29C060008CB0}';

-- --------------------------------------------------------
-- 表的结构 `renren_page`
CREATE TABLE IF NOT EXISTS `renren_page` (
  `hollow_id` int(10) unsigned NOT NULL,
  `page_id` int(10) unsigned NOT NULL,
  FOREIGN KEY `fk_hollow_id` (`hollow_id`) REFERENCES `hollow` (`hollow_id`) ,
  PRIMARY KEY (`hollow_id`, `page_id`),
  KEY `k_hollow_id` (`hollow_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
-- --------------------------------------------------------
-- 转存表中的数据 `renren_page`
INSERT INTO `renren_page`(`hollow_id`, `page_id`) 
SELECT (SELECT `hollow_id` FROM `hollow` WHERE CONCAT('RR',`page`.`sid`)=`hollow`.`hollow_sid` LIMIT 1) AS `hollow_id`, `page_id` 
FROM `page` 
WHERE (SELECT COUNT(*) FROM `renren_page` WHERE `page`.`page_id`=`renren_page`.`page_id`)=0;

-- --------------------------------------------------------
-- 表的结构 `renren_page_manager`
CREATE TABLE IF NOT EXISTS `renren_page_manager` (
  `page_id` int(10) unsigned NOT NULL,
  `manager_id` int(10) unsigned NOT NULL,
  `manager_valid` int(10) unsigned NOT NULL DEFAULT '1',
  FOREIGN KEY `fk_manager_id` (`manager_id`) REFERENCES `renren_oauth` (`user_id`) ,
  PRIMARY KEY (`manager_id`, `page_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
-- --------------------------------------------------------
-- 转存表中的数据 `renren_page`
INSERT INTO `renren_page_manager`(`manager_id`, `page_id`) 
SELECT RIGHT(`access_token`,9), `page_id` 
FROM `page` 
WHERE (SELECT COUNT(*) FROM `renren_page_manager` WHERE `page`.`page_id`=`renren_page_manager`.`page_id` AND RIGHT(`page`.`access_token`,9)=`renren_page_manager`.`manager_id`)=0;

-- --------------------------------------------------------
-- 表的结构 `record`
CREATE TABLE IF NOT EXISTS `record` (
  `record_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hollow_id` int(11) NOT NULL,
  `record_time` int(10) unsigned DEFAULT '0',
  `record_ip` int(10) unsigned DEFAULT '0',
  `record_data` text NOT NULL,
  `record_state` int(11) NOT NULL,
  `record_error_code` int(11) NOT NULL DEFAULT '0',
  `record_error_msg` text,
  PRIMARY KEY (`record_id`),
  KEY `i_hollow_id` (`hollow_id`,`record_id`),
  KEY `i_time` (`record_time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;
-- --------------------------------------------------------
-- 转存表中的数据 `record`
/* UPDATE `record` SET `hollow_id`=(SELECT `hollow`.`hollow_id` FROM `hollow` LEFT JOIN `renren_page` ON `renren_page`.`hollow_id`=`hollow`.`hollow_id` WHERE `renren_page`.`page_id`=`record`.`page_id` LIMIT 1) WHERE `page_id`<>0 */

-- --------------------------------------------------------
-- 表的结构 `offensive_word`
CREATE TABLE IF NOT EXISTS `offensive_word` (
  `hollow_id` int(11) NOT NULL DEFAULT '-1',
  `word_type` int(10) unsigned NOT NULL DEFAULT '0',
  `word_data` longtext NOT NULL,
  `word_valid` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`hollow_id`,`word_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- --------------------------------------------------------
-- 转存表中的数据 `offensive_word`
INSERT INTO `offensive_word` (`word_type`, `word_valid`, `word_data`, `hollow_id`) VALUES
(0, 1, '妈逼,傻逼,它妈的,他妈的,她妈的,妈了个逼,操你妈,草你妈', -1);

-- --------------------------------------------------------
-- 表的结构 `user`
CREATE TABLE IF NOT EXISTS `user` (
`user_uid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`user_site` INT NOT NULL ,
`user_id` INT NOT NULL ,
INDEX (  `user_site` ,  `user_id` )
) ENGINE = MYISAM ;