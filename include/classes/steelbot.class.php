<?php

/**
 * SteelBot class for SteelBot
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 *
 */

class SteelBot extends SComponent {

    public $config,
           $db,
           $proto;

    public $database,
           $msgdropped = false,
           $lng,
                         
           $msgEvent,
           $plugins = array(); 

    private $_timermanager = null,
            $_eventmanager = null,
            $_pluginmanager = null,
            $_commandmanager = null,
            $_sessionmanager = null;

			   
    const OPTBOT = 1;
    const OPTPLUGIN = 2;
    const OPTPROTOCOL = 3;

    private $current_plugin = null;

    public function __construct($config) {
        parent::__construct($config);
        $this->config = $config;
        $this->cfg = $config;

        include_once STEELBOT_DIR.'/database/'.$config['db']['engine'] .'/steelbotdb.class.php';
        include_once STEELBOT_DIR.'/protocol/'.$config['proto']['engine'].'/proto.class.php';

        $this->_timermanager = new TimerManager($this);
        $this->_eventmanager = new EventManager($this);
        foreach (array(
            'EVENT_RECONNECT',
            'EVENT_EXIT',
            'EVENT_PRE_PLUGIN_LOAD',
            'EVENT_PLUGIN_LOADED',
            'EVENT_CMD_ACCESS_CHANGED',
            'EVENT_CMD_REGISTERED',
            'EVENT_CMD_UNREGISTERED',
            'EVENT_CMD_LOADED',
            'EVENT_CMD_DISABLED',
            'EVENT_CMD_ENABLED',
            'EVENT_TIMER_ADDED',
            'EVENT_BOT_LOADED',
            'EVENT_HELP_NOTFOUND',
        ) as $event) {
            $this->eventManager->AddEventType($event);
        }
        
        $this->_pluginmanager = new PluginManager($this);
        $this->_commandmanager = new CommandManager($this);
        $this->_sessionmanager = new SessionManager($this);
        $this->db = new SteelBotDB($this);
        $this->proto = new Proto($this);
    }

    public function getTimerManager() {
        return $this->_timermanager;
    }
    public function getEventManager() {
        return $this->_eventmanager;
    }

    public function getPluginManager() {
        return $this->_pluginmanager;
    }

    public function getCommandManager() {
        return $this->_commandmanager;
    }

    public function getSessionManager() {
        return $this->_sessionmanager;
    }

    public function getPlugin() {
        return $this->_pluginmanager->getPluginInstance();
    }

    function Init() {

        // i18n    
        //self::$lng = new SteelBotLng(self::$cfg['language']); 
        /* $files = glob(STEELBOT_DIR.'/include/lang/*.php');
        foreach ($files as $langfile) {
            S::logger()->log( "Lang: ".basename($langfile)." ");
            self::$lng->AddDict($langfile);
        }
        */
        S::bot()->db->connect();
        
        // загрузка плагинов
        foreach ($this->config['plugins'] as $k=>$v) {
            if (is_array($v)) {
                $pluginName = $k;
                $params = $v;
            } else {
                $pluginName = $v;
                $params = array();
            }

            if ($filename = $this->pluginManager->pluginAvailable($pluginName))
            {
                $this->pluginManager->LoadPlugin($filename, $params);
            }  else {
                throw new BotException("Unknown plugin: $pluginName", 0);
            }      
        }        
    }

    function Msg($text, $to = false) {
        if (!$to) {
            $to = $this->msgEvent->sender;
            if (empty($to)) {
                S::logger()->log("Can't determine message sender",'steelbot', BaseLog::LEVEL_WARNING);
                return false;
            }        
        }	
        $event = new Event(EVENT_PRE_MSG_SEND, array('content'=>$text, 'to'=>$to));
        $this->eventManager->EventRun( $event );
        $this->proto->msg($event->content, $event->to);    
        $ev = $this->eventManager->EventRun( new Event(EVENT_MSG_SENT, array('text'=>$text, 'to'=>$to)) );    
        S::logger()->log("> $to ".$text);
        return true;
    }
        
    function getSender() {
        return $this->msgEvent->sender;
    }

    function getMsgText() {
        return $this->msgEvent->content;
    }  

    function getAlias() {
        return $this->msgEvent->alias;
    }     

    /**
     * @desc Регистрирует пользовательскую команду в системе.
     *
     * @param string $command - имя команды
     * @param string $func - функция, которая будет вызвана при получении этой команды
     * @param int $access - уровень доступа к команде
     * @param string $helpstr - текст, который будет отправляться при обращении
     * к помощи по данной команде
     * @param bool $create_alias - создать алиас с именем команды. Устанавливается в
     * false, если у команды должно быть другое название.
     *
     * @return BotCommand object
     *
     */
    function RegisterCmd($command, $func, $access = 1, $helpstr = null, $create_alias = true) {
            
        if (!($command instanceof BotCommand)) {
           $command = $this->commandManager->BuildCommand($command, $func, $access, $helpstr);       
        }
        $this->commandmanager->RegisterCommand($command);

        if ($create_alias) {
            $this->commandManager->createAlias($command, $command->name);
        }
        return $command;
    }

    function GetVersion() {
        return STEELBOT_VERSION;
    }

/*
function AddDependence($dep, $version, $type='plugin') {
    if (self::$current_plugin == null) {
        trigger_error("SteelBot::AddDependence() out of plugin call", E_USER_WARNING);
        return false;
    } else {    
        return self::$current_plugin->AddDependence($dep, $version, $type);
    }
}

/*
function CheckDependency($plugin) {
    if (array_key_exists($plugin, self::$plugins)) {
        S::logger()->log("Checking for dependencies $plugin... ");
        $dependencies = self::$plugins[$plugin]->GetDependencies();
        
        // plugins check
        foreach ($dependencies['plugin'] as $dep) {
            if ( array_key_exists($dep['dep'], self::$plugins) ) {
                $info = self::$plugins[$dep['dep']]->GetInfo();                    
                if ( !(CheckVersion( $dep['version'], $info['version']) === false) ) {
                         continue;
                }
            }
            throw new BotException("Plugin $plugin require {$dep['dep']} {$dep['version']} plugin", ERR_DEPENDENCY);            
        }
        
        // protocol check
        $proto_info = Proto::GetProtoInfo();
        foreach ($dependencies['proto'] as $dep) {
            if ($proto_info['name'] == $dep['dep']) {
                if ( CheckVersion($dep['version'], $proto_info['version']) === false ) {
                    throw new BotException("Plugin $plugin require protocol {$proto_info['name']} {$dep['version']} or higher", ERR_DEPENDENCY);
                } else {
                    break;
                }
            }
        }

        // database check
        $db_info = self::$database->GetDBInfo();
        foreach ($dependencies['database'] as $dep) {           
            if ($db_info['name'] == $dep['dep']) {

                if ( CheckVersion($dep['version'], $db_info['version']) === false ) {
                    throw new BotException("Plugin $plugin require database {$db_info['name']} {$dep['version']} or higher", ERR_DEPENDENCY);
                } else {                    
                    break;
                }
            }
        }

        // bot check
        if ( count($dependencies['bot']) && CheckVersion($dependencies['bot']['version'], self::GetVersion())===false ) {
            throw new BotException("Plugin $plugin require SteelBot {$dependencies['bot']['version']} or higher", ERR_DEPENDENCY);
        }
      
    }
}

function FindAlias($plugin, $command) {
    if (is_object(self::$cmdlist[$command][$plugin])) {
        return self::$cmdlist[$command][$plugin]->GetAliases(true);
    } else {
        return false;
    }
}

 */

    static function ExportInfo($name, $version, $author) {
        $info = array(
            'name' => $name,
            'author' => $author,
            'version' => $version
        );
        $plugin = S::bot()->plugin;
        if (!is_null($plugin)) {
                S::bot()->plugin->ExportInfo($info);
        }
    }

/**

function DropMsg() {
    self::$msgdropped = true;
}

/**
 * @desc Устанавливает уровень доступа к указанной пользовательской команде.
 *
 * @param unknown_type $cmd
 * @param unknown_type $level
 * @return bool - true, если уровень установлен
 *                false,если получен некорректный уровень доступа или
 *                команда не найдена 
 *

function SetOption($option, $value, $type=self::OPTBOT, $id=0) {
	if ($type==self::OPTBOT) {
        self::$cfg[$option] = $value;
        if (SteelBot::DbIgnoredOption($option)) return;
    }
	self::$database->SetOption($option, $value, $type, $id);
}

function GetOption($option, $type=self::OPTBOT, $id=0) {
    return self::$database->GetOption($option, $type, $id);
}

function DbIgnoredOption($option) {
	foreach (SteelBot::$cfg['db.ignore_config_options'] as $regexp) {
			if (preg_match($regexp, $option)) {
				return true;
			}
	}
	return false;
}


    /**
     * @desc Установливает пользователю уровень доступа к боту
     *
     * @param string $user - id пользователя
     * @param int $level - уровень доступа от 1 до 100 
     * @return boolean
     */
    function SetUserAccess($user,$level) {
        if (!is_numeric($level) || $level > 100) {
            S::logger()->log("Incorrect users access level: $level");     
            return false;
        } else {
            $this->db->SetUserAccess($user, $level);
            return true;
        }    
    }

	/**
	 * @desc Возвращает уровень доступа, установленный для пользователя. Если 
	 * пользователь не указан, то возвращает уровень доступа приславшего сообщение.
	 *
	 * @param  string $user
	 * @return int
	 */
	public function getUserAccess($user = false) {
		$user = $user?$user:$this->msgEvent->sender;
		if ($this->proto->IsAdmin($user)) {
			return S::bot()->config['bot']['user.max_access'];
		} else {
			return $this->db->GetUserAccess($user);
		}
	}



    /**
     * @desc Анализирует и исполняет присланную пользовательскую команду
     *
     * @return bool
     */
    public function Parse($event) {
        if (strpos($event->content, ' ')) {
            list($alias, $params) = explode(' ', $event->content, 2);
        } else {
            $alias = $event->content;
            $params = null;
        }

        S::logger()->log("< ".$event->sender." ".$event->content);
    
        if (!$this->config['bot']['msg_case_sensitive']) {
            $alias = mb_strtolower($alias, 'utf-8');
        }

        $event->alias = $alias;
        $event->params = $params;
        $this->msgEvent = $event;    
        $command = $this->commandManager->getCommandByAlias($alias);
    
        if ($command instanceof BotCommand) {
            try {
                $command->execute($params, $event);
                			
            } catch (BotException $e) {
                S::logger()->log( $e->getMessage() );
                switch ($e->getCode()) {
                    case ERR_CMD_ACCESS:
                        S::logger()->log("ERR_CMD_ACCESS");
                        self::Msg( $e->getMessage() );
                        break;
	                
                    case ERR_FUNC:
                        S::logger()->log("ERR_FUNC");
                        //self::Msg( LNG(LNG_ERRFUNC));
                        break;
                }
            }
        } else {
            $this->eventManager->EventRun(new Event(EVENT_MSG_UNHANDLED, array(
                'alias' => $alias,
                'params' => $params,
                'event' => $event
            )));
        }    
        return true;    
    }

    /**
     * @desc Последовательно вызывает все выходные функции, а затем завершает работу
     * скрипта.
     *
     */
    function DoExit() {
        S::logger()->log("Exit requested");

        /* ## if (self::$cfg['save_actual_timers']) {
            self::SaveTimers();
        } */
        S::bot()->eventManager->EventRun( new Event(EVENT_EXIT) );
        exit("\n");
    }

}
