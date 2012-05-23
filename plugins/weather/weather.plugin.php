<?php

/**
 * text-tools - плагин для SteelBot
*/

function plg_weather($val) {
	if (empty($val)) {
		return;
	}

	require_once(dirname(__FILE__)."/weather.class.php");

	$rates = new weather();

	$data = $rates->searchCity($val);
	if (empty($data)) {
		S::bot()->Msg("{$val} not found");
		return;
	}
	
	foreach($data[1] as $key => $link) {
		if (strtolower($data[2][$key]) != strtolower($val)) {
			continue;
		}
		
		$dataWeather = $rates->getShortWeather($link);
		
		S::bot()->Msg($dataWeather);
	}

}

S::bot()->RegisterCmd("weather", "plg_weather", 1, "{alias} <town> - получить прогноз погоды по указанному городу");
