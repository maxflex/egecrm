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

			return self::whereIn($ids, 'id_contract');
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

		protected static function parseSearch()
	   {
		   return isset($_COOKIE['contracts']) ? json_decode($_COOKIE['contracts']) : (object)[];
	   }

	   protected static function _generateQuery($search, $select, $with_colors = false)
	   {
		   $main_query = "
						from " . static::$mysql_table . " c
						join   " . static::$info_table. " ci on ci.id_contract = c.id_contract
						join  students s on ci.id_student = s.id
						left join representatives r on r.id = s.id_representative
						where 1 ".
						(!isBlank($search->year) ? " and ci.year={$search->year} " : '') .
						(!isBlank($search->version) ? "
						   and c.id = (
							   SELECT " . ($search->version == 1 ? "MIN" : "MAX") . "(id) as min_id FROM " . static::$mysql_table . " c2
							   JOIN " . static::$info_table. " ci2 on ci2.id_contract = c2.id_contract
							   WHERE ci2.id_student=s.id AND ci2.year=ci.year
						   )
						" : "") . "
						order by c.id desc";


		   $color_counts = " (select count(id_subject) from contract_subjects cs where cs.id_contract = c.id AND cs.status = 3) as green, " .
						   " (select count(id_subject) from contract_subjects cs where cs.id_contract = c.id AND cs.status = 2) as yellow, " .
						   " (select count(id_subject) from contract_subjects cs where cs.id_contract = c.id AND cs.status = 1) as red, " .
						   " (select count(id) from contracts h
							   where c.date_changed > h.date_changed and h.id_contract = if(c.id_contract, c.id_contract, c.id)) as version ";

		   return "select " . $select . ($with_colors ? $color_counts : ''). $main_query;
	   }
    }

    class ContractInfo extends BaseContractInfo
    {
        public static $mysql_table	= "contract_info";
    }
