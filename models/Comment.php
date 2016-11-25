<?php
	class Comment extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/
        public static $cached_users = [];
		public static $mysql_table	= "comments";
		
		# Места, где отображаются комментарии
		const PLACE_STUDENT = 'STUDENT';
		const PLACE_REQUEST = 'REQUEST';
		const PLACE_GROUP 	= 'GROUP';
		const PLACE_TEACHER = 'TEACHER';

        public static $places = [
            'STUDENT',
            'REQUEST',
            'REQUEST_EDIT',
            'TESTING',
            'GROUP',
            'TEACHER',
            'TASK',
            'REVIEW',
        ];

		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array)
		{
			parent::__construct($array);
			
			$this->coordinates = $this->getCoordinates();

            if ($this->id_user) {
                if (!isset(self::$cached_users[$this->id_user])) {
                    self::$cached_users[$this->id_user] = User::findById($this->id_user);
                }
                $this->User = self::$cached_users[$this->id_user];
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