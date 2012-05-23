<?php

/**
 * Proto class for SteelBot
 * 
 * http://steelbot.net
 * 
 * @author vanchester
 * 
 */

require_once(dirname(__FILE__)."/class.irc.php");

//реализация интерфейса
class Proto extends SBotProtocol  {

    protected $_masterAccounts = array(),
              $_irc,
              $_messagesQueue = array(),

       /*
        * Антифлуд: по умолчанию допускается не более
        * 22 сообщений в течение 22 секунд.
        */
       $_t=22, /* интервал времени антифлуда */
       $_n=22; /* Максимальное количество сообщений за интервал времени */

	public function __construct($bot) {
		parent::__construct($bot);
		
		
	}

	public function GetProtoInfo() {
		return array(
			'name' => 'irc',
			'version' => '1.0'
		);
	}

	public function Connect() {
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
		
		try {
			echo "irc(".
				S::bot()->config['proto']['uin'].", ".
				S::bot()->config['proto']['uin'].", ".
				S::bot()->config['proto']['uin'].", ".
				S::bot()->config['proto']['host'].", ".
				S::bot()->config['proto']['port']. 
			");\n";
			
			$this->_irc = new irc(
				S::bot()->config['proto']['uin'], 
				S::bot()->config['proto']['uin'], 
				S::bot()->config['proto']['uin'], 
				S::bot()->config['proto']['host'], 
				S::bot()->config['proto']['port']
			);
			
			S::bot()->eventmanager->EventRun( new Event(EVENT_CONNECTED) );
		} catch (XMPPHP_Exception $e) {
			S::logger()->log($e->GetMessage());
			return false;
		}
		
		if (!empty(S::bot()->config['proto']['channel'])) {
			$channels = S::bot()->config['proto']['channel'];
			if (!is_array(S::bot()->config['proto']['channel'])) {
				$channels = explode(',', S::bot()->config['proto']['channel']);
			}
			foreach($channels as $channel) {
				$this->_irc->joinChan('#'.$channel);
			}
		}
		return true;
	}

	public function Disconnect() {
		$this->_irc->disconnect();
	}

	public function Connected() {
		return $this->_irc->state();
	}

	public function GetMessage() {
		$msg = $this->_irc->readMessage();
		
		$msg["message"] = mb_convert_encoding($msg['message'], 'UTF-8', 'WINDOWS-1251');
		
		$msg['message'] = rtrim($msg["message"]);                                              
		
		if (mb_substr($msg['message'], 0, 1) == '%') {
			$msg['message'] = ltrim($msg['message'], '%');
			if ($msg['channel'] != '#') {
				$msg['from'] = $msg['channel'];
			}
			return new Event(EVENT_MSG_RECEIVED, array(
				'type' => 'message',
				'sender' => $msg['from'],
				'content' => $msg['message']
			));
		}
		unset($msg);
		return false;
	}

	public function SetStatus($status) {
		return true;
	}

	public function Error() {
		return false;
	}

	public function Msg($txt, $to) {
		echo "message to {$to}\n";
		$txt = mb_convert_encoding($txt, 'cp1251', 'utf-8');
		if ( $this->TimeToSend() ) {
			$this->_irc->msg(trim($txt), $to);
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
		return false;
	}

	public function IsAdmin($uin) {
		return in_array($uin, $this->_masterAccounts);
	}

	public function SetXStatus($status) {
		return true;
	}

	public function BotId() {
		return S::bot()->config['proto']['uin'];
	}

	public function GetClGroups() {
		return false;
	}

	public function addContactGroup($name) {
		return false;
	}

	public function deleteContactGroup($name) {
		return false;
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
		return false;
	}

	public function deleteContact($uin) {
		return false;
	}

	public function getAuthorization($uin, $reason='') {
		return false;
	}

	public function setAuthorization($uin, $granted=true, $reason='')	{
		return false;
	}

}

?>
