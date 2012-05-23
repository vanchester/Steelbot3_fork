<?php

return array(
	'proto' => array(
              // имя аккаунта
              'jid'   => '',         
              
              // пароль от JID
              'password'   => '',
              
              // название ресурса
              'resource'   => 'steelbot',
              
              // сервер (в случае, если отличается от имени хоста)
              'server'     => null,
              
              'port' => 5222,
              
			  // JID администратора бота (или несколько аккаунтов через запятую)
			  'master_accounts'    => array( ),
			  
			  // автоматически принимать все запросы авторизации
			  'autosubscribe' => true,
			  
			  // использовать шифрование
			  'encryption.enabled' => false,
			  
			  // использовать SSL
			  'ssl.enabled' => false,

              // обрабатывать сообщения, присланные в оффлайн
              'messages.process_delayed' => true,

              // обрабатывать пустые сообщения
              'messages.proccess_null' => false,

              'delaylisten' => 1
			  )
			  );
