<?php
    class ContractTest extends BaseContract
    {
        public static $mysql_table      = "contracts_test";
        protected static $info_table    = "contract_info_test";

        public function getSubjects()
        {
            return ContractSubjectTest::getContractSubjects($this->id);
        }

        public function getInfo()
        {
            return ContractInfoTest::get($this->id_contract);
        }

        public static function deleteSubjects($Contract)
        {
            ContractSubjectTest::deleteAll([
                "condition" => "id_contract = {$Contract->id}"
            ]);
        }

        public static function deleteInfo($Contract)
        {
            if ($Contract->id == $Contract->id_contract) { // удаление инфо если это базовая версия
                ContractInfoTest::deleteAll(['condition' => 'id_contract = ' . $Contract->id]);
            }
        }

        public static function addInfoIfNotExists(&$newContract, $data)
        {
            // если нет соответствующее ContractInfoTest, то добавим его.
            if (! ContractInfoTest::count(['condition' => 'id_contract = ' . $newContract->id_contract])) {
                $newContract->info = ContractInfoTest::add(array_merge($data['info'], ['id_contract' => $newContract->id]));
            } else {
                $newContract->info = new ContractInfoTest(array_merge($data['info'], ['id_contract' => $newContract->id]));
            }
        }

        public static function addSubjects($contract_subjects, $new_contract_id)
        {
            return ContractSubjectTest::addData($contract_subjects, $new_contract_id);
        }

        static public function edit($Contract)
        {
            Contract::updateById($Contract['id'], $Contract);
            ContractInfoTest::updateById($Contract['info']['id_contract'], $Contract['info']);
            ContractSubjectTest::addData($Contract['subjects'], $Contract['id']);
        }

        /*====================================== ФУНКЦИИ КЛАССА ======================================*/

        protected static function parseSearch()
        {
            return isset($_COOKIE['contracts_test']) ? json_decode($_COOKIE['contracts_test']) : (object)[];
        }

        protected   static function _generateQuery($search, $select, $with_colors = false)
        {
            $main_query = "
                         from " . static::$mysql_table . " c
                         join " . static::$info_table . " ci on ci.id_contract = c.id_contract
                         join  students s on ci.id_student = s.id
                         where 1 ".
                         (!isBlank($search->start_date) ? " and str_to_date(c.date, '%d.%m.%Y') >= str_to_date('" . $search->start_date . "', '%d.%m.%Y') " : "") .
                         (!isBlank($search->end_date) ? " and str_to_date(c.date, '%d.%m.%Y') <= str_to_date('" . $search->end_date . "', '%d.%m.%Y') " : "") .
                         (!isBlank($search->id_student) ? " and (s.id = " . $search->id_student . ") " : "") . "
                         order by str_to_date(c.date, '%d.%m.%Y') desc, c.date_changed desc";


            $color_counts = " (select count(id_subject) from contract_subjects_test cs where cs.id_contract = c.id AND cs.status = 3) as green, " .
                            " (select count(id_subject) from contract_subjects_test cs where cs.id_contract = c.id AND cs.status = 2) as yellow, " .
                            " (select count(id_subject) from contract_subjects_test cs where cs.id_contract = c.id AND cs.status = 1) as red, " .
                            " (select count(id) from contracts_test h
                                where c.date_changed > h.date_changed and h.id_contract = if(c.id_contract, c.id_contract, c.id)) as version ";

            return "select " . $select . ($with_colors ? $color_counts : ''). $main_query;
        }
    }

    class ContractInfoTest extends BaseContractInfo
    {
        public static $mysql_table  = 'contract_info_test';
    }
