<?php
	class Contract extends Model
	{

		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "contracts";

		// условие, которое не берет в расчет версии договора

	    const PER_PAGE = 30;

		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array)
		{
			parent::__construct($array);

			// Добавляем предметы в контракт
			$this->subjects = ContractSubject::getContractSubjects($this->id);

			$this->_setNull($this->duty);
			$this->_setNull($this->sum);

			// логин пользователя
			if (!$this->isNewRecord) {
				$this->user_login = User::getLogin($this->id_user);
                $this->info = ContractInfo::get($this->id_contract);
			}
		}

		private static function setCurrentVersionToPrev($Contract) {
            self::dbConnection()->query(
                "update contracts " .
                "set current_version = 1 " .
                "where id = (" .
                "select max(c.id) from (select id from contracts where id_contract = {$Contract->id_contract} and id <> {$Contract->id}) as c" .
                ")"
            );
        }

        private static function unsetCurrentVersionOfPrev($id_contract) {
            # @todo logging
            self::dbConnection()->query(
                "update contracts " .
                "set current_version = 0 " .
                "where id_contract = {$id_contract} "
            );
        }

		public static function deleteById($id) {
		    $Contract = self::findById($id);

            if ($Contract->current_version) { // установка текущей версии на предыдущую версию
                self::setCurrentVersionToPrev($Contract);
		    }
		    ContractSubject::deleteAll([
                "condition" => "id_contract = {$Contract->id}"
            ]);
            $Contract->delete();

            if ($Contract->id == $Contract->id_contract) { // удаление инфо если это базовая версия
                ContractInfo::deleteById($Contract->id);
            }
        }

        public static function add($data = false)
        {
            self::unsetCurrentVersionOfPrev($data['id_contract']);
            $newContract = parent::add($data);
            $newContract->info = ContractInfo::add(array_merge($data['info'], ['id_contract' => $newContract->id]));
            return $newContract;
        }

		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/

		private function _setNull(&$field)
		{
			if ($field == 0) {
				$field = null;
			}
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

		public static function addNew($Contract)
		{
			// Создаем договор
			$NewContract = self::add($Contract);
			// логин пользователя
			if ($NewContract->id_user) {
				$user = findObjectInArray(User::getCached(), ['id' => $NewContract->id_user]);
				$NewContract->user_login = $user['login'];
			}

			// Создаем логин-пароль пользователя
			$Student = Student::findById($NewContract->info->id_student, true);
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

        // @contract-refactored
        public static function edit($Contract)
        {
            Contract::updateById($Contract['id'], $Contract);
            ContractInfo::updateById($Contract['info']['id_contract'], $Contract['info']);
            ContractSubject::addData($Contract['subjects'], $Contract['id']);
        }

		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		public function beforeSave()
		{
			// Если сумма договора равна нулю, то ставим, будто не указана
			// иначе если в новый договор не подставить сумму, автоматом ставится ноль
			if (!$this->sum) {
				$this->sum = NULL;
			}

            // @contract-refactored убрали условия изменения даты
			if ($this->isNewRecord) {
				// дата изменения и пользователь МЕНЯЮТСЯ ТОЛЬКО В СЛУЧАЕ ЕСЛИ ЭТО НЕ ПОДВЕРСИЯ
				$this->date_changed = now();
				// договор всегда создается новый, поэтому нет условия if ($this->isNewRecord) { ... }
				$this->id_user		= User::fromSession()->id;
			}
		}

        // @contract-refactored
        public function afterSave()
        {
            if (! $this->id_contract) {
                $this->id_contract = $this->id;
                $this->current_version = 1;
                $this->save();
            }
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

        /**
         * Получить предыдущую версию договора, используется в статистике
         * @depricated
         */
		public function getPreviousVersion()
		{
            return self::find([
                "condition" => "id < " . $this->id . " AND id_contract=" . $this->id_contract,
                "order"		=> "id DESC",
            ]);
		}
        /**
         * Получить предыдущую версию договора в рамках года сущности договора, используется в статистике
         */
		public function getPreviousVersionInYear()
		{
            $query = dbConnection()->query("
                SELECT id FROM contracts c
                JOIN contract_info ci ON ci.id_contract = c.id_contract
                WHERE ci.id_student={$this->info->id_student} AND ci.year={$this->info->year} AND c.id < {$this->id}
                ORDER BY id DESC
                LIMIT 1
            ");
            if ($query->num_rows) {
                return self::findById($query->fetch_object()->id);
            } else {
                return false;
            }
		}


		/**
		 * Является ли договор оригинальным?
		 *
		 */
		public function isOriginal()
		{
            // @contract-refactored
			return $this->id == $this->id_contract;
		}

        /**
         * Первый договор в учебном году
         */
        public function isFirstInYear()
        {
            // получаем первый договор ученика в году, равному году текущей сущности договора
            $query = dbConnection()->query("
                SELECT MIN(id) as min_id FROM contracts c
                JOIN contract_info ci ON ci.id_contract = c.id_contract
                WHERE ci.id_student={$this->info->id_student} AND ci.year={$this->info->year}
            ");
            if ($query->num_rows) {
                return $query->fetch_object()->min_id == $this->id;
            } else {
                return false;
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

        /**
         * Вернуть по ID студента
         */
        public static function getIdsByStudent($id_student)
        {
            return dbConnection()->query("SELECT GROUP_CONCAT(id_contract) as contract_ids FROM contract_info WHERE id_student=" . $id_student)->fetch_object()->contract_ids;
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
            $query = static::_generateQuery($search, "s.id as id_student, s.first_name, s.last_name, s.middle_name, c.sum, c.date, ci.year, ", true);
            $result = dbConnection()->query($query . ($page == -1 ? "" : " LIMIT {$start_from}, " . Contract::PER_PAGE));

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
                         from contracts c
                         join  contract_info ci on ci.id_contract = c.id_contract
                         join  students s on ci.id_student = s.id
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

    class ContractInfo extends Model
	{
		public static $mysql_table	= "contract_info";

        public static function get($id_contract)
        {
            return ContractInfo::find([
                'condition' => "id_contract={$id_contract}"
            ]);
        }

        public static function updateById($id, $data)
        {
            self::deleteAll(["condition" => "id_contract = {$id}"]);
            self::add($data);
        }
    }
