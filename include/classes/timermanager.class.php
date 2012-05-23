<?php

class TimerManager extends SComponent {

	protected $_timers = array(),
			  $_maxtimerId,

              $_bot = null;

	public function __construct($bot) {
        $this->_bot = $bot;
		parent::__construct($bot);
		$this->_maxtimerId = 1;
	}

	/**
     * @desc Записывает вызов заданной функции через заданное время
     * 
     * @param int $time    время в секундах, по прохождении которого будет вызвана функция
     * @param string $func имя функции
     * @param array $param параметры вызываемой функции
     * @param bool $absoluteTime абсолютное или относительное устанавливаемое время
     * @return int идентификатор таймера.
     */
    public function TimerAdd($time, $func, $params = array(), $absoluteTime = false) {
		if (!$absoluteTime) {
			$time = time()+$time;
		}
		$timer = new Timer($time, $func, $params);
        $this->_timers[ $this->_maxtimerId ] = $timer;
        S::logger()->log("Timer #{$this->_maxtimerId} created", __CLASS__, BaseLog::LEVEL_DEBUG);       
        return $this->_maxtimerId++;
    }

	/**
	 * Get timer by id
	 */
	public function getById($id) {
		return $this->_timers[$id];
	}

	public function findByCallback($hash) {
		$result = array();
		foreach ($this->_timers as $id=>$timer) {
			if ($timer->hasCallback($hash)) {
				$result[$id] = $timer;
			}
		}
		return $result;
	}

	public function checkTimers() {
		$time = time();
		foreach ($this->_timers as $id=>$timer) {
			if ($timer->timeEvent($time)) {
				unset($this->_timers[$id]);
			}
		}
	}

    /**
     * Delete timer by id
     * 
     * @param int $id
     * @return bool
     * @deprecated
     */
    public function TimerDeleteById($id) {
		trigger_error("Function ".__FUNCTION__." is deprecated", E_USER_WARNING);
        return $this->deleteById($id);
    }

	/**
	 * Delete timer by id
	 *
	 * @param int $id
     * @return bool
	 */
    public function deleteById($id) {
		if (array_key_exists($id, $this->_timers)) {
			unset( $this->_timers[$id] );
			S::logger()->log("Timer #{$this->_maxtimerId} deleted", __CLASS__, BaseLog::LEVEL_DEBUG);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Delete timer by callback hash
	 *
	 * @param string $hash
	 * @return int - deleted timers count
	 */
	public function deleteByCallback($hash) {
		$deleted = 0;
		foreach ($this->findByCallback($hash) as $id=>$timer) {
			if ($this->deleteById($id)) {
				$deleted++;
			}
		}
		return $deleted;
	}

	

}
