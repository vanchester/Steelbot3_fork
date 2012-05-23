<?php

/**
 * SDatabase class for Steelbot
 *
 * http://steelbot.net
 *
 * @author N3x^0r
 * @package steelbot
 *
 */

abstract class SDatabase extends SComponent {
    /**
     * Конструктор класса
     */
    public function __construct($bot) {
		parent::__construct($bot);

        $bot->eventManager
        ->AddEventType('EVENT_DB_CONNECTED')
        ->AddEventType('EVENT_DB_ERROR');
    }

    abstract public function GetDBInfo();
    
    abstract public function Connect();
    abstract public function Disconnect();
    
    abstract public function CreateUser($user, $access = null);
    abstract public function DeleteUser($user);
    abstract public function UserExists($user);
    abstract public function GetUserAccess($user);
    abstract public function SetUserAccess($user, $access);
    
    abstract public function GetCmdAccess($plugin, $command); 
    abstract public function SetCmdAccess($plugin, $command, $newlevel);
    abstract public function SetOption($option, $value, $type, $id=0);
    abstract public function GetOption($option, $type=SteelBot::OPTBOT, $id=0);
    abstract public function Flush();
}
