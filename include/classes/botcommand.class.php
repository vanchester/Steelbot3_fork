<?php

class BotCommand extends SComponent {
    public  $access = 1,
            $helpShort,
            $helpFull;
            
    protected $_name,
              $_plugin,
              $_callbackList = array();

    
    public function __construct($name = null, $plugin = null) {
        if (!is_null($name)) {
            $this->_name = $name;
        }
        $this->_plugin = $plugin==null?S::bot()->plugin:$plugin;
    }    
      
    public function getName() {
        return $this->_name;
    }

    public function getAccess() {
        return $this->access;
    }

    public function setAccess($level) {
        $this->access = $level;
    }

    public function getHelpShort($alias = null) {
        return str_replace(array('{alias}'), array($alias), $this->helpShort);
    }

    public function getHelpFull($alias = null) {
        return str_replace(array('{alias}'), array($alias), $this->helpFull);
    }
    
    public function getCallbackList() {
        return $this->_callbackList;
    }
    
    public function addCallbackFunc($func, $allowDuplicate = false) {
        if (!in_array($func, $this->callbackList)) {
            $this->_callbackList[] = $func;
            return $this;
        } elseif ($allowDuplicate) {
            $this->_callbackList[] = $func;
            return $this;
        } else {
            throw new BotException("Function already is in callbacks",0);
        }
    }
    
    public function delCallbackFunc($func) {
        foreach ($this->_callbackList as $k=>$c) {
            if ($c==$func) {
                unset($this->_callbackList[$k]);
            }
        }
        return $this;
    }    

    public function getPlugin() {
        return $this->_plugin;
    }

    public function Execute($params, &$msgevent) {
        //access check
        
        if ( $ac=S::bot()->GetUserAccess() < $this->access) {
            throw new BotException("{$this->name}: acces denied (user: $ac, cmd: {$this->access})", ERR_CMD_ACCESS);
        }
       
        foreach ($this->_callbackList as $callback) {
            if ( is_callable($callback) )  {
                call_user_func($callback, $params, $msgevent);
            } else {
                throw new BotException("Function ".S::func2str($callback)." does not exists", ERR_FUNC);
            }
        }

        return true;
    }
   
}
