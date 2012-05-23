CREATE TABLE IF NOT EXISTS `@aliases` (
`plugin` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
`command` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
`alias` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
PRIMARY KEY ( `plugin` , `command` ) 
) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci;
