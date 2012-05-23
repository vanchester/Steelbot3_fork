<?php

/**
 * Proto class for SteelBot
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * @version 1.1.0
 * 
 */

require_once(dirname(__FILE__)."/XMPPHP/XMPP.php");
require_once(dirname(__FILE__)."/XMPPHP/Log.php");

/**
 * @property XMPPHP_XMPP $conn
 * @property array $payloads
 * @property array $master_accounts
 * @property string $message_callback
 * @property $online
 */
class Proto extends SBotProtocol  {
    
public $conn, 
       $payloads = array(),
       $master_accounts = array(),
       $message_callback = null,
       $online;

public function __construct($bot) {
	parent::__construct($bot);
      
    $bot->eventManager
    ->AddEventType('EVENT_GROUPCHATMSG_RECEIVED')
    ->AddEventType('EVENT_GROUPCHAT_PRESENCE')
    ->AddEventType('EVENT_GROUPCHAT_INVITE')
    ->AddEventType('EVENT_SESSION_START');

    $bot->eventManager->registerEventhandler(EVENT_SESSION_START, array($this, 'SessionStartHandler'));
}

public function Connect( ) {
    $c = $this->_config = S::bot()->config['proto'];
	list($username, $host) = explode('@', $c['jid']);
    $this->conn = new XMPPHP_XMPP( 
		$host, 
        $c['port'], 
        $username, 
        $c['password'], 
        $c['resource'], 
        $c['server'], 
        $printlog=true, 
        $loglevel=XMPPHP_Log::LEVEL_WARNING
    );
    $this->conn->autoSubscribe( $c['autosubscribe'] );
    $this->conn->useEncryption( $c['encryption.enabled'] );
    $this->conn->useSSL( $c['ssl.enabled'] );
    
    //master_accounts
    if (!is_array($c['master_accounts'])) {
        throw new BotException("config option ['proto']['master_accounts'] must be an array");
    }
    $this->master_accounts = $this->parseMasterAccounts($c['master_accounts']);
    
    try {
        $this->conn->connect();
        S::bot()->eventmanager->EventRun( new Event(EVENT_CONNECTED) );
    } catch (XMPPHP_Exception $e) {
        S::logger()->log($e->GetMessage());
        return false;
    }
    return true;
}

public function GetProtoInfo() {
    return array(
        'version' => '1.1.0',
        'name' => 'jabber'
    );
}

public function IsIMAccount($str) {
    return preg_match('~^(\S+)?@(\S+)?\.([A-Za-z]{1,5})$~');
}

public function IsAdmin($account) {
    foreach ($this->master_accounts as $pat) {
        if (preg_match($pat, $account)) {
            return true;
        }
    }
    return false;
}

public function Disconnect() {
    $this->conn->disconnect();
}

public function Connected() {
    return !$this->conn->isDisconnected();
}

public function GetMessage() {
    $payloads = $this->conn->processUntil(array('message', 'presence', 'end_stream', 'session_start'), $this->_config['delaylisten']);
    foreach($payloads as $event) {
        $this->payloads[] = $event;
    }    
   
    $event = array_shift($this->payloads); 
    $pl = $event[1];
    switch($event[0]) {
    	case 'message': 
    	   switch ($pl['type']) {
    	       
    	       // стандартное сообщение
    	       case 'chat':
                   switch ($pl['subtype']) {
                       case 'delayed':
                           if(!$this->_config['messages.process_delayed']) {
                               return false;
                           }
                           break;
                   }

                   if (!$this->_config['messages.proccess_null'] && empty($pl['body'])) {
                       return false;
                   }

                   list($jid, $res) = explode('/', $pl['from']);
                   return new Event(EVENT_MSG_RECEIVED, array(
                       'type'   => 'message',
                       'sender' => $jid,
                       'resource' => $res,
                       'content'=> $pl['body']
                   ));
                   
    	           break;
    	           
    	       // сообщение в конференции - специфичное для jabber сообщение
    	       case 'groupchat':
    	           return new Event(EVENT_GROUPCHATMSG_RECEIVED,
    	               array(
                       'type'  => 'groupchat',
                       'subtype' => $pl['subtype'],
    	               
    	               'sender' => $pl['from'],
    	               'content' => $pl['body']    	               
    	           ));
    	           break;
    	           
    	       // приглашение на конференцию
    	       case 'normal':
                   switch ($pl['subtype']) {
                       case 'invite':
                           return new Event(EVENT_GROUPCHAT_INVITE, array(
            	               'type'    => 'groupchatinvite',
            	               'sender'  => $pl['from'],
            	               'content' => $pl['body'] 
                           ));
                           break;
                            
                        default: trigger_error("Unknown message type", E_USER_WARNING);
                   }
    	           ###
    	           return false;
    	           
    	           break;
    	   }
    	   break;
    	   
    	case 'presence':                
                return new Event(EVENT_USR_STATUS, array( 
    			     'type'     => $pl['type'],                                            
                     'show'     => $pl['show'],
                     'sender'   => $pl['from'],
                     'status'   => $pl['status'],
                     'priority' => $pl['priority']
                )); 
    			break;
    			
    	case 'session_start':
		   	$this->conn->getRoster();
    		$this->conn->presence($status="Cheese!");
    		return new Event(EVENT_SESSION_START);    		  
    		break;
    		
    	case 'end_stream':
    	    return new Event(EVENT_DISCONNECTED);
    	    break;
    }
    	
    return false;
}

public function Msg($txt, $to) {
	if (is_null($this->message_callback)) {
        $this->conn->message($to, $txt);
    } else {
        $args = func_get_args();
        call_user_func_array( $this->message_callback,  $args);
    }
}

public function SetMessageCallback($func) {
    $this->message_callback = $func;
}

public function SetStatus($status) {
    $this->conn->presence($status);
}

public function SetXStatus($status) {
    //$this->icq->SetXStatus($status);
}

public function BotId() {
    return $this->_config['jid'];
}

public function PresenceHandler($event) {
    ###
    echo "PRESENCE\n";
}

public function SessionStarthandler($event) {
    S::logger()->log("Session started");
}

private function parseMasterAccounts($list) {
	$result = array();	
	foreach ($list as $v) { 
        $v = str_replace('*', '.*', $v);
        $result[] = "~^$v\$~";
    }
    return $result;
}

}

?>
