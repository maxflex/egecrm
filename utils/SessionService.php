<?php

use GuzzleHttp\Client;

class SessionService
{
	private static $client;
	private static $redis;

	private static function init()
	{
		if (! self::$client) {
			self::$client = new Client([
	            'base_uri' => SESSION_SERVICE_URL,
	        ]);
		}
		if (! self::$redis) {
			self::$redis = new Predis\Client();
		}
	}

	public static function action($skip_cache = false)
	{
		self::init();
		// если уже отсылали недавно обновление времени последнего действия
		if (!$skip_cache && self::setCache()) {
			return;
		}

		$params = [
			'user_id' => User::id(),
			'type'    => User::fromSession()->type
		];

		self::$client->post('sessions/action', [
            'form_params' => $params,
        ]);
	}

	public static function exists($skip_cache = false)
	{
		self::init();
		if (! $skip_cache) {
			$key = "egecrm:session:exists:" . User::id();
			if (self::$redis->exists($key)) {
				return self::$redis->get($key);
			}
		}
		$response = self::$client->get("sessions/exists/" . User::id());
		$exists = json_decode($response->getBody()->getContents());
		self::$redis->set($key, $exists ? 1 : 0, 'EX', 15);
		return $exists;
	}

	public static function clearCache()
	{
		self::init();
		$key = "egecrm:session:exists:" . User::id();
		self::$redis->del($key);
	}

	/**
	 * Закешировать установку ACTION.
	 * ACTION можно делать раз в минуту
	 */
	public static function setCache($seconds = 30)
	{
		self::init();
		$key = "egecrm:session:action:" . User::id();
		if (self::$redis->get($key)) {
			return true;
		}
		self::$redis->set($key, 1, 'EX', $seconds);
		return false;
	}

	public static function destroy()
	{
		self::init();
		self::clearCache();
		self::$client->get("sessions/destroy/" . User::id());
	}
}
