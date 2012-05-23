<?php

class MemUsage {
    static $delay=60;
    
    public static function Refresh() {
        $write = date('H:i:s '.( ceil(memory_get_usage()/1024))."kb"); 
	S::logger()->log( $write, 'memusage');
        
	S::bot()->timerManager->TimerAdd( self::$delay, array('MemUsage', 'Refresh'));
          
    }
        
}

MemUsage::Refresh();
