<?php

class Event extends SComponent {
    
    private /**
		     * event code
			 */
            $code, 
           
		    /**
		     *  event parameters
			 */
		    $params,
		   
		    /**
             *  handlers results
			 */
			$hresults;
    
    public function __construct($ev_code, $ev_params = array()) {
        $this->code = $ev_code;
        $this->params = $ev_params; 
    }
   
    public function GetCode() {
        return $this->code;
    }
   
    public function __get($name) {
        if (array_key_exists($name, $this->params)) {
            return $this->params[$name];
        } else {
            throw new Exception("Event parameter '$name' does not exist",0);
        }
    }
   
    public function __set($name, $value) {
        //if (array_key_exists($name, $this->params)) {
            $this->params[$name] = $value;
           
        //} else {
        //    throw new Exception("Event parameter '$name' does not exist",0);
        //}
    }
   
    public function GetCountHandlers() {
		return count($this->hresults);
    } 
	
    public function AddHandleResult($result) {
		$this->hresults[] = $result;
	}
	
	public function GetHandleResults() {
		return $this->hresults;
	}
}
