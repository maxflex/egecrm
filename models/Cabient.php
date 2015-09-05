<?php
	class Cabinet extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "cabinets";
		
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array)
		{
			parent::__construct($array);
			
		}
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		public static function getByBranch($id_branch)
		{
			if (!$id_branch) {
				return false;
			}
			
			return self::findAll([
				"condition" => "id_branch=$id_branch"
			]);
		}
		
		public static function getBranchCabinetIds($id_branch)
		{
			return self::getIds([
				"condition" => "id_branch=$id_branch"
			]);
		}
		
		public static function getCabinetGroups($id_branch)
		{
			$ids = self::getBranchCabinetIds($id_branch);
			
			$Cabinets = self::getByBranch($id_branch);
									
			$Groups = Group::findAll([
				"condition" => "cabinet IN (". implode(",", $ids) . ")",
				"order"		=> "day ASC, start ASC"
			]);
			
			foreach ($Groups as $Group) {
				foreach ($Cabinets as $Cabinet) {
					if ($Cabinet->id == $Group->cabinet) {
						$return[$Cabinet->number][] = $Group;
					}
				}
			}
			
			return $return;
		}
		
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
	}