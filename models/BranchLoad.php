<?php
	class BranchLoad extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "branch_load";
		
		# Места, где отображаются комментарии
		const PLACE_STUDENT = 'STUDENT';
		const PLACE_REQUEST = 'REQUEST';
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array)
		{
			parent::__construct($array);
			
		}
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		public static function getSorted()
		{
			$BranchLoad = self::findAll();
			
			foreach ($BranchLoad as $bl) {
				$return[$bl->id_branch][] = $bl;
			}
			
			return $return;
		}
		
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
	}