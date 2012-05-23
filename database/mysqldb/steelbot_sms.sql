CREATE TABLE IF NOT EXISTS `@sms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uin` varchar(255) NOT NULL,
  `recipient` varchar(12) NOT NULL,
  `message` text NOT NULL,
  `answer` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uin` (`uin`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
