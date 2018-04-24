<?php
	class ContractPayment extends Model
	{

		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "contract_payments";

		const PER_PAGE = 30;


		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array)
		{
			parent::__construct($array);

			$this->date_original = $this->date;
			if ($this->date) {
				$this->date = toDotDate($this->date);
			}
		}

		public function beforeSave()
        {
			if ($this->date) {
				$this->date = fromDotDate($this->date);
			}
		}

		public static function get($id_contract)
		{
			return ContractPayment::findAll([
				'condition' => "id_contract={$this->id}",
				'order' => 'date asc'
			]);
		}

		/**
		 * Добавление, изменение, удаление
		 */
		public static function process($data, $id_contract)
		{
			$all_ids = self::getIds(['condition' => "id_contract={$id_contract}"]);

			$present_ids = [];
			foreach($data as $d) {
				if ($d['id']) {
					$present_ids[] = intval($d['id']);
					self::updateById($d['id'], $d);
				} else {
					$d['id_contract'] = $id_contract;
					self::add($d);
				}
			}

			foreach($all_ids as $id) {
				if (! in_array($id, $present_ids)) {
					self::deleteById($id);
				}
			}
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
           $start_from = ($page - 1) * self::PER_PAGE;

           // $search = static::parseSearch();

		   $select = "select cp.lesson_count, cp.date, ci.id_student, s.first_name, s.last_name, s.middle_name,
			   c.discount, c.sum, (select sum(`count`) from contract_subjects cs where cs.id_contract = c.id) as subject_count";

           // получаем данные
           $query = "
				from contract_payments cp
				join contracts c on c.id = cp.id_contract
				join contract_info ci on ci.id_contract = c.id_contract
				join students s on s.id = ci.id_student
				where (cp.date is not null and cp.date != '0000-00-00')
				order by cp.date asc
		   ";

		   $counts['all'] = dbConnection()->query("select count(*) as cnt " . $query)->fetch_object()->cnt;

           $result = dbConnection()->query($select . $query . ($page == -1 ? "" : " LIMIT {$start_from}, " . self::PER_PAGE));

           while ($row = $result->fetch_object()) {
			   // сумма договора с учетом скидки
			   if ($row->discount) {
				   $row->sum = round($row->sum - ($row->sum * ($row->discount / 100)));
			   }
			   // сумма платежа
			   $row->sum = round($row->lesson_count * ($row->sum / $row->subject_count));
               $data[] = ($page == -1 ? $row->id : $row);
           }

           return compact('data', 'counts');
       }
	}
