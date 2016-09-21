<?php
	class Time extends Model {

		public static $mysql_table	= "time";

		const WEEKDAYS = [
			1 => "ПН",
			2 => "ВТ",
			3 => "СР",
			4 => "ЧТ",
			5 => "ПТ",
			6 => "СБ",
			7 => "ВС",
		];

		const WEEKDAYS_FULL = [
			1 => "Понедельник",
			2 => "Вторник",
			3 => "Среда",
			4 => "Четверг",
			5 => "Пятница",
			6 => "Суббота",
			7 => "Воскресенье",
		];

		// карта времени
		const MAP = [
			1 => [1,  2,  3,  4],
			2 => [5,  6,  7,  8 ],
			3 => [9,  10, 11, 12],
			4 => [13, 14, 15, 16],
			5 => [17, 18, 19, 20],
			6 => [21, 22, 23, 24],
			7 => [25, 26, 27, 28],
		];

		public function __construct($array)
		{
			parent::__construct($array);

			if (! $this->isNewRecord) {
				$this->weekday_name = static::WEEKDAYS[$this->day];
			}
		}

		/**
		 * Отсортировано по дням
		 */
		public static function get()
		{
			// @todo: надо кешировать, очерь редко меняется
			$Time = static::findAll();

			$return = [];

			foreach(self::MAP as $day => $time_data) {
				foreach($time_data as $id_time) {
					$return[$day][] = reset(array_filter($Time, function($T) use ($id_time) {
						return $T->id == $id_time;
					}));
				}
			}

			return $return;
		}

		/**
		 * Без дней и сортировки
		 */
		public function getLight()
		{
			$restult = dbConnection()->query("SELECT id, time FROM time");

			while($row = $restult->fetch_object()) {
				$return[$row->id] = $row->time;
			}

			return $return;
		}

        /*
		 * Получить день по TIME ID
		 */
		public static function getDay($id_time)
		{
			return array_keys(array_filter(Time::MAP, function($e) use ($id_time) {
				return in_array($id_time, $e);
			}))[0];
		}
	}
