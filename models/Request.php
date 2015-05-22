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
			
			// Если после создания нет ученика
			if (!$this->id_student) {
				$this->id_student = Student::add()->id;
			}
			
			// Включаем связи
			$this->Student 			= Student::findById($this->id_student);
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
		 * Создать ученика для заявки. Пустой ученик создается обязательно вместе с новой заявкой
		 * Это нужно по ряду вещей: чтобы заявки сливались, чтобы сохранялись поля в редактировании и т.д.
		 */
		public function createStudent()
		{
			// Перед созданием ученика заявки смотрим, может быть
			// это дублирующаяся заявка и ученик уже существует
			if (!$this->bindToExistingStudent()) {
				// если заявка от нового ученика, создаем нового пустого ученика
				$this->id_student = Student::add()->id;
			}
		}
		
		/**
		 * Привязать заявку к существующему студенту по номеру телефона.
		 * 
		 */
		public function bindToExistingStudent()
		{
			// Ищем заявку с таким же номером телефона
			$Request = Request::find([
				"condition"	=> "phone='".$this->phone."'"
			]);
			
			// Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
			if ($Request) {
				$this->id_student = $Request->id_student;
				return true;
			} else {
				return false;
			}
		}
		
	}