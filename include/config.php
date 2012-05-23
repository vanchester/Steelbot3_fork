<?php

return array(
	'bot' => array(		  
			  //интервал прослушки сокета, рекомендуется 1
			  'delaylisten'   => 1,
			  
			  //максимальное количество попыток подключения
			  'connect_attempts' => 5,
			  
			  // чувствительность команд к регистру
			  'msg_case_sensitive' => false,
			  
			  // маска имени файла в логах
			  // переменные: см. http://php.net/date
			  'log.filename_format' => 'd_M_Y',			     
			     
			  // формат даты в логах
			  // cинтаксис аналогичен переменным в php функции date()
			  // переменные: см. http://php.net/date
			  'log.dateformat' => 'H:i:s',

			  /**
			   * Класс, используемый для логирования
			   */
			  'log.class' => 'baselog',

			  /**
			   * Правила вывода сообщений лога
			   * 
			   * По умолчанию сообщения лога имеют уровень BaseLog::LEVEL_NOTICE
			   *
			   * Возможные уровни:
			   * BaseLog::LEVEL_INFO     - выводятся все сообщения
			   * BaseLog::LEVEL_NOTICE   - выводятся замечания 
			   * BaseLog::LEVEL_WARNING  - выводятся предупреждения
			   * BaseLog::LEVEL_ERROR    - выводятся ошибки
			   * BaseLog::LEVEL_NONE     - ничего не выводится
			   */
			  'log.rules' => array(
				 // уровень вывода для steelbot				  
				 'steelbot' => BaseLog::LEVEL_NOTICE,

				 // уровень вывода для ядра бота
				 'core' => BaseLog::LEVEL_NOTICE,

				 // уровень вывода для сообщений логгера				 
				 'logger' => BaseLog::LEVEL_WARNING,

				 // уровень вывода для интерпретатора PHP
				 'PHP' => BaseLog::LEVEL_WARNING,

				 // уровень вывода для всех остальных модулей				 
				 '*' => BaseLog::LEVEL_NOTICE
			  ),
			  			  
			  /**
			   * Формат вывода каждой команды в помощи
			   *   %c - команда
			   *   %s - текст помощи
			   */
			  'help.format' => "%s\n",
			  'help.format_full' => "%s",
			  
			  /**
			   * Сохранять таймеры в файл при завершении работы бота
			   */
			  'save_actual_timers' => true,
			  
			  /**
			   * автоматически подключаемый при выходе в онлайн файл.
			   */
			  'autoinclude_file' => dirname(__FILE__)."/../autorun.php",
			  
			  
			  // Язык по умолчанию
			  'language' => 'ru',

              // максимально возможный уровень доступа
              'user.max_access' => 100,

              // минимально возможный уровень доступа
              'user.min_access' => 0,

              // уровень доступа для всех пользоваталей по умолчанию
			  'user.default_access' => 1,

			  // тестовый запуск без подключения к серверу
			  'test' => false
	),
	'proto' => array(
        // интервал ожидания между попытками переподключения (в секундах)
        'reconnect_delay'=>10,
    ),
	'db' => array(
		// тип используемой базы данных			  
		'engine' => 'mysql',

		// загружать конфигурацию из базы данных
		'use_config' => true,

		// не записывать в базу данных следующие опции
		// (регулярное выражение)
		'ignore_config_options' => array(
			'/^db\.use_config$/',
			'/^db\.ignore_config_options$/',
			'/^master_accounts$/',
			'/^password$/'	
		),

		// использовать БД для получения уровня доступа к командам
		'use_cmd_access'	=> true,

		// использовать БД для алиасов
		'use_cmd_aliases'  => true
	),
    'plugins' => array()
);
