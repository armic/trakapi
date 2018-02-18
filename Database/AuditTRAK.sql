/*
Navicat MySQL Data Transfer

Source Server         : Local
Source Server Version : 100113
Source Host           : localhost:3306
Source Database       : AuditTRAK

Target Server Type    : MYSQL
Target Server Version : 100113
File Encoding         : 65001

Date: 2018-02-19 07:34:16
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for audittraktransactions
-- ----------------------------
DROP TABLE IF EXISTS `audittraktransactions`;
CREATE TABLE `audittraktransactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Auto Incremented',
  `custid` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL COMMENT 'Transaction Type 0 - Issue  1 - Return',
  `datetimeissued` date DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `tailid` int(11) DEFAULT NULL,
  `lockerid` int(11) DEFAULT NULL,
  `kitid` int(11) DEFAULT NULL,
  `datereturned` date DEFAULT NULL,
  `kittoolid` int(11) DEFAULT NULL,
  `workorder` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='AuditTRAK transaction storage';

-- ----------------------------
-- Records of audittraktransactions
-- ----------------------------

-- ----------------------------
-- Table structure for customers
-- ----------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Auto Incremented',
  `name` varchar(100) DEFAULT NULL,
  `contactperson` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `datecreated` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COMMENT='Customer Table';

-- ----------------------------
-- Records of customers
-- ----------------------------
INSERT INTO `customers` VALUES ('1', 'Jetstar', 'Alisdair', 'alisdair@henchman.com.au', null, 'Australia');
INSERT INTO `customers` VALUES ('2', 'Toll', 'Brent Doven', 'brent@henchman.com.au', null, 'Australia');

-- ----------------------------
-- Table structure for employees
-- ----------------------------
DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `custid` int(11) DEFAULT NULL,
  `photo` varchar(200) NOT NULL,
  `mobilenumber` varchar(20) DEFAULT NULL,
  `createddate` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COMMENT='Employee table';

-- ----------------------------
-- Records of employees
-- ----------------------------
INSERT INTO `employees` VALUES ('1', 'Alisdair', 'Chambers', 'alisdair', 'd2f742f605bb832ebab0f7034f1a1a53', 'alisdair@henchman.com.au', '2', '', null, null);
INSERT INTO `employees` VALUES ('2', 'Gabriel', 'Tolentino', 'artolentino', '23954fa85672bc4895bf936295b5e05a', 'artolentino@gmail.com', '2', '', '09179833980', '2018-02-18 10:11:32');

-- ----------------------------
-- Table structure for kits
-- ----------------------------
DROP TABLE IF EXISTS `kits`;
CREATE TABLE `kits` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '1 - Reserved  0 - Availabe',
  `lockerid` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `custid` int(11) DEFAULT NULL,
  `reserved` int(1) DEFAULT NULL,
  `qrcode` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Kit Table';

-- ----------------------------
-- Records of kits
-- ----------------------------

-- ----------------------------
-- Table structure for kittools
-- ----------------------------
DROP TABLE IF EXISTS `kittools`;
CREATE TABLE `kittools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kitid` int(11) DEFAULT NULL,
  `toolid` varchar(255) DEFAULT NULL,
  `custid` int(11) DEFAULT NULL,
  `reserved` int(1) DEFAULT NULL COMMENT '1 - Reserved   0 - Available',
  `qrcode` varchar(255) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Individual kit tool table';

-- ----------------------------
-- Records of kittools
-- ----------------------------

-- ----------------------------
-- Table structure for locations
-- ----------------------------
DROP TABLE IF EXISTS `locations`;
CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT NULL,
  `custid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Locker location Table';

-- ----------------------------
-- Records of locations
-- ----------------------------

-- ----------------------------
-- Table structure for lockers
-- ----------------------------
DROP TABLE IF EXISTS `lockers`;
CREATE TABLE `lockers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `custid` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `code` varchar(200) DEFAULT NULL COMMENT 'Code to unlock locker via wifi or bluetooth',
  `locationid` int(11) DEFAULT NULL COMMENT 'Location ID',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Locker Table';

-- ----------------------------
-- Records of lockers
-- ----------------------------

-- ----------------------------
-- Table structure for reservations
-- ----------------------------
DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reservationdate` date DEFAULT NULL,
  `reservationtime` time DEFAULT NULL,
  `custid` int(11) DEFAULT NULL,
  `userid` int(11) DEFAULT NULL,
  `kitid` int(11) DEFAULT NULL,
  `toolid` int(11) DEFAULT NULL,
  `flag` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of reservations
-- ----------------------------

-- ----------------------------
-- Table structure for tails
-- ----------------------------
DROP TABLE IF EXISTS `tails`;
CREATE TABLE `tails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `custid` int(11) DEFAULT NULL,
  `number` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Aircraft/Tail Number table';

-- ----------------------------
-- Records of tails
-- ----------------------------

-- ----------------------------
-- Table structure for toolcategories
-- ----------------------------
DROP TABLE IF EXISTS `toolcategories`;
CREATE TABLE `toolcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT NULL COMMENT 'Tool Categoy Description  kabtrak,portatrak,cribtrak,audittrak',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Tool category table';

-- ----------------------------
-- Records of toolcategories
-- ----------------------------

-- ----------------------------
-- Table structure for tools
-- ----------------------------
DROP TABLE IF EXISTS `tools`;
CREATE TABLE `tools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `custid` int(11) DEFAULT NULL COMMENT 'Foreign Key from Customer Table',
  `stockcode` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `serialno` varchar(100) DEFAULT NULL,
  `categoryid` int(11) DEFAULT NULL,
  `toolimage` varchar(255) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `reserved` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Tool table';

-- ----------------------------
-- Records of tools
-- ----------------------------

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Auto Incremented',
  `custid` int(11) DEFAULT NULL COMMENT 'Foreign Key from customer table',
  `active` int(1) DEFAULT '1' COMMENT '1 - Active  0 - inActive',
  `kabtrak` int(1) DEFAULT '0' COMMENT '1 - kabtrak user 0 - non kabTRAK user',
  `portatrak` int(1) DEFAULT '0' COMMENT '1 - portatrak user  0 - noneportatrak user',
  `cribtrak` int(1) DEFAULT '0' COMMENT '1 - cribTRAK user 0 - notportatrak user',
  `auditrak` int(1) DEFAULT '0' COMMENT '1 - audittrak user   0 - not audittrak user',
  `role` int(1) DEFAULT '0' COMMENT '0 - User  1  - Admin',
  `userid` varchar(100) DEFAULT NULL COMMENT 'RFID Card ID -FK',
  `level` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COMMENT='User table';

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES ('2', '2', '1', '0', '0', '0', '1', '0', '1', '1');
SET FOREIGN_KEY_CHECKS=1;
