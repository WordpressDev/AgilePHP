/*!40101 SET NAMES utf8 */;
/*!40101 SET SQL_MODE=''*/;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/`agilephp_test` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `agilephp_test_mysql`;

DROP TABLE IF EXISTS `inventory`;

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `price` decimal NOT NULL,
  `category` varchar(255) NOT NULL,
  `image` blob NOT NULL,
  `video` blob,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `mailing`;
CREATE TABLE `mailing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `enabled` bit(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `name` varchar(25) NOT NULL,
  `description` text,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

insert  into `roles`(name,description) values ('admin','This is an administrator account'),('test','This is a test account');

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(21) NOT NULL DEFAULT '',
  `data` text,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `username` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created` datetime NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `roleId` varchar(25) DEFAULT NULL,
  `sessionId` varchar(21) DEFAULT NULL,
  `enabled` bit(1) DEFAULT NULL,
  PRIMARY KEY (`username`),
  KEY `FK_UserRoles` (`roleId`),
  KEY `FK_UserSessions` (`sessionId`),
  CONSTRAINT `FK_UserRoles` FOREIGN KEY (`roleId`) REFERENCES `roles` (`name`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_UserSessions` FOREIGN KEY (`sessionId`) REFERENCES `sessions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/* NOTE: Passwords are set to 'test' !!!!!!!!!!!!!! */
insert  into `users`(username,password,email,created,last_login,roleId,sessionId,enabled) values ('admin','9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08','root@localhost','2009-09-06 15:27:44','2010-01-26 22:27:02','admin',NULL,'1'),('test','9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08','test','2010-01-22 19:01:00','2010-01-24 16:26:22','test',NULL,NULL);

DELIMITER $$
CREATE PROCEDURE authenticate( 
	IN userid VARCHAR(150),
	IN passwd VARCHAR(255),
	OUT authenticate BOOL
)
SELECT enabled FROM users WHERE username = userid AND PASSWORD = passwd INTO authenticate $$

CREATE PROCEDURE getusers()
select * from users $$

DELIMITER ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
