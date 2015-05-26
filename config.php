<?php
	/* Файл конфигурации */
	// Настройки
	$GLOBALS["settings"] = (object)[
		"version" 			=> "1.1",				// Версия сайта (нужная для обновления кэша JS и CSS)
	];
	
	// Константы
	$_constants = array(
		"DB_LOGIN"		=> "root",
		"DB_PASSWORD"	=> "root",
		"DB_HOST"		=> "localhost",
		"DB_PREFIX"		=> "",
		"BASE_ADDON"	=> "/egecrm/",
		"BASE_ROOT"		=> $_SERVER["DOCUMENT_ROOT"]."/egecrm/",
		"DEBUG"			=> true,
	);

	/*// Контроллеры и модели 
	$_controllers	= array(
		"", "User", "Index", "Profile", "Test", 
	);
	
	$_models		= array(
	 	"Model", "User", "Adjective", "Vote", "DefaultAdjective", "Subscription", "Subscriber", "NewsType",
	 	"Feed", "Comment",
	 );*/
	
	/********************************************************************/
	
	
	// Объявляем константы
	foreach ($_constants as $key => $val)
	{
		define($key, $val);
	}
		
	// Конфигурация ошибок (error_reporing(0) - отключить вывод ошибок)
	error_reporting(81);
		
	// Открываем соединение с основной БД
	$db_connection = new mysqli(DB_HOST, DB_LOGIN, DB_PASSWORD, DB_PREFIX."egecrm");

	// Установлено ли соединение
	if (mysqli_connect_errno($db_connection))
	{
		die("Failed to connect to MySQL: " . mysqli_connect_error());
	}
	
	// Устанавливаем кодировку БД
	$db_connection->set_charset("utf8");
	
	include_once("functions.php");				// Подключаем основные функции
	
	// Подключаем расширения
	foreach (glob("extentions/*.php") as $filename)
	{
	    include $filename;
	}
	
	require_once("models/Model.php");			// Подключаем основную модель
	require_once("controllers/Controller.php");	// Подключаем основной контроллер
	
	// Подключаем остальные модели
	foreach (glob("models/*.php") as $filename)
	{
		if (strpos($filename, "_template") || strpos($filename, "Model.php")) {
			continue;
		}
	    include $filename;
	}
	
	// Подключаем остальные контроллеры
	foreach (glob("controllers/*.php") as $filename)
	{
		if (strpos($filename, "_Template") || strpos($filename, "/Controller.php")) {
			continue;
		}
	    include $filename;
	}
	
	// Подключаем Factory
	foreach (glob("factory/*.php") as $filename)
	{
	    include $filename;
	}
?>