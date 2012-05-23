<?php

/**
 * inet-tools - плагин для SteelBot
 * 
 * http://steelbot.net
 * 
 * @author N3x^0r
 * @version 3
 * 
 * 2011-03-13
 *
 */
function plg_ip2host($params) {
    S::bot()->Msg(gethostbyaddr($params));
}

function plg_host2ip($host) {
    S::bot()->Msg(gethostbyname($host));
}

function plg_url_enc($val) {
    S::bot()->Msg("encoded URL = " . urlencode($val));
}

function plg_url_dec($val) {
    S::bot()->Msg("decoded URL = " . urldecode($val));
}

function plg_echo($val) {
    if (!empty($val)) {
        S::bot()->Msg($val);
    }
}

function plg_short_url($val) {
    if (!empty($val)) {
        include_once(dirname(__FILE__) . '/shorturl.class.php');
        $short = new shorturl();
        $shorturl = $short->shorten($val);
        S::bot()->Msg($shorturl);
    }
}

function plg_time() {
    $timeserver = "time-C.timefreq.bldrdoc.gov";
    $timercvd = query_time_server($timeserver, 13);
    if (!$timercvd[1]) { # if no error from query_time_server
        $timevalue = preg_match('/([\d]+-[\d]+-[\d]+ [\d]+:[\d]+:[\d]+)/',$timercvd[0], $data);
		$timevalue = '20'.$data[1]." UTC (NIST)\n";
		$timevalue .= date('Y-m-d H:i:s').' SERVER TIME';
        S::bot()->Msg($timevalue);
    } else {
        S::bot()->Msg("The time server $timeserver could not be reached at this time. " .
                "{$timercvd[1]} {$timercvd[2]}");
    }
}

function query_time_server($timeserver, $socket) {
    $fp = fsockopen($timeserver, $socket, $err, $errstr, 5);
    # parameters: server, socket, error code, error text, timeout
    if ($fp) {
        fputs($fp, "\n");
        $timevalue = fread($fp, 49);
        fclose($fp); # close the connection
    } else {
        $timevalue = " ";
    }

    $ret = array();
    $ret[] = $timevalue;
    $ret[] = $err;     # error code
    $ret[] = $errstr;  # error text
    return($ret);
}

function plg_proxy() {
    $url = 'http://fineproxy.org/';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $data = curl_exec($ch);
    curl_close($ch);

    if (empty($data)) {
        return false;
    }

    preg_match_all('/([\w.\-]+:[\d]+)/', $data, $out);
    if (empty($out[1])) {
        return false;
    }

    $step = 0;
    do {
        $step++;
        $proxy = $out[1][rand(0, count($out[1]))];

        $url = 'http://ya.ru/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_PROXY, $proxy);

        $data = curl_exec($ch);
        curl_close($ch);

        if (!empty($data) && strpos($data, 'http://images.yandex.ru/yandsearch?text=') !== false) {
            break;
        }
        $proxy = '';
    } while ($step < 15);

    S::bot()->Msg($proxy);
}

function plg_whois($domain) {
    $domain = trim($domain);

    if (empty($domain)) {
        return false;
    }
    include_once(dirname(__FILE__) . '/whois/whois.main.php');
    include_once(dirname(__FILE__) . '/whois/whois.utils.php');

    $whois = new Whois();

    $whois->deep_whois = false;

    // To use special whois servers (see README)
    //$whois->UseServer('uk','whois.nic.uk:1043?{hname} {ip} {query}');
    //$whois->UseServer('au','whois-check.ausregistry.net.au');
    // Comment the following line to disable support for non ICANN tld's
    //$whois->non_icann = true;

    $result = $whois->Lookup($domain);
    print_r($result);

    $out = '';
    if (!empty($result['regrinfo'])) {
        foreach ($result['regrinfo'] as $param => $val) {
            $out .= "{$param}: ";
            if (is_array($val)) {
                $out .= "\r\n";
                foreach ($val as $param2 => $val2) {
                    $out .= "   {$param2}: ";
                    if (is_array($val2)) {
                        $out .= "\r\n";
                        foreach ($val2 as $param3 => $val3) {
                            $out .= "     {$param3}: {$val3}\r\n";
                        }
                    } else {
                        $out .= "{$val2}\r\n";
                    }
                }
            } else {
                $out .= "{$val}\r\n";
            }
        }
    }

    if (!empty($result['regyinfo'])) {
        foreach ($result['regyinfo'] as $param => $val) {
            $out .= "{$param}: ";
            if (is_array($val)) {
                $out .= "\r\n";
                foreach ($val as $param2 => $val2) {
                    $out .= "   {$param2}: ";
                    if (is_array($val2)) {
                        $out .= "\r\n";
                        foreach ($val2 as $param3 => $val3) {
                            $out .= "     {$param3}: {$val3}\r\n";
                        }
                    } else {
                        $out .= "{$val2}\r\n";
                    }
                }
            } else {
                $out .= "{$val}\r\n";
            }
        }
    }

    S::bot()->Msg($out);
}

function plg_lookup($address) {
    $address = trim($address);
    if (empty($address)) {
        return false;
    }

    $url = 'http://www.whois-service.ru/lookup/';
    echo $url . "\r\n";

    $ch = curl_init();
    $postData = "domain={$address}&real=true2.1simple";
    echo $postData . "\r\n";

    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

    $data = iconv('koi8-r', 'utf-8', curl_exec($ch));
    curl_close($ch);

    if (empty($data)) {
        return false;
    }

    $pos = strpos($data, 'Reverse Lookup');
    if (empty($pos)) {
        S::bot()->Msg('Wrong address');
        return;
    }

    $res = substr($data, strpos($data, 'Reverse Lookup'));
    $res = substr($res, 0, strpos($res, '</div'));
    $res = preg_replace('/\<[^\>]+\>/', '', $res);

    S::bot()->Msg($res);
}

function plg_ping($val) {
	$val = trim($val);
	if (empty($val)) {
		return;
	}
	
	if (getenv('COMSPEC')) { //it's windows server
		exec("ping {$val}", $res);
	} else {
		exec("ping -c4 {$val}", $res);
	}
	
	$msg = "Пинг хоста {$val}:\n";
	$msg .= implode("\n", $res);
	
	S::bot()->Msg($msg);
}

function plg_port($val) {
	preg_match('/([^:\s]+)[:\s](\d+)/', $val, $data);
	if (empty($data[1]) || empty($data[2])) {
		S::bot()->Msg('Данные должны быть введены как host port');
		return;
	}
	
	$host = preg_replace('/[a-zA-Z]+:\/\//', '', $data[1]);
	$port = $data[2];
	
	$socket = fsockopen('tcp://'.$host, $port, $errno, $errstr, 2);
	if (!$socket) {
		$msg = "[TCP] {$host}:{$port} - закрыт\n";
	} else {
		$msg = "[TCP] {$host}:{$port} - открыт\n";
	}
	if (is_resource($socket)) {
		fclose($socket);
	}
	
	$socket = fsockopen('udp://'.$host, $port, $errno, $errstr, 2);
	if (!$socket) {
		$msg .= "[UDP] {$host}:{$port} - закрыт";
	} else {
		$msg .= "[UDP] {$host}:{$port} - открыт";
	}
	if (is_resource($socket)) {
		fclose($socket);
	}
	
	S::bot()->Msg($msg);
}

function plg_geo($ip) {
	include_once(dirname(__FILE__).'/geo.php');
    $o['ip'] = $ip;
    $o['charset'] = 'utf-8';
    
    $geo = new Geo($o); // запускаем класс
    
    // этот метод позволяет получить все данные по ip в виде массива.
    // массив имеет ключи 'inetnum', 'country', 'city', 'region', 'district', 'lat', 'lng'
    $data = $geo->get_value(); 
	
	$msg = '';
	foreach($data as $key => $val) {
		$msg .= "{$key}: {$val}\n";
	}
	
	S::bot()->Msg(trim($msg));
}

function plg_sms($val) {
	$url = 'http://websms.ru/xml_in5.asp';
	$username = 'vanchester';
	$password = '174562784511';
	
	$data = explode(' ', $val);
	
	if (!empty($data[0])) {
		if (strtolower($data[0]) == 'status') {
			if (empty($data[1]) || (int)$data[1] <= 0) {
				S::bot()->Msg('Неверный ID смс');
				return;
			}
			
			$smsId = (int)$data[1];
			
			$body = "<request id='{$smsId}' login='{$username}' password='{$password}' extended='1'>status</request>";
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
			curl_setopt($ch, CURLOPT_URL, $url);
			$result = trim(curl_exec($ch));
			curl_close($ch);
			
			if (empty($result)) {
				S::bot()->Msg('Произошла ошибка. Повторите попытку позже');
				return;
			}
			
			$xml = new SimpleXMLElement($result);
			$message = "Статус: {$xml->state}";
			S::bot()->Msg($message);
			return;
		}
	
		$data[0] = str_replace(array(' ', '-'), '', $data[0]);
	}
	if (empty($data[0]) || !preg_match('/^\+[\d]{11}$/', $data[0])) {
		S::bot()->Msg('Неверный формат номера. Проверьте, чтобы номер был в виде +7-111-222-3333');
		return;
	}
	
	$recipientNum = $data[0];
	
	unset($data[0]);
	
	$sendOn = null;

	if (!empty($data[1])) {
		$time = @strtotime($data[1]);
		if ($time > 0) {
			unset($data[1]);
			$sendOn = "start='".date('r', $time)."'";
		}
	}
	
	$message = implode(' ', $data);
	if (empty($message)) {
		S::bot()->Msg('Вы не ввели текст SMS');
		return;
	}
	
	$db = S::bot()->db;
	
	$query = "SELECT MAX(id) FROM sms";
		
	$messageId = (int)$db->QueryValue($query) + 1;
	
	$body = <<<BODY
<message>
	<service id='single' login='{$username}' password='{$password}' {$sendOn} source='vanchester' test='0' uniq_key='{$messageId}'/>
	<to>{$recipientNum}</to>
	<body>{$message}</body>
</message>
BODY;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
	curl_setopt($ch, CURLOPT_URL, $url);
	$result = trim(curl_exec($ch));
	curl_close($ch);
	
	if (empty($result)) {
		S::bot()->Msg('Произошла ошибка. Повторите попытку позже');
		return;
	}
	
	$xml = new SimpleXMLElement($result);
	$message = "Статус: {$xml->state}";
	
	if ((int)$xml['id'] > 0) {
		$message .= "\nДля получения информации о доставке отправьте команду sms status {$xml['id']}";
	}
	
	S::bot()->Msg($message);
}

S::bot()->RegisterCmd("ip2host", "plg_ip2host", 1, "{alias} <ip> - узнать имя хоста по ip-адресу");
S::bot()->RegisterCmd("host2ip", "plg_host2ip", 1, "{alias} <host>- узнать ip-адрес по имени хоста");
S::bot()->RegisterCmd("urle", "plg_url_enc", 1, "{alias} <url> - закодировать URL");
S::bot()->RegisterCmd("urld", "plg_url_dec", 1, "{alias} <encoded url> - раскодировать URL");
//S::bot()->RegisterCmd("echo", "plg_echo", 1, "{alias} <string> - отправить присланную строку");
S::bot()->RegisterCmd("shorturl", "plg_short_url", 1, "{alias} <string> - сократить URL (через сервис goo.gl)");
S::bot()->RegisterCmd("time", "plg_time", 1, "{alias} - получить точное время");
S::bot()->RegisterCmd("proxy", "plg_proxy", 1, "{alias} - показать бесплатный рабочий прокси-сервер");
S::bot()->RegisterCmd("geo", "plg_geo", 1, "{alias} <ip> - показать город по IP");
S::bot()->RegisterCmd("whois", "plg_whois", 1, "{alias} <domain> - показать информацию о владельце домена");
S::bot()->RegisterCmd("lookup", "plg_lookup", 1, "{alias} <address> - показать информацию по адресу");
S::bot()->RegisterCmd("ping", "plg_ping", 1, "{alias} <host> - пинг указанного хоста");
S::bot()->RegisterCmd("port", "plg_port", 1, "{alias} <host> <port> - проверка порта");
S::bot()->RegisterCmd("sms", "plg_sms", 100, "{alias} <num> [date] <text> - отправка SMS. date - время в формате YYYY-MM-DD HH:MM:SS, когда нужно доставить сообщение.");

