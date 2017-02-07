<?php
	// Глобальные функции сайта

	/**
	 * Логировать объект
	 */
	 function logObject($Object, $title = false)
	 {
		 if ($title) {
			 error_log('==================================');
			 error_log('		' . strtoupper($title));
			 error_log('==================================');
		 }
		 foreach ($Object as $key => $value) {
		 	error_log($key . ' => ' . $value);
		 }
	 }

	/*
	 * Пре-тайп
	 */
	function preType($anything, $exit = NULL)
	{
		if (allowed(Shared\Rights::IS_DEVELOPER)) {
			echo "<pre>";
			print_r($anything);
			echo "</pre>";

			if ($exit)
			{
				exit();
			}
		}
	}

    function dd($objects)
    {
        if (allowed(Shared\Rights::IS_DEVELOPER) || IS_LOCAL_DEVELOPMENT) {
            $objects = is_array($objects) ? $objects : func_get_args();
            echo "<pre>";
            foreach($objects as $o) {
                print_r($o);
            }
			echo "</pre>";
            echo "<hr>";
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

	function dbEgerep()
	{
		global $db_egerep;
		return $db_egerep;
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
	function now($no_time = false)
    {
        return date('Y-m-d' . ($no_time ? '' : ' H:i:s'));
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

		include($called_dir."/_".$string.".php");
	}

	/*
	 * Включить PARTIAL
	 * $string	– название включаемого файла
	 * $vars	– переменные, которые будут доступны в файле
	 */
	function printPartial($string, $vars = array())
	{
		// Если передаем переменные в инклуд, то объявляем их здесь (иначе будут недоступны)
		if (!empty($vars)) {
			// Объявляем переменные, соответсвующие элементам массива
			foreach ($vars as $key => $value) {
				$$key = $value;
			}
		}

		$called_dir = dirname(debug_backtrace()[0]["file"]);	// Получаем путь к директории, откуда была вызвана функция

//		dd($called_dir."/print/_".$string.".php");
		include($called_dir."/print/_".$string.".php");
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
		include(BASE_ROOT."/views/_partials/_".$string.".php");
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
	    $return = '';
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

	function returnJsonAng($Object)
	{
		echo json_encode($Object, JSON_NUMERIC_CHECK);
		exit();
	}

	/**
	 * Проверить есть ли хотя бы одно значение в массиве.
	 *
	 */
	function hasValues($array)
	{
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

	function pathLevelUp($path)
	{
		$pos = strripos($path, '/');

		return mb_strimwidth($path, 0, $pos);
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
	 * Возвратить номер телефона в удобном для частичного поиска формате.
	 *
	 */
	function cleanNumberForSearch($number)
	{
		// вариант с пропусками
		// Оствим только знаки 0-9,_
		//$number = preg_replace("/[^0-9_]/","",$number);

		$number = preg_replace("/^\+7/","",$number);
		$number = preg_replace("/[^0-9]/","",$number);
		return $number;
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

		// Если номер не начинается с семаки, добавляем семаку
		if ($number[0] != "7") {
			$number = "7". $number;
		}

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

	function convertDate($date)
	{
		return date("Y-m-d", strtotime($date));
	}

	function convertDateBack($date)
	{
		if (!$date) {
			return "";
		}
		return date("d.m.Y", strtotime($date));
	}

	// если не указан id_request, то ищет по всей базе
	// иначе ищет учитывая связанные заявки студента
	function isDuplicate($phone, $id_request, $id_student = false)
	{
		if ($id_student) {
			$OriginalRequest = (object)['id_student' => $id_student];
		} else {
			// Находим оригинальную заявку
			$OriginalRequest = Request::findById($id_request);
		}

		$phone = cleanNumber($phone);

		# Ищем заявку с таким же номером телефона
		$Request = Request::count([
			"condition"	=> "(phone='".$phone."' OR phone2='".$phone."' OR phone3='".$phone."')"
				. ($id_request ? " AND id_student!=".$OriginalRequest->id_student : "")
		]);

		// Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
		if ($Request) {
			return true;
		}

		# Ищем ученика с таким же номером телефона
		$student_count = Student::count([
			"condition"	=> "(phone='".$phone."' OR phone2='".$phone."' OR phone3='".$phone."')"
				. ($id_request ? " AND id!=".$OriginalRequest->id_student : "")
		]);

		// Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
		if ($student_count) {
			return true;
		}

		# Ищем представителя с таким же номером телефона
		$represetative_phone_duplicate = dbConnection()->query("
			SELECT r.id FROM ".Representative::$mysql_table." r
			LEFT JOIN ".Student::$mysql_table." s on r.id = s.id_representative
			WHERE (r.phone='".$phone."' OR r.phone2='".$phone."' OR r.phone3='".$phone."')"
				. ($id_request ? " AND s.id!=".$OriginalRequest->id_student : "")
		);

		// Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
		if ($represetative_phone_duplicate->num_rows) {
			return true;
		}


		// возвращается, если номера нет в базе
		return false;
	}

	function translit($str) {
		$rus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
		$lat = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
		return str_replace($rus, $lat, $str);
	}

	function russian_month($month)
	{
		switch ($month) {
			case 1: $m='января'; break;
			case 2: $m='февраля'; break;
			case 3: $m='марта'; break;
			case 4: $m='апреля'; break;
			case 5: $m='мая'; break;
			case 6: $m='июня'; break;
			case 7: $m='июля'; break;
			case 8: $m='августа'; break;
			case 9: $m='сентября'; break;
			case 10: $m='октября'; break;
			case 11: $m='ноября'; break;
			case 12: $m='декабря'; break;
		}

		return $m;
	}

	function russian_month_id_by_name($month_name)
	{
		switch ($month_name) {
			case 'января': return 1;
			case 'февраля': return 2;
			case 'марта': return 3;
			case 'апреля': return 4;
			case 'мая': return 5;
			case 'июня': return 6;
			case 'июля': return 7;
			case 'августа': return 8;
			case 'сентября': return 9;
			case 'октября': return 10;
			case 'ноября': return 11;
			case 'декабря': return 12;
		}
	}

	// 12 сентября
	function today_text($not_today = false)
	{
		if ($not_today) {
			$current_month = date("n", strtotime($not_today));
			$current_day   = date("d", strtotime($not_today));
		} else {
			$current_month = date("n");
			$current_day   = date("d");
		}

		$current_month = russian_month($current_month);

		return $current_day . " " . $current_month;
	}


	/**
	 * Сгенерировать случайную строку.
	 *
	 * $mode =  digits | uppercase | lowercase (по умолчанию - всё)
	 */
	function generateRandomString($length = 10, $mode = ['digits', 'uppercase', 'lowercase']) {
		$characters = '';

		if (in_array('digits', $mode)) {
			$characters .= '0123456789';
		}
		if (in_array('uppercase', $mode)) {
			$characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		}
		if (in_array('lowercase', $mode)) {
			$characters .= 'abcdefghijklmnopqrstuvwxyz';
		}

	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	function getName($last_name, $first_name, $middle_name, $order = 'fio')
	{
		if (empty(trim($last_name)) && empty(trim($first_name)) && empty(trim($middle_name))) {
			return "Неизвестно";
		}

		if ($last_name) {
			$name[0] = $last_name;
		}

		if ($first_name) {
			$name[1] = $first_name;
		}

		if ($middle_name) {
			$name[2] = $middle_name;
		}

		$order_values = [
			'f' => 0,
			'i' => 1,
			'o' => 2,
		];

        foreach ([0, 1, 2] as $part) {
            if (isset($order[$part])) {
                $name_ordered[] = $name[$order_values[$order[$part]]];
            }
        }

		return trim(implode(" ", $name_ordered));
	}


	function searcharray($value, $key, $array) {
	   foreach ($array as $k => $val) {
	       if ($val[$key] == $value) {
	           return $array[$k];
	       }
	   }
	   return null;
	}

	function isBlank($value) {
		return empty($value) && !is_numeric($value);
	}

	/**
	 * Текущий учебный год
	 */
	function academicYear($date = false)
	{
		if ($date === false) {
			$date = now();
		}
		$year = date("Y", strtotime($date));
		$day_month = date("m-d", strtotime($date));

		if ($day_month >= '01-01' && $day_month <= '07-15') {
			$year--;
		}
		return $year;
	}

	/*
	 * указать кол-во минут для кэша
	 */
	function minutes($seconds)
	{
		return ($seconds * 60);
	}

	function tillNextDay()
	{
		return strtotime('tomorrow') - time() + 20;
	}

	function trim_strings($value)
    {
        if (is_array($value)) {
            foreach ($value as &$item) {
                $item = trim($item);
            }
        } else {
            $value = trim($value);
        }
        return $value;
    }

    /**
     * Удалить пустые строки
     */
    function filterParams($values)
    {
        return (object)array_filter((array)$values, function($v) {
            return $v !== '';
        });
    }

    /**
     * Деформатировать дату
     */
    function fromDotDate($date, $add_year = null)
    {
        $parts = explode('.', $date);
        if ($add_year !== null) {
            $parts[2] = $add_year . $parts[2];
        }
        return implode('-', array_reverse($parts));
    }

    function findObjectInArray($array, $params) {
        foreach ($array as $item) {
            $found = true;
            foreach ($params as $field => $value) {
                if ($item->$field != $value) {
                    $found = false;
                }
            }

            if ($found) return $item;
        }

        return false;
    }

    function allowed($right, $return_int = false)
    {
        $allowed = User::fromSession()->allowed($right);
        return $return_int ? (int)$allowed : $allowed;
    }
