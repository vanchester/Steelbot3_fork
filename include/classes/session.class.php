<?php 
class Session extends SComponent {
    public $user;
    const MODE_HANDLE = 0;
    const MODE_PUSH = 1;
    const MODE_POP = 2;
        
    protected $_handlers = array(),
              $_lastAccess,
              $_durability = 60; //100 seconds

	public function __construct($user) {
        parent::__construct(S::bot());
        $this->user = $user;
        $this->_lastAccess = time();
	}

    /**
     * @param Event $event
     * @param int $mode
     */ 
    public function callHandler($event, $mode = self::MODE_HANDLE) {
        call_user_func(end($this->_handlers), $event, $mode);
    }
    
	public function pushHandler($handler) {
        array_push($this->_handlers, $handler);
        return true;
	}

	public function popHandler() {
        return array_pop($this->_handlers);
	}

    public function isExpired() {
        return abs($this->_lastAccess - time()) > $this->_durability;
    }
}
