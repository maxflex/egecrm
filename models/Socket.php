<?php
	class Socket {
		const EGECRM_APP_ID 	 = '175234';
		const EGECRM_APP_KEY 	 = 'a9e10be653547b7106c0';
		const EGECRM_APP_SECRET	 = 'c8aba02dd5bb804eb1be';
		
		const EGEREP_APP_ID 	 = '188241';
		const EGEREP_APP_KEY 	 = '2d212b249c84f8c7ba5c';
		const EGEREP_APP_SECRET	 = '8fa5d1c99fb85f47ff89';
		
		/*
		 * Вызвать event
		 *
		 * $crm – на какую CRM запустить event: egecrm | egerep
		 */
		public static function trigger($channel, $event, $data, $crm = 'egecrm')
		{
			switch ($crm) {
				case 'egerep':
					$pusher = static::_getEgerepInstance();
					break;
				default:
					$pusher = static::_getEgecrmInstance();		
			}
			$pusher->trigger($channel, $event, $data);
		}
		
		
		private static function _getEgecrmInstance()
		{
			return new Pusher(
				static::EGECRM_APP_KEY,
				static::EGECRM_APP_SECRET,
				static::EGECRM_APP_ID,
				['encrypted' => true]
			);
		}
		
		private static function _getEgerepInstance()
		{
			return new Pusher(
				static::EGEREP_APP_KEY,
				static::EGEREP_APP_SECRET,
				static::EGEREP_APP_ID,
				['encrypted' => true, 'cluster' => 'eu']
			);
		}
	}