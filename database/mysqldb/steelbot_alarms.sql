CREATE TABLE IF NOT EXISTS `@alarms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timer_id` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `type` enum('timer','alarm') NOT NULL,
  `function` varchar(255) NOT NULL,
  `uin` varchar(255) NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `type` (`type`),
  KEY `timer_id` (`timer_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
