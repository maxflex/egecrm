<?php
	class Teacher extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "teachers";
		
		protected $_inline_data = ["branches", "subjects"];

		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array)
		{
			parent::__construct($array);
			
			if (!$this->id_a_pers) {
				$this->id_a_pers = null;
			}
			
			foreach ($this->branches as $id_branch) {
				if (!$id_branch) {
					continue;
				}
				$this->branch_short[$id_branch] = Branches::getShortColoredById($id_branch);
			}
		}
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		public static function getActiveGroups()
		{
			$result = dbConnection()->query("
				SELECT id_teacher FROM groups
				WHERE (id_teacher!=0 AND id_teacher IS NOT NULL)
			");
			
			while ($row = $result->fetch_object()) {
				$ids[] = $row->id_teacher;
			}
			
			return self::findAll([
				"condition" => "id IN (" . implode(",", $ids) . ")"
			]);
		}
		
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
		/**
		 * Сколько номеров установлено.
		 * 
		 */
		public function phoneLevel()
		{
			if (!empty($this->phone3)) {
				return 3;
			} else
			if (!empty($this->phone2)) {
				return 2;
			} else {
				return 1;
			}
		}
		
		public function getInitials()
		{
			return $this->last_name . " " . mb_substr($this->first_name, 0, 1, 'utf-8') . ". " . mb_substr($this->middle_name, 0, 1, 'utf-8') . ".";
		}
		
		/**
		 * Получить свободное время ученика.
		 * 
		 */
		public function getFreetime()
		{
			$Freetime = TeacherFreetime::findAll([
				"condition"	=> "id_teacher=" . $this->id
			]);
			
			if (!$Freetime) {
				return [];
			}
			
			foreach ($Freetime as $FreetimeData) {
				$index = Freetime::getIndexByTime($FreetimeData->time);
				$return[$FreetimeData->id_branch][$FreetimeData->day][$index] = $FreetimeData->time;
			}
			
			return $return;
		}	
	}
	
	
	
	class TeacherFreetime extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "teacher_freetime";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		public static function getOrange($id_group, $id_branch, $id_teacher, $teacher_freetime_red, $teacher_freetime_red_full)
		{
			foreach (Freetime::$weekdays as $day => $schedule) {
				foreach ($schedule as $time) {
					// текущий кирпич не должен быть занят в другой группе у этого преподавателя
					if (!$time || in_array($time, $teacher_freetime_red[$day])) {
						continue;		
					}
					
					// текущий кирпич обязательно должен соседствовать с красным кирпичом в рамках одного дня
					$red_neighbour = false;
					
					$current_index = array_search($time, Freetime::$weekdays[$day]);
					
					# проверяем следующий день
					$red_neighbour_right 		= false;
					$red_neighbour_right_data 	= false;
					if ($current_index < 3) {
						$red_neighbour_right = in_array(Freetime::$weekdays[$day][$current_index + 1], $teacher_freetime_red[$day]);
						if ($red_neighbour_right) {
							// сохраняем данные найденного справа красного кирпича
							$red_neighbour_right_data = [
								"day" 	=> $day,
								"time"	=> Freetime::$weekdays[$day][$current_index + 1],
							];
						}
					}
					
					# проверяем предыдущий день
					$red_neighbour_left 	= false;
					$red_neighbour_left_data= false;
					if ($current_index > 0) {
						$red_neighbour_left = in_array(Freetime::$weekdays[$day][$current_index - 1], $teacher_freetime_red[$day]);
						if ($red_neighbour_left) {
							// сохраняем данные найденного слева красного кирпича
							$red_neighbour_left_data = [
								"day" 	=> $day,
								"time"	=> Freetime::$weekdays[$day][$current_index - 1],
							];
						}
					}
					
					// если нашелся красный сосед, идем дальше
					if ($red_neighbour_left || $red_neighbour_right) {
						// филиал текущей группы должен отличаться от филиала группы соседствующего красного кирпича
						if ($red_neighbour_left) {
							$is_orange = self::_branchDifferent($id_group, $id_branch, $id_teacher, $red_neighbour_left_data['day'], $red_neighbour_left_data['time']);
							
							if ($is_orange) {
								if (in_array(Freetime::$weekdays[$day][$current_index - 1], $teacher_freetime_red_full[$day])) {
									$return_full[$day][] = $time; // добавляем оранжевое время	
								} else {
									$return_half[$day][] = $time; // добавляем оранжевое время	
								}
								continue;
							}
						}
						
						// филиал текущей группы должен отличаться от филиала группы соседствующего красного кирпича
						if ($red_neighbour_right) {
							$is_orange = self::_branchDifferent($id_group, $id_branch, $id_teacher, $red_neighbour_right_data['day'], $red_neighbour_right_data['time']);
							if ($is_orange) {
								if (in_array(Freetime::$weekdays[$day][$current_index + 1], $teacher_freetime_red_full[$day])) {
									$return_full[$day][] = $time; // добавляем оранжевое время	
								} else {
									$return_half[$day][] = $time; // добавляем оранжевое время	
								}
							}
						}
					}
				}
			}
			
			return [
				"half" 	=> $return_half,
				"full"	=> $return_full,
			];
		}
		
		// подфункция проверки, что другой филиал
		private static function _branchDifferent($id_group, $id_branch, $id_teacher, $day, $time)
		{
			return dbConnection()->query("
				SELECT g.id FROM groups g
					LEFT JOIN group_time gt ON gt.id_group = g.id
				WHERE g.id != $id_group AND g.id_branch != $id_branch AND gt.day = '$day' AND gt.time = '$time' AND g.id_teacher = $id_teacher
			")->num_rows;
		}
		
		public static function get($id_teacher)
		{
			$Freetime = TeacherFreetime::findAll([
				"condition"	=> "id_teacher=" . $id_teacher
			]);
			
			if (!$Freetime) {
				return [];
			}
			
			foreach ($Freetime as $FreetimeData) {
				$return[$FreetimeData->day][] = $FreetimeData->time;
/*
				
				if (!in_array($FreetimeData->time, $return[0][$FreetimeData->day])) {
					$return[0][$FreetimeData->day][] = $FreetimeData->time;
				}
*/
			}
			
			return $return;
		}
		
		
		/**
		 * 
		 */
		public static function getRed($id_group, $id_teacher) 
		{
			foreach (Freetime::$weekdays as $day => $schedule) {
				foreach ($schedule as $time) {
					if (TeacherFreetime::inRed($id_group, $id_teacher, $day, $time)) {
						if (!in_array($time, $return_red[$day])) {
							$return_red[$day][] = $time;
						}
					}
				}
			}
			
			return $return_red;
		}
		
		/**
		 * 
		 */
		public static function getRedFull($id_group, $id_teacher) 
		{
			foreach (Freetime::$weekdays as $day => $schedule) {
				foreach ($schedule as $time) {
					if (TeacherFreetime::inRedFull($id_group, $id_teacher, $day, $time)) {
						if (!in_array($time, $return_red[$day])) {
							$return_red[$day][] = $time;
						}
					}
				}
			}
			
			return $return_red;
		}
		
		/**
		 * Если ученик присутствует в группе и вместе с этим у него стоит метка "полностью согласен", 
		   если у этого ученика есть другие группы, то в них расписании у него соответствующий кирпичик должен быть красным.
		 * 
		 */
		public static function inRedFull($id_group,$id_teacher, $day, $time) 
		{
			return dbConnection()->query("
				SELECT g.id FROM group_teacher_statuses gts
					LEFT JOIN groups g ON g.id = gts.id_group
					LEFT JOIN group_time gt ON g.id = gt.id_group
				WHERE g.id != $id_group AND gt.time = '$time' AND gt.day = '$day' AND gts.id_status = ". GroupTeacherStatuses::AGREED ." AND gts.id_teacher = $id_teacher
				LIMIT 1
			")->num_rows;	
		}
		
		public static function inRed($id_group, $id_teacher, $day, $time)
		{
			return dbConnection()->query("
				SELECT g.id FROM groups g
					LEFT JOIN group_time gt ON g.id = gt.id_group
				WHERE g.id != $id_group AND gt.time = '$time' AND gt.day = '$day' AND g.id_teacher=$id_teacher 
				LIMIT 1
			")->num_rows;
		}
		
		/**
		 * Добавить свободное время
		 * 
		 */
		public static function addData($data, $id_teacher) 
		{
			TeacherFreetime::deleteAll([
				"condition" => "id_teacher=$id_teacher"
			]);
			
// 			dbConnection()->query("DELETE FROM teacher_freetime WHERE id_teacher=$id_teacher");
			
			foreach ($data as $id_branch => $branch_data) {
				foreach ($branch_data as $day => $day_data) {
					foreach ($day_data as $time) {
						if (empty(trim($time))) {
							continue;
						}
						$Freetime = new self([
							"id_teacher"	=> $id_teacher,
							"id_branch"		=> $id_branch,
							"day"			=> $day,
							"time"			=> $time,
						]);
						
						$Freetime->save();
					}
				}
			}
		}
	}