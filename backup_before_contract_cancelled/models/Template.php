<?php
	class Template extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "templates";
		protected $_inline_data = ["who"];
		
		const STUDENT 		 = 1;
		const REPRESENTATIVE = 2;
		const TEACHER 		 = 3;
		
		public static function get($number, $params)
		{
			$Template = self::find([
				"condition" => "number=$number"
			]);
			
			$Template->_complie_vars($params);
			
			return $Template->text;
		}
		
		public static function getFull($number, $params)
		{
			$Template = self::find([
				"condition" => "number=$number"
			]);
			
			$Template->_complie_vars($params);
			
			return $Template;
		}
		
		public function toStudents()
		{
			return in_array(self::STUDENT, $this->who);
		}
		
		public function toRepresentatives()
		{
			return in_array(self::REPRESENTATIVE, $this->who);
		}
		
		public function toTeachers()
		{
			return in_array(self::TEACHER, $this->who);
		}
		
		private function _complie_vars($params)
		{
			$this->text = str_replace('{name}',  User::fromSession()->first_name, $this->text);
			
			foreach ($params as $param => $value) {
				$this->text = str_replace('{' . $param .'}', $value, $this->text);
			}
		}
		
	}