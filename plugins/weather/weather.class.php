<?php
	class weather {
	
		public $url = "http://rp5.ru";
	
		public function searchCity($name) 
		{
			$url = $this->url.'/vsearch.php?lang=ru&txt='.$name;

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
			
			$html = substr($data, strpos($data, 'searchResults'));
			preg_match_all('/\<span[\s]+class[\s\t]*=[\s\t]*[\'"]innerTextResult[\'"]\>[\s\t]*\<a[\s\t]+href[\s\t]*=[\s\t]*[\'"]([^\'"]+)[\'"]\>([^\<]+)/', $data, $out);
			
			return $out;
		}
		
		public function getShortWeather($cityId)
		{
			$url = $this->url.$cityId;

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
			
			$html = str_get_html($data);
			$res = $html->getElementById('forecastShort')->innertext;
			$res = preg_replace('/\<[^\>]+style[\s\t]*=[\s\t]*[\'"]display:[\s\t]*none[;]*[\'"][^\>]*\>[^\<]*\<[^\>]+\>/', '', $res);
			$res = html_entity_decode(preg_replace('/\<[^\>]+\>/', '', $res));
			$res = preg_replace('/[\s\t]{2,}/', ' ', $res);
			$res = trim(str_replace('&mdash;', ' - ', $res));
			return $res.' '.$url;
		}
		
		public function getFullWeather($cityId)
		{
			$url = $this->url.'/'.$cityId;

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
			
			$html = str_get_html($data);
			$res = $html->getElementById('forecastTable');
			
			$htmlTable = str_get_html($res);
			$datesTr = $htmlTable->find('tr', 0);
			
			$weather = array();
			
			$htmlDatesTr = str_get_html($datesTr);
			for($i=0; $i<=10; $i+=2) {
				$date = $htmlDatesTr->find('span', $i+1)->innertext;
				$dayOfWeek = $htmlDatesTr->find('span', $i)->innertext;
				$weather[]['date'] = $date;
				$weather[]['dateOfWeek'] = $dayOfWeek;
			}
			
			$datesTr = $htmlTable->find('tr', 1);
			$htmlTimeTr = str_get_html($dateTr);
			
			
			return $weather;
		}
	}
?>