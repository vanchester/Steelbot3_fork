<?php

class S {

	private static $_app = null,
				   $_logger = null;

	public static function init($config)
	{ 
		$basicConfig = require STEELBOT_DIR.'/include/config.php';
		$dbConfig = require STEELBOT_DIR.'/database/'.$config['db']['engine'] .'/config.php';
		$protoConfig = require STEELBOT_DIR.'/protocol/'.$config['proto']['engine'].'/config.php';
		$steelbotConfig = require STEELBOT_DIR.'/config.php';

		$cfg = self::mergeArray($basicConfig, $dbConfig);
		$cfg = self::mergeArray($cfg, $protoConfig);
		$cfg = self::mergeArray($cfg, $steelbotConfig);
		$cfg = self::mergeArray($cfg, $config);

        self::$_logger = new $cfg['bot']['log.class']($cfg['bot']['log.rules']) ;
       
		self::$_app = new Steelbot($cfg);
        self::$_app->init();
	}

	public static function mergeArray($a,$b)
	{
        foreach($b as $k=>$v)
        {
                if(is_integer($k))
                        $a[]=$v;
                else if(is_array($v) && isset($a[$k]) && is_array($a[$k]))
                        $a[$k]=self::mergeArray($a[$k],$v);
                else
                        $a[$k]=$v;
        }
        return $a;
	}

	public static function bot() {
		return self::$_app;
	}

	public static function logger() {
		return self::$_logger;
	}

	public static function func2str($func) {
		if (is_array($func)) {
			if (is_object($func[0])) {
				return get_class($func[0]).'->'.$func[1];
			} else {
				return $func[0].'::'.$func[1];
			}
		} else {
			return $func;
		}
	}


}
