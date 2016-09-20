<?php
	class Contract extends Model
	{
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "contracts";
		
		// какие поля не сравнивать при сравнении объектов
		// в функции self::changed()
		private static $dont_compare = ["id", "id_student", "id_contract", "id_user", "date_changed"];	
		
		// условие, которое не берет в расчет версии договора
		const ZERO_OR_NULL_CONDITION 		= " AND (id_contract=0 OR id_contract IS NULL)";
		const ZERO_OR_NULL_CONDITION_JOIN 	= " AND (c.id_contract=0 OR c.id_contract IS NULL)";
	    const PER_PAGE = 30;
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array) 
		{
			parent::__construct($array);
			
			// Добавляем предметы в контракт
			$this->subjects = ContractSubject::getContractSubjects($this->id);
			
			// история изменений
			if ($this->id_student) {
				$this->History = self::findAll([
					"condition" => "id_contract=" . $this->id
				]);
			}
			
			$this->_setNull($this->duty);
			$this->_setNull($this->sum);
			
			// логин пользователя
			if (!$this->isNewRecord) {
				$this->user_login = User::getLogin($this->id_user);
			}
		}
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		private function _setNull(&$field)
		{
			if ($field == 0) {
				$field = null;
			}
		}
		
		/**
		 * Добавить новые контракты и обновить старые.
		 *
		 * договоры с (id < 0) – это новые договоры. у новых договоров отрицательный ID, их нужно добавить
		 * договоры с положительным ID – уже существующие, их нужно обновить
		 */
		public static function addAndUpdate($Contracts, $id_student)
		{
			foreach ($Contracts as $id => $Contract) {
				// Добавляем ID Ученика в данные по контракту
				$Contract["id_student"] = $id_student;
				
				// Если добавляем новый договор
				if ($id < 0) {
					// Создаем договор
					$NewContract = self::add($Contract);
					// Добавляем предметы договора
					ContractSubject::addData($Contract["subjects"], $NewContract->id);
				} else {
					// Контроль версий
					$changed = self::versionControl($id, $Contract);
					
					// если были изменения, обновляем
					if ($changed) {
						// Обновляем старый договор
						$OriginalContract = self::updateById($id, $Contract);
						
						// Загружаем туда файлы
						$OriginalContract->uploadFile();
						
						// Добавляем предметы договора
						ContractSubject::addData($Contract["subjects"], $id);	
					}
				}
			}
		}
		
		/**
		 * Добавить новые контракты и обновить старые.
		 *
		 * договоры с (id < 0) – это новые договоры. у новых договоров отрицательный ID, их нужно добавить
		 * договоры с положительным ID – уже существующие, их нужно обновить
		 */
		public static function addNew($Contract)
		{
			// Создаем договор
			$NewContract = self::add($Contract);
			// Добавляем предметы договора
			ContractSubject::addData($Contract["subjects"], $NewContract->id);
			
			echo $NewContract->id;
		}
		
		
		/**
		 * Сгенерировать акционный код.
		 * [1-9]{1}[A-Z]{3}
		 * 
		 */
		public static function _generateCode()
		{
			// генерировать код пока он станет уникальным 
			do {
				$code = generateRandomString(1, ['uppercase']) . generateRandomString(3, ['digits']);
			} while (Student::find(["condition" => "code = '$code'"]));
			
			return $code;
		}
		
		public static function addNewAndReturn($Contract)
		{
			// Создаем договор
			$NewContract = self::add($Contract);
			
			// логин пользователя
			if ($NewContract->id_user) {
				$NewContract->user_login = User::getCached()[$NewContract->id_user]['login'];
			}
			
			// Создаем логин-пароль пользователя
			$Student = Student::findById($NewContract->id_student);	
			if (!$Student->login) {
				$Student->login 	= $NewContract->id;
				$Student->password	= mt_rand(10000000, 99999999);
				$Student->code		= self::_generateCode();
				$Student->save();
				
				User::add([
					"login" 		=> $Student->login,
					"password"		=> $Student->password,
					"first_name"	=> $Student->first_name,
					"last_name"		=> $Student->last_name,
					"middle_name"	=> $Student->middle_name,
					"type"			=> Student::USER_TYPE,
					"id_entity"		=> $Student->id
				]);
			}
			
			// Добавляем предметы договора
			ContractSubject::addData($Contract["subjects"], $NewContract->id);
			
			return $NewContract;
		}
		
		public static function edit($Contract)
		{
			// если нужно без проводки
			if ($Contract["no_version_control"]) {
				$changed = true;
			} else {
				// Контроль версий
				$changed = self::versionControl($Contract);	
			}
			
			// если были изменения, обновляем
			if ($changed) {
				// Обновляем старый договор
				//$OriginalContract = self::updateById($Contract["id"], $Contract);
				// избавляемся от путальницы с сохранением user_id + time
				$OriginalContract = new Contract($Contract);
				$OriginalContract->date_changed = now();
				$OriginalContract->id_user	   = User::fromSession()->id;
				$OriginalContract->save();
				
				// Добавляем предметы договора
				ContractSubject::addData($Contract["subjects"], $Contract["id"]);
				
				return $OriginalContract;
			}
		}
		
		
		/**
		 * Договор был изменен?
		 * 
		 * @return boolean true – изменен, false – не изменен
		 */
		public static function changed($Contract)
		{	
			# Находим оригинальный доГАВАр
			$OriginalContract = Contract::findById($Contract["id"]);
			

			// ансет всех FALSE
			foreach($Contract["subjects"] as $id => $subject) {
				if ($subject === false) {
					unset($Contract["subjects"][$id]);
				}
			}
			
			# Проверяем изменились ли предметы договора
			// проверяем изменилось ли количество предметов в договорах
			if (count($Contract["subjects"]) != $OriginalContract->getSubjectCount()) {
//				var_dump($OriginalContract->subjects);
//				echo "SUBJECTS COUNT CHANGED: ".count($Contract["subjects"])." - ".($OriginalContract->getSubjectCount())."<br>";
				return true;
			}
			
			// проверяем изменились ли предметы договора
			foreach ($Contract["subjects"] as $id => $ContractSubject) {
//				preType([$ContractSubject, $OriginalContract->subjects[$id]]);
				foreach ($ContractSubject as $key => $value) {
//					preType($OriginalContract->subjects[$id]);
					if ($value != $OriginalContract->subjects[$id][$key]) {
//						echo "<b>$key</b>:" . $OriginalContract->subjects[$id][$key] . " => ". $value . "<br>";
						return true;
					}
				}
			}
			
			// Проверяем изменились ли основные поля договора
			foreach ($OriginalContract->mysql_vars as $field) {
				// если поле не надо сравнивать
				if (in_array($field, static::$dont_compare)) {
					continue;
				}
				
//				echo "<b>$field</b>:" . $OriginalContract->{$field}. " => ". $Contract[$field]. "<br>";
				
				if (array_key_exists($field, $Contract)) {
					if ($Contract[$field] != $OriginalContract->{$field}) {
//						echo "'$field' has been changed! ";
						return true;
					}
				}
			}
			
			return false;
		}
		
		
		/**
		 * Создать версию договора (храним все изменения договора)
		 * 
		 */
		public static function versionControl($Contract)
		{
			// если договор был изменен, то создать версию изменения
			if (self::changed($Contract)) {
				// находим оригинальный контракт
				$OriginalContract = Contract::findById($Contract["id"]);
				
				// создаем копию оригинального контракта для истории
				$ContractCopy = clone $OriginalContract;
				
				unset($ContractCopy->id);
				$ContractCopy->isNewRecord = true;
				$ContractCopy->id_student = null;
				$ContractCopy->id_contract = $Contract["id"];
				
				$ContractCopy->save();
				
				// Добавляем предметы нового договора
				ContractSubject::addData($OriginalContract->subjects, $ContractCopy->id);
				return true;	
			} else {
				return false;
			}	
		}
						
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/
		
		public function beforeSave()
		{
			// Если сумма договора равна нулю, то ставим, будто не указана
			// иначе если в новый договор не подставить сумму, автоматом ставится ноль
			if (!$this->sum) {
				$this->sum = NULL;
			}
/*
			if (!$this->id_contract) {
				$this->id_contract = NULL;
				
				if ($this->isNewRecord) {
					// дата изменения и пользователь МЕНЯЮТСЯ ТОЛЬКО В СЛУЧАЕ ЕСЛИ ЭТО НЕ ПОДВЕРСИЯ
					$this->date_changed = now();
					// договор всегда создается новый, поэтому нет условия if ($this->isNewRecord) { ... }
					$this->id_user		= User::fromSession()->id;
				}
			}
*/
			if ($this->isNewRecord && !$this->id_contract) {
				// дата изменения и пользователь МЕНЯЮТСЯ ТОЛЬКО В СЛУЧАЕ ЕСЛИ ЭТО НЕ ПОДВЕРСИЯ
				$this->date_changed = now();
				// договор всегда создается новый, поэтому нет условия if ($this->isNewRecord) { ... }
				$this->id_user		= User::fromSession()->id;
			}
		}
		
		public function afterFirstSave()
		{
			// parent::afterFirstSave();
			
			// Загружаем файл после сохранения, т.к. нужен ID  договора
			// $this->uploadFile();
		}
		
		
		
		/**
		 * Получить количество предметов у договора.
		 *
		 * Потому что при отсутствии предметов возвращает 1: 
		 * http://stackoverflow.com/questions/3776882/count-of-false-gives-1-and-if-of-an-empty-array-gives-false-why
		 */
		public function getSubjectCount()
		{
			if (is_array($this->subjects)) {
			    return count($this->subjects);
			} else {
				return 0;
			}
		}
		
		public function getPreviousVersion()
		{
			if ($this->id_contract) {
				return self::find([
					"condition" => "id < " . $this->id . " AND id_contract=" . $this->id_contract,
					"order"		=> "id DESC",
				]);	
			} else {
				return self::find([
					"condition" => "id > " . $this->id . " AND id_contract=" . $this->id,
					"order"		=> "id DESC",
				]);
			}
		}
		
		
		/**
		 * Является ли договор оригинальным? (Либо активный без версий, либо самая старая версия)
		 * 
		 */
		public function isOriginal()
		{
			// если договор активный, нужно проверить единственный ли он.
			// потому что если у активного договора есть версии, то это не оригинальный договор
			if (!$this->id_contract) {
				return self::find([
					"condition" => "id_contract=".$this->id
				]) == false;
			} else {
				// если это версия договора, нужно найти оригинальную (самую старую) и сравнить по ID
				$OriginalContract = self::find([
					"condition" => "id_contract=".$this->id_contract,
					"order"		=> "id ASC",
				]);
								
				return $this->id == $OriginalContract->id;
			}
		}
		
		
		/**
		 * Кол-во расторженных предметов в договоре.
		 * 
		 */
		public function cancelledSubjectsCount()
		{
			$count = 0;
			foreach($this->subjects as $subject) {
				if ($subject['status'] == 1) {
					$count++;
				}	
			}
			return $count;
		}
		
		/**
		 * Кол-во активных предметов в договоре.
		 * 
		 */
		public function activeSubjectsCount()
		{
			return (count($this->subjects) - $this->cancelledSubjectsCount());
		}
		
		/**
		 * Договор полностью расторгнут (все предметы внутри расторгнуты).
		 * 
		 */
		public function isCancelled()
		{
			return $this->activeSubjectsCount() <= 0;
		}

        /*
         * Получить данные для основного модуля
         * $page==-1 – получить без лимита
         */
        public static function getData($page)
        {
            if (!$page) {
                $page = 1;
            }
            // С какой записи начинать отображение, по формуле
            $start_from = ($page - 1) * Contract::PER_PAGE;

            $search = isset($_COOKIE['contracts']) ? json_decode($_COOKIE['contracts']) : (object)[];


            // получаем данные
            $query = static::_generateQuery($search, "s.id as id_student, s.first_name, s.last_name, s.middle_name, c.sum, c.date, c.year, ", true);
            $result = dbConnection()->query($query . ($page == -1 ? "" : " LIMIT {$start_from}, " . Contract::PER_PAGE));
//dd($query);
            while ($row = $result->fetch_object()) {
                $data[] = ($page == -1 ? $row->id : $row);
            }

            return [
                'data'   => $data,
                'counts' => ['all' => static::_count($search)]
            ];
        }

        private static function _count($search) {
            return dbConnection()
                ->query(static::_generateQuery($search, "COUNT(*) AS cnt"))
                ->fetch_object()
                ->cnt;
        }


        private static function _generateQuery($search, $select, $with_colors = false)
        {
            $main_query = "
                         from
                            (
                                select c1.*, if(c1.id_contract, (select c2.id_student from contracts c2 where c2.id = c1.id_contract), 0) as parent_id_student
                                from contracts c1
                            ) c
                         join  students s on c.id_student = s.id or c.parent_id_student = s.id
                         where 1 ".
                         (!isBlank($search->start_date) ? " and str_to_date(c.date, '%d.%m.%Y') >= str_to_date('" . $search->start_date . "', '%d.%m.%Y') " : "") .
                         (!isBlank($search->end_date) ? " and str_to_date(c.date, '%d.%m.%Y') <= str_to_date('" . $search->end_date . "', '%d.%m.%Y') " : "") .
                         (!isBlank($search->id_student) ? " and (s.id = " . $search->id_student . ") " : "") . "
                         order by str_to_date(c.date, '%d.%m.%Y') desc, c.date_changed desc";


            $color_counts = " (select count(id_subject) from contract_subjects cs where cs.id_contract = c.id AND cs.status = 3) as green, " .
                            " (select count(id_subject) from contract_subjects cs where cs.id_contract = c.id AND cs.status = 2) as yellow, " .
                            " (select count(id_subject) from contract_subjects cs where cs.id_contract = c.id AND cs.status = 1) as red, " .
                            " (select count(id) from contracts h
                                where c.date_changed > h.date_changed and h.id_contract = if(c.id_contract, c.id_contract, c.id)) as version ";

            return "select " . $select . ($with_colors ? $color_counts : ''). $main_query;
        }
	}