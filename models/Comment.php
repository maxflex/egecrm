<?php
	class Comment extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "comments";
		
		# Места, где отображаются комментарии
		const PLACE_STUDENT = 'STUDENT';
		const PLACE_REQUEST = 'REQUEST';
		const PLACE_GROUP 	= 'GROUP';
		const PLACE_TEACHER = 'TEACHER';

		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array)
		{
			parent::__construct($array);
			
			$this->coordinates = $this->getCoordinates();
			
			if ($this->id_user) {
				$this->User = User::findById($this->id_user);
			}
		}
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		public static function getByPlace($place, $place_id)
		{
			return self::findAll([
				"condition" => "place='". $place ."' AND id_place=" . $place_id,
			]);
		}
				
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
		public function beforeSave()
		{
			$this->date 	= now();
			$this->id_user 	= User::fromSession()->id;
		}
		
		
		/**
		 * Получить отформатированные данные сохранившего.
		 * 
		 */
		public function getCoordinates()
		{
			return date("d.m.y в H:i", strtotime($this->date));
		}
		
	}