<?php

class SMS
{
	public static function sendToNumbers($numbers, $message) {
		foreach ($numbers as $number) {
			self::send($number, $message);
		}	
	}
	
	public static function send($to, $message)
	{
		$to = explode(",", $to);
		foreach ($to as $number) {
			$number = cleanNumber($number);
			$number = trim($number);
			if (!preg_match('/[0-9]{10}/', $number)) {
				continue;
			}
			$params = array(
				"api_id"	=>	"8d5c1472-6dea-d6e4-75f4-a45e1a0c0653",
				"to"		=>	$number,
				"text"		=>	$message,
				"from"      =>  "EGE-Centr",
			);		
			$result = self::exec("http://sms.ru/sms/send", $params);
		}
                return $result;
	}
	
	protected static function exec($url, $params)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;		
	}
}