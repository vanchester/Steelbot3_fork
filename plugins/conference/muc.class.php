<?php

class MUC {
    
    public $host,
            $room,
            
            $nick,
            $commands = array(),
            
            $status = 'available';
	public $userlist = array();
    const ROLE_NONE = 0;
    const ROLE_VISITOR = 1;
    const ROLE_PARTICIPANT = 2;
    const ROLE_MODERATOR = 3;

    public function __construct($host, $room = false) {
        if (!$room) {
            list($room, $host) = explode("@", $host, 2);
        }
        $this->host = $host;
        $this->room = $room;
    }
    
    public function Presence($user, $status) {
        if (array_key_exists($user, $this->userlist)) {
			if ($status == 'unavailable') {
				unset($this->userlist[$user]);
			} else {
				$this->userlist[$user]->setStatus($status);
			}
		} else {
			$this->userlist[$user] = new MucUser($user);
			$this->userlist[$user]->setStatus($status);
		}
        
    }
    
    public function GetAddr() {
        return $this->room."@".$this->host;
    }
    
    public function Join($nick) {
		S::logger()->log("Joining as $nick", 'conference');
        $this->nick = $nick;
        $addr = $this->room."@".$this->host."/".$nick;
        S::logger()->log("presence to $addr", 'conference');
        S::bot()->proto->conn->presence( null, $this->status, $addr);
    }
    
    public function Part() {
        $addr = $this->room."@".$this->host."/".$this->nick;
        S::logger()->log("exiting $addr", 'conference');
        S::bot()->proto->conn->presence( null, 'unavailable', $addr);
    }
    
    public function SetNick($newnick) {
        $this->nick = $newnick;
        $addr = $this->room."@".$this->host."/".$this->nick;
        S::logger()->log("changing nick: $addr", 'conference');
        S::bot()->proto->conn->presence( null, $this->status, $addr);
    }
    
    public function Parse($sender, $body, &$event) {
        if ($this->nick == $sender) {
            return;
        }
        S::bot()->proto->SetMessageCallback(array($this, 'Message'));
        S::logger()->log("Parsing $body", 'conference');
        @list($command, $params) = explode(' ', $body, 2);
        
        if (array_key_exists($command, $this->commands)) {
            try {
                $this->commands[$command]->Execute( $params, $event );
  
            } catch (BotException $e) {
	           print_r($e);
	           switch ($e->getCode()) {
	               case ERR_CMD_ACCESS:
	                   //self::Msg( LNG(LNG_CMDNOACCESS) );
	                   break;
	                
	               case ERR_FUNC:
	                   break;
	           }
	        }
        } elseif ($command == '!help') {
            $command_list = array_keys($this->commands);
            $this->Message("\nСписок команд:\n".implode(", ", $command_list));
        }
        
        S::bot()->proto->SetMessageCallback( null );
    }
    
    public function EnableCommand($commandRoute, $alias) {
        list($pluginName, $commandName) = explode('/', $commandRoute);
        if ( S::bot()->pluginManager[$pluginName][$commandName] instanceof BotCommand) {
            
            $this->commands[$alias] =  S::bot()->pluginManager[$pluginName][$commandName];
        } else {
            S::logger()->log("Can't bind '{$pluginName}/{$commandName}' to conference object - no such command", 'conferences', BaseLog::LEVEL_WARNING);
        }
    }
    
    public function Message($msg) {
        S::bot()->proto->conn->message("{$this->room}@{$this->host}", $msg, 'groupchat');
    }
    
}
