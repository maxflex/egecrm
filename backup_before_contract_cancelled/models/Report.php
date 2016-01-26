<?php
	class Report extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "reports";
		
		public function __construct($array) 
		{
			parent::__construct($array);
			
			// Включаем связи
			if (!$this->isNewRecord) {
			}
		}
		
		public function getEmail()
		{
			$Student = Student::findById($this->id_student);
			return $Student->Representative->email;
		}
		
	}
?>