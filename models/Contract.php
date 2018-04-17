<?php
    class Contract extends BaseContract
    {
        public static $mysql_table      = 'contracts';
        protected static $info_table    = 'contract_info';

		public function __construct($array)
		{
			parent::__construct($array);

			if ($this->isNewRecord) {
				$this->payments = [];
			} else {
				$payments = ContractPayment::findAll(['condition' => "id_contract={$this->id}"]);
				$this->payments = $payments ? $payments : [];
			}
		}

		/**
		 * Найти все договоры года
		 */
		public static function findAllByYear($year)
		{
			$result = dbConnection()->query("
				select id_contract
				from contract_info
				where `year`={$year}
			");

			$ids = [];

			while ($row = $result->fetch_object()) {
				$ids[] = $row->id_contract;
			}

			return self::whereIn($ids);
		}

        public function getSubjects()
        {
            return ContractSubject::getContractSubjects($this->id);
        }

        public function getInfo()
        {
            return ContractInfo::get($this->id_contract);
        }

        public static function deleteSubjects($Contract)
        {
            ContractSubject::deleteAll([
                "condition" => "id_contract = {$Contract->id}"
            ]);
        }

        public static function deleteInfo($Contract)
        {
            if ($Contract->id == $Contract->id_contract) { // удаление инфо если это базовая версия
                ContractInfo::deleteAll(['condition' => 'id_contract = ' . $Contract->id]);
            }
        }

        public static function addInfoIfNotExists(&$newContract, $data)
        {
            // если нет соответствующее ContractInfo, то добавим его.
            if (! ContractInfo::count(['condition' => 'id_contract = ' . $newContract->id_contract])) {
                $newContract->info = ContractInfo::add(array_merge($data['info'], ['id_contract' => $newContract->id]));
            } else {
                $newContract->info = new ContractInfo(array_merge($data['info'], ['id_contract' => $newContract->id]));
            }
        }

        public static function addSubjects($contract_subjects, $new_contract_id)
        {
            return ContractSubject::addData($contract_subjects, $new_contract_id);
        }

        public static function edit($Contract)
        {
            Contract::updateById($Contract['id'], $Contract);
            ContractInfo::updateById($Contract['info']['id_contract'], $Contract['info']);
            ContractSubject::addData($Contract['subjects'], $Contract['id']);
			ContractPayment::process($Contract['payments'], $Contract['id']);
        }
    }

    class ContractInfo extends BaseContractInfo
    {
        public static $mysql_table	= "contract_info";
    }
