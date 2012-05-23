<?php


$dict = array(NULL,
    'help_notfound',
    'nohelp',
    'helpcommands',
    'cmdnoaccess',
	'cmdnotfound',
	'errfunc'
);

for ($i=1; $i<count($dict); $i++) {
    define( 'LNG_'.strtoupper($dict[$i]),  $i);
}

function LNG($key) {
    throw new BotException(1,1);
    $translated = SteelBot::$lng->GetTranslate( $key );
    if (func_num_args() > 1) {
        $params = func_get_args();
        array_splice($params,0,1 );
        for ($i=0; $i<count($params); $i++) {
            $translated = str_replace('%'.($i+1), $params[$i], $translated);
        }       
    }
    return $translated;
    
}
