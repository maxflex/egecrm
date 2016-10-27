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

		/**
		 * Получить кабинеты по id филиалов.
		 *
		 * $branch – если передан массив, ищет все кабинеты по ID, если передано число,
		 * то ищутстя только кабинеты этого id филиала
		 *
		 * @param bool $groupByBranches     Groups cabinets by branches,
		 * 									multilevel array returned instead of dimensional array
		 *
		 * @return Cabinet[] 					Array of cabinets.
		 * 										Cabinets are grouped by branch if groupByBranches param passed
		 */
		public static function getByBranch($branch, $id_group = 0, $groupByBranches = false)
		{
			if (!$branch) {
				return false;
			}

			if (is_array($branch)) {
				$condition = "id_branch IN (". implode(",", $branch) . ")";
			} else {
				$condition = "id_branch=$branch";
			}

			$return = self::findAll([
				"condition" => $condition,
			]);

			foreach ($return as &$cabinet) {
				$cabinet->number = "Кабинет №".$cabinet->number;
			}


			//группировка по филиалам
			if ($groupByBranches) {
				$groupedCabinets = [];

				foreach ($return as &$cabinet) {
					if(isset($groupedCabinets[$cabinet->id_branch])) {
						$groupedCabinets[$cabinet->id_branch] = array_merge(
																		$groupedCabinets[$cabinet->id_branch],
																		[$cabinet]
																);
					} else {
						$groupedCabinets[$cabinet->id_branch] = [$cabinet];
					}
				}
				return $groupedCabinets;
			}

			// если выбрано много филиалов, подписывать название филиала к кабинету
			// чтобы было понятно филиал какого кабинета это
			if (is_array($branch) && count($branch) > 1) {
				foreach ($return as &$cabinet) {
					$cabinet->number = Branches::$short[$cabinet->id_branch] . ": " . $cabinet->number;
				}
			}

			return $return;
		}

		public static function getBranchId($id_branch)
		{
			return self::findAll([
				"condition" => "id_branch=$id_branch"
			]);
		}

		/**
		 * Получить $field по id_cabinet
		 */
		public function getField($id_cabinet, $field = 'id_branch')
		{
			return dbConnection()->query("SELECT $field FROM cabinets WHERE id={$id_cabinet}")->fetch_object()->{$field};
		}

		public static function getBlock($id_cabinet, $id_branch = false)
		{
		    if (!$id_branch) {
                $id_branch = self::getField($id_cabinet);
            }

			return [
				'id' 	=> $id_cabinet,
				'color' => Branches::metroSvg($id_branch, false, true),
				'label'	=> Branches::$short[$id_branch] . "–" . self::getField($id_cabinet, 'number'),
			];
		}

		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

	}