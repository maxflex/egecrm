<?php
	class Template extends Model
	{
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "templates";
		protected $_inline_data = ["who"];

		const STUDENT 		 = 1;
		const REPRESENTATIVE = 2;
		const TEACHER 		 = 3;

		public static function get($number, $params)
		{
            /* @var $Template Template */
			$Template = self::find([
				"condition" => "number=$number"
			]);

			$Template->_complie_vars($params, $number);

			return $Template->text;
		}

		public static function getFull($number, $params)
		{
			$Template = self::find([
				"condition" => "number=$number"
			]);

			$Template->_complie_vars($params);

			return $Template;
		}

		public function toStudents()
		{
			return in_array(self::STUDENT, $this->who);
		}

		public function toRepresentatives()
		{
			return in_array(self::REPRESENTATIVE, $this->who);
		}

		public function toTeachers()
		{
			return in_array(self::TEACHER, $this->who);
		}

		private function _complie_vars($params, $number = null)
		{
			if ($number == 18) {
				$Student = Student::findById($params['id']);
				// $Contract  = $Student->getLastContract(academicYear());
				$Payments = $Student->getPayments(academicYear());

				$price = $Student->getLastContract(academicYear())->final_sum;

				foreach($Payments as $payment) {
					if ($payment->id_type == 2) {
						$price += $payment->sum;
					} else {
						$price -= $payment->sum;
					}
				}

				$this->text = str_replace('{name}', $Student->first_name . ' ' . $Student->middle_name, $this->text);
				$this->text = str_replace('{price}', $price, $this->text);
			} else {
				$this->text = str_replace('{name}',  User::fromSession()->first_name, $this->text);

				foreach ($params as $param => $value) {
					$this->text = str_replace('{' . $param .'}', $value, $this->text);
				}
			}
		}

	}