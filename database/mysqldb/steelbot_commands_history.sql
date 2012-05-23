CREATE TABLE IF NOT EXISTS `@commands_history` (
  `uin` varchar(255) NOT NULL,
  `command` text NOT NULL,
  `date` datetime NOT NULL,
  `status` tinyint(1) NOT NULL,
  KEY `uin` (`uin`),
  KEY `date` (`date`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
