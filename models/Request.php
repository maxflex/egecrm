<?php
	class Request extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		const PER_PAGE = 20; // Сколько заявок отображать на странице списка заявок
		
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
		 * Получить статус задачи (список) из $_GET
		 * 
		 */
		public static function getIdStatus()
		{
			// Получаем список
			$id_status = constant('RequestStatuses::' . strtoupper($_GET['id_status']));
			
			// если ID статус пустой, то по умолчанию отображать новые заявки
			if (empty($id_status)) {
				$id_status = RequestStatuses::NEWR;
			}
			
			return $id_status;
		}
		
		/**
		 * Подсчитать количество новых заявок.
		 * 
		 */
		public static function countNew()
		{
			return self::count([
				"condition"	=> "id_status=".RequestStatuses::NEWR." and adding=0"
			]);
		}
		
		
		/**
		 * Получить количество заявок из каждого списка.
		 * 
		 */
		public static function getAllStatusesCount()
		{
			foreach (RequestStatuses::$all as $id => $status) {
				$result[$id] = self::count(["condition" => "adding=0 AND id_status=".$id]);
			}
			
			return $result;
		}
		
		
		
		/**
		 * Получить заявки по номеру страницы и ID списка из RequestStatuses Factory.
		 * 
		 */
		public static function getByPage($page, $id_status)
		{
			// С какой записи начинать отображение, по формуле
			$start_from = ($page - 1) * self::PER_PAGE;
			
			$Requests = self::findAll([
				"condition"	=> "id_status=".$id_status." AND adding=0",
				"order"		=> "id DESC",
				"limit" 	=> $start_from. ", " .self::PER_PAGE
			]);
			
			// Добавляем дубликаты
			foreach ($Requests as &$Request) {
				$Request->duplicates = $Request->getDuplicates();	
			}
			
			return $Requests;
		}
		
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
		public function beforeSave() {
			if ($this->isNewRecord) {
				$this->date = now();
			} 			
/*
			else {
				// если не первое сохранение, то всё, забей – уже не добавление
				$this->adding = 0;
			}
*/	
		}
		
		/**
		 * Привязать заявку к существующему ученику (склейка клиентов).
		 * 
		 */
		public function bindToStudent($id_student)
		{
			$this->id_student = $id_student;
			return ($this->save("id_student") > 0 ? true : false);
		}
		
		/**
		 * Получить ID заявок от этого же ученика.
		 * $get_self – включать свой же ID в список дубликатов?
		 */
		public function getDuplicates($get_self = false)
		{
			return self::getIds([
				"condition"	=> "adding=0 AND id_student=".$this->id_student.($get_self ? "" : " AND id!=".$this->id)
			]);
		}
		
		
		/**
		 * Сгенерировать HTML дубликатов через запятую.
		 * 
		 * @access public
		 * @return void
		 */
		public function generateDuplicatesHtml()
		{
			// Ищем дубликаты
			$request_duplicates = $this->getDuplicates();
			
			// Если дубликаты нашлись
			if ($request_duplicates) {
				foreach ($request_duplicates as $id_request) {
					$html .= "<a class='link-white' href='requests/edit/$id_request'>#$id_request</a>, ";
				}
				// Удаляем последнюю запятую
				$html = rtrim($html, ", ");
				return "<span class='pull-right'>Другие заявки этого клиента: $html</span>";
			}
		}
		
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
			// если номер телефона не установлен
			if (!$this->phone) {
				return false;
			}
			
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