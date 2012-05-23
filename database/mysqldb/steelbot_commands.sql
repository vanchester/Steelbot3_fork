CREATE TABLE IF NOT EXISTS `@commands` (
  `plugin` varchar(50) collate utf8_unicode_ci NOT NULL,
  `command` varchar(200) collate utf8_unicode_ci NOT NULL,
  `access` int(11) NOT NULL default '1',
  PRIMARY KEY  (`plugin`,`command`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;