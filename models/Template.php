<?php
	class Template extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "templates";
		protected $_inline_data = ["who"];
		
		public static function get($number, $params)
		{
			$Template = self::find([
				"condition" => "number=$number"
			]);
			
			$Template->_complie_vars($params);
			
			return $Template->text;
		}
		
		private function _complie_vars($params)
		{
			$this->text = str_replace('{name}',  User::fromSession()->first_name, $this->text);
			
			foreach ($params as $param => $value) {
				$this->text = str_replace('{' . $param .'}', $value, $this->text);
			}
		}
		
	}