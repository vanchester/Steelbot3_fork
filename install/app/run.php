<?php

define('APP_DIR', dirname(__FILE__));
define('STEELBOT_DIR', dirname(__FILE__).'/steelbot3');
define('PROTOCOL', 'icq');

$config = array();
if (file_exists(STEELBOT_DIR.'/config.'.PROTOCOL.'.php')) {
	include_once(STEELBOT_DIR.'/config.'.PROTOCOL.'.php');
}

$config = array_merge($config, array());

include STEELBOT_DIR.'/bot.php';
