<?php
	class Socket {
		const APP_ID 	 = '175234';
		const APP_KEY 	 = 'a9e10be653547b7106c0';
		const APP_SECRET = 'c8aba02dd5bb804eb1be';
		
		
		/*
		 * Вызвать event
		 */
		public static function trigger($channel, $event, $data)
		{
			$pusher = static::_getInstance();
			$pusher->trigger($channel, $event, $data);
		}
		
		
		private static function _getInstance()
		{
			return new Pusher(
				static::APP_KEY,
				static::APP_SECRET,
				static::APP_ID,
				['encrypted' => true]
			);
		}
	}