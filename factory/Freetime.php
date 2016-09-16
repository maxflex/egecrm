<?php
	class Freetime
	{

		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/
		const BAR_YEAR = 2016;

		// ID => TIME
		const TIME = [
			7 => "16:15",
			8 => "18:40",
			3 => "11:00",
			4 => "13:30",
			5 => "16:00",
			6 => "18:30",
			1 => "11:00",
			2 => "13:30",
		];

		const DAYS_SHORT = [
			1 => "ПН",
			2 => "ВТ",
			3 => "СР",
			4 => "ЧТ",
			5 => "ПТ",
			6 => "СБ",
			7 => "ВС",
		];

		const DAYS_FULL = [
			1 => "Понедельник",
			2 => "Вторник",
			3 => "Среда",
			4 => "Четверг",
			5 => "Пятница",
			6 => "Суббота",
			7 => "Воскресенье",
		];

		public static $weekdays_time = [
			1 => [1, 2, 7, 8],
			2 => [1, 2, 7, 8],
			3 => [1, 2, 7, 8],
			4 => [1, 2, 7, 8],
			5 => [1, 2, 7, 8],
			6 => [3, 4, 5, 6],
			7 => [3, 4, 5, 6],
		];

		public static $weekdays = [
			1 => [self::TIME[1], self::TIME[2], self::TIME[7], self::TIME[8]],
			2 => [self::TIME[1], self::TIME[2], self::TIME[7], self::TIME[8]],
			3 => [self::TIME[1], self::TIME[2], self::TIME[7], self::TIME[8]],
			4 => [self::TIME[1], self::TIME[2], self::TIME[7], self::TIME[8]],
			5 => [self::TIME[1], self::TIME[2], self::TIME[7], self::TIME[8]],
			6 => [self::TIME[3], self::TIME[4], self::TIME[5], self::TIME[6]],
			7 => [self::TIME[3], self::TIME[4], self::TIME[5], self::TIME[6]],
		];

		public static $title = "время занятия";

		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/



		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/

		/**
		 * Построить селектор
		 * $multiple - множественный выбор
		 */
		public static function buildMultiSelector($selected = false, $attrs, $multiple = false)
		{
			echo "<select ".($multiple ? "multiple" : "")." class='form-control watch-select' ".Html::generateAttrs($attrs).">";

			// Заголовок
			if (!$multiple) {
				echo "<option selected style='cursor: default; outline: none' value=''>". static::$title ."</option>";
				echo "<option disabled style='cursor: default' value=''>──────────────</option>";
			}

			foreach (self::$weekdays_time as $day => $time_data) {
				echo "<optgroup label='" . self::DAYS_FULL[$day] . "'>";
				foreach ($time_data as $time) {
					$value = "{$day}-{$time}";
					// если это массив выбранных элементов (при $multiple = true)
					// $option_selected = in_array($time_id, $selected);
					$option_selected = $selected == $value;

					// если опция не удалена (если удалена, то отображается только в том случае, если удаленный вариант был выбран ранее)
					echo "<option ".($option_selected ? "selected" : "")." value='{$value}'>" . self::TIME[$time] ."</option>";
				}
				echo '</optgroup>';
			}
			echo "</select>";
		}


		public static function getIndexByTime($time) {
			switch ($time) {
				case self::TIME[4]: {
					return 1;
				}
				case self::TIME[5]:
				case self::TIME[1]: {
					return 2;
				}
				case self::TIME[6]:
				case self::TIME[2]: {
					return 3;
				}
				default: {
					return 0;
				}
			}
		}

		public static function getId($time)
		{
			foreach (self::TIME as $id => $t) {
				if ($t == $time) {
					return $id;
				}
			}
		}

		public static function dayIndexByIdTime($id_time)
		{
			return array_search($id_time, self::$weekdays_time);
		}

		public static function getFreetimeBar($id_entity, $type_entity)
		{
			foreach (self::$weekdays_time as $day => $time_data) {
				foreach ($time_data as $time_index => $time_id) {
					$bar[$day][$time_id] = EntityFreetime::hasFreetime($id_entity, $type_entity, $day, $time_id) ? 'green' : 'empty';
				}
			}
			return $bar;
		}

		public static function emptyBar()
		{
			foreach (self::$weekdays_time as $day => $time_data) {
				foreach ($time_data as $time_index => $time_id) {
					$bar[$day][$time_id] = 'gray';
				}
			}
			return $bar;
		}
		
		public static function getStudentBar($id_student, $with_freetime = false, $id_group = false)
		{
			if ($with_freetime) {
				$bar = Freetime::getFreetimeBar($id_student, 'student');
			} else {
				$bar = [];
			}
			foreach (self::$weekdays_time as $day => $time_data) {
				foreach ($time_data as $time_index => $time_id) {
                    $result = dbConnection()->query("
						SELECT COUNT(*) AS cnt, g.id as id_group FROM group_time gt
						LEFT JOIN groups g ON g.id = gt.id_group
						WHERE FIND_IN_SET({$id_student}, g.students) AND g.ended = 0 AND gt.id_time=$time_id AND g.year = ".self::BAR_YEAR."
					")->fetch_object();
                    static::_brushBar($result, $with_freetime, $bar, $day, $time_id, $id_group);
                }
            }
            return $bar;
        }

		public static function getTeacherBar($id_teacher, $with_freetime = false, $id_group = false)
		{
		    if ($with_freetime) {
				$bar = Freetime::getFreetimeBar($id_teacher, 'teacher');
			} else {
				$bar = [];
			}
			foreach (self::$weekdays_time as $day => $time_data) {
				foreach ($time_data as $time_index => $time_id) {
                    $result = dbConnection()->query("
						SELECT COUNT(*) AS cnt, g.id as id_group FROM group_time gt
						LEFT JOIN groups g ON g.id = gt.id_group
						WHERE g.id_teacher = {$id_teacher} AND g.ended = 0 AND gt.day=$day AND gt.time=$time_id AND g.year = ".self::BAR_YEAR."
					")->fetch_object();
					static::_brushBar($result, $with_freetime, $bar, $day, $time_id, $id_group);
                }
            }
            return $bar;
        }

        private static function _brushBar($result, $with_freetime, &$bar, $day, $time_id, $id_group)
        {
	        if ($result->cnt >= 1) {
                if ($result->cnt > 1) {
                    if ($with_freetime && $bar[$day][$time_id] !== 'empty') {
                        $bar[$day][$time_id] = 'blink red-green';
                        if ($id_group && $id_group != $result->id_group) {
	                    	$bar[$day][$time_id] = 'blink quater-red-green';
                    	}
                    } else {
                    	$bar[$day][$time_id] = 'blink red';
                    	if ($id_group && $id_group != $result->id_group) {
	                    	$bar[$day][$time_id] .= ' half-opacity';
                    	}
                    }
                } else {
                    if ($with_freetime && $bar[$day][$time_id] !== 'empty') {
                        $bar[$day][$time_id] = 'red-green';
                        if ($id_group && $id_group != $result->id_group) {
	                    	$bar[$day][$time_id] = 'quater-red-green';
                    	}
                    } else {
                    	$bar[$day][$time_id] = 'red';
                    	if ($id_group && $id_group != $result->id_group) {
	                    	$bar[$day][$time_id] .= ' half-opacity';
                    	}
                    }
                }
            } else {
				if ($with_freetime && $bar[$day][$time_id] !== 'empty') {
					if ($id_group && $id_group != $result->id_group) {
                    	$bar[$day][$time_id] .= ' half-opacity';
                	}
				} else {
					$bar[$day][$time_id] = 'gray';
				}
            }
        }

		public static function getCabinetBar($cabinet, $Group = null)
		{
		    $bar = [];

			foreach (self::$weekdays_time as $day => $time_data) {
				foreach ($time_data as $time_index => $time_id) {
					// подсчитываем кол-во групп в этом кабинете в это время
					$result = dbConnection()->query("
						SELECT COUNT(*) AS cnt FROM group_time gt
						LEFT JOIN groups g ON g.id = gt.id_group
						WHERE g.cabinet = $cabinet AND g.ended = 0 AND gt.day=$day AND gt.time=$time_id AND g.year = " . self::BAR_YEAR . " " . ($Group ? " AND g.id!={$Group->id}" : "")
					);

                    // если нет группы
                    if ($result === false) {
                        $groups_at_this_time_count = false;
                    } else {
                        $groups_at_this_time_count = $result->fetch_object()->cnt;
                    }

                    if ($groups_at_this_time_count >= 1) {
                        if ($groups_at_this_time_count > 1) {
                            if (static::_cabinetFree($Group, $day, $time_id)) {
			                    $bar[$day][$time_id] = 'blink red';
		                    } else {
			                    $bar[$day][$time_id] = 'half-opacity blink red';
		                    }
                        } else {
                            if (static::_cabinetFree($Group, $day, $time_id)) {
			                    $bar[$day][$time_id] = 'red';
		                    } else {
			                    $bar[$day][$time_id] = 'half-opacity red';
		                    }
                        }
                    } else {
	                    if (static::_cabinetFree($Group, $day, $time_id)) {
		                    $bar[$day][$time_id] = 'red';
	                    } else {
		                    $bar[$day][$time_id] = 'gray';
	                    }
                    }
                }
            }
            return $bar;
        }

        private static function _cabinetFree($Group, $day, $time_id)
        {
	        if ($Group && isset($Group->day_and_time[$day])) {
		    	if (in_array(self::TIME[$time_id], $Group->day_and_time[$day])) {
			    	return true;
		    	}
	        }
	        return false;
        }

		public static function checkFreeCabinets($id_group, $year, $day_and_time)
		{
			foreach($day_and_time as $day => $data) {
				foreach($data as $time) {
					$time_id = Freetime::getId($time);
					$conditions[] = "(gt.day=$day AND gt.time=$time_id)";
				}
			}
			// Получаем филиалы
			$branches = Branches::getBranches();

			foreach ($branches as $branch) {
				$id_branch = $branch['id'];
				$Cabinets = Cabinet::getBranchId($id_branch);
				foreach($Cabinets as $Cabinet) {
					$query = dbConnection()->query("
						SELECT g.id FROM group_time gt
						JOIN groups g ON g.id = gt.id_group
						WHERE g.id!=$id_group AND g.year=$year AND g.id_branch=$id_branch AND g.cabinet = {$Cabinet->id}
							AND (" . implode(" OR ", $conditions) . ")
						LIMIT 1
					");
					$return[$id_branch][$Cabinet->id] = $query->num_rows ? true : false;
				}
			}

			return $return;
		}
    }
