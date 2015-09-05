<?php
	$ch = curl_init();
	
	curl_setopt_array($ch, [
		CURLOPT_URL 	=> "http://www.s-shot.ru/login/",
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.52 Safari/537.17',
		CURLOPT_AUTOREFERER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_POST 	=> true,
		CURLOPT_POSTFIELDS	=> [
			"login" 	=> "maxflex2",
			"password" 	=> "fuckyou",
			"redirect"	=> "/",
			"submit"	=> "Войти",
			"store"		=> 0,
		],
		CURLOPT_RETURNTRANSFER 	=> true
	]);
	
	$result = curl_exec($ch);
	
	var_dump($result);
	