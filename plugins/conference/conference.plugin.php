<?php

/**
 * Conference plugin for SteelBot 3
 *
 * http://steelbot.net
 *
 * @author nexor
 *
 */

require_once dirname(__FILE__).'/muc.class.php';

S::bot()->eventManager
	->AddEventType('EVENT_INVITED')
	->RegisterEventHandler(EVENT_SESSION_START, array('ConferenceHandlers', 'OnConnect') )
	->RegisterEventHandler(EVENT_GROUPCHATMSG_RECEIVED, array('ConferenceHandlers', 'ParseMessage') )
	//->RegisterEventHandler(EVENT_INVITED, array('ConferenceHandlers', 'OnInvite') )
	->RegisterEventHandler(EVENT_USR_STATUS, array('ConferenceHandlers', 'OnPresence'));

S::bot()->RegisterCmd('count', array('ConferenceHandlers', 'CmdCount'), 1, null, false);

class MucException extends BotException {}

class MucUser {
	private $role,
			$status,
			$nickname;
	public function __construct($nickname, $role = MUC::ROLE_NONE) {
		$this->nickname = $nickname;
		$this->role = $role;
	}

	public function setNickname($newNickname) {
		$this->nickname = $nickName;
	}

	public function setRole($newRole) {
		$this->role = $newRole;
	}

	public function setStatus($newStatus) {
		$this->status = $newStatus;
	}

	public function getRole() {
		return $this->role;
	}

	public function getNickname() {
		return $this->nickname;
	}

	public function getStatus() {
		return $this->status;
	}

	public function __get($key) {
			return $this->$key;
	}
}



/**
 * Список конференций, обрабатываемых ботом
 */
class Conferences {
    /**
     * Список чатов
     *
     * @var array of MUC
     */
    public static $objects = array();
    
    /**
     * Добавить объект-конференцию в список
     *
     * @param Chat $object
     */
    public static function Add($object) {
        self::$objects[ $object->GetAddr() ] = $object;    
    }
    
    /**
     * Уничтожить объект-конференцию по его полному имени
     *
     * @param string $conference_addr
     * @example Del('steelbot@conference.jabber.org')
     */
    public static function Del($conference_addr) {
        unset( self::$objects[$conference_addr] );
    }
    
    /**
     * Получить объект-конференцию по его полному имени
     *
     * @param string $conference_addr
     * @return Chat
     */
    public static function Get($conference_addr) {
        if (array_key_exists($conference_addr, self::$objects)) {
            return self::$objects[ $conference_addr ];
        } else {
            return null;
        }
    }

    public static function isSelfMessage($sender) {
        list($conf, $nick) = explode('/',$sender);
        if (isset(self::$objects[$conf])) {
            return self::$objects[$conf]->nick == $nick;
        } else {
            return false;
        }
    }
    
}

class ConferenceHandlers extends Conferences {

	public static function CmdCount($text, $event) {
		list($conf, $nick) = explode('/', $event->sender);
		$conf = self::Get($conf);
		S::bot()->Msg("Количество пользователей конференции: ".count($conf->userlist));
	}

    public function CheckPolicy($muc) {
		$mode = S::bot()->config['plugins']['conference']['conferences.mode'];
		if ( $mode == 'allowed') {
			foreach (S::bot()->config['plugins']['conference']['conferences.allowed'] as $mask) {
				$regexp = '~^'.str_replace('*', '\w*?', $mask).'$~i';
				if (preg_match($regexp, $muc)) return true;
			}
			return false;
		} elseif ($mode == 'forbidden') {
			foreach (S::bot()->config['plugins']['conferences']['conferences.forbidden'] as $mask) {
				$regexp = '~^'.str_replace('*', '\w*?', $mask).'$~i';
				if (preg_match($regexp, $muc)) return false;
			}
			return true;
		
		} else {
			S::logger()->log("Unknown policy mode: '$mode'", 'conference');
			return false;
		}
    }
	
    /**
     * Событие подключения бота к серверу
     *
     */
    public static function OnConnect() {
        $configs = glob(dirname(__FILE__)."/conferences/*.php");
        foreach ($configs as $cfg) {
            $config = self::LoadConfig($cfg);            
            S::logger()->log("Loading muc config from ".basename($cfg)."...",
            'conference');          
            try {
                self::startMuc( $config );
            } catch (MucException $e) {
                print_r($e);
                continue;
            }        
        }
    }

    public static function OnPresence($event) {
		list($conf, $nick) = explode('/', $event->sender);
		if (array_key_exists($conf, self::$objects)) {
			self::$objects[$conf]->Presence($nick, $event->show);
		}
	}

    private static function LoadConfig($file) {
		return include $file;
    }
    
    static function ParseMessage($event) {    
        list($conference, $sender) = explode('/', $event->sender);
        // Не обрабатываем "оффлайновые" сообщения
        if ($event->subtype == 'delayed') {
            S::logger()->log("Ignoring delayed message",'conference', BaseLog::LEVEL_DEBUG);
            return false;
        }
        
        if (array_key_exists($conference, self::$objects)) {
            self::$objects[$conference]->Parse($sender, $event->content, $event);
        } else {
            S::logger()->log('Unknown conference '.$conference, 'conference', BaseLog::LEVEL_WARNING);
            return false;
        }

	    return true;    
    }

	/**
	 * Запустить конференцию с указанной конфигурацией
	 */
    private static function StartMuc($conf) {
		if (!$conf['enabled']) {
			S::logger()->log("Profile disabled.", 'conference', BaseLog::LEVEL_NOTICE);
			return;
		}
		if (empty($conf['addr'])) {
			throw new MucException("Empty conference address");
		}
        S::logger()->log("Creating MUC object ( {$conf['addr']} )...", 'conference');
        $muc = new MUC($conf['addr']);
        self::add($muc);
        $muc->Join($conf['nick']);
        S::logger()->log("Attaching commands...", 'conference');
        foreach ($conf['commands'] as $alias=>$cmdroute) {
            $muc->EnableCommand($cmdroute, $alias);
        }
        S::logger()->log("Done.", 'conference');
	}	
}


