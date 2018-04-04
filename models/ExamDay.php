<?php
	class ExamDay extends Model
	{

		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "exam_days";


		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array)
		{
			parent::__construct($array);

			$this->date_original = $this->date;
			if ($this->date) {
				$this->date = toDotDate($this->date);
			}
		}


		public static function addData($data, $year)
		{
			self::deleteAll([
				"condition" => "YEAR(`date`) = " . ($year + 1),
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

		public function beforeSave()
        {
			if ($this->date) {
				$this->date = fromDotDate($this->date);
			}
        }

		public static function getData($year)
		{
			$data = self::findAll([
				"condition" => "YEAR(`date`) = " . ($year + 1),
			]);

			foreach($data as $d) {
				$return[$d->grade][$d->id_subject][$d->letter] = $d->date;
			}

			return $return;
		}

		public static function getExamDates($Group)
		{
			$data = self::findAll([
				"condition" => "grade={$Group->grade} AND YEAR(`date`) = " . ($Group->year + 1)
			]);

			$return = [
				'this_subject' 	=> [],
				'other_subject' => [],
			];

			foreach($data as $d) {
				if ($d->id_subject == $Group->id_subject) {
					$return['this_subject'][] = $d->date_original;
				} else {
					$return['other_subject'][] = $d->date_original;
				}
			}

			return $return;
		}

		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

	}
