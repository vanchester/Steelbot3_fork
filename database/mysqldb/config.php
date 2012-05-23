<?php

/**
 * Настройки для подключения к БД
 */
return array(
	'db' => array(
		// имя пользователя
		'user' => 'root',

		// пароль
		'password' => '',

		// имя базы данных
		'database' => 'steelbot',

		// хост для подключения
		'host' => 'localhost',

		// префикс названий таблиц бота
		'table_prefix' => 'steelbot_',

		// таблица для конфигурационных переменных
		'table_config' => 'options',

		'setnames' => 'utf8',

		// добавляем опции в игнор-лист для загрузки из базы данных
		'ignore_config_options' => array(
			'/^table_prefix$/', // префикс названий таблиц
			'/^mysql_\S+/', // все опции mysql
		),

		'option.wait_timeout' => 28800
	)
);
