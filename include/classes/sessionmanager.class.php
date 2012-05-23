<?php

class SessionManager extends SComponent {

	protected $_sessions = array();
	protected $defaultHandler = null;
    
	public function __construct($bot) {
		parent::__construct($bot);
        S::logger()->log("Session Manager starting...", __CLASS__, BaseLog::LEVEL_DEBUG);
	}    

    public function setDefaultHandler($handler) {
        $this->defaultHandler = $handler;
    }

    public function restoreDefaultHandler() {
        $this->defaultHandler = null;
    }

	public function pushHandler($handler, $user) {
        if (array_key_exists($user, $this->_sessions)) {
            S::logger()->log("Pushing new handler for $user...", __CLASS__, BaseLog::LEVEL_DEBUG);
            $this->_sessions[$user]->pushHandler($handler);
            return true;
        } else {
            return false;
        }
	}

    public function popHandler($user) {
        if (array_key_exists($user, $this->_sessions)) {
            return $this->_sessions[$user]->popHandler();
        } else {
            return false;
        }
    }

    public function SessionStart($user) {
        if (!array_key_exists($user, $this->_sessions)) {
            $this->_sessions[$user] = new Session($user);
            if (is_null($this->defaultHandler)) {
                $this->_sessions[$user]->pushHandler(array(S::bot(), 'Parse'));
            } else {
                $this->_sessions[$user]->pushHandler($this->defaultHandler);
            }
            return $this->_sessions[$user];
        } else {
            return null;
        }
    }

    public function SessionExists($user) {
        return array_key_exists($user, $this->_sessions);
    }

    public function sessionDestroy($user) {
        if (array_key_exists($user, $this->_sessions)) {
            ## ?
            unset($this->_sessions[$user]);
            return true;
        } else {
            return false;
        }
    }

    public function callHandler($event, $mode = Session::MODE_HANDLE) {
        $user = $event->sender;
        //$this->sessionGC(1);
        if (!array_key_exists($user, $this->_sessions)) {
            S::logger()->log("Starting session...");
            $this->SessionStart($user);
        }

        S::logger()->log("Call handler for $user", __CLASS__, BaseLog::LEVEL_DEBUG);
        $this->_sessions[$user]->callHandler($event, $mode);
    }

    protected function sessionGC($p) {
        foreach ($this->_sessions as $user=>$s) {
            if ($s->isExpired()) {
                $this->sessionDestroy($user);
            }
        }
    }
}
