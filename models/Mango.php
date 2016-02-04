<?php
	class Mango {
		const API_URL		= 'https://app.mango-office.ru/vpbx/';
		const API_KEY		= 'goea67jyo7i63nf4xdtjn59npnfcee5l';
		const API_SALT		= 't9mp7vdltmhn0nhnq0x4vwha9ncdr8pa';

		# команды
		const COMMAND_HANGUP = 'call/hangup';

		/**
		 * Завершение вызова
		 */
		public static function hangup($call_id)
		{
			return static::_run(static::COMMAND_HANGUP, [
				'call_id' => $call_id,
			]);
		}

		public static function call()
		{
		}

		/**
		 * Запустить команду
		 * @return результат $ch
		 */
		private static function _run($command, $data)
		{
			# command id неважно какой
			$data['command_id'] = 1;
			$json = json_encode($data);
			$sign = hash('sha256', static::API_KEY . $json . static::API_SALT);

			$post_data = [
				'vpbx_api_key'	=> static::API_KEY,
				'sign'			=> $sign,
				'json'			=> $json,
			];

			$post = http_build_query($post_data);

			$ch = curl_init();

			// logObject($data, 'data');
			// logObject($post_data, 'post');
			//
			// error_log($post);
			// error_log(static::_command($command));

			curl_setopt_array($ch, [
				CURLOPT_URL 			=> static::_command($command),
				CURLOPT_RETURNTRANSFER 	=> true,
				CURLOPT_POST			=> true,
				CURLOPT_POSTFIELDS		=> $post,
			]);

			$response = curl_exec($ch);
			curl_close($ch);
			return $response;
		}

		/**
		 * Создать URL с командой
		 */
		private static function _command($command)
		{
			return static::API_URL . 'commands/' . $command;
		}
	}
