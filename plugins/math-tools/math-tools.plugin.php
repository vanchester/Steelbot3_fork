<?php

/**
 * math-tools - плагин для SteelBot
*/

function toRoman($num) {

	$onesArray = array("I","II","III","IV","V","VI","VII","VIII","IX");
	$tensArray = array("X","XX","XXX","XL","L","LX","LXX","LXXX","XC");
	$hundredsArray = array("C","CC","CCC","CD","D","DC","DCC","DCCC","CM");

	$ones = $num % 10;
	$num = ($num - $ones) / 10;
	$tens = $num % 10;
	$num = ($num - $tens) / 10;
	$hundreds = $num % 10;
	$num = ($num - $hundreds) / 10;

	$Roman = "";

	for ($i=0; $i < $num; $i++){
		$Roman .= "M";
	}

	if ($hundreds) {
		$Roman .= $hundredsArray[$hundreds-1];
	}

	if ($tens) {
		$Roman .= $tensArray[$tens-1];
	}

	if ($ones) {
		$Roman .= $onesArray[$ones-1];
	}

	return $Roman;
}
 
function toArabic ($num) {
	$Arabic = 0;
	$last_digit = 1000;
 
	for ($i=0; $i<strlen($num); $i++) {
		if (strtoupper(substr($num, $i, 1)) == "I") {$digit=1;}
		if (strtoupper(substr($num, $i, 1)) == "V") {$digit=5;}
		if (strtoupper(substr($num, $i, 1)) == "X") {$digit=10;}
		if (strtoupper(substr($num, $i, 1)) == "L") {$digit=50;}
		if (strtoupper(substr($num, $i, 1)) == "C") {$digit=100;}
		if (strtoupper(substr($num, $i, 1)) == "D") {$digit=500;}
		if (strtoupper(substr($num, $i, 1)) == "M") {$digit=1000;}
		
		if ($last_digit < $digit) {
			$Arabic -= 2 * $last_digit;
		}
	 
		$last_digit = $digit;
		$Arabic += $last_digit;
	}
	return $Arabic;
}
 
function plg_roman($val) {
	$val = trim($val);
	
	if (empty($val)) {
		S::bot()->Msg('Введите число');
		return;
	}
	
	if (preg_match('/^[\d]+$/', $val)) {
		S::bot()->Msg(toRoman($val));
	} elseif (preg_match('/^[MDCLXVImdclxvi]+$/', $val)) {
		S::bot()->Msg(toArabic($val));
	} else {
		S::bot()->Msg('Вы ввели некорректные символы');
	}
}

function plg_calculator($val) {
	$val = mb_strtolower(trim($val));
	
	$operations = array(
		"+" => "сложение",
		"-" => "вычитание",
		"*" => "умножение",
		"/" => "деление",
		"%" => "остаток от деления",
		"abs" => "Модуль числа",
		"acos" => "Арккосинус",
		"acosh" => "Гиперболический арккосинус",
		"asin" => "Арксинус",
		"asinh" => "Гиперболический арксинус",
		"atan2" => "Арктангенс двух переменных",
		"atan" => "Арктангенс",
		"atanh" => "Гиперболический арктангенс",
		"ceil" => "Округляет дробь в большую сторону",
		"cos" => "Косинус",
		"cosh" => "Гиперболический косинус",
		"deg2rad" => "Преобразует значение из градусов в радианы",
		"exp" => "Вычисляет число e в степени",
		"expm1" => "Возвращает exp(number) - 1, рассчитанное таким образом, что результат точен, даже если number близок к нулю.",
		"floor" => "Округляет дробь в меньшую сторону",
		"fmod" => "Возвращает дробный остаток от деления по модулю",
		"hypot" => "Рассчитывает длину гипотенузы прямоугольного треугольника",
		"log10" => "Десятичный логарифм",
		"log1p" => "Возвращает log(1 + number), рассчитанный таким, что результат точен, даже если значение number близко к нулю",
		"log" => "Натуральный логарифм",
		"pi" => "Возвращает число Пи",
		"pow" => "Возведение в степень",
		"rad2deg" => "Преобразует значение из радианов в градусы",
		"rand" => "Генерирует случайное число",
		"round" => "Округляет число типа float",
		"sin" => "Синус",
		"sinh" => "Гиперболический синус",
		"sqrt" => "Квадратный корень",
		"tan" => "Тангенс",
		"tanh" => "Гиперболический тангенс",
	);
	
	if (empty($val) || $val == 'help') {
		$msg = "Список поддерживаемых операций:\n";
		foreach($operations as $func => $desc) {
			$msg .= $func.' - '.$desc."\n";
		}
		$msg = trim($msg);
		S::bot()->Msg($msg);
		return;
	}
	
	preg_match_all('/[\w]+/', $val, $out);
	if (empty($out[0]) || preg_match('/[^\w\s\+\-\/%\(\)\*]/', $val)) {
		S::bot()->Msg("Проверьте правильность ввода выражения. Список поддерживаемых операций вы можете узнать, отправив сообщение calc help");
		return;
	}
	
	foreach($out[0] as $func) {
		if (!isset($operations[$func])) {
			if ((string)(int)($func) == (string)$func) {
				continue;
			}
			S::bot()->Msg("Проверьте правильность ввода выражения. Список поддерживаемых операций вы можете узнать, отправив сообщение calc help");
			return;
		}
	}
	
	set_error_handler("myErrorHandler");
	try {
		eval('$result = '.$val.';');
		if (empty($result) && $result != 0) {
			S::bot()->Msg("Введенное выражение сосчитать не удалось. Возможно, вы ошиблись при вводе");
			return;
		}
	} catch (Exception $e) {
		S::bot()->Msg("Введенное выражение сосчитать не удалось. Возможно, вы ошиблись при вводе");
		echo $e->getMessage();
	}
	S::bot()->Msg($result);
	restore_error_handler();
}

function myErrorHandler($errno, $errstr, $errfile, $errline) {
	if (!(error_reporting() & $errno)) {
		// Этот код ошибки не включен в error_reporting
		return;
	}

	switch ($errno) {
	case E_USER_ERROR:
		S::bot()->Msg("ERROR [$errno] $errstr\n");
		break;
	case E_USER_WARNING:
		S::bot()->Msg("WARNING [$errno] $errstr\n");
		break;
	case E_USER_NOTICE:
		S::bot()->Msg("NOTICE [$errno] $errstr\n");
		break;
	default:
		S::bot()->Msg("Неизвестная ошибка: [$errno] $errstr\n");
		break;
	}

	/* Не запускаем внутренний обработчик ошибок PHP */
	return true;
}

S::bot()->RegisterCmd("calc", "plg_calculator", 1,"{alias} <expr> - калькурятор. Отправьте calc help для вывода поддерживаемых операций");
S::bot()->RegisterCmd("roman", "plg_roman", 1,"{alias} <string> - конвертер римских чисел");
