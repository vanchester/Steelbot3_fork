<?php

define('APP_DIR', dirname(__FILE__));
define('STEELBOT_DIR', dirname(__FILE__).'/steelbot3');

$config = array(
	'db' => array(
		'engine' => 'mysqldb',
		'password' => '123456',
		'database' => 'steelbot'
	),
	'proto' => array(
		'engine' => 'jabber',
		'jid' => 'test@test.ru',
		'password' => '123'
	),
	'plugins' => array(
		'default',
		'help'
		)
);

include STEELBOT_DIR.'/bot.php';
