<?php

use GuzzleHttp\Client;

class SessionService
{
	private static $client;

	private static function init()
	{
		if (! self::$client) {
			self::$client = new Client([
	            'base_uri' => SESSION_SERVICE_URL,
	        ]);
		}
	}

	public static function action($type = null)
	{
		self::init();
		// если уже отсылали недавно обновление времени последнего действия
		if (self::setCache()) {
			return;
		}
		$params = ['user_id' => User::id()];
		if ($type) {
			$params['type'] = $type;
		}
		self::$client->post('sessions/action', [
            'form_params' => $params,
        ]);
	}

	public static function exists()
	{
		self::init();
		$client = new Predis\Client();
		$key = "egecrm:session:exists:" . User::id();
		if ($client->exists($key)) {
			return $client->get($key);
		}
		$response = self::$client->get("sessions/exists/" . User::id());
		$exists = json_decode($response->getBody()->getContents());
		$client->set($key, $exists ? 1 : 0, 'EX', 60);
		return $exists;
	}

	/**
	 * Закешировать установку ACTION.
	 * ACTION можно делать раз в минуту
	 */
	public static function setCache($seconds = 60)
	{
		$key = "egecrm:session:action:" . User::id();
		$client = new Predis\Client();
		if ($client->get($key)) {
			return true;
		}
		$client->set($key, 1, 'EX', $seconds);
		return false;
	}
}
