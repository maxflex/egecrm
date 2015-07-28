<?php
	// Глобальные функции сайта
	
	/*
	 * Пре-тайп
	 */
	function preType($anything, $exit = NULL)
	{
		echo "<pre>";
		print_r($anything);
		echo "</pre>";
		
		if ($exit)
		{
			exit();
		}
	}
	
	/*
	 * Возвращает соединение DB_SETTINGS
	 */
	function dbConnection()
	{
		global $db_connection;
		return $db_connection;
	}
	
	/*
	 * Возвращает соединение DB_SETTINGS
	 */
	function memcached()
	{
		global $memcached;
		return $memcached;
	}
	
	/*
	 * Создаем подключение к БД user_x
	 */
	function initUserConnection($id_user)
	{
		global $db_user; 

		// Открываем соединение с основной БД		
		$db_user = new mysqli(DB_HOST, DB_LOGIN, DB_PASSWORD, DB_PREFIX."user_{$id_user}");
		
		// Установлено ли соединение
		if (mysqli_connect_errno($db_user))
		{
			die("Failed to connect to USER {$id_user} MySQL: " . mysqli_connect_error());
		}
		
		// Устанавливаем кодировку
		$db_user->set_charset("utf8");		
	}
	
	/*
	 * Показываем к какой таблице пользователя подключены (бд user_x)
	 */
	function showDbUser()
	{
		global $db_user;
		echo $db_user->query("SELECT DATABASE()")->fetch_array()[0];
	}
	
	/*
	 * Получает текущее время
	 */
	function now()
	{
		return date("Y-m-d H:i:s");
	}
	
	/*
	 * Обрезает пробелы и извлекает теги
	 */
	function secureString($string)
	{
		return trim(strip_tags($string));
	}
	
	/*
	 * Настоящий IP пользователя
	 */
	function realIp()
	{
	    $client  = @$_SERVER['HTTP_CLIENT_IP'];
	    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
	    $remote  = $_SERVER['REMOTE_ADDR'];
	
	    if(filter_var($client, FILTER_VALIDATE_IP))
	    {
	        $ip = $client;
	    }
	    elseif(filter_var($forward, FILTER_VALIDATE_IP))
	    {
	        $ip = $forward;
	    }
	    else
	    {
	        $ip = $remote;
	    }
	
	    return $ip;
	}
	
	/*
	 * Включить PARTIAL
	 * $string	– название включаемого файла
	 * $vars	– переменные, которые будут доступны в файле
	 */
	function partial($string, $vars = array())
	{
		// Если передаем переменные в инклуд, то объявляем их здесь (иначе будут недоступны)
		if (!empty($vars)) {
			// Объявляем переменные, соответсвующие элементам массива
			foreach ($vars as $key => $value) {
				$$key = $value;
			}
		}
			
		$called_dir = dirname(debug_backtrace()[0]["file"]);	// Получаем путь к директории, откуда была вызвана функция
		
		include_once($called_dir."/_".$string.".php");
	}
	
	
	/*
	 * Включить глобальный PARTIAL
	 * $string	– название включаемого файла
	 * $vars	– переменные, которые будут доступны в файле
	 */
	function globalPartial($string, $vars = array())
	{
		// Если передаем переменные в инклуд, то объявляем их здесь (иначе будут недоступны)
		if (!empty($vars)) {
			// Объявляем переменные, соответсвующие элементам массива
			foreach ($vars as $key => $value) {
				$$key = $value;
			}
		}
					
		include_once(BASE_ROOT."/views/_partials/_".$string.".php");
	}
	
	/*
	 * В формат ангуляра
	 */
	function angInitSingle($name, $Object)
	{
		return $name." = ".htmlspecialchars(json_encode($Object, JSON_NUMERIC_CHECK)) ."; ";
	}
	
	/*
	 * Инициализация переменных ангуляра
	 * $array – [var_name = {var_values}; ...]
	 * @return строка вида 'a = {test: true}; b = {var : 12};' 
	 */
	function angInit($array)
	{
		foreach ($array as $var_name => $var_value) {
			// Если значение не установлено, то это пустой массив по умолчанию
			if (!$var_value && !is_int($var_value)) {
				$var_value = "[]";
			} else {
				// иначе кодируем объект в JSON
				$var_value = htmlspecialchars(json_encode($var_value, JSON_NUMERIC_CHECK)); 
			}
			$return .= $var_name." = ". $var_value ."; ";
		}
		
		return $return;
	}
	
	/*
	 * Преобразование true/false в 1/0 для сохранения в БД
	 */
	function trueFalseConvert(&$array)
	{
		foreach ($array as $key => $val)
		{
			if ($val === "true") {
				$array[$key] = true;
			} elseif ($val === "false") {
				$array[$key] = false;
			}
		}
	}
	
	/*
	 * Возвратить значение, если оно установлено
	 * $value 	- проверяемое значение
	 * $pre		- если значение установлено, добавить при выводе
	 */ 
	function ifSet($value, $pre = "")
	{
		return (isset($value) ? $pre.$value : "");
	}
	
	/*
	 * Создать URL
	 * $params = array (controller, action, text, 
	 * params - массив, дополнительные параметры, будут переданы в GET 
	 * htmlOptions - массив, аттрибуты HTML элемента)
	 */
	function createUrl($params)
	{
		// Если есть опции HTML (атрибуты)
		if (isset($params["htmlOptions"])) {
			foreach ($params["htmlOptions"] as $option => $value) {
				$htmlOptions .= $option."='$value' ";
			}
		}
		
		echo "<a $htmlOptions href='".$params['controller']
			 	.ifSet($params["action"])
			 	.(isset($params["params"]) ? "&".http_build_query($params["params"]) : "")."'>"
			 	.$params["text"]."</a>";
	}
	
	/*
	 * Проверяет активен ли пункт меню
	 * $controller	– контроллер, при котором пункт меню активен
	 * $action		– экшн, при котором пункт меню становится активен
	 * $paramsNotEqual (array) - дополнительные параметры для сравнения, которые должны быть не равны 	[ВАЖНО: параметр берется из $_GET]
	 * $paramsEqual (array) – дополнитльные параметры для сравнения, которые должны быть равны 			[ВАЖНО: параметр берется из $_GET]
	 */
	function menuActive($controller, $action = null, $paramsEqual = array(), $paramsNotEqual = array())
	{
		// Проверяем контроллер
		if ($_GET["controller"] != $controller) {
			return;
		}
		
		// Проверяем экшн
		if (isset($action) && $_GET["action"] != $action) {
			return;
		}
		
		// Проверяем дополнительные параметры НЕРАВЕНСТВА
		foreach ($paramsNotEqual as $param_name => $param_val) {
			if ($param_val == $_GET[$param_name]) {
				return;
			}
		}
		
		// Проверяем дополнительные параметры РАВЕНСТВА
		foreach ($paramsEqual as $param_name => $param_val) {
			if ($param_val != $_GET[$param_name]) {
				return;
			}
		}
		
		// Если все проверки пройдены, возвращаем активный класс
		return "class='active'";
	}
	
	/*
	 * Удаляем куку
	 * $cookie_name – какую куку удаляем
	 * $domain – где удаляется кука (это нужно было для очистки куки PHPSESSID, она удаляется с домена «/», а ratie_token с пустого только)
	 */
	function removeCookie($cookie_name, $domain = "") 
	{
		unset($_COOKIE[$cookie_name]);
		setcookie($cookie_name, "", time() - 3600, $domain);
	}
	
	/*
	 * Функция возвращает настройки $GLOBALS['settings']
	 */
	function settings()
	{
		return $GLOBALS["settings"];
	}
	
	/*
	 * Функция просто отображает через H1 (для тестирования)
	 */
	function h1($text)
	{
		echo "<h1 class='text-white'>$text</h1><br>";
	}
	
	/*
	 * Функция выводит дату в относительном формате
	 * $dont_format - не переводить время из DateTime
	 */
	function relativeDate($date, $dont_format = false) // $date --> время в формате Unix time
	{
		if ($date == "0000-00-00 00:00:00" || $date == null || $date == 0) {
			return "Время неизвестно";
		}
		
		// Если нужно форматировать дату
		if (!$dont_format) {
			$date = strtotime($date);	
		}
		
	    $stf = 0;
	    $cur_time = time();
	    $diff = $cur_time - $date;
	 
	    $seconds = array('секунда', 'секунды', 'секунд');
	    $minutes = array('минута', 'минуты', 'минут');
	    $hours = array('час', 'часа', 'часов');
	    $days = array('день', 'дня', 'дней');
	    $weeks = array('неделя', 'недели', 'недель');
	    $months = array('месяц', 'месяца', 'месяцев');
	    $years = array('год', 'года', 'лет');
	    $decades = array('десятилетие', 'десятилетия', 'десятилетий');
	 
	    $phrase = array($seconds, $minutes, $hours, $days, $weeks, $months, $years, $decades);
	    $length = array(1, 60, 3600, 86400, 604800, 2630880, 31570560, 315705600);
	 
	    for ($i = sizeof($length) - 1; ($i >= 0) && (($no = $diff / $length[$i]) <= 1); $i--) ;
	    if ($i < 0) $i = 0;
	    $_time = $cur_time - ($diff % $length[$i]);
	    $no = floor($no);
	    $value = sprintf("%d %s ", $no, getPhrase($no, $phrase[$i]));
	 
	    if (($stf == 1) && ($i >= 1) && (($cur_time - $_time) > 0)) $value .= time_ago($_time);
	 
	    return $value . ' назад';
	}
	function getPhrase($number, $titles)
	{
	    $cases = array (2, 0, 1, 1, 1, 2);
	    return $titles[ ($number%100>4 && $number%100<20)? 2 : $cases[min($number%10, 5)] ];
	}
	
	/*
	 * Перобразовать в строку
	 */
	function toString($str)
	{
		return "'".$str."'";
	}
	
	/*
	 * Строка в цвет
	 */
	function stringToColor($str) {
	  $code = dechex(crc32($str));
	  $code = substr($code, 0, 6);
	  return $code;
	}
	
	
	
	/**
	 * Является ли строка JSON-объектом.
	 * 
	 */
	function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
	
	/*
	 * JSON-ответ
	 */
	function toJson($response)
	{
		echo json_encode($response);
	}
	
	/*
	 * JSON-ответ
	 */
	function returnJson($response)
	{
		toJson($response);
		exit();
	}
	
	/**
	 * Проверить есть ли хотя бы одно значение в массиве.
	 * 
	 */
	function hasValues($array)
	{
	//	echo "HAS_VALS=".(count(array_filter($array)))."<br>";
		return count(array_filter($array));
	}
	
	
	/**
		Переводит массив вида:
		Array
		(
		    [0] => Array
		        (
		            [name] => 110_1.jpg
		        )
		
		    [1] => Array
		        (
		            [name] => 110_2.png
		        )
		        
		В массив вида:
		)
		Array
		(
		    [0] => 110_1.jpg
		    [1] => 110_2.png
		)
	 */
	function arrayLevelUp($array)
	{
		return array_map(function($a) {  return array_pop($a); }, $array);
	}
	
	
	/**
	 * Форматировать дату в наш формат.
	 * 
	 */
	function dateFormat($date, $notime = false)
	{
		$date = date_create($date);
		return date_format($date, $notime ? "d.m.Y" : "d.m.Y в H:i");
	}
	
	
	/**
	 * Возвратить чистый номер телефона.
	 * 
	 */
	function cleanNumber($number) 
	{
		return preg_replace("/[^0-9]/","",$number);	
	}
	
	
	/**
	 * Проверка даты на то, что оно пустое.
	 * 
	 */
	function emptyDate($date)
	{
		return $date == "0000-00-00 00:00:00";
	}
	
	/**
	 * Обратная функция – вернуть форматированный номер из 7290556776.
	 * 
	 */
	function formatNumber($number) {
		$part1 = substr($number, 1, 3);
		$part2 = substr($number, 4, 3);
		$part3 = substr($number, 7, 2);
		$part4 = substr($number, 9, 2);
		
		return "+7 ($part1) $part2-$part3-$part4";
	}
	
	function pluralize($one, $few, $many, $n)
	{
		return $n%10==1&&$n%100!=11?$one:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?$few:$many);
	}
	
	// 10,13,9 (1)
	// 10,9 (3)
	// 2,15,12 (1)
	// 2,3,7,4 (1)
	// 7,4 (42)
?>