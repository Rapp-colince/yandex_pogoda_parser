<?php 
/**
 * Класс для получения списка регионов и записи из в БД
 * 
 * @author Ilya.Bakanev <i.bakanev@gmail.com>
 * 
 */
class City{
	
	
	/**
	 * Основная бизнес-логика
	 * 
	 * @return void
	 */
	public function write(){
		$regions = $this->getRregionsLink();
		foreach($regions as $regionLink=>$regionName){
			$regionHtml = $this->getPageContent('http://pogoda.yandex.ru/region/'.$regionLink.'/');
			
			/* Исходим из того, что название города и региона может содержать русские буквы, проблеы, дефисы, скобки */
			preg_match_all('/<a class="b-link b-link_type_with-temperature"[^>]*>([ёа-я- ()]+)<\/a>/ui', $regionHtml, $city);
			
			foreach($city[1] as $cityName){
				$regionId = $this->saveRegion($regionName);
				$this->saveCity($cityName, $regionId);
			}
			die();
		}
	}
	
	
	/**
	 * Получаем ассоциативный массив из контента страници
	 * 
	 * @return Array
	 */
	public function getRregionsLink(){
		$regionsPage = $this->getPageContent('http://pogoda.yandex.ru/russia/');
		preg_match_all('/href="\/region\/([0-9a-f]+)\/"[^>]*>([ёа-я- ()]+)<\/a>/ui', $regionsPage, $regions);
		
		$regions = array_combine($regions[1], $regions[2]);
		return $regions;
	}
	
	
	/**
	 * Возвращаем id региона в БД, если регион не существует, то записаваем его
	 * 
	 * @param string $name Имя региона
	 * @return integer
	 */
	private function saveRegion($name){
		// Так как данные валидны, то можно пренебречь фильтрацией
		$regionId = DB::select("SELECT `id` FROM `regions` WHERE `name` = '".$name."';");
		if(!empty($regionId)){
			$regionId = (int)$regionId[0]['id'];
		}else{
			$regionId = DB::query("INSERT INTO `regions` SET `name` = '".$name."'", 'lastInsertId');
		}
		return $regionId;
	}
	
	
	/**
	 * Записываем в БД город, если запись не существует
	 * 
	 * @param string $name
	 * @param integer $regionId
	 */
	private function saveCity($name, $regionId){
		$cityId = DB::select("SELECT `id` FROM `city` WHERE `region_id` = '".$regionId."' AND `name` = '".$name."';");
		if(empty($cityId)){
			DB::query("INSERT INTO `city` SET `name` = '".$name."', `region_id` = '".$regionId."';");
		}
	}
	
	
	/**
	 * Получаем HTML код страницы
	 * 
	 * @param string $url
	 * @return string
	 */
	private function getPageContent($url){
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		
		$html = curl_exec($ch);
		curl_close($ch);
		return $html;
	}
	
}
