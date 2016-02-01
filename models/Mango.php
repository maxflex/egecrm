<?php
	class Mango {
		const API_URL		= 'https://app.mango-office.ru/vpbx/';
		const API_KEY		= 'goea67jyo7i63nf4xdtjn59npnfcee5l';
		const API_SALT		= 't9mp7vdltmhn0nhnq0x4vwha9ncdr8pa';
		
		
		public static function call()
		{
			$data = [
			    "command_id" => "1",
			    "from"       => [
			        "extension" => "12",
			        "number"    => ""
			    ],
			    "to_number"  => "79169097512"
			];
			
			$json = json_encode($data);
			
			$sign = hash('sha256', static::API_KEY . $json . static::API_SALT);
			
			$post_data = [
				'vpbx_api_key'	=> static::API_KEY,
				'sign'			=> $sign,
				'json'			=> $json,	
			];
			
			$post = http_build_query($post_data);
/*
			
			$opts = [
				'http' => [
					'method' => 'POST',
					'header' => 'Content-type: application/x-www-form-urlencoded',
					'content' => $post,
				]
			];
			
			$context = stream_context_create($opts);
			
			$response = file_get_contents(const::API_URL);
*/
			
			$ch = curl_init();
			
			curl_setopt_array($ch, [
				CURLOPT_URL 			=> static::_command('commands/callback'),
				CURLOPT_RETURNTRANSFER 	=> true,
				CURLOPT_POST			=> true,
				CURLOPT_POSTFIELDS		=> $post,
			]);
			
			$response = curl_exec($ch);
			curl_close($ch);
			
			preType($response);
		}
		
		/**
		 * Создать URL с командой
		 */
		private static function _command($command)
		{
			return static::API_URL . $command;
		}
	}