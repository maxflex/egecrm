<?php
	class ContractPayment extends Model
	{

		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "contract_payments";


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
	}
