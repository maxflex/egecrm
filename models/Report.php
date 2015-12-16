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
				if (!$this->expected_score) {
					$this->expected_score = '';
				}
			}
		}
		
	}
?>