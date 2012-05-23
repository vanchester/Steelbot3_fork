<?php

S::bot()->eventManager->registerEventHandler(EVENT_BOT_LOADED, array('Response', 'Init'));
S::bot()->eventManager->RegisterEventHandler(EVENT_MSG_RECEIVED, array('Response', 'Parse'), 60);

require_once dirname(__FILE__).'/responsestore.class.php';
require_once dirname(__FILE__).'/responsefilestore.class.php';

class Response {
    
    static private $_store = null;
    static public $case_sens = false;

    static public function Init() {
        self::$_store = new ResponseFileStore;
    }

    static public function Parse($event) {
	return include dirname(__FILE__).'/response.parse.function.php';
	
        $phrase = self::$case_sens ? $event->content : mb_strtolower($event->content, 'UTF-8'); 
        if ($msg = self::$_store->FindMatch($phrase)) {
                S::bot()->Msg($msg, $event->sender);
                throw new EventDrop;
        }              
    }
}


