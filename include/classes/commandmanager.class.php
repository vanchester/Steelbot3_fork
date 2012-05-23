<?php

class CommandManager extends SComponent implements ArrayAccess {

	protected $commands = array(),
              $aliases = array();
	
	public function __construct($bot) {
		parent::__construct($bot);
	}

    public function getAliases() {
        return array_keys($this->aliases);
    }

    public function getCommands() {
        return array_unique(array_values($this->commands));
    }

	public function RegisterCommand($command) {
		$pluginName = S::bot()->pluginmanager->AddCommand($command);
        $dbAccess = S::bot()->db->getCmdAccess($pluginName, $command->name);
        if ($dbAccess >= 0) {
            $command->SetAccess($dbAccess);
        } else {
            S::bot()->db->setCmdAccess($pluginName, $command->name, $command->GetAccess());
        }		
        $this->commands[$pluginName][$command->name] = $command;
        S::bot()->eventManager->EventRun(new Event(EVENT_CMD_REGISTERED, array('command' => $command)));
        return $this;
	}

    /**
     * @since 3.0
     * @param mixed $command - string or BotCommand
     * @todo specify the behavior of this command
     */
    public function UnregisterCommand($command) {
        if ($command instanceof BotCommand) {
            foreach ($this->aliases as $k=>$c) {
                if ($c == $command) {
                    unset($this->aliases[$k]);
                }
            }
            //$c->plugin->DelCommand($command->name);
            S::bot()->eventManager->EventRun( new Event(EVENT_CMD_UNREGISTERED,
            array('command' => $command)));
            
        } elseif (strpos($command, '/')) {
            list($pluginName, $commandName) = explode('/', $command);
            $c = S::bot()->pluginManager[$pluginName][$commandName];
            $this->UnregisterCommand($c);
        }
    }

    public function runCommand($command, $event, $params) {
        if ($command instanceof BotCommand) {
            $command->Execute($params, $event);
            
        } elseif (strpos($command, '/')) {
            list($pluginName, $commandName) = explode('/', $command);
            $c = S::bot()->pluginManager[$pluginName][$commandName];
            $c->Execute($params, $event);
        }
    }

    public function CreateAlias($command, $alias) {
        $alias = mb_strtolower($alias, 'utf-8');
        if (array_key_exists($alias, $this->aliases)) {
            throw new BotException("Alias '$alias' already bind to ".
                get_class($this->aliases[$alias]), 0);
        }
        S::logger()->log("Creating alias $alias", __CLASS__, BaseLog::LEVEL_DEBUG);
        $this->aliases[$alias] = $command;
        return $this;
    }

    public function GetCommandByAlias($alias) {
        if (array_key_exists($alias, $this->aliases)) {
            return $this->aliases[$alias];
        } else {
            return null;
        }
    }
    
    /**
     * 
     *
    function CommandExists($plugin, $cmd) {
        return array_key_exists($cmd,self::$cmdlist) &&
               array_key_exists($plugin, self::$cmdlist[$cmd]);   
    }

    function AliasExists($alias) {
        return array_key_exists($alias, self::$aliases);
    }
    
    
    function SetCmdAccess($plugin, $cmd,$level) {
	if (!is_numeric($level)) {
	    throw new BotException("$level is not a numeric value", ERR_NOT_NUMERIC);
	} elseif (self::CommandExists($plugin, $cmd)) {
		if (self::$cmdlist[$cmd][$plugin]->SetAccess( $level )) {
		    self::$database->SetCmdAccess($plugin, $cmd, $level);
		    $ev = new Event(EVENT_CMD_ACCESS_CHANGED, array(
		                                               'plugin' => $plugin,
		                                               'command'=>$cmd, 
		                                               'level'=>$level
		                                              )
		    );
		    self::EventRun($ev);
		}
		return true;
	} else throw new BotException("Command does not exists", ERR_CMD_NOTFOUND);
}
*/

    public function BuildCommand($name, $func, $access = 1, $helpstr = null) {
        $name = mb_strtolower($name, 'utf-8');
	    if (!is_numeric($access)) {
	         $access = 1;
	    }
        $command = new BotCommand($name);
        $command->addCallbackFunc($func);
        $command->setAccess($access);
        $command->helpFull = $helpstr;
        $command->helpShort = $name;
        
	    return $command;
    }

    /**
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to check on
	 * @return boolean
     * @since 3.0
	 */
	public function offsetExists($offset)
	{
        $offset = mb_strtolower($offset, 'utf-8');
        return isset($this->aliases[$offset]);
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
     * @since 3.0
	 */
	public function offsetGet($offset)
	{
        $offset = mb_strtolower($offset, 'utf-8');
        return isset($this->aliases[$offset]) ? $this->aliases[$offset] : null;
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to set element
	 * @param mixed $item the element value
     * @since 3.0
	 */
	public function offsetSet($offset,$item)
	{
		trigger_error("Illegal array operation");
    }

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to unset element
     * @since 3.0
	 */
	public function offsetUnset($offset)
	{
        unset( $this->aliases[$offset] );
	}

}
