<?php

    class BaseContract extends Model
    {
        protected static $info_table = null;

        const PER_PAGE = 30;

        /*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
        public function __construct($array, $light = false)
        {
            parent::__construct($array);

            // Добавляем предметы в контракт
            $this->subjects = $this->getSubjects();

            // Цена с учетом скидки
            $this->final_sum = $this->discount > 0 ? ($this->sum * ((100 - $this->discount) / 100)) : $this->sum;

            $this->_setNull($this->duty);
            $this->_setNull($this->sum);

            if (! $light) {
                // логин пользователя
                if (! $this->isNewRecord) {
                    $this->user_login = User::getLogin($this->id_user);
                    $this->info = $this->getInfo();
                    $this->payments_info = implode('-', [$this->payments_split, $this->payments_queue]);
                    if ($this->info->grade == Grades::EXTERNAL) {
                        $this->info->grade_label = 'экстернат';
                        $this->info->grade_short = 'Э';
                    } else {
                        $this->info->grade_label = $this->info->grade . ' класс';
                        $this->info->grade_short = $this->info->grade;
                    }
                }
            }
        }

        private static function setCurrentVersionToPrev($Contract)
        {
            self::dbConnection()->query(
                "update " . static::$mysql_table . " " .
                "set current_version = 1 " .
                "where id = (" .
                "select max(c.id) from (select id from " . static::$mysql_table . " where id_contract = {$Contract->id_contract} and id <> {$Contract->id}) as c" .
                ")"
            );
        }

        private static function unsetCurrentVersionOfPrev($id_contract)
        {
            # @todo logging
            self::dbConnection()->query(
                "update " . static::$mysql_table . " " .
                "set current_version = 0 " .
                "where id_contract = {$id_contract} "
            );
        }

        public static function deleteById($id)
        {
            $Contract = self::findById($id);

            if ($Contract->current_version) { // установка текущей версии на предыдущую версию
                self::setCurrentVersionToPrev($Contract);
            }

            static::deleteSubjects($Contract);
            static::deleteInfo($Contract);
            $Contract->delete();
        }

        public static function add($data = false)
        {
            self::unsetCurrentVersionOfPrev($data['id_contract']);
            $newContract = parent::add($data);
            static::addInfoIfNotExists($newContract, $data);

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
            if (! User::byType($Student->id, Student::USER_TYPE, 'count')) {
                User::add([
                    "email" => $Student->email,
                    "phone" => $Student->phone,
                    "first_name" => $Student->first_name,
                    "last_name" => $Student->last_name,
                    "middle_name" => $Student->middle_name,
                    "type" => Student::USER_TYPE,
                    "id_entity" => $Student->id
                ]);

                $Representative = $Student->Representative;
                User::add([
                    "email" => $Representative->email,
                    "phone" => $Representative->phone,
                    "first_name" => $Representative->first_name,
                    "last_name" => $Representative->last_name,
                    "middle_name" => $Representative->middle_name,
                    "type" => Representative::USER_TYPE,
                    "id_entity" => $Representative->id
                ]);
            }

            // Добавляем предметы договора
            $NewContract->subjects = static::addSubjects($Contract["subjects"], $NewContract->id);

            return $NewContract;
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
                $this->id_user = User::fromSession()->id;
            }
        }

        // @contract-refactored
        public function afterSave()
        {
            if (!$this->id_contract) {
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
                "order" => "id DESC",
            ]);
        }

        /**
         * Получить предыдущую версию договора в рамках года сущности договора, используется в статистике
         */
        public function getPreviousVersionInYear()
        {
            $query = dbConnection()->query("
                SELECT id FROM " . static::$mysql_table . " c
                JOIN " . static::$info_table . " ci ON ci.id_contract = c.id_contract
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
            return dbConnection()->query("
                SELECT MIN(id) as min_id FROM " . static::$mysql_table . " c
                JOIN " . static::$info_table . " ci ON ci.id_contract = c.id_contract
                WHERE ci.id_student={$this->info->id_student} AND ci.year={$this->info->year}
            ")->fetch_object()->min_id == $this->id;
        }


        /**
         * Кол-во расторженных предметов в договоре.
         *
         */
        public function cancelledSubjectsCount()
        {
            $count = 0;
            foreach ($this->subjects as $subject) {
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
            return dbConnection()->query(
                "SELECT GROUP_CONCAT(id_contract) as contract_ids " .
                "FROM " . static::$info_table . " WHERE id_student=" . $id_student
            )->fetch_object()->contract_ids;
        }

        /*================= methods should be overridden =================*/
        public function getSubjects()
        {
            throw new Exception(get_class() . ': method should be overriden');
        }

        public function getInfo()
        {
            throw new Exception(get_class() . ': method should be overriden');
        }

        public static function deleteSubjects($Contract)
        {
            throw new Exception(get_class() . ': method should be overriden');
        }

        public static function deleteInfo($Contract)
        {
            throw new Exception(get_class() . ': method should be overriden');
        }

        public static function addInfoIfNotExists(&$newContract, $data)
        {
            throw new Exception(get_class() . ': method should be overriden');
        }

        public static function addSubjects($contract_subjects, $contract_id)
        {
            throw new Exception(get_class() . ': method should be overriden');
        }

        public static function edit($Contract)
        {
            throw new Exception(get_class() . ': method should be overriden');
        }
    }

    class BaseContractInfo extends Model
    {
        public static function get($id_contract)
        {
            return static::find([
                'condition' => "id_contract={$id_contract}"
            ]);
        }

        public static function updateById($id, $data)
        {
            self::deleteAll(["condition" => "id_contract = {$id}"]);
            self::add($data);
        }
    }
