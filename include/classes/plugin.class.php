<?php

require_once "botcommand.class.php";

class Plugin extends SComponent implements ArrayAccess{
    protected $_name,
            $filename,
            
            $info = array(
                'name'    => '',
                'author'  => '',
                'version' => '99.99'
            ),
            
            $dependencies = array(
                'plugin'   => array(),
                'database' => array(),
                'proto' => array(),
                'bot' => array()
            ),
            
            $commands = array();
    
    public function __construct($filename) {
        parent::__construct(S::bot());
        $this->filename = $filename;
        $this->_name = str_replace('.plugin.php', '', basename($this->filename) );
    }

    public function getName() {
        return $this->_name;
    }
    
    public function GetInfo() {
        return $this->info;
    }
    
    public function ExportInfo($info) {
        $this->info = $info;
    }
    
    public function Load() {
        if ( is_readable($this->filename) ) {
            include_once($this->filename);         
        } else {
            throw new BotException("Can't get access to {$this->filename}", 0);
        }
    }
    
    public function AddDependence($dep, $version, $type) {
        switch ($type) {
            case 'plugin':
                    $this->dependencies['plugin'][$dep] = array(
                        'dep' => $dep,
                        'version' => $version
                    );
                    break;

            case 'database':
                    $this->dependencies['database'][$dep] = array(
                        'dep' => $dep,
                        'version' => $version
                    );
                    break;

            case 'proto':
                    $this->dependencies['proto'][$dep] = array(
                        'dep' => $dep,
                        'version' => $version
                    );
                    break;

            case 'bot':
                    $this->dependencies['bot'] = array(
                        'dep' => $dep,
                        'version' => $version
                    );
                    break;
            
            default: 
                throw new BotException("Unknown dependency type: $type",0);
        }
    }
    
    public function GetDependencies($filter = 'all') {
        if ($filter != 'all') {
            return $this->dependencies[$filter];
        } else {
            return $this->dependencies;    
        }
    }
    
    public function AddCommand($cmdobject) {
        $this->commands[$cmdobject->GetName()] = $cmdobject;
        return true;    
    }
    
    public function DelCommand($cmdname) {
        unset( $this->commands[$cmdname] );
    }

    public function GetCommand($commandName) {
        if (isset($this->commands[$commandName])) {
            return $this->commands[$commandName];
        } else {
            return null;
        }
    }
    
    public function GetCommands() {
        return $this->commands;
    }

    /**
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to check on
	 * @return boolean
     * @since 3.0
	 */
	public function offsetExists($offset)
	{
        return isset($this->commands[$offset]);
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
     * @since 3.0
	 */
	public function offsetGet($offset)
	{
        return isset($this->commands[$offset]) ? $this->commands[$offset] : null;
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
        unset( $this->commands[$cmdname] );
	}
}
