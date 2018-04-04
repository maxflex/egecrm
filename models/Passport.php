<?php
	class Passport extends Model
	{

		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "passports";

		// Паспорт представителя
		const TYPE_REPRESENTATIVE 	= 1;
		// Паспорт ученика
		const TYPE_STUDENT 			= 2;

		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array)
		{
			parent::__construct($array);

			if ($this->series == 0) {
				$this->series = null;
			} else {
				$this->series = $this->series . ' '; // FORCE STRING
			}

			if ($this->number == 0) {
				$this->number = null;
			} else {
				$this->number = $this->number . ' '; // FORCE STRING
			}

			if ($this->date_issued) {
				$this->date_issued = toDotDate($this->date_issued);
			}

			if ($this->date_birthday) {
				$this->date_birthday = toDotDate($this->date_birthday);
			}
		}

		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/


		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		public function beforeSave()
		{
			if ($this->date_issued) {
				$this->date_issued = fromDotDate($this->date_issued);
			}

			if ($this->date_birthday) {
				$this->date_birthday = fromDotDate($this->date_birthday);
			}
		}

	}
