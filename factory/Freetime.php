<?php
	class Freetime
	{

		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $title = "время занятия";

		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/

		/**
		 * Построить селектор
		 * $multiple - множественный выбор
		 * @time-refactored @time-checked селектор времени в списке групп
		 */
		public static function buildMultiSelector($selected = false, $attrs, $multiple = false)
		{
			$Time = Time::getLight();

			echo "<select ".($multiple ? "multiple" : "")." class='form-control watch-select' ".Html::generateAttrs($attrs).">";

			// Заголовок
			if (!$multiple) {
				echo "<option selected style='cursor: default; outline: none' value=''>". static::$title ."</option>";
				echo "<option disabled style='cursor: default' value=''>──────────────</option>";
			}

			foreach (Time::MAP as $day => $time_data) {
				echo "<optgroup label='" . Time::WEEKDAYS_FULL[$day] . "'>";
				foreach ($time_data as $id_time) {
					// если это массив выбранных элементов (при $multiple = true)
					$option_selected = $selected == $id_time;

					// если опция не удалена (если удалена, то отображается только в том случае, если удаленный вариант был выбран ранее)
					echo "<option ".($option_selected ? "selected" : "")." value='{$id_time}'>" . $Time[$id_time] ."</option>";
				}
				echo '</optgroup>';
			}
			echo "</select>";
		}

		/**
		 * @refactored
		 */
		public static function getFreetimeBar($id_entity, $type_entity)
		{
			foreach(Time::MAP as $day => $data) {
				foreach ($data as $id_time) {
				    if ($id_time < 10) {
                        $id_time = '0'.$id_time;
                    }
					$bar[$day][$id_time] = EntityFreetime::hasFreetime($id_entity, $type_entity, $id_time) ? 'green' : 'empty';
				}
			}
			return $bar;
		}

        // @time-refactored @time-checked
		public static function getStudentBar($id_student, $with_freetime = false, $id_group = false)
		{
			if ($with_freetime) {
				$bar = Freetime::getFreetimeBar($id_student, 'student');
			} else {
				$bar = [];
			}

			foreach(Time::MAP as $day => $data) {
				foreach ($data as $id_time) {
                    if ($id_time < 10) {
                        $id_time = '0'.$id_time;
                    }
				    $result = dbConnection()->query("
						SELECT COUNT(*) AS cnt, g.id as id_group, gt.id_cabinet FROM group_time gt
						LEFT JOIN groups g ON g.id = gt.id_group
						WHERE FIND_IN_SET({$id_student}, g.students) AND g.ended = 0 AND gt.id_time=$id_time
					")->fetch_object();
                    static::_brushBar($result, $with_freetime, $bar, $day, $id_time, $id_group);
                }
            }
            return $bar;
        }

        // @time-refactored @time-checked
		public static function getTeacherBar($id_teacher, $with_freetime = false, $id_group = false)
		{
		    if ($with_freetime) {
				$bar = Freetime::getFreetimeBar($id_teacher, 'teacher');
			} else {
				$bar = [];
			}
			// кол-во групп в предыдущей итерации
			$previous_group_cnt = 0;
			foreach(Time::MAP as $day => $data) {
				foreach ($data as $id_time) {
                    if ($id_time < 10) {
                        $id_time = '0'.$id_time;
                    }
                    $result = dbConnection()->query("
						SELECT COUNT(*) AS cnt, g.id as id_group FROM group_time gt
						LEFT JOIN groups g ON g.id = gt.id_group
						WHERE g.id_teacher = {$id_teacher} AND g.ended = 0 AND gt.id_time={$id_time} AND g.year = ".Years::getAcademic()."
					")->fetch_object();
					static::_brushBar($result, $previous_group_cnt, $bar, $day, $id_time, $id_group);
					$previous_group_cnt = $result->cnt;
                }
            }
            return $bar;
        }

        private static function _brushBar($result, $previous_group_cnt, &$bar, $day, $id_time, $id_group)
        {
            // зуб находится на позиции будни 17:20 и 18:40?
            if (in_array($id_time, [29, 30, 31, 32, 33, 4, 8, 12, 16, 20])) {
                // текущий зуб – зуб хотя бы 2 групп?
				if ($result->cnt >= 2) {
					$bar[$day][$id_time] = 'blink red';
				} else {
					// текущий зуб – зуб хотя бы 1 группы?
					if ($result->cnt >= 1) {
						// слева есть зуб хотя бы 1 группы?
						if ($previous_group_cnt > 1) {
							$bar[$day][$id_time] = 'blink red';
						} else {
							$bar[$day][$id_time] = 'branch-' . Cabinet::getField($result->id_cabinet, 'id_branch');
						}
					} else {
						// слева есть зуб хотя бы 1 группы?
						if ($previous_group_cnt > 1) {
							$bar[$day][$id_time] = 'branch-' . Cabinet::getField($result->id_cabinet, 'id_branch');
						} else {
							$bar[$day][$id_time] = 'gray';
						}
					}
				}
            } else {
				// текущий зуб – зуб хотя бы 2 групп?
				if ($result->cnt >= 2) {
					$bar[$day][$id_time] = 'blink red';
				} else {
					// текущий зуб – зуб хотя бы 1 группы?
					if ($result->cnt >= 1) {
						$bar[$day][$id_time] = 'branch-' . Cabinet::getField($result->id_cabinet, 'id_branch');
					} else {
						$bar[$day][$id_time] = 'gray';
					}
				}
			}
        }

		// @time-refactored @time-checked multiple cabinets
		public static function getCabinetBar($Group, $cabinet = null)
		{
		    $bars = [];

			if ($cabinet) {
				$cabinet_ids = [$cabinet];
			} else {
				$cabinet_ids = Group::getCabinetIds($Group->id);
			}

			foreach($cabinet_ids as $id_cabinet) {
				foreach(Time::MAP as $day => $data) {
					foreach ($data as $id_time) {
                        if ($id_time < 10) {
                            $id_time = '0'.$id_time;
                        }
						// подсчитываем кол-во групп в этом кабинете в это время
						$result = dbConnection()->query("
							SELECT COUNT(*) AS cnt FROM group_time gt
							LEFT JOIN groups g ON g.id = gt.id_group
							WHERE gt.id_cabinet = $id_cabinet AND g.ended = 0 AND gt.id_time={$id_time} AND g.year = " . Years::getAcademic() . " " . ($Group ? " AND g.id!={$Group->id}" : "")
						);

	                    // если нет группы
	                    if ($result === false) {
	                        $groups_at_this_time_count = false;
	                    } else {
	                        $groups_at_this_time_count = $result->fetch_object()->cnt;
	                    }

	                    if ($groups_at_this_time_count >= 1) {
	                        if ($groups_at_this_time_count > 1) {
	                            if (static::_cabinetFree($Group, $day, $id_time, $id_cabinet)) {
				                    $bars[$id_cabinet][$day][$id_time] = 'blink red';
			                    } else {
				                    $bars[$id_cabinet][$day][$id_time] = 'half-opacity blink red';
			                    }
	                        } else {
	                            if (static::_cabinetFree($Group, $day, $id_time, $id_cabinet)) {
				                    $bars[$id_cabinet][$day][$id_time] = 'red';
			                    } else {
				                    $bars[$id_cabinet][$day][$id_time] = 'half-opacity red';
			                    }
	                        }
	                    } else {
		                    if (static::_cabinetFree($Group, $day, $id_time, $id_cabinet)) {
			                    $bars[$id_cabinet][$day][$id_time] = 'red';
		                    } else {
			                    $bars[$id_cabinet][$day][$id_time] = 'gray';
		                    }
	                    }
	                }
	            }
			}
            return ($cabinet ? $bars[$cabinet] : $bars);
        }

        private static function _cabinetFree($Group, $day, $id_time, $id_cabinet)
        {
	        if ($Group && isset($Group->day_and_time[$day])) {
				return static::_hasTimeid($Group, $day, $id_time, $id_cabinet);
	        }
	        return false;
        }

		/**
		 * В группе есть указанные timeid
		 */
		private static function _hasTimeid($Group, $day, $id_time, $id_cabinet)
		{
			$a = array_filter($Group->day_and_time[$day], function($d) use ($id_time, $id_cabinet) {
				return ($d->id_time == $id_time && $d->id_cabinet == $id_cabinet);
			});
			return count($a) > 0;
		}

		// @time-refactored @time-checked
		public static function checkFreeCabinets($id_group, $year)
		{
            $exclude_group_sql = " 1 ";
		    if ($id_group) {
		        $exclude_group_sql = " g.id != $id_group ";
            }
			$Cabinets = Cabinet::findAll();
			foreach(Time::getLight() as $id_time => $time) {
				foreach($Cabinets as $Cabinet) {
					$sql = "
						SELECT g.id FROM group_time gt
						JOIN groups g ON g.id = gt.id_group
						WHERE {$exclude_group_sql} AND g.year=$year AND gt.id_cabinet={$Cabinet->id}
						AND gt.id_time = {$id_time}
						LIMIT 1
					";
					$query = dbConnection()->query($sql);
					$return[$id_time][$Cabinet->id] = $query->num_rows ? true : false;
				}
			}
			return $return;
		}
    }
