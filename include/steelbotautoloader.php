<?php

function SteelbotAutoloader($classname) {
	$name = mb_strtolower($classname);
	if (file_exists(dirname(__FILE__)."/classes/$name.class.php")) {
		include dirname(__FILE__)."/classes/$name.class.php";
		return true;
	} else {
		return false;
	}
}

spl_autoload_register('SteelbotAutoloader');
