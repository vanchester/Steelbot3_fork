<?php

/**
 * user-tools - плагин для SteelBot
*/

S::bot()->eventManager->RegisterEventHandler(EVENT_CONNECTED, array('plg_timer', 'loadSavedAlarms'));
S::bot()->RegisterCmd("timer", array("plg_timer", "analize"), 1,"{alias} <period> <message> - добавить таймер (пример period: 1d7h4m10s). Команда без параметров выведет все ваши таймеры");
S::bot()->RegisterCmd("alarm", "plg_alarm", 1,"{alias} <time> [days] <message> - добавить будильник (пример time: 7:05, пример days: 1235 (пн,вт,ср,пт)). Команда без параметров выведет все ваши будильники");
S::bot()->RegisterCmd("memo", array("Memo", "analize"), 1,"{alias} [add|del|list] <name> [text] - управление личными заметками");
S::bot()->RegisterCmd("hist", "plg_hist", 1,"{alias} [num] - история введенных команд. если указан num, команда под номером num будет повторена");

class Memo {
	const TABLENAME = 'user_memo';
	const MAX_COUNT = 100;
	
	public static function analize($val) {
		$val = trim($val);
		if (empty($val)) {
			self::lst();
			return;
		}
		
		$val = preg_replace('/[\s]{2,}/', ' ', $val);
		
		$val = explode(" ", $val);
		
		$command = mb_strtolower($val[0]);
		if (in_array($command, array('add', 'del'))) {
			if (empty($val[1])) {
				S::bot()->Msg("Неверный формат команды");
				return;
			}
			$memoName = $val[1];
			unset($val[0]);
		}
				
		switch ($command) {
			case 'add':
				unset($val[1]);
				$text = implode(' ', $val);
				if (empty($text)) {
					S::bot()->Msg("Вы не ввели текст заметки");
					return;
				}
				self::add($memoName, $text);
				break;
			case 'del':
				self::del($memoName);
				break;
			case 'list':
				self::lst();
				break;
			default:
				$memoName = $command;
				self::load($memoName);
		}
	}

	private static function add($memoName, $memoText) {
		$db = S::bot()->db;
		
		$query = $db->FormatQuery(
			"SELECT 
				COUNT(*) 
			FROM 
				".S::bot()->config['db']['table_prefix'].self::TABLENAME."
			WHERE
				`uin` = {uin} AND `memo_name` = {memo_name}",
			array(
				'uin' => S::bot()->msgEvent->sender,
				'memo_name' => $memoName,
			)
		);
		
		$memoCount = $db->QueryValue($query);
		
		if ($memoCount > 0) {
			S::bot()->Msg("Заметка с таким именем уже существует");
			return;
		}
		
		$query = $db->FormatQuery(
			"SELECT 
				COUNT(*) 
			FROM 
				".S::bot()->config['db']['table_prefix'].self::TABLENAME."
			WHERE
				`uin` = {uin}",
			array(
				'uin' => S::bot()->msgEvent->sender,
			)
		);
		
		$memoCount = $db->QueryValue($query);
		
		if ($memoCount >= self::MAX_COUNT) {
			S::bot()->Msg("У вас добавлено максимальное количество заметок. Для добавления новой удалите старые");
			return;
		}
		
		$query = $db->EscapedQuery(
				"INSERT INTO ".S::bot()->config['db']['table_prefix'].self::TABLENAME."
				 (`uin`, `memo_name`, `date`, `text`)
				 VALUES ({uin}, {memo_name}, {date}, {text})",
				 array(
					'uin' => S::bot()->msgEvent->sender,
					'memo_name' => $memoName,
					'date' => date('Y-m-d H:i:s'),
					'text' => $memoText,
				 )
			);
		
		S::bot()->Msg("Заметка '{$memoName}' успешно добавлена");
	}

	private static function load($val) {
		$db = S::bot()->db;
		
		$query = $db->FormatQuery(
				"SELECT text FROM ".S::bot()->config['db']['table_prefix'].self::TABLENAME."
				 WHERE `uin` = {user} AND
					   `memo_name` = {memo_name}",
				 array(
					'user' => S::bot()->msgEvent->sender,
					'memo_name' => $val
				 )
			);

		$result = $db->QueryValue($query);
		
		if (empty($result)) {
			S::bot()->Msg("Заметки с именем '{$val}' не найдено");
			return;
		}
		
		S::bot()->Msg($result);
	}

	private static function del($val) {
		$val = trim($val);
		$val = current(explode(" ", $val));
		
		if (empty($val)) {
			S::bot()->Msg("Укажите имя заметки");
			return;
		}
		
		$db = S::bot()->db;
		
		$query = $db->EscapedQuery(
				"DELETE FROM ".S::bot()->config['db']['table_prefix'].self::TABLENAME."
				 WHERE `uin` = {user} AND
					   `memo_name` = {memo_name}",
				 array(
					'user' => S::bot()->msgEvent->sender,
					'memo_name' => $val
				 )
			);

		if ($db->RowsAffected() == 0) {
			S::bot()->Msg("Заметка с именем '{$val}' не найдена");
			return;
		}
		
		S::bot()->Msg("Заметка с именем '{$val}' успешно удалена");
	}
	
	private static function lst() {
		$db = S::bot()->db;
		
		$result = $db->Query(
			"SELECT 
				memo_name 
			FROM 
				".S::bot()->config['db']['table_prefix'].self::TABLENAME." 
			WHERE 
				`uin` = '".S::bot()->msgEvent->sender."'
			ORDER BY
				date DESC");
		
		$memo_name = array();
		while ($row = $db->FetchArray($result)) {
			$memo_name[] = $row['memo_name'];
		}
		
		if (empty($memo_name)) {
			S::bot()->Msg("У вас еще нет ни одной заметки");
			return;
		}
		
		S::bot()->Msg("Ваши заметки (".count($memo_name).'/'.self::MAX_COUNT."):\n".implode("\n", $memo_name));
	}
}

function plg_hist($val) {
	$val = (int)$val;
	if (!empty($val) && ($val > 10 || $val < 1)) {
		S::bot()->Msg("Неверный номер команды. Введите hist без параметра для просмотра доступных команд");
		return;
	}
	
	$db = S::bot()->db;
	
	$result = $db->Query(
		"SELECT 
			command 
		FROM 
			".S::bot()->config['db']['table_prefix']."commands_history
		WHERE 
			`uin` = '".S::bot()->msgEvent->sender."' AND status = 1 AND command NOT LIKE 'hist%'
		ORDER BY
			date DESC
		LIMIT
			10");
	
	$command = array();
	while ($row = $db->FetchArray($result)) {
		$command[] = $row['command'];
	}
	
	if (empty($command)) {
		S::bot()->Msg("Вы еще не ввели ни одной команды");
		return;
	}
	
	if (empty($val)) {
		$msg = "Ваши команды:\n";
		foreach($command as $num => $cmd) {
			$num++;
			$msg .= "  {$num}: {$cmd}\n";
		}
		$msg .= "Для повтора сохраненной команды введите hist номер_команды";
		S::bot()->Msg($msg);
		return;
	}
	
	if (empty($command[$val-1])) {
		S::bot()->Msg("Неверный номер команды. Введите hist без параметра для просмотра доступных команд");
		return;
	}
	
	$event = new Event(EVENT_MSG_RECEIVED, array(
		'type' => 'message',
		'sender' => S::bot()->msgEvent->sender, 
		'content' => $command[$val-1],
	));
	
	S::bot()->eventManager->EventRun($event);
}

class plg_timer
{
    public static function analize($val) {
        $val = preg_replace('/[\s]{2,}/', ' ', trim($val));
        
        if (empty($val)) {
            self::_showTimers();
            return;
        }
        
        self::_addTimer($val);
    }
    
    public static function loadSavedAlarms() {
        $db = S::bot()->db;
        
        $db->Query("DELETE FROM ".S::bot()->config['db']['table_prefix']."alarms WHERE time < ".time());

        $result = $db->Query("SELECT * FROM ".S::bot()->config['db']['table_prefix']."alarms");

        while ($row = $db->FetchArray($result)) {
            $timerId = S::bot()->timermanager->timerAdd($row['time'], $row['function'], (array)  json_decode($row['params']), true);
            $db->Query("UPDATE ".S::bot()->config['db']['table_prefix']."alarms SET timer_id = {$timerId} WHERE id = {$row['id']}");
        }
    }
        
    private static function _showTimers() {
        $db = S::bot()->db;
        
        $query = $db->FormatQuery("SELECT * FROM ".S::bot()->config['db']['table_prefix']."alarms WHERE type= 'timer' AND `time` > {time} AND `uin` = {uin} ORDER BY time", array(
                'time' => time(),
                'uin' => S::bot()->msgEvent->sender,
        ));

        $result = $db->Query($query);

        $i = 0;
        $timers = array();
        while ($row = $db->FetchArray($result)) {
                $i++;
                $params = (array)current((array)json_decode($row['params']));
                $timers[] = "  {$i}: [".date('Y-m-d H:i:s', $row['time'])."] {$params['message']}";
        }

        if (empty($timers)) {
                S::bot()->Msg("У вас нет активных таймеров");
                return;
        }

        $msg = "Ваши активные таймеры:\n".implode("\n", $timers);
        S::bot()->Msg(trim($msg));

        return;
    }
    
    private static function _addTimer($val) {
        $db = S::bot()->db;
        
        $data = explode(' ', trim($val));
	if (count($data) < 2 || !preg_match('/^((?:[\d]+[dDдД])*(?:[\d]+[hHчЧ])*(?:[\d]+[mMмМ])*(?:[\d]+[sSсС])*)$/', $data[0], $out)) {
		S::bot()->Msg("Неверные параметры команды");
		return;
	}
	
	$time = @strtotime('+'.str_replace(array('d','д','h','ч','m','м','s','с'), array('day','day','hour','hour','min','min','sec','sec'), mb_strtolower($out[1])));
	if ($time <= time()) {
		S::bot()->Msg("Неверный формат времени");
		return;
	}
	
	unset($data[0]);
	$message = implode(' ', $data);
	
	echo 'New timer: '.date('Y-m-d H:i:s', $time).' ['.$time.'] '.$message."\n";
	
	$params = array(array(
		'sender' => S::bot()->msgEvent->sender,
		'message' => $message,
	));
	
	$timerId = S::bot()->timermanager->timerAdd($time, 'timerMessage', $params, true);
	
	$query = $db->EscapedQuery(
		"INSERT INTO ".S::bot()->config['db']['table_prefix']."alarms
		(`timer_id`, `time`, `type`, `function`, `uin`, `params`)
		VALUES ({timer_id}, {time}, {type}, {function}, {uin}, {params})",
		array(
			'timer_id' => (int)$timerId,
			'time' => $time,
			'type' => 'timer',
			'function' => 'timerMessage',
			'uin' => S::bot()->msgEvent->sender,
			'params' => json_encode($params),
		)
	);
	
	S::bot()->Msg("Таймер успешно установлен на ".date('Y-m-d H:i:s', $time));
    }
}

function plg_alarm($val) {
	$val = preg_replace('/[\s]{2,}/', ' ', trim($val));
	
	if (empty($val)) {
		$db = S::bot()->db;
		
		$query = $db->FormatQuery("SELECT * FROM ".S::bot()->config['db']['table_prefix']."alarms WHERE type= 'alarm' AND `time` > {time} AND `uin` = {uin} ORDER BY time", array(
			'time' => time(),
			'uin' => S::bot()->msgEvent->sender,
		));
		
		$result = $db->Query($query);
	
		$i = 0;
		$timers = array();
		while ($row = $db->FetchArray($result)) {
			$i++;
			$params = (array)current((array)json_decode($row['params']));
			$timers[] = "  {$i}: [".date('Y-m-d H:i:s', $row['time'])."] {$params['message']}";
		}
		
		if (empty($timers)) {
			S::bot()->Msg("У вас нет активных будильников");
			return;
		}
		
		$msg = "Ваши активные будильники:\n".implode("\n", $timers)."\nДля удаления будильника введите alarm del номер_будильника\n";
		S::bot()->Msg(trim($msg));
		
		return;
	}
	
	$data = explode(' ', trim($val));
	
	if (strtolower($data[0]) == 'del') {
		if (empty($data[1]) || (int)$data[1] <= 0) {
			S::bot()->Msg("Не указан номер будильника");
			return;
		}
		
		$val = (int)$data[1];
	
		$db = S::bot()->db;
		
		$query = $db->FormatQuery("SELECT * FROM ".S::bot()->config['db']['table_prefix']."alarms WHERE type= 'alarm' AND `time` > {time} AND `uin` = {uin} ORDER BY time", array(
			'time' => time(),
			'uin' => S::bot()->msgEvent->sender,
		));
		
		$result = $db->Query($query);

		$i = 0;
		$timers = array();
		while ($row = $db->FetchArray($result)) {
			$i++;
			$params = (array)current((array)json_decode($row['params']));
			$timers[] = array(
				'rowId' => $row['id'],
				'timerId' => $row['timer_id'],
			);
		}
		
		if (empty($timers)) {
			S::bot()->Msg("У вас нет активных будильников");
			return;
		}
		
		if (empty($timers[$val-1])) {
			S::bot()->Msg("Указанный будильник не найден");
			return;
		}
		
		$db = S::bot()->db;
			
		$query = $db->EscapedQuery(
				"DELETE FROM ".S::bot()->config['db']['table_prefix']."alarms
				WHERE `uin` = {user} AND
					   `id` = {id}",
				array(
					'user' => S::bot()->msgEvent->sender,
					'id' => $timers[$val-1]['rowId'],
				)
			);

		if ($db->RowsAffected() == 0) {
			S::bot()->Msg("Будильник {$val} не найден");
			return;
		}

		if (S::bot()->timermanager->deleteById($timers[$val-1]['timerId'])) {
			S::bot()->Msg("Будильник {$val} успешно удален");
			return;
		}
		
		S::bot()->Msg("Будильник {$val} [{$timers[$val-1]['timerId']}] невозможно удалить");
		return;
	}
	
	if (!preg_match('/^([\d]{1,2}):([\d]{1,2})(?::([\d]{1,2}))*$/', $data[0], $timeData)) {
		S::bot()->Msg("Неверный формат времени");
		return;
	}
	
	$hours	 = $timeData[1] > 60 ? 60 : $timeData[1];
	$minutes = $timeData[2] > 60 ? 60 : $timeData[2];
	$seconds = $timeData[3] > 60 ? 60 : $timeData[3];
	
	if (empty($seconds)) {
		$seconds = '00';
	}
	
	unset($data[0]);
	
	if (empty($data)) {
		S::bot()->Msg("Вы не ввели текст будильника");
		return;
	}
	
	$days = array(1,2,3,4,5,6,7);
	
	if (preg_match('/^[1-7]+$/', $data[1])) {
		$days = array();
		for($i=0; $i<mb_strlen($data[1]); $i++) {
			$day = $data[1][$i];
			if (!in_array($day, $days)) {
				$days[] = $day;
			}
		}
		sort($days);
		unset($data[1]);
	}
	
	$message = implode(' ', $data);
	
	$date = null;
	$day = 0;
	while(empty($date)) {
		$time = strtotime("+{$day} days");
		$dayNum = date('w', strtotime("+{$day} days"));
		if ($dayNum == 0) {
			$dayNum = 7;
		}
		
		$day++;
		
		$tmpDate = strtotime(date("Y-m-d {$hours}:{$minutes}:{$seconds}", $time));
		if (in_array($dayNum, $days) && $tmpDate > time()) {
			$date = $tmpDate;
		}
	}
	
	echo 'New alarm: '.date('Y-m-d H:i:s', $date).' ['.$date.'] '.$message."\n";
	
	$params = array(array(
		'sender' => S::bot()->msgEvent->sender,
		'days' => $days,
		'time' => $date,
		'message' => $message,
	));
	
	$timerId = S::bot()->timermanager->timerAdd($date, 'alarmMessage', $params, true);
	
	$db = S::bot()->db;
	$query = $db->EscapedQuery(
		"INSERT INTO ".S::bot()->config['db']['table_prefix']."alarms
		(`timer_id`, `time`, `type`, `function`, `uin`, `params`)
		VALUES ({timer_id}, {time}, {type}, {function}, {uin}, {params})",
		array(
			'timer_id' => (int)$timerId,
			'time' => $date,
			'type' => 'alarm',
			'function' => 'alarmMessage',
			'uin' => S::bot()->msgEvent->sender,
			'params' => json_encode($params),
		)
	);
	
	S::bot()->Msg("Будильник успешно установлен на ".date('Y-m-d H:i:s', $date));
}

function timerMessage($params) {
	$params = (array)$params;
	echo "Timer for {$params['sender']}\n";
	S::bot()->Msg('Timer: '.$params['message'], $params['sender']);
}

function alarmMessage($params) {
	$params = (array)$params;
	echo "Alarm for {$params['sender']}\n";
	S::bot()->Msg('Alarm: '.$params['message'], $params['sender']);
	
	$days = (array)$params['days'];
	
	$sender = $params['sender'];
	$message = $params['message'];
	
	$hours = date('H', $params['time']);
	$minutes = date('i', $params['time']);
	$seconds = date('s', $params['time']);
	
	$date = null;
	$day = 0;
	while(empty($date)) {
		$time = strtotime("+{$day} days");
		$dayNum = date('w', strtotime("+{$day} days"));
		if ($dayNum == 0) {
			$dayNum = 7;
		}
		
		$day++;
		
		$tmpDate = strtotime(date("Y-m-d {$hours}:{$minutes}:{$seconds}", $time));
		if (in_array($dayNum, $days) && $tmpDate > time()) {
			$date = $tmpDate;
		}
	}
	
	echo 'New alarm: '.date('Y-m-d H:i:s', $date).' ['.$date.'] '.$message."\n";
	
	$params = array(array(
		'sender' => $sender,
		'days' => $days,
		'time' => $date,
		'message' => $message,
	));
	
	$timerId = S::bot()->timermanager->timerAdd($date, 'alarmMessage', $params, true);
	
	$db = S::bot()->db;
	$query = $db->EscapedQuery(
		"INSERT INTO ".S::bot()->config['db']['table_prefix']."alarms
		(`timer_id`, `time`, `type`, `function`, `uin`, `params`)
		VALUES ({timer_id}, {time}, {type}, {function}, {uin}, {params})",
		array(
			'timer_id' => (int)$timerId,
			'time' => $date,
			'type' => 'alarm',
			'function' => 'alarmMessage',
			'uin' => $sender,
			'params' => json_encode($params),
		)
	);
}