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
		}
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
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
			self::deleteAll([
				"condition" => "id_teacher=$id_teacher"
			]);
			
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