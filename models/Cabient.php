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
		
		/**
		 * Получить занятое время кабинета.
		 * 
		 */
		public static function getFreetime($id_group, $cabinet)
		{
			if (!$cabinet) {
				return [];
			}
			
			$result = dbConnection()->query("
				SELECT gt.id FROM group_time gt
				LEFT JOIN groups g ON g.id = gt.id_group
				WHERE g.cabinet = $cabinet " . ($id_group ? "AND g.id != $id_group" : "") ."
			");
			
			while ($row = $result->fetch_assoc()) {
				$ids[] = $row['id'];	
			}
			
			$GroupTime = GroupTime::findAll([
				"condition"	=> "id IN (" . implode(",", $ids) . ")"
			]);
			
			if (!$GroupTime) {
				return [];
			}
			
			foreach ($GroupTime as $GroupTimeData) {
				$index = Freetime::getIndexByTime($GroupTimeData->time);
				$return[$GroupTimeData->day][$index] = $GroupTimeData->time;
			}
			
			return $return;
		}
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
	}