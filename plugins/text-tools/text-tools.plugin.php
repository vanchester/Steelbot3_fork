<?php

/**
 * text-tools - плагин для SteelBot
*/

function plg_translate($val) {
	$val = trim($val);
	if (empty($val)) {
		S::bot()->Msg('Вы не ввели текст для перевода');
		return;
	}
	if (preg_match('/^[^а-яА-Я]+$/', $val)) {
		translate($val, 'ru', 'en');
	} elseif (preg_match('/^[^a-zA-Z]+$/', $val)) {
		translate($val, 'en', 'ru');
	} else {
		$rusLine = preg_replace('/[a-zA-Z]/', '', $val);
		$engLine = preg_replace('/[а-яА-Я]/', '', $val);
		if (mb_strlen($rusLine) > $engLine) {
			translate($val, 'en', 'ru');
		} else {
			translate($val, 'ru', 'en');
		}
	}
}

function translate($val, $targetLang, $sourceLang = 'auto') {
	if (empty($val)) {
		return;
	}
	
	require_once(dirname(__FILE__)."/translate.class.php");

	// Create GT Object
    $gt = new GoogleTranslater();

	// Translate text
	// Usage: GoogleTranslater :: translateText(string $text, string $fromLanguage, string $tolanguage, bool $translit = false)
	$translatedText = $gt->translateText($val, $sourceLang, $targetLang);
    if ($translatedText !== false) {
        S::bot()->Msg($translatedText);
    } else {
        //If some errors present
        S::bot()->Msg($gt->getErrors()); 
    }
}

function plg_stamp($val) {
	date_default_timezone_set('UTC');
	if (empty($val)) {
		S::bot()->Msg(time());
		return;
	}
	
	if ((string)(int)$val == (string)$val) {
		S::bot()->Msg(date('Y-m-d H:i:s', (int)$val));
	} else {
		S::bot()->Msg(strtotime($val));
	}
}

function plg_base64encode($val) {
	S::bot()->Msg(base64_encode($val));
}

function plg_base64decode($val) {
	S::bot()->Msg(base64_decode($val));
}

function plg_md5($val) {
	S::bot()->Msg(md5($val));
}

function plg_sha1($val) {
	S::bot()->Msg(sha1($val));
}

function plg_crc32($val) {
	S::bot()->Msg(crc32($val));
}

function plg_translit($str) {
    $tr = array(
        "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
        "Д"=>"D","Е"=>"E","Ё"=>"YO","Ж"=>"J","З"=>"Z","И"=>"I",
        "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
        "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
        "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
        "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
        "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
        "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ё"=>"yo","ж"=>"j",
        "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
        "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
        "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
        "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
        "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
    );
    S::bot()->Msg(strtr($str,$tr));
}

function plg_morze($str) {
	$str = mb_strtolower($str);
	
    $tr = array(
        'а' => '.- ',
		'б' => '-... ',
		'в' => '.-- ',
		'г' => '--. ',
		'д' => '-.. ',
		'е' => '. ',
		'ж' => '...- ',
		'з' => '--.. ',
		'и' => '.. ',
		'й' => '.--- ',
		'к' => '-.- ',
		'л' => '.-.. ',
		'м' => '-- ',
		'н' => '-. ',
		'о' => '--- ',
		'п' => '.--. ',
		'р' => '.-. ',
		'с' => '... ',
		'т' => '- ',
		'у' => '..- ',
		'ф' => '..-. ',
		'х' => '.... ',
		'ц' => '-.-. ',
		'ч' => '---. ',
		'ш' => '---- ',
		'щ' => '--.- ',
		'ы' => '-.-- ',
		'ь' => '-..- ',
		'э' => '..-.. ',
		'ю' => '..-- ',
		'я' => '.-.- ',
		'a' => '.- ',
		'b' => '-... ',
		'c' => '-.-. ',
		'd' => '-.. ',
		'e' => '. ',
		'f' => '..-. ',
		'g' => '--. ',
		'h' => '.... ',
		'i' => '.. ',
		'j' => '.--- ',
		'k' => '-.- ',
		'l' => '.-.. ',
		'm' => '-- ',
		'n' => '-. ',
		'o' => '--- ',
		'p' => '.--. ',
		'q' => '--.- ',
		'r' => '.-. ',
		's' => '... ',
		't' => '- ',
		'u' => '..- ',
		'v' => '...- ',
		'w' => '.-- ',
		'x' => '-..- ',
		'y' => '-.-- ',
		'z' => '--.. ',
		'0' => '----- ',
		'1' => '.---- ',
		'2' => '..--- ',
		'3' => '...-- ',
		'4' => '....- ',
		'5' => '..... ',
		'6' => '-.... ',
		'7' => '--... ',
		'8' => '---.. ',
		'9' => '----. ',
		' '	=> '  ',
    );
    S::bot()->Msg(strtr($str,$tr));
}

S::bot()->RegisterCmd("md5",    "plg_md5",        1,"{alias} <string> - вычислить md5 хеш строки");
S::bot()->RegisterCmd("sha1",    "plg_sha1",        1,"{alias} <string> - вычислить sha1 хеш строки");
S::bot()->RegisterCmd("crc32",    "plg_crc32",        1,"{alias} <string> - вычислить crc32 хеш строки");
S::bot()->RegisterCmd("base64e", "plg_base64encode", 1, "{alias} <text> - закодировать текст алгоритмом base64");
S::bot()->RegisterCmd("base64d", "plg_base64decode", 1, "{alias} <text> - раскодировать текст, зашифрованный алгоритмом base64");
S::bot()->RegisterCmd("translate", "plg_translate", 1, "{alias} <text> - перевести текст (русский <=> английский)");
S::bot()->RegisterCmd("stamp", "plg_stamp", 1, "{alias} <stamp> - конвертирование Unix-timestamp <=> дата и время");
S::bot()->RegisterCmd("translit", "plg_translit", 1, "{alias} <text> - преобразовать текст в транслит");
S::bot()->RegisterCmd("morze", "plg_morze", 1, "{alias} <text> - преобразовать текст в азбуку Морзе");
