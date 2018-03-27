<?php
	class Cabinet extends Model
	{
		// удаленные кабинеты (скрыть)
		const DELETED = [23];

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
					$cabinet->number = Branches::getField($cabinet->id_branch, 'short') . ": " . $cabinet->number;
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
			if (! $id_cabinet) {
				return 0;
			}
			return dbConnection()->query("SELECT $field FROM cabinets WHERE id={$id_cabinet}")->fetch_object()->{$field};
		}

		public function isDeleted()
		{
			return in_array($this->id, self::DELETED);
		}

		public static function getBlock($id_cabinet, $id_branch = false)
		{
		    if (!$id_branch) {
                $id_branch = self::getField($id_cabinet);
            }

			$branch = Branches::getOne($id_branch);

			return [
				'id' 	=> $id_cabinet,
				'color' => $branch->color,
				'label'	=> $branch->short . "–" . self::getField($id_cabinet, 'number'),
                'short' => $branch->short,
                'id_branch' => $id_branch,
			];
		}

		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

	}
