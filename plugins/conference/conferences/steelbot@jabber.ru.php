<?php
return array(

    // включить профиль	
    'enabled' => true,
    
    // адрес конференции
    'addr' => 'steelbot@conference.jabber.ru',
    
    // ник, под которым бот зайдет на конференцию
    'nick' => 'SteelBot',
    
    // включенные команды
    'commands' => array(         
        '!md5'     => 'default/md5',        
        '!count' => 'conference/count',               
     )
);
