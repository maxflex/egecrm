<?php

/**
 * Обрабатываем Single Sign On
 */

class SsoHandler
{
	public static function handle($url)
	{
		$parsed = parse_url($url);

		return $parsed['scheme'] . '://' . $parsed['host'] . "/auth?key=" . base64_encode(implode('|', [
			date('Y-m-d H:i'),
			User::id()
		])) . "&redirect=$url";
	}
}
