<?php
	class ExamDay extends Model
	{

		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "exam_days";


		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array)
		{
			parent::__construct($array);
		}


		public static function addData($data, $year)
		{
			self::deleteAll([
				"condition" => "YEAR(STR_TO_DATE(date, '%d.%m.%Y')) = " . ($year + 1),
			]);

			foreach($data as $grade => $d) {
				foreach($d as $id_subject => $d2) {
					foreach ($d2 as $letter => $date) {
						if ($date) {
							self::add([
								"id_subject"	=> $id_subject,
								"letter"		=> $letter,
								"date"			=> $date,
								"grade"			=> $grade,
							]);
						}
					}
				}
			}
		}

		public static function getData($year)
		{
			$data = self::findAll([
				"condition" => "YEAR(STR_TO_DATE(date, '%d.%m.%Y')) = " . ($year + 1),
			]);

			foreach($data as $d) {
				$return[$d->grade][$d->id_subject][$d->letter] = $d->date;
			}

			return $return;
		}

		public static function getExamDates($Group)
		{
			$data = self::findAll([
				"condition" => "grade={$Group->grade} AND YEAR(STR_TO_DATE(date, '%d.%m.%Y')) = " . ($Group->year + 1)
			]);

			$return = [
				'this_subject' 	=> [],
				'other_subject' => [],
			];

			foreach($data as $d) {
				if ($d->id_subject == $Group->id_subject) {
					$return['this_subject'][] = date("Y-m-d", strtotime($d->date));
				} else {
					$return['other_subject'][] = date("Y-m-d", strtotime($d->date));
				}
			}

			return $return;
		}

		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

	}
