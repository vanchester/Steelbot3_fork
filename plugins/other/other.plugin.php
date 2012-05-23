<?php

/**
 * text-tools - плагин для SteelBot
*/

function plg_rates($val) {
	require_once(dirname(__FILE__)."/rates.class.php");

	$rates = new rates();

	S::bot()->Msg($rates->getCourse($val));
}

function plg_defCodes($val) {
	$val = $val;
	preg_match('/^[+]*[\d]*[\s-]*([\d]{3})[\s-]*([\d]{3}[\s-]*[\d]{4})$/', $val, $data);
	
	if (empty($data[1]) || empty($data[2])) {
		S::bot()->Msg('Неверно введен номер телефона');
		return;
	}
	
	$ch = curl_init();

	$data = "act=search&abcdef={$data[1]}&number={$data[2]}";
	curl_setopt($ch, CURLOPT_URL, "http://www.rossvyaz.ru/activity/num_resurs/registerNum/");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

	$html = curl_exec($ch);
	curl_close($ch);

	$html = str_get_html($html);
	$data = $html->find('.TblList',0)->children(1);
	
	$result['def'] = iconv('cp1251', 'UTF-8', $data->children(0)->innertext);
	$result['range'] = iconv('cp1251', 'UTF-8', $data->children(1)->innertext);
	$result['operator'] = iconv('cp1251', 'UTF-8', $data->children(3)->innertext);
	$result['region'] = iconv('cp1251', 'UTF-8', $data->children(4)->innertext);
	
	$msg = '';
	foreach($result as $key => $val) {
		$msg .= "{$key}: {$val}\n";
	}
	
	S::bot()->Msg(trim($msg));
}

S::bot()->RegisterCmd("rates", "plg_rates", 1, "{alias} [volute] - получить курс указанной валюты (без параметра - получить список валют)");
S::bot()->RegisterCmd("def", "plg_defCodes", 1, "{alias} <number> - определение региона и оператора по номеру телефона");
