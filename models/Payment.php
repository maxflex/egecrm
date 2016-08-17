<?php
	class Payment extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/
		public static $mysql_table	= "payments";
		
		# Список статусов
		const PAID_CARD		= 1;
		const PAID_CASH		= 2;
		const PAID_BILL		= 4;
		const CARD_ONLINE	= 5;
		const MUTUAL_DEBTS	= 6;

		# Все
		static $all  = [			
			self::PAID_CARD		=> "карта",
			self::PAID_CASH		=> "наличные",
			self::PAID_BILL		=> "счет",
			self::CARD_ONLINE	=> "карта онлайн",
			self::MUTUAL_DEBTS	=> "взаимозачет",
		];

		const PER_PAGE = 30;

		# удаленные записи коллекции
		static $deleted = [
//			self::NOT_PAID_BILL,
		];
		
		# Заголовок
		static $title = "способ оплаты";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array) {
			parent::__construct($array);
			
			if ($this->card_number) {
				$this->card_number .= ' ';
			}
			
			// Добавляем данные
			$this->user_login = User::findById($this->id_user)->login;
		}
		
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		/**
		 * Найти все платежи студента (клиента).
		 *
		 */
		public static function getByStudentId($id_student)
		{
			return Payment::findAll([
				"condition" => "entity_id=" . $id_student." and entity_type='".Student::USER_TYPE."' "
			]);
		}
		
		/**
		 * Построить селектор из всех записей.
		 * $selcted - что выбрать по умолчанию
		 * $name 	– имя селектора, по умолчанию имя класса
		 * $attrs	– остальные атрибуты
		 * 
		 */
		public static function buildSelector($selcted = false, $name = false, $attrs = false, $skip = [])
		{
			$class_name = strtolower(get_called_class());
			echo "<select class='form-control' id='".$class_name."-select' name='".($name ? $name : $class_name)."' ".Html::generateAttrs($attrs).">";
			if (static::$title) {
				echo "<option selected value=0>". static::$title ."</option>";
				echo "<option disabled>──────────────</option>";
			}
			foreach (static::$all as $id => $value) {
				if (!in_array($id, $skip)) {
					// удаленные записи коллекции отображать только в том случае, если они уже были выбраны
					// (т.е. были использованы ранее, до удаления)
					if (!in_array($id, static::$deleted) || ($id == $selcted)) {
						echo "<option value='$id' ".($id == $selcted ? "selected" : "").">$value</option>";
					}
				}
			}
			echo "</select>";
		}
		
		public static function countUnconfirmed()
		{
			return self::count([
				"condition" => "confirmed = 0 and entity_type = '".Student::USER_TYPE."' "
			]);
		}
				
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		public function getEntity()
		{
			if ($this->entity_type == Teacher::USER_TYPE) {
				$entity = Teacher::getLight($this->entity_id);
				$entity->profile_link = "teachers/edit/{$this->entity_id}";
			} else {
				$entity = Student::getLight($this->entity_id);
				$entity->profile_link = "student/{$this->entity_id}";
			}
			return $entity;
		}

		/**
		 * Добавить платежи
		 * 
		 */
		public static function addData($payments_array, $entity_id, $entity_type = 'STUDENT')
		{	
			// Сохраняем данные
			foreach ($payments_array as $id => $one_payment) {
				// если у платежа есть ID, то обновляем его
				if ($one_payment["id"]) {
					$Payment = Payment::findById($one_payment["id"]);
					$Payment->update($one_payment);
				} else {
					// иначе добавляем новый платеж
					$Payment = new self($one_payment);
					$Payment->entity_id 	  = $entity_id;
					$Payment->entity_type 	  = $entity_type;
					$Payment->id_user		  = User::fromSession()->id;
					$Payment->first_save_date = now();
					$Payment->save();
				}
			}
		}
		
		
		/**
		 * Коливество дней/недель/месяцев/лет с момента первой оплаты
		 * 
		 * @param string $mode (default: 'days')
		 * $mode = days | weeks | months | years
		 */
		public static function timeFromFirst($mode = 'days')
		{
			$today = time(); // or your date as well
			
		    $first_payment_date = 1431932400; // #hardcoded first payment timestamp
		    
		    $datediff = $today - $first_payment_date;

			switch ($mode) {
				case 'days': {
					return ceil($datediff / (60 * 60 * 24));
				}
				case 'weeks': {
					return floor($datediff / (60 * 60 * 24 * 7));
				}
				case 'months': {
					return ceil($datediff / (60 * 60 * 24 * 30));
				}
				case 'years': {
					return ceil($datediff / (60 * 60 * 24 * 365));
				}
			}
		}

		public function afterSave()
        {
            if (($this->id_status == self::PAID_CASH) && ($this->id_type == 2) && !$this->document_number) {
                $this->document_number = self::dbConnection()->query('select max(document_number) + 1 as last_doc_num from payments')->fetch_object()->last_doc_num;
                $this->update(['document_number' => $this->document_number]);
            }
            parent::afterSave();
        }
    }