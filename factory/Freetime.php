<?php
	class Freetime
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/
		
		
		// ID => TIME
		const TIME = [
			1 => "16:15",
			2 => "18:40",
			3 => "11:00",
			4 => "13:30",
			5 => "16:00",
			6 => "18:30",
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
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/

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
	}
	
	class TeacherFreetime
	{
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		public static function getOrange($id_group, $id_branch, $id_student, $teacher_freetime_red, $teacher_freetime_red_full)
		{
			foreach (Freetime::$weekdays_time as $day => $time_array) {
				foreach ($time_array as $time) {
					// текущий кирпич не должен быть занят в другой группе у этого преподавателя
					if (in_array($time, $teacher_freetime_red[$day])) {
						continue;		
					}
					
					// текущий кирпич обязательно должен соседствовать с красным кирпичом в рамках одного дня
					$red_neighbour = false;
					
					$current_index = array_search($time, Freetime::$weekdays_time[$day]);

					# проверяем следующий день
					$red_neighbour_right 		= false;
					$red_neighbour_right_data 	= false;
					if ( ($day < 6 && $current_index < 1) || ($day >= 6 && $current_index < 3) ) {
						$red_neighbour_right = in_array(Freetime::$weekdays_time[$day][$current_index + 1], $teacher_freetime_red[$day]);
						if ($red_neighbour_right) {
							// сохраняем данные найденного справа красного кирпича
							$red_neighbour_right_data = [
								"day" 	=> $day,
								"time"	=> Freetime::$weekdays_time[$day][$current_index + 1],
							];
						}
					}
					
					# проверяем предыдущий день
					$red_neighbour_left 	= false;
					$red_neighbour_left_data= false;
					if ($current_index > 0) {
						$red_neighbour_left = in_array(Freetime::$weekdays_time[$day][$current_index - 1], $teacher_freetime_red[$day]);
						if ($red_neighbour_left) {
							// сохраняем данные найденного слева красного кирпича
							$red_neighbour_left_data = [
								"day" 	=> $day,
								"time"	=> Freetime::$weekdays_time[$day][$current_index - 1],
							];
						}
					}
					// если нашелся красный сосед, идем дальше
					if ($red_neighbour_left || $red_neighbour_right) {
						// филиал текущей группы должен отличаться от филиала группы соседствующего красного кирпича
						if ($red_neighbour_left) {
							$is_orange = self::_branchDifferent($id_group, $id_branch, $id_student, $red_neighbour_left_data['day'], $red_neighbour_left_data['time']);
							
							if ($is_orange) {
								if (in_array(Freetime::$weekdays_time[$day][$current_index - 1], $teacher_freetime_red_full[$day])) {
									$return_full[$day][] = $time; // добавляем оранжевое время	
								} else {
									$return_half[$day][] = $time; // добавляем оранжевое время	
								}
								continue;
							}
						}
						
						// филиал текущей группы должен отличаться от филиала группы соседствующего красного кирпича
						if ($red_neighbour_right) {
							$is_orange = self::_branchDifferent($id_group, $id_branch, $id_student, $red_neighbour_right_data['day'], $red_neighbour_right_data['time']);
							if ($is_orange) {
								if (in_array(Freetime::$weekdays_time[$day][$current_index + 1], $teacher_freetime_red_full[$day])) {
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
		
		
		/**
		 * 
		 */
		public static function getRed($id_group, $id_teacher) 
		{
			foreach (Freetime::$weekdays_time as $day => $time_array) {
				foreach ($time_array as $time) {
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
			foreach (Freetime::$weekdays_time as $day => $time_array) {
				foreach ($time_array as $time) {
					if (TeacherFreetime::inRedFull($id_group, $id_teacher, $day, $time)) {
						if (!in_array($time, $return_red[$day])) {
							$return_red[$day][] = $time;
						}
					}
				}
			}
			
			return $return_red;
		}
		
		public static function getRedAll($id_group, $id_teacher)
		{
			foreach (Freetime::$weekdays_time as $day => $time_array) {
				foreach ($time_array as $time) {
					$count = TeacherFreetime::inRed($id_group, $id_teacher, $day, $time);
					if ($count) {
						if (!in_array($time, $return_red_half[$day])) {
							$return_red_half[$day][] = $time;
						}
					}
					if ($count > 1) {
						if (!in_array($time, $red_doubleblink[$day])) {
							$red_doubleblink[$day][] = $time;
						}
					}
					/************************************************************************/
					$count = TeacherFreetime::inRedFull($id_group, $id_teacher, $day, $time);
					if ($count) {
						if (!in_array($time, $return_red_full[$day])) {
							$return_red_full[$day][] = $time;
						}
					}
					if ($count > 1) {
						if (!in_array($time, $red_doubleblink[$day])) {
							$red_doubleblink[$day][] = $time;
						}
					}
				}
			}
			
			return [
				'red_half' 			=> $return_red_half,
				'red_full'			=> $return_red_full,
				'red_doubleblink'	=> $red_doubleblink,
			];
		}
		
		/**
		 * Если ученик присутствует в группе и вместе с этим у него стоит метка "полностью согласен", 
		   если у этого ученика есть другие группы, то в них расписании у него соответствующий кирпичик должен быть красным.
		 * 
		 */
		public static function inRedFull($id_group,$id_teacher, $day, $time) 
		{
			return dbConnection()->query("
				SELECT g.id FROM group_agreement ga
					LEFT JOIN groups g ON g.id = ga.id_group
					LEFT JOIN group_time gt ON g.id = gt.id_group
				WHERE g.id != $id_group AND gt.time = '$time' AND gt.day = '$day' AND ga.id_status = ". GroupTeacherStatuses::AGREED ." 
					AND ga.id_entity = $id_teacher AND ga.type_entity='TEACHER'
			")->num_rows;	
		}
		
		public static function inRed($id_group, $id_teacher, $day, $time)
		{
			return dbConnection()->query("
				SELECT g.id FROM groups g
					LEFT JOIN group_time gt ON g.id = gt.id_group
				WHERE g.id != $id_group AND gt.time = '$time' AND gt.day = '$day' AND g.id_teacher=$id_teacher 
			")->num_rows;
		}
	}