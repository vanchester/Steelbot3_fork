CREATE TABLE IF NOT EXISTS `@user_memo` (
  `uin` varchar(255) NOT NULL,
  `memo_name` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `text` text NOT NULL,
  KEY `uin` (`uin`),
  KEY `memo_name` (`memo_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
