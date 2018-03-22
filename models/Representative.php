<?php
	class Representative extends Model
	{

		const USER_TYPE = 'REPRESENTATIVE';

		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "representatives";

		// Номера телефонов
		public static $_phone_fields = ["phone", "phone2", "phone3"];


		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array)
		{
			parent::__construct($array);

			// Включаем связи
			$this->Passport	= Passport::findById($this->id_passport);
		}

		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/



		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		public function name($order = 'fio')
		{
			return getName($this->last_name, $this->first_name, $this->middle_name, $order);
		}

		public function beforeSave()
		{
			// Очищаем номера телефонов
			foreach (static::$_phone_fields as $phone_field) {
				$this->{$phone_field} = cleanNumber($this->{$phone_field});
			}
		}

		public function afterSave()
		{
			// синхронизация email
			$user = User::find([
				'condition' => "id_entity={$this->id} && type='" . self::USER_TYPE . "'"
			]);
			if ($user) {
				$user->email = $this->email;
				$user->save('email');
			}
		}

		/**
		 * Сколько номеров установлено.
		 *
		 */
		public function phoneLevel()
		{
			if (!empty($this->phone3)) {
				return 3;
			} else
			if (!empty($this->phone2)) {
				return 2;
			} else {
				return 1;
			}
		}

		/**
		 * Добавить паспорт.
		 *
		 * $save - сохранить новое поле?
		 */
		public function addPassport($Passport, $save = false)
		{
			$this->Passport 		= $Passport;
			$this->id_passport		= $Passport->id;

			if ($save) {
				$this->save("id_passport");
			}
		}



		/**
		 * Получить студента.
		 *
		 */
		public function getStudent()
		{
			return Student::find([
				"condition"	=> "id_representative={$this->id}"
			]);
		}

	}
