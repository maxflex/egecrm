<?php
	class Metro extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "stations";
		
		const LINE_COLORS = [
			1 => '#EF1E25',	// Красный
			2 => '#029A55', // Зеленый
			3 => '#0252A2', // Синий
			4 => '#019EE0', // Голубой
			5 => '#745C2F', // Коричневый
			6 => '#C07911', // Оранжевый
			7 => '#B61D8E', // Фиолетовый
			8 => '#FFD803',	// Желтый
			9 => '#ACADAF', // Серый
			10 => '#B1D332',// Салатовый
			11 => '#5091BB',// Бледно-синий (Варшавская)
			12 => '#85D4F3',// Светло-голубая (Бульвар Адмирала Ушакова)
		];
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
/*
		public function __construct($array)
		{
			parent::__construct($array);
			$this->color = static::LINE_COLORS[$this->line_id];
		}
*/
		
		
		// Получить пользователей из кеша
		public static function getCached()
		{
			$Metros = static::findAll();

			foreach ($Metros as $Metro) {
				$return[$Metro->id] = $Metro->dbData();
// 				$return[$Metro->id]['color'] = static::LINE_COLORS[$Metro->line_id];
			}
			
			return $return;	
			
/*
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
*/
		}
		
		private static function _getDistance($lat1, $lon1, $lat2, $lon2, $unit = "K") {
		  $theta = $lon1 - $lon2;
		  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		  $dist = acos($dist);
		  $dist = rad2deg($dist);
		  $miles = $dist * 60 * 1.1515;
		  $unit = strtoupper($unit);
		
		  if ($unit == "K") {
		    return round($miles * 1.609344 * 1000); // сразу в метры
		  } else if ($unit == "N") {
		      return ($miles * 0.8684);
		    } else {
		        return $miles;
		      }
		}
		
		
		/**
		 * Получить 10 ближайших станций метро.
		 * 
		 */
		private static function _getClosest($lat, $lng)
		{
			$Metors = static::getCached();
			foreach ($Metors as $Metro) {
				$distance = self::_getDistance($Metro['lat'], $Metro['lng'], $lat, $lng);
				$return[] = $Metro + [
					'meters' 	=> $distance,
					'station'	=> [
						'id'	=> $Metro['id'],
						'title' => $Metro['title'],
						'color'	=> static::LINE_COLORS[$Metro['line_id']],
					],
				];
			}
			
			$d = [];
			foreach ($return as $id => $row) {
				$d[$id] = $row['meters'];
			}
			array_multisort($d, SORT_ASC, $return);
			
			//return $return;
			return array_slice($return, 0, 3);
		}
		
		// возвращает сколько ехать от метро до метро в минутах
		private static function _distanceBetweenMetros($id_metro_1, $id_metro_2)
		{
			$ch = curl_init();
			
			curl_setopt_array($ch, [
				CURLOPT_URL 		=> "http://crm.a-perspektiva.ru:8080/graph/ajax/distance",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS	=> [
					"p1" 		=> $id_metro_1,
					"p2" 		=> $id_metro_2,
					"calcMode" 	=> 1,
				],
			]);
			
			return curl_exec($ch);
		}
		
		// $s - расстояние
		private static function _metersToMinutes($s)
		{
			// если расстояние > 1000, то на транспорте, иначе пешком
			// k - коэффициент, который при s<=1000 равен 1, а при s>1000 равен корень степени 1,9 от (s/1000)
			$k = $distance <= 1000 ? 1 : ($s / 1000) * (1 / 1.9);
			
			$t = $s / 100 * $k;
			
			return round($t, 1);
		}
		
		// Новый алгоритм нахождения ближайших станций метро #703
		public static function calculate($lat, $lng)
		{
			$Metros =  self::_getClosest($lat, $lng);
			
			// первую самую ближайшую станцию включать всегда
			$ClosestMetro = $Metros[0];
			$ClosestMetro['minutes'] = self::_metersToMinutes($ClosestMetro['meters']);
			$return[] = $ClosestMetro;
			
			// смотрим другие 2 ближайшие станции
			foreach (range(1, 2) as $n) {
				// если до первой другой ближайшей станции расстояние больше,
				// чем 2x (где х – расстояние до первой ближайшей станции), то завершить
				if ($Metros[$n]['meters'] > ($ClosestMetro['meters'] * 2)) {
					break;	
				} else {
					$Metros[$n]['minutes'] = self::_metersToMinutes($Metros[$n]['meters']);
					$return[] = $Metros[$n];
				}
			}
			
			return $return;
		}
		
		/**
		 * Алгоритм подсчета ближайших станций метро.
		 * 
		 */
		public static function calculateOld($lat, $lng)
		{
			$Metros =  self::_getClosest($lat, $lng);
			// первую самую ближайшую станцию включать всегда
			$ClosestMetro = $Metros[0];
			$ClosestMetro['minutes'] = self::_metersToMinutes($ClosestMetro['distance']);
			$return[] = $ClosestMetro;
			
			$return['comments'][] = "Ближайшая станция – {$ClosestMetro['title']} ({$ClosestMetro['distance']} м., {$ClosestMetro['minutes']} мин.)";
			
			// если самое ближайшее метро меньше 600 метров, то возвращаем только его
			if ($ClosestMetro['distance'] <= 600) {
				$return['comments'][] = "До ближайшей станции <= 600 м. ВЫХОД";
				return $return;	
			} else {
				// максимум 2 раза применяем алгоритм X
				for ($i = 1; $i <= 2; $i ++) {
					$Metro = $Metros[$i];
					
					# высчитываем сколько минут ехать до станции через ближайшую
					$minutes_through_closest = self::_distanceBetweenMetros($ClosestMetro['id'], $Metro['id']);					
					
					# высчитываем сколько минут идти пешком до станции
					$Metro['minutes'] = self::_metersToMinutes($Metro['distance']);
					
					$return['comments'][] = "Следующая по близости станция {$Metro['title']} ({$Metro['distance']} м., {$Metro['minutes']} мин.)";
										
					# если через метро ближе, то станцию не записываем
					if (($minutes_through_closest + $ClosestMetro['minutes']) <= $Metro['minutes']) {
						$return['comments'][] = "Станция {$Metro['title']} не записана, т.к. от ближайшего м. {$ClosestMetro['title']} до м. {$Metro['title']} ехать $minutes_through_closest мин. + {$ClosestMetro['minutes']} мин. добираться до станции {$ClosestMetro['title']}, сумма в пути равна " . ($minutes_through_closest + $ClosestMetro['minutes']) . " мин. Время в пути до станции {$Metro['title']} напрямую – {$Metro['minutes']} мин. ". ($i == 2 ? "ВЫХОД" : "ПРОДОЛЖЕНИЕ_ПОИСКА") ."_C_КОДОМ: " . ($minutes_through_closest + $ClosestMetro['minutes']) . " 
							< {$Metro['minutes']}";
						continue;
					} else {
						# если соотношение > 1/2 
						# (если вторая по близости станция больше чем в 2 раза удаленнее ближайшей, то приостановить выполнение скрипта)
						if ($Metro['distance'] > ($ClosestMetro['distance'] * 2)) {
							$return['comments'][] = "Следующая по близости станция {$Metro['title']} ({$Metro['distance']} м.) удалена от ближайшей {$ClosestMetro['title']} ({$ClosestMetro['distance']} м.) более чем в 2 раза. ВЫХОД";
							return $return;
						} else {
							$return['comments'][] = "Станция {$Metro['title']} ({$Metro['distance']} м., {$Metro['minutes']} мин.) записана! " . ($i == 2 ? "ВЫХОД" : "ПРОДОЛЖЕНИЕ_ПОИСКА");
							# если соотношение < 1/2, то записываем станцию
							$return[] = $Metro;
						}
					}
				}
				return $return;	
			}
		}


		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
	}