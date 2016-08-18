<?php
	class Freetime
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/
		const BAR_YEAR = 2016;
		
		// ID => TIME
		const TIME = [
			1 => "16:15",
			2 => "18:40",
			3 => "11:00",
			4 => "13:30",
			5 => "16:00",
			6 => "18:30",
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
			1 => [1, 2],
			2 => [1, 2],
			3 => [1, 2],
			4 => [1, 2],
			5 => [1, 2],
			6 => [3, 4, 5, 6],
			7 => [3, 4, 5, 6],
		];
		
		public static $weekdays = [
			1 => ["", "", self::TIME[1], self::TIME[2]],
			2 => ["", "", self::TIME[1], self::TIME[2]],
			3 => ["", "", self::TIME[1], self::TIME[2]],
			4 => ["", "", self::TIME[1], self::TIME[2]],
			5 => ["", "", self::TIME[1], self::TIME[2]],
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
						
			foreach (self::$weekdays as $day => $time_data) {
				echo "<optgroup label='" . self::DAYS_FULL[$day] . "'>";
				foreach ($time_data as $time_index => $time) {
					if (empty($time)) {
						continue;
					}
					$value = "{$day}-{$time_index}";
					// если это массив выбранных элементов (при $multiple = true)
					// $option_selected = in_array($time_id, $selected);
					$option_selected = $selected == $value;
					
					// если опция не удалена (если удалена, то отображается только в том случае, если удаленный вариант был выбран ранее)
					echo "<option ".($option_selected ? "selected" : "")." value='{$value}'>" . $time ."</option>";
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

		public static function getStudentFreetimeBar($id_student)
		{
			foreach (self::$weekdays_time as $day => $time_data) {
				foreach ($time_data as $time_index => $time_id) {
					$bar[$day][$time_id] = static::_studentHasFreetime($id_student, $day, $time_id) ? 'green' : 'empty';
				}
			}
			return $bar;
		}

		/*
		 * В этот день свободно
		 */
		private static function _studentHasFreetime($id_student, $day, $time_id)
		{
			return dbConnection()->query("SELECT id FROM students_freetime WHERE id_student={$id_student} AND day={$day} AND time_id={$time_id}")->num_rows;
		}


		public static function getStudentBar($id_student)
		{
		    $bar = [];
			foreach (self::$weekdays_time as $day => $time_data) {
				foreach ($time_data as $time_index => $time_id) {
//					// подгоняем правильный $time_index
//					if ($day <= 5) {
//						$correct_time_index = $time_index + 2;
//					} else {
//						$correct_time_index = $time_index;
//					}

                    $result = dbConnection()->query("
						SELECT COUNT(*) AS cnt FROM group_time gt
						LEFT JOIN groups g ON g.id = gt.id_group
						WHERE FIND_IN_SET({$id_student}, g.students) AND g.ended = 0 AND gt.day=$day AND gt.time=$time_id AND g.year = ".self::BAR_YEAR."
					");

                    // если нет группы
                    if ($result === false) {
                        $groups_at_this_time_count = false;
                    } else {
                        $groups_at_this_time_count = $result->fetch_object()->cnt;
                    }

                    if ($groups_at_this_time_count >= 1) {
                        if ($groups_at_this_time_count > 1) {
                            $bar[$day][$time_id] = 'blink red';
                        } else {
                            $bar[$day][$time_id] = 'red';
                        }
                    } else {
                        $bar[$day][$time_id] = 'gray';
                    }
                }
            }
            return $bar;
        }

		public static function getTeacherBar($id_teacher)
		{
		    $bar = [];

			foreach (self::$weekdays_time as $day => $time_data) {
				foreach ($time_data as $time_index => $time_id) {
//					// подгоняем правильный $time_index
//					if ($day <= 5) {
//						$correct_time_index = $time_index + 2;
//					} else {
//						$correct_time_index = $time_index;
//					}

                    $result = dbConnection()->query("
						SELECT COUNT(*) AS cnt FROM group_time gt
						LEFT JOIN groups g ON g.id = gt.id_group
						WHERE g.id_teacher = {$id_teacher} AND g.ended = 0 AND gt.day=$day AND gt.time=$time_id AND g.year = ".self::BAR_YEAR."
					");

                    // если нет группы
                    if ($result === false) {
                        $groups_at_this_time_count = false;
                    } else {
                        $groups_at_this_time_count = $result->fetch_object()->cnt;
                    }

                    if ($groups_at_this_time_count >= 1) {
                        if ($groups_at_this_time_count > 1) {
                            $bar[$day][$time_id] = 'blink red';
                        } else {
                            $bar[$day][$time_id] = 'red';
                        }
                    } else {
                        $bar[$day][$time_id] = 'gray';
                    }
                }
            }
            return $bar;
        }

		public static function getCabinetBar($cabinet)
		{
		    $bar = [];

			foreach (self::$weekdays_time as $day => $time_data) {
				foreach ($time_data as $time_index => $time_id) {

					// подсчитываем кол-во групп в этом кабинете в это время
					$result = dbConnection()->query("
						SELECT COUNT(*) AS cnt FROM group_time gt
						LEFT JOIN groups g ON g.id = gt.id_group
						WHERE g.cabinet = $cabinet AND g.ended = 0 AND gt.day=$day AND gt.time=$time_id AND g.year = ".self::BAR_YEAR."
					");

                    // если нет группы
                    if ($result === false) {
                        $groups_at_this_time_count = false;
                    } else {
                        $groups_at_this_time_count = $result->fetch_object()->cnt;
                    }

                    if ($groups_at_this_time_count >= 1) {
                        if ($groups_at_this_time_count > 1) {
                            $bar[$day][$time_id] = 'blink red';
                        } else {
                            $bar[$day][$time_id] = 'red';
                        }
                    } else {
                        $bar[$day][$time_id] = 'gray';
                    }
                }
            }

            return $bar;
        }
    }