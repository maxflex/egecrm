<?php
	class Request extends Model
	{

	    public $log_except = [
	        'ip',
            'user_agent',
            'referer_url',
            'referer'
        ];
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		const PER_PAGE = 20; // Сколько заявок отображать на странице списка заявок

        const MAX_REQ_PER_HOUR = 30;
        const MAX_REQ_PER_HOUR_FROM_IP = 10;


		public static $mysql_table	= "requests";

		protected $_inline_data = ["subjects", "branches"]; // Предметы (в БД хранятся строкой "1, 2, 3" – а тут в массиве

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
			if ($this->branches[0] != "") {
				$this->addBranchInfo();
			}

			// Генерируем форматированные номера
			foreach (static::$_phone_fields as $phone_field) {
				if ($this->{$phone_field} != "") {
					$this->{$phone_field . "_formatted"} = formatNumber($this->{$phone_field});
				}
			}
            // Включаем связи
            $this->Student  = $this->light ? Student::getLight($this->id_student) : Student::findById($this->id_student);
            $this->Comments = Comment::findAll([
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
		public static function getAllStatusesCount($search)
		{
		    $return = [];

            foreach (RequestStatuses::$all as $id => $status) {
                $condition = ' adding = 0 '. (!isBlank($search->id_user) ? ' and ifnull(id_user, 0) = '.$search->id_user : '');
                if ($id != RequestStatuses::ALL) {
                    $condition .= ' and id_status = '.$id;
                }
                $result[$id] = self::count(["condition" => $condition]);
            }

            return $result;
		}

		/**
		 * Получить количество заявок из каждого списка.
		 *
		 */
		public static function getUserCounts($search)
        {
            $return = [];

            $query = "select ifnull(r.id_user, 0) as id_user, count(r.id) as cnt ".
                     "from requests r ".
                     "where r.adding = 0 ". (!isBlank($search->id_status) && $search->id_status != RequestStatuses::ALL ? " and r.id_status = {$search->id_status} " : "") .
                     "group by r.id_user ";

            $result = self::dbConnection()->query($query);
            if ($result) {
                while ($row = $result->fetch_object()) {
                    if ($return[$row->id_user])
                        $return[$row->id_user] += $row->cnt;
                    else
                        $return[$row->id_user] += $row->cnt;
                }
            }

            return $return;
        }



		/**
		 * Получить заявки по номеру страницы и ID списка из RequestStatuses Factory.
		 *
		 */
		public static function getByPageRelevant($page, $grade, $id_branch, $id_subject)
		{
			if (!$page) {
				$page = 1;
			}
			// С какой записи начинать отображение, по формуле
			$start_from = ($page - 1) * self::PER_PAGE;

			$Requests = self::findAll([
				"condition"	=> "adding=0 AND id_status=" . RequestStatuses::AWAITING
					. (!empty($grade) ? " AND grade=$grade" : "")
					. (!empty($id_branch) ? " AND CONCAT(',', CONCAT(branches, ',')) LIKE '%,{$id_branch},%'" : "")
					. (!empty($id_subject) ? " AND CONCAT(',', CONCAT(subjects, ',')) LIKE '%,{$id_subject},%'" : "")
					. (empty($_COOKIE["id_user_list"]) ? "" : " AND id_user=".$_COOKIE["id_user_list"]) ,
				"order"		=> "date DESC",
				"limit" 	=> $start_from. ", " .self::PER_PAGE
			]);

			// Добавляем дубликаты
			foreach ($Requests as &$Request) {
				$Request->duplicates = $Request->getDuplicates();

				$Request->has_contract = $Request->hasContract();

				if ($Request->has_contract) {
					$Request->contract_time = $Request->contractTimeDiff();
				}

				if ($Request->duplicates) {
					$Request->total_count = count($Request->duplicates) + 1;
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

		/**
		 * Получить заявки по номеру страницы и ID списка из RequestStatuses Factory.
		 *
		 */
		public static function countByPageRelevant($grade = '', $id_branch = '', $id_subject = '')
		{
			$Requests = self::count([
				"condition"	=> "adding=0 AND id_status=" . RequestStatuses::AWAITING
					. (!empty($grade) ? " AND grade=$grade" : "")
					. (!empty($id_branch) ? " AND CONCAT(',', CONCAT(branches, ',')) LIKE '%,{$id_branch},%'" : "")
					. (!empty($id_subject) ? " AND CONCAT(',', CONCAT(subjects, ',')) LIKE '%,{$id_subject},%'" : "")
					. (empty($_COOKIE["id_user_list"]) ? "" : " AND id_user=".$_COOKIE["id_user_list"]) ,
			]);

			return $Requests;
		}

		/**
		 * Получить релевантные заявки по номеру страницы
		 *
		 */
		public static function getByPage($page, $id_status)
		{
			// С какой записи начинать отображение, по формуле
			$start_from = ($page - 1) * self::PER_PAGE;

			$Requests = self::findAll([
				"condition"	=> "adding=0"
					. ($id_status == RequestStatuses::ALL ? "" : " AND id_status=".$id_status)
					. (isBlank($_COOKIE["id_user_list"]) ? "" : " AND IFNULL(id_user,0) = ".$_COOKIE["id_user_list"]) ,
				"order"		=> "date DESC",
				"group"		=> ($id_status == RequestStatuses::NEWR ? "id_student" : ""), // если список "неразобранные", то отображать дубликаты
				"limit" 	=> $start_from. ", " .self::PER_PAGE,
                "light"     => true
			]);

			// Добавляем дубликаты
			foreach ($Requests as &$Request) {
				$Request->duplicates = $Request->getDuplicates();
				$Request->has_contract = $Request->hasContract();

				if ($Request->has_contract) {
					$Request->contract_time = $Request->contractTimeDiff();
				}

				if ($Request->duplicates) {
					$Request->total_count = count($Request->duplicates) + 1;
				}

				if ($Request->id_status == RequestStatuses::NEWR && $id_status != RequestStatuses::ALL) {
					$Request->list_duplicates = $Request->countListDuplicates();
				}

				// дубликаты для подсветки
				foreach (static::$_phone_fields as $phone_field) {
					if (!empty($Request->{$phone_field})) {
						$Request->{$phone_field . "_duplicate"} = isDuplicate($Request->{$phone_field}, $Request->id, $Request->id_student);
					}
				}
			}

			return $Requests;
		}

		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		public function beforeSave() {
			if ($this->isNewRecord || $this->adding) {
				$this->date = now();
				Socket::trigger('requests', 'incoming');
			} else {
				if (intval($this->id_status) === 0 && intval(static::_getStatus($this->id)) !== 0) {
					Socket::trigger('requests', 'incoming');
				} else {
					Socket::trigger('requests', 'incoming', ['delete' => true]);
				}
			}

			if (empty(trim($this->date))) {
				$this->date = now();
			}

			// Очищаем номера телефонов
			foreach (static::$_phone_fields as $phone_field) {
				$this->{$phone_field} = cleanNumber($this->{$phone_field});
			}
		}

		private static function _getStatus($id_request)
		{
			return dbConnection()->query('SELECT id_status FROM requests WHERE id = ' . $id_request)->fetch_object()->id_status;
		}

		public function hasContract()
		{
			return ContractInfo::count([
				"condition" => "id_student=" . $this->id_student
			]) > 0;
		}


		/**
		 * Время между созданием договора и созданием заявки.
		 *
		 */
		public function contractTimeDiff()
		{
			$OriginalContract = Contract::find([
				"condition" => "id_contract IN (" . Contract::getIdsByStudent($this->id_student) . ")",
				"order"		=> "id ASC"
			]);

			return (strtotime($OriginalContract->date_changed) - strtotime($this->date));
		}

        /**
         * @return bool
        */
		public function processIncoming()
		{
			// На всякий случай очищаем номер челефона (через "ч" написано специально)
			$this->phone = cleanNumber($this->phone);

            if (!$this->checkRequestLimit()) {
                return false;
            }

			// Создаем нового ученика по заявке, либо привязываем к уже существующему
			$this->createStudent();


			// Устанавливаем статус заявки
/*
			if (time() - $this->delay_time < 10) {
				$this->id_status = RequestStatuses::SPAM;
			} else
*/
			if ($this->_phoneExists()) {
				$this->id_status = RequestStatuses::DUPLICATE;
			}

            return true;
		}

        /**
         * check request count for last hour.
         * limits:
         *  from 1 ip   - 10 req
         *  from all ip - 30 req
         * @return bool
         */
        private function checkRequestLimit() {
            $req_from_ip = dbConnection()->query("
				SELECT COUNT(*) as cnt FROM requests
				WHERE date > DATE_SUB(NOW(), INTERVAL 1 HOUR) AND ip = '".$this->ip."'
			");

            if ($req_from_ip->fetch_object()->cnt > static::MAX_REQ_PER_HOUR_FROM_IP) {
                return false;
            }

            $total_req = dbConnection()->query("
				SELECT COUNT(*) as cnt FROM requests
				WHERE date > DATE_SUB(NOW(), INTERVAL 1 HOUR)
			");

            if ($total_req->fetch_object()->cnt > static::MAX_REQ_PER_HOUR) {
                return false;
            }

            return true;
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
			foreach ($this->branches as $id_branch) {
				$this->branches_data[$id_branch] = [
					"id"	=> $id_branch,
					"short"	=> Branches::$short[$id_branch],
					"color" => Branches::metroSvg($id_branch, false, true),
				];
			}
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


		/**
		 * Коливество дней/недель/месяцев/лет с момента первой заявки
		 *
		 * @param string $mode (default: 'days')
		 * $mode = days | weeks | months | years
		 */
		public static function timeFromFirst($mode = 'days')
		{
			$today = time(); // or your date as well

		    $first_request_date = 1432071360; // #hardcoded first request timestamp

		    $datediff = $today - $first_request_date;

		    switch ($mode) {
			    case 'days': {
				    return ceil($datediff / (60 * 60 * 24));
			    }
			    case 'weeks': {
				    return ceil($datediff / (60 * 60 * 24 * 7));
			    }
			    case 'months': {
				    return ceil($datediff / (60 * 60 * 24 * 30)) + 1;
			    }
			    case 'years': {
				    return ceil($datediff / (60 * 60 * 24 * 365));
			    }
		    }
		}

	}
