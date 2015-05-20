<?php
	class Request extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "requests";
		
		protected $_inline_data = ["subjects"]; // Предметы (в БД хранятся строкой "1, 2, 3" – а тут в массиве
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array) 
		{
			parent::__construct($array);
			
			// Включаем связи
			$this->Student 			= Student::findById($this->id_student);
			$this->Representative	= Representative::findById($this->id_representative);
			$this->Contract			= Contract::findById($this->id_contract);
			$this->Notification 	= Notification::findById($this->id_notification);
		}
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		
		/**
		 * Подсчитать количество новых заявок.
		 * 
		 */
		public static function countNew()
		{
			return self::findAll([
				"condition"	=> "id_status=".RequestStatuses::NEWR
			], true);
		}
		
		
		/**
		 * Получить количество заявок из каждого списка.
		 * 
		 */
		public static function getAllStatusesCount()
		{
			foreach (RequestStatuses::$all as $id => $status) {
				$result[$id] = self::count(["condition" => "id_status=".$id]);
			}
			
			return $result;
		}
		
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
		/**
		 * Найти все платежи заявки.
		 * 
		 */
		public function getPayments()
		{
			return Payment::findAll([
				"condition" => "deleted=0 AND id_request=" . $this->id
			]);
		}
	
	}