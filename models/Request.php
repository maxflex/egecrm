<?php
	class Request extends Model
	{

		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		const PER_PAGE = 20; // Сколько заявок отображать на странице списка заявок

		public static $mysql_table	= "requests";

		protected $_inline_data = ["subjects"]; // Предметы (в БД хранятся строкой "1, 2, 3" – а тут в массиве
		
		// Номера телефонов
		public static $_phone_fields = ["phone", "phone2", "phone3"];

		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array)
		{
			parent::__construct($array);

			// Если после создания нет ученика
			//if (!$this->id_student) {
			//	$this->id_student = Student::add()->id;
			//}

			// Таймстемп даты
			$this->date_timestamp = strtotime($this->date) . "000"; // добавляем миллесекунды, чтобы JS воспринимал timestamp

			if ($this->id_branch) {
				$this->addBranchInfo();
			}
			
			// Генерируем форматированные номера
			foreach (static::$_phone_fields as $phone_field) {
				if ($this->{$phone_field} != "") {
					$this->{$phone_field . "_formatted"} = formatNumber($this->{$phone_field});
				}
			}
			
			// Включаем связи
			$this->Student 			= Student::findById($this->id_student);
			$this->Notification 	= Notification::findById($this->id_notification);
			$this->Comments			= Comment::findAll([
				"condition" => "place='". Comment::PLACE_REQUEST ."' AND id_place=" . $this->id,
			]);
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
		
		public static function findByStudent($id_student)
		{
			return self::find([
				"condition" => "id_student=$id_student"
			]);			
		}

		/**
		 * Подсчитать количество новых заявок.
		 *
		 */
		public static function countNew()
		{
			return self::count([
				"condition"	=> "id_status=".RequestStatuses::NEWR." and adding=0"
			]); // ТОТ ЖЕ КОСТЫЛЬ
		}


		/**
		 * Получить количество заявок из каждого списка.
		 *
		 */
		public static function getAllStatusesCount()
		{
			foreach (RequestStatuses::$all as $id => $status) {
				if ($id == RequestStatuses::ALL) {
					$result[$id] = self::count(["condition" => "adding=0"]);
				} else {
					$result[$id] = self::count(["condition" => "adding=0 AND id_status=".$id]);
				}
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
				"condition"	=> "adding=0"
					. ($id_status == RequestStatuses::ALL ? "" : " AND id_status=".$id_status)
					. (empty($_COOKIE["id_user_list"]) ? "" : " AND id_user=".$_COOKIE["id_user_list"]) ,
				"order"		=> "date DESC",
				"group"		=> ($id_status == RequestStatuses::NEWR ? "id_student" : ""), // если список "неразобранные", то отображать дубликаты
				"limit" 	=> $start_from. ", " .self::PER_PAGE
			]);

			// Добавляем дубликаты
			foreach ($Requests as &$Request) {
				$Request->duplicates = $Request->getDuplicates();
				
				$Request->has_contract = $Request->hasContract();
				
				if ($Request->duplicates) {
					$Request->total_count = count($Request->duplicates) + 1;
				}

				if ($Request->id_status == RequestStatuses::NEWR && $id_status != RequestStatuses::ALL) {
					$Request->list_duplicates = $Request->countListDuplicates();
				}
				
				// дубликаты для подсветки
				foreach (static::$_phone_fields as $phone_field) {
					if (!empty($Request->{$phone_field})) {
						$Request->{$phone_field . "_duplicate"} = isDuplicate($Request->{$phone_field}, $Request->id);
					}
				}
			}
			
			return $Requests;
		}

		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		public function beforeSave() {
			if ($this->isNewRecord || $this->adding) {
				$this->date = now();
			}
			
			if (empty(trim($this->date))) {
				$this->date = now();
			}
			
			// Очищаем номера телефонов
			foreach (static::$_phone_fields as $phone_field) {
				$this->{$phone_field} = cleanNumber($this->{$phone_field});
			}
		}
		
		public function hasContract()
		{
			return Contract::count([
				"condition" => "id_student=" . $this->id_student
			]) > 0;
		}

		public function processIncoming()
		{
			// На всякий случай очищаем номер челефона (через "ч" написано специально)
			$this->phone = cleanNumber($this->phone);
			
			// Создаем нового ученика по заявке, либо привязываем к уже существующему
			$this->createStudent();
			
			
			// Устанавливаем статус заявки
			if (time() - $this->delay_time < 10) {
				$this->id_status = RequestStatuses::SPAM;	
			} else
			if ($this->_phoneExists()) {
				$this->id_status = RequestStatuses::DUPLICATE;
			}
		}

		private function _phoneExists()
		{
			// если номер телефона не установлен
			if (!$this->phone) {
				return false;
			}

			# Ищем заявку с таким же номером телефона
			$Request = Request::find([
				"condition"	=> "(phone='".$this->phone."' OR phone2='".$this->phone."'
					OR phone3='".$this->phone."') AND id_status=".RequestStatuses::NEWR,
			]);

			// Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
			if ($Request) {
				return true;
			}

			# Ищем ученика с таким же номером телефона
			$Student = Student::find([
				"condition"	=> "(phone='".$this->phone."' OR phone2='".$this->phone."'
				 	OR phone3='".$this->phone."')"
			]);

			// Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
			if ($Student && ($Student->getRequest()->id_status == RequestStatuses::NEWR)) {
				return true;
			}

			# Ищем представителя с таким же номером телефона
			$Representative = Representative::find([
				"condition"	=> "(phone='".$this->phone."' OR phone2='".$this->phone."'
				 	OR phone3='".$this->phone."')"
			]);

			// Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
			if ($Representative && ($Representative->getStudent()->getRequest()->id_status == RequestStatuses::NEWR)) {
				return true;
			}

			return false;
		}


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

		/**
		 * Добавить инфо по филиалу.
		 *
		 */
		public function addBranchInfo() {
			$this->Branch = [
				"name"	=> Branches::$all[$this->id_branch],
				"color"	=> Branches::metroSvg($this->id_branch, false, true),
			];
		}

		/**
		 * Привязать заявку к существующему ученику (склейка клиентов).
		 * $delete_original_student - по умолчанию ученик удаляется, если это его единственная заявка
		 */
		public function bindToStudent($id_student, $delete_original_student = false)
		{
			// если ученик есть и надо удалить
			if ($this->id_student && $delete_original_student && !$this->getDuplicates()) {
				Student::fullDelete($this->id_student);
			}
			// если у ученика после переноса нет заявок (и ученика не надо удалять), создаем пустую заявку
			if (!$delete_original_student  && !$this->getDuplicates()) {
				$data = $this->dbData();
				unset($data["id"]);
				Request::add($data);
			}
			
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
		
		public function getDuplicateComments($get_self = false)
		{
			$ids = self::getIds([
				"condition"	=> "adding=0 AND id_student=".$this->id_student.($get_self ? "" : " AND id!=".$this->id)
			]);
			
			foreach ($ids as $id) {
				$return[$id] = Comment::count(["condition" => "place='REQUEST' AND id_place=$id"]) > 0;
			}
			
			return $return;
		}

		/**
		 * Получить ID заявок от этого же ученика.
		 */
		public function countListDuplicates()
		{
			return self::count([
				"condition"	=> "adding=0 AND id_student=" . $this->id_student . " AND id!=" . $this->id ." AND id_status=" .RequestStatuses::NEWR,
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

			# Ищем заявку с таким же номером телефона
			$Request = Request::find([
				"condition"	=> "phone='".$this->phone."' OR phone2='".$this->phone."' || phone3='".$this->phone."'"
			]);

			// Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
			if ($Request) {
				$this->id_student = $Request->id_student;
				return true;
			}

			# Ищем ученика с таким же номером телефона
			$Student = Student::find([
				"condition"	=> "phone='".$this->phone."' OR phone2='".$this->phone."' || phone3='".$this->phone."'"
			]);

			// Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
			if ($Student) {
				$this->id_student = $Student->id;
				return true;
			}

			# Ищем представителя с таким же номером телефона
			$Representative = Representative::find([
				"condition"	=> "phone='".$this->phone."' OR phone2='".$this->phone."' || phone3='".$this->phone."'"
			]);

			// Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
			if ($Representative) {
				$this->id_student = $Representative->getStudent()->id;
				return true;
			}

			return false;
		}
	}
