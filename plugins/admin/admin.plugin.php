<?php

/**
 * admin - SteelBot 3 plugin
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * @version 3.0.0
 *
 * 
 */

 //S::bot()->ExportInfo('admin', '3.0.0', 'nexor');
 
class AdminCommand extends BotCommand {
    protected $_prefix = '.';
    
   

    public function getAccess() {
        return S::bot()->config['bot']['user.max_access'];
    }

    public function GetPrefix() {
        return $this->_prefix;
    }

}

/*

class SteelBotAdmin {

static $firstchar = '.',
       $lng,
       $starttime = 0,
      
   
static function _($key) {
    $translated = self::$lng->GetTranslate( $key );
    if (func_num_args() > 1) {
        $params = func_get_args();
        array_splice($params,0,1 );
        for ($i=0; $i<count($params); $i++) {
            $translated = str_replace('%'.($i+1), $params[$i], $translated);
        }       
    }
    return $translated;    
}

}

*/

//SteelBotAdmin::$lng = new SteelBotLng( 'ru', 'ru' );
//SteelBotAdmin::$lng->AddDict( dirname(__FILE__).'/'.SteelBot::$cfg['language'].'.php' );
//SteelBotAdmin::$lng->AddDict( dirname(__FILE__).'/ru.php' );

$classes = include dirname(__FILE__).'/commands.php';
foreach ($classes as $adminCommand) {
   $command = new $adminCommand;
   $alias = $command->prefix.$command->name;
   S::bot()->commandManager->RegisterCommand($command)
   ->CreateAlias($command, $command->prefix.$command->name); 
}

