<?php

/**
 * Логирование сообщений для SteelBot
 * 
 * @author N3x^0r
 * @version 1.0
 * 
 * 2011-02-28
 *
 */

class BaseLog {

    const LEVEL_DEBUG = 4;
    const LEVEL_NOTICE = 3;
    const LEVEL_WARNING = 2;
    const LEVEL_ERROR = 1;
    const LEVEL_NONE = 0;
    
    private $last_ignored = false;

    protected $_rules = array();

    public function __construct($rules = array()) {
        $this->_rules = $rules;
        $this->log("Logger ".__CLASS__." started at ".date("r"));
        set_error_handler(array($this, 'errorHandler'));
    }   

   /**
    * @deprecated
    *
   public function add($name, $msg, $code = '000', $sender = false, $level = LOG_LEVEL_NOTICE) {
        if (!$this->checkRule($name, $level, $code)) {
		    $this->last_ignored = true;
            return;
        }
        $this->last_ignored = false;
       
        $date = date( SteelBot::$cfg['log.dateformat'] );       
        $logmsg = $this->format($date, $sender, $msg, $name, $code);
       
        if (strlen($name) < 8) {
		   $offset = 8-strlen($name);
	    } else {
	       $offset = 1;
	    }
        $logmsg = $this->format($date, $sender, $msg, $name.@str_repeat(' ', $offset), '');
        echo "\n".$logmsg;
        return true;  
    } */

	/**
	 * @param string $msg
	 * @param string $component
	 */
    public function log($msg, $component = null, $level = self::LEVEL_NOTICE) {
        if ($this->checkRule($component, $level)) {
            echo date("[H:i:s] ");
            if (!is_null($component)) {
                echo "[$component] ";
            }
            echo $msg."\n";
            return true;
        }
        return false;
    }

    /**
     * @param string $res
     */
    public function result($res) {       
	    if ($this->last_ignored) return;
        echo $res;
        return true;
    }

    public function errorHandler($error_level, $error_message, $error_file, $error_line) {
		static $levels = array(
			E_USER_ERROR => array('E_USER_ERROR',LOG_LEVEL_ERROR),
			E_ERROR => array('E_ERROR',LOG_LEVEL_ERROR),
			E_USER_WARNING => array('E_USER_WARNING', LOG_LEVEL_WARNING),
			E_WARNING => array('E_WARNING', LOG_LEVEL_WARNING),
			E_USER_NOTICE => array('E_USER_NOTICE', LOG_LEVEL_NOTICE),
			E_NOTICE => array('E_NOTICE', LOG_LEVEL_NOTICE)
		);

		if (isset($levels[$error_level])) {
			$this->log(
				"{$levels[$error_level][0]} in $error_file, line $error_line: $error_message",
				'PHP',
				$levels[$error_level][1]
			);
		} else {
			$this->log(
				"unknown error($error_level) in $error_file, line $error_line: $error_message",
				'PHP',
				LOG_LEVEL_ERROR
			);
		}
   }

    private function checkRule($name, $level) {
        if (is_null($name) || !isset($this->_rules[$name])) {
            $name = '*';
        }
        return $this->_rules[$name] >= $level;
		
   }   
}
