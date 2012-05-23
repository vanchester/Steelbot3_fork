<?php 
class Timer extends SComponent {
	protected $_time,
		      $_callback,
		      $_callbackHash,
		      $_parameters = array();

	public function __construct($time, $callback, $parameters = array()) {
		$this->_time = $time;
		$this->setCallback($callback);
		$this->_parameters = $parameters;
	}

	public function timeEvent($time) {
		if ($time >= $this->_time) {
			call_user_func_array($this->_callback, $this->_parameters);
			return true;
		}
		return false;
	}

	public function setCallback($callback) {
		$this->_callback = $callback;
		$this->_callbackHash = self::hashCallback($callback);
	}

	public function hasCallback($hash) {
		return $this->_callbackHash == $hash;
	}

	public function setParameters($parameters) {
		$this->_parameters = $parameters;
	}

	/**
	 * Get hash of the callback function as string
	 *
	 * @param mixed $callback
	 * @return string
	 */
    public static function hashCallback($callback) {
		if (is_string($callback)) {
			return $callback;
		} elseif (is_array($callback)) {
			if (is_object($callback[0])) {
				return spl_object_hash($callback[0]).$callback[1];
			} else {
				return $callback[0].$callback[1];
			}
		}
	}

}
