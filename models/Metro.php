<?php
	class Metro extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "geo_station";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		/*
		 * Функция определяет соединение БД
		 */
		public static function dbConnection()
		{
			// Открываем соединение с основной БД		
			$db_repetitors = new mysqli(DB_HOST, DB_LOGIN, DB_PASSWORD, DB_PREFIX."repetitors");
			
			// Устанавливаем кодировку
			$db_repetitors->set_charset("utf8");
			
			return $db_repetitors;	
		}
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		// Получить пользователей из кеша
		public static function getCached()
		{
			if (LOCAL_DEVELOPMENT) {
				$Metros = self::findAll();
				
				foreach ($Metros as $Metro) {
					$return[$Metro->id] = $Metro->dbData();
				}
				
				return $return;				
			} else {
				$Metros = memcached()->get("Metros");
				
				if (!$Metros) {
					$Metros = self::findAll();
				
					foreach ($Metros as $Metro) {
						$return[$Metro->id] = $Metro->dbData();
					}
					
					$Metros = $return;
					memcached()->set("Metros", $Metros, 365 * 24 * 3600); // кеш на год
				}
				
				return $Metros;
			}
		}
		
		public static function getDistance($lat1, $lon1, $lat2, $lon2, $unit = "K") {
		  $theta = $lon1 - $lon2;
		  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		  $dist = acos($dist);
		  $dist = rad2deg($dist);
		  $miles = $dist * 60 * 1.1515;
		  $unit = strtoupper($unit);
		
		  if ($unit == "K") {
		    return ($miles * 1.609344);
		  } else if ($unit == "N") {
		      return ($miles * 0.8684);
		    } else {
		        return $miles;
		      }
		}


		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

	}