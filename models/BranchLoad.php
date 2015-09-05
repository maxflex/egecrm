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
			$BranchLoad = self::findAll([
				"condition" => "id_subject IS NULL AND grade IS NULL"
			]);
			
			foreach ($BranchLoad as $bl) {
				$return[$bl->id_branch][] = $bl;
			}
			
			return $return;
		}
		
		
		public static function getSortedBranch($id_branch)
		{
			$BranchLoad = self::findAll([
				"condition" => "id_subject IS NOT NULL AND grade IS NOT NULL AND id_branch=" . $id_branch
			]);
			
			foreach ($BranchLoad as $bl) {
				$return[$bl->grade][$bl->id_subject][] = $bl;
			}
			
			return $return;
		}
		
		public static function getSortedSubject($id_subject)
		{
			$BranchLoad = self::findAll([
				"condition" => "id_branch IS NOT NULL AND grade IS NOT NULL AND id_subject=" . $id_subject
			]);
			
			foreach ($BranchLoad as $bl) {
				$return[$bl->grade][$bl->id_branch][] = $bl;
			}
			
			return $return;
		}
		
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
	}