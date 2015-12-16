<?php
	class ExamDay extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "exam_days";
		
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array)
		{
			parent::__construct($array);
		}
		
		
		public static function addData($data)
		{
			self::deleteAll();
			
			foreach($data as $grade => $d) {
				foreach($d as $id_subject => $date) {
					if ($date) {
						self::add([
							"id_subject"	=> $id_subject,
							"date"			=> $date,
							"grade"			=> $grade,
						]);
					}
				}
			}
		}
		
		public static function getData()
		{
			$data = self::findAll();
			
			foreach($data as $d) {
				$return[$d->grade][$d->id_subject] = $d->date;
			}
			
			return $return;
		}
		
		public static function getExamDates()
		{
			$data = self::findAll();
			
			foreach($data as $d) {
				$return[] = date("Y-m-d", strtotime($d->date));
			}
			
			return $return;
		}
		
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
	}