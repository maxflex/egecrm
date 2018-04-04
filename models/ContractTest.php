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
    }

    class ContractInfoTest extends BaseContractInfo
    {
        public static $mysql_table  = 'contract_info_test';
    }
