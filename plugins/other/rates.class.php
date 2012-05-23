<?php
	class rates {
	
		public $url = "http://www.cbr.ru/scripts/XML_daily.asp";
	
		public function getCourse($valute) 
		{
			include_once(STEELBOT_DIR.'/extensions/simple_html_dom.php');
			
			$valute = strtolower(trim($valute));
			
			$url = $this->url;

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
			
			$content = new SimpleXMLElement($data);
			
			$list = array();
			foreach($content->Valute as $id) {
				if (!empty($valute)) {
					if (strtolower($id->CharCode) == $valute) {
						return str_replace(',', '.', $id->Value);
					}
				} else {
					$list[] = $id->CharCode;
				}
			}
			
			if (!empty($list)) {
				return implode(',', $list);
			}
			
			return 'Unknown volute';
		}
	}
?>