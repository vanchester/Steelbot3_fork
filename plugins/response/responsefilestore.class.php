<?php

class ResponseFileStore extends ResponseStore {
    protected $_dict,
              $_path;
    
    public function __construct()  {
        $this->_path = dirname(__FILE__).'/answers';
        $commandsFile = dirname(__FILE__).'/commands.txt';
        if (file_exists($commandsFile)) {
            $lines = explode("\n", file_get_contents($commandsFile) );
            foreach($lines as $d) {
                $d = trim($d);
                $d = explode('::', $d);
                $this->_dict[$d[0]] = $d[1];
            }
            S::logger()->log(count($this->_dict)." commands has been added", 'response');
        } else {
            S::logger()->log("Can't open commands files", 'response', BaseLog::LEVEL_WARNING);
        }
    }
    
    public function FindMatch($key) {
        if (array_key_exists($key, $this->_dict)) {
            $filename = $this->_path.'/'.$this->_dict[$key];
            return file_get_contents($filename);    
        }
    }

}
