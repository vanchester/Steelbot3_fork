<?php

/**
 * Proto class for SteelBot
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * @thx to MAKAPOH
 * 
 */

require_once(dirname(__FILE__)."/WebIcqPro.class.php");

//реализация интерфейса
class Proto extends SBotProtocol  {

    protected $_masterAccounts = array(),
              $_icq,
              $_messagesQueue = array(),

       /*
        * Антифлуд: по умолчанию допускается не более
        * 22 сообщений в течение 22 секунд.
        */
       $_t=22, /* Интервал времени антифлуда */
       $_n=22; /* Максимальное количество сообщений за интервал времени */

public function __construct($bot) {
    parent::__construct($bot);
    
    
}

public function GetProtoInfo() {
    return array(
        'name' => 'icq',
        'version' => '1.1.1'
    );
}

public function Connect() { 
    $this->_icq = new WebIcqPro();
    $this->_icq->setOption('timeout',5);
    $this->_icq->setOption('MessageCapabilities', 'utf-8');    
    $this->_icq->setOption('MessageType', 'plain_text'); 
    $this->_icq->setOption('Encoding', 'UNICODE');   

    
    /**
    * Преобразуем строку в массив с одним элементом,
    * либо если в строке есть запятая, то считаем что 
    * ей разделены несколько уинов администратора.
    */
    $this->_masterAccounts = array();
    $adm = S::bot()->config['proto']['master_accounts'];
    if (!is_array($adm)) {        
        $adm = strpos($adm, ',') ? explode(',', $adm):array($adm);
    }
    
    foreach ($adm as $uin) {
        $this->_masterAccounts[] = trim($uin);
    }

    
    return $this->_icq->connect(S::bot()->config['proto']['uin'], S::bot()->config['proto']['password']);
}

public function Disconnect() {
    $this->_icq->disconnect();
}

public function Connected() {
    return $this->_icq->IsConnected();
}

public function GetMessage() {
    $msg = $this->_icq->ReadMessage();
    
    if ( count($this->messagesQueue) ) {        
        while (count( $this->_messagesQueue ) && $this->TimeToSend()) {
            
            $msg = array_pop($this->_messagesQueue);
            $this->_icq->sendMessage($msg[1], $msg[0]);
        }
    }
    
    switch($msg['type']) {
        case 'message':
            switch ( mb_strtoupper( $msg['encoding']['numset']) ) {
                case 'UNICODE':
                    $msg["message"] = mb_convert_encoding($msg['message'], 'UTF-8', 'UNICODE');
                    break;
                case 'UTF-8':
                    break;                     
                default: $msg["message"] = mb_convert_encoding($msg['message'], 'UTF-8', 'WINDOWS-1251');
            }
         
            $msg['message'] = rtrim($msg["message"]);                                              
            //slog::add('proto', "сообщение: " . $msg['message'] . ' (' . $msg['type'] . ')');
            return new Event(EVENT_MSG_RECEIVED, array(
                'type' => 'message',
                'sender' => $msg['from'], 
                'content' => $msg['message']
            ));
        break;
         
        case 'accepted':
            unset($msg);
            return false;
            break;
         
        case 'offlinemessage':
            //slog::add('proto', "offline message recieved");
            unset($msg);
            return false;
            break;
            
        case 'authrequest':
            return new Event(EVENT_AUTH_REQUEST, array(
                'sender' => $msg['from'],
                'reason' => $msg['reason']
            ));
            
        case 'error':
            S::logger()->log("Error ".@$msg['code'].": ".@$msg['error']);
            unset($msg);
            break;
                
            
        default: unset($msg); return false;
    }
    
}

public function SetStatus($status) {
    $this->_icq->setStatus($status);
}

public function Error() {
    return $this->_icq->error;
}

public function Msg($txt, $to) {	
	 $txt = mb_convert_encoding($txt, 'UNICODE', 'utf-8');
    if ( $this->TimeToSend() ) {
        $this->_icq->sendMessage($to, $txt);
    } else {
       S::logger()->log("Antiflood enabled",0);
       array_unshift( $this->_messagesQueue, array($txt, $to) );
    }
}

public function TimeToSend() {
    static $wait = array();
    foreach ($wait as $k=>$time) {
        if (time()-$time>$this->_t) {
            unset($wait[$k]);
        }
    }

    if (count($wait)<$this->_n) {
        $wait[] = time();
        return true;
    } else {
        return false;
    }
}

public function IsIMAccount($string) {
    return is_numeric($string) && (strlen($string) < 10);
}

public function IsAdmin($uin) {
    return in_array($uin, $this->_masterAccounts);
}

public function SetXStatus($status) {
    $this->_icq->SetXStatus($status);
}

public function BotId() {
    return S::bot()->config['proto']['uin'];
}

public function GetClGroups() {
    return $this->_icq->getContactListGroups();
}

public function addContactGroup($name) {
	return $this->_icq->addContactGroup($name);
}

public function deleteContactGroup($name) {
	return $this->_icq->deleteContactGroup($name, "");
}

	/**
	 * Add uins to list of contacts. First argument is group name. Ather uins to add.
	 * Also posible to add with custom name:
	 * 
	 * addContact("Buddies", array('uin' => UIN_TO_ADD, 'name' => "Custom name"), ...)
	 * 
	 * @param mixed list of uins
	 * @return boolean
	 */
public function addContact($group, $contact) {
	return $this->_icq->addContact($group, $contact);
}

public function deleteContact($uin) {
	return $this->_icq->deleteContact($uin);
}

public function getAuthorization($uin, $reason='') {
	return $this->_icq->getAuthorization($uin, $reason);
}

public function setAuthorization($uin, $granted=true, $reason='')	{
	return $this->_icq->setAuthorization($uin, $granted, $reason);
}

}

?>
