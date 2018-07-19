<?php
	class Payment extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/
		public static $mysql_table	= "payments";

		protected $_json = ['extra'];

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

		const PER_PAGE = 100;

		# удаленные записи коллекции
		static $deleted = [
//			self::NOT_PAID_BILL,
		];

		# Заголовок
		static $title = "способ оплаты";

		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array, $flag = false) {
			parent::__construct($array);

			if ($this->card_number) {
				$this->card_number .= ' ';
			}
			if ($this->card_first_number) {
				$this->card_first_number .= 'XXX';
			}

			// Добавляем данные
            if (!$flag) {
                $this->user_login = User::findById($this->id_user)->login;
            }

			$this->date_original = $this->date;
			if ($this->date) {
				$this->date = toDotDate($this->date);
			}
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
			} else if ($this->entity_type == Student::USER_TYPE) {
				$entity = Student::getLight($this->entity_id);
				$entity->profile_link = "student/{$this->entity_id}";
			} else {
				$entity = $this->extra;
				$entity->profile_link = null;
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
					$Payment->id_user		  = User::id();
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
		public static function timeFromFirst($mode = 'd')
		{
			$today = time(); // or your date as well

		    $first_payment_date = 1431932400; // #hardcoded first payment timestamp

		    $datediff = $today - $first_payment_date;

			switch ($mode) {
				case 'w': {
					return floor($datediff / (60 * 60 * 24 * 7));
				}
				case 'm': {
					return ceil($datediff / (60 * 60 * 24 * 30));
				}
				case 'y': {
					//определяем учебный год, первого платежа
					if(date("j", $first_payment_date) > 1 && date("n", $first_payment_date) >= 5) {
						$first_request_year = date("Y", $first_payment_date);
					} else {
						$first_request_year = date("Y", $first_payment_date) - 1;
					}

					//определяем текущий учебный год
					if(date("j", $today) > 1 && date("n", $today) >= 5) {
						$current_year = date("Y", $today);
					} else {
						$current_year = date("Y", $today) - 1;
					}

					$count_years = 0;

					for($i = $first_request_year; $i<= $current_year; $i++){
						$count_years++;
					}


					return $count_years;
				}
                default: {
					return ceil($datediff / (60 * 60 * 24));
				}
			}
		}

		public function beforeSave()
        {
            if ($this->id_status == self::PAID_CARD) {
                $this->card_first_number = intval($this->card_first_number);
            }
			if ($this->date) {
				$this->date = fromDotDate($this->date);
			}
            // наличные и платеж и не имеет номера и (клиент или анонимный)
           if ($this->isNewRecord && $this->id_status == self::PAID_CASH && $this->id_type == PaymentTypes::PAYMENT && $this->entity_type != Teacher::USER_TYPE) {
               if (! $this->document_number) {
                   $this->document_number = self::dbConnection()->query("select max(document_number) + 1 as last_doc_num from payments where YEAR(`date`) = YEAR(NOW())")->fetch_object()->last_doc_num;
               }
           }

		   if ($this->isNewRecord) {
			   $this->first_save_date = now();
			   $this->id_user = User::id();
		   }
        }

        /**
         * Чтобы не трансформировало account_id в 0, а оставляло NULL
         */
        public function afterSave()
        {
            if ($this->id && ! $this->account_id) {
                dbConnection()->query("UPDATE payments SET account_id=NULL WHERE id=" . $this->id);
            }
        }

        public static function tobePaid($entity_id, $entity_type)
        {
			return self::dbConnection()->query("select ".
                "(select ifnull(sum(v.price), 0) from visit_journal v where v.id_entity = {$entity_id} and year=" . academicYear() . " and v.type_entity = '{$entity_type}') " .
                " - " .
                "(select ifnull(sum(if(p.id_type = 1, p.sum, -1*p.sum)), 0) from payments p where p.entity_id = {$entity_id} and year=" . academicYear() . " and p.entity_type = '{$entity_type}') " .
                "as tobe_paid"
            )->fetch_object()->tobe_paid;
        }
    }
