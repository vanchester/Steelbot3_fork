<?php

class PluginManager extends SComponent implements ArrayAccess {	
	protected $instances = array(),
              $_current_plugin,
              $plugins;
	
	
	public function __construct($bot) {
		parent::__construct($bot);
        $this->plugins = $this->FindPlugins(STEELBOT_DIR.'/plugins');

        if (is_dir(APP_DIR.'/plugins')) {
            $userplugins = $this->FindPlugins(APP_DIR.'/plugins');
            $this->plugins = S::mergeArray($this->plugins, $userplugins);
        }

        if (isset($bot->config['plugin_sources'])) {
            foreach ($bot->config['plugin_sources'] as $path) {
                if (is_dir($path)) {
                    $plugins = $this->FindPlugins($path);
                    $this->plugins = S::mergeArray($this->plugins, $plugins);
                } else {
                    S::logger()->log("Invalid path: $path", __CLASS__, BaseLog::LEVEL_WARNING);
                }
            }
        }
	}
    
    public function getPluginInstance($name = null) {
        if (is_null($name)) {
            if ($this->_current_plugin != null) {
                return $this->_current_plugin;
            } else {
                $backtrace = debug_backtrace();
                $name = str_replace('.plugin.php', '', basename($backtrace[3]['file']));
                $items = explode('.', $name, 2);
                $name = array_shift($items);
                if ($this->PluginLoaded($name)) {
                    return $this->instances[$name];
                } else {
                    return null;
                }            
            }
        } else {
            if (array_key_exists($name, $this->instances)) {
                return $this->instances[$name];
            } else {
                return null;
            }
        }
    }

    /**
     * Check if plugin is available
     * 
     * @return string
     * @since 3.0
     */
    public function pluginAvailable($name) {
        if (array_key_exists($name, $this->plugins)) {
            return $this->plugins[$name];
        } else {
            return false;
        }
    }

    function FindPlugins($dir) {
        $names = glob($dir.'/*');
        $result = array();
        foreach ($names as $fileName) {
            if (is_dir($fileName)) {
                $result += $this->FindPlugins($fileName);
            } elseif (is_file($fileName) && substr($fileName, -11) == '.plugin.php') {
                $name = str_replace('.plugin.php', '', basename($fileName));
                S::logger()->log("Plugin found: $fileName", __CLASS__, BaseLog::LEVEL_DEBUG)
                or
                S::logger()->log("Plugin found: $name", __CLASS__, BaseLog::LEVEL_NOTICE);
                $result[$name] = realpath($fileName);
            }
        }
        return $result;
    }

    public function LoadPlugin($filename, $params) {
        S::logger()->log("Loading $filename...", __CLASS__, BaseLog::LEVEL_DEBUG);
        $name = str_replace('.plugin.php', '', basename($filename) );
        
        if ($this->PluginLoaded($name)) {
            throw new BotException(("Plugin $name already loaded"));
        } else {
            $plug = new Plugin($filename);
            $this->_current_plugin = $plug;
            $plug->Load();        
            $this->instances[$name] = $plug;
            S::logger()->log("'$name' load OK");
            S::bot()->eventManager->EventRun(
                new Event(EVENT_PLUGIN_LOADED, array('name'=>$name))
            );                   
            $this->_current_plugin = null;
            return true;        
        }
    }

    /**
     * @param BotCommand $command
     * @return string
     * @since 3.0
     */
    public function AddCommand(BotCommand $command) {
        $plugin = $this->pluginInstance;
        if ($plugin != null) {
            $plugin->AddCommand($command);
            return $plugin->name;
        } else {
            return null;
        }
    }    

    /**
     * Check if plugin is loaded
     *
     * @param string $param
     * @return bool
     * @since 3.0
     */
    public function PluginLoaded($plugin) {
        return array_key_exists($plugin, $this->instances);
    }

    /**
     * Get all plugin instances
     *
     * @return array
     * @since 3.0
     */
    public function getPluginInstances() {
        return $this->instances;
    }

    /**
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to check on
	 * @return boolean
     * @since 3.0
	 */
	public function offsetExists($offset)
	{
        return isset($this->instances[$offset]);
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
     * @since 3.0
	 */
	public function offsetGet($offset)
	{
        return isset($this->instances[$offset]) ? $this->instances[$offset] : null;
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
        trigger_error("Illegal array operation");
	}
}
