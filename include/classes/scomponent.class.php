<?php

class SComponent {

    public $logger = array(
        'level' => BaseLog::LEVEL_WARNING
    );

	public function __construct($bot) {
		$config = $this->config();
	}
    
    public function log($message) {
        S::logger()->log($message, '', $this->logger['level']);
    }

	public function config() {
		return array();
	}

	public function __get($property) {
		$method = 'get'.ucfirst($property);
		if (method_exists($this, $method)) {
			return $this->$method();
		}
	}

	public function __set($property, $value) {
		$method = 'set'.$property;
		if (method_exists($this, $method)) {
			return $this->$method($value);
		}
	}

}
