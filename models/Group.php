<?php
	class Group extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "groups";
		
		protected $_inline_data = ["students"];
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array)
		{
			parent::__construct($array);
			
			if (empty($this->students[0])) {
				$this->students = [];
			}
			
			$this->Teacher = Teacher::findById($this->id_teacher);
		}
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
		public function getSchedule()
		{
			return GroupSchedule::findAll([
				"condition" => "id_group=".$this->id
			]);
		}		
	}
	
	class GroupSchedule extends Model
	{
		public static $mysql_table	= "group_schedule";
		
		public function __construct($array)
		{
			parent::__construct($array);
			
			if ($this->time) {
				$this->time = mb_strimwidth($this->time, 0, 5);
			}
		}
		
		public static function getVocationDates()
		{
			$Vocations = self::findAll([
				"condition" => "id_group=0"
			]);
			
			$vocation_dates = [];
			
			foreach ($Vocations as $Vocation) {
				$vocation_dates[] = $Vocation->date;
			}
			
			return $vocation_dates;
		}
	}