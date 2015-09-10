<?php	// Контроллер	class SearchController extends Controller	{		public $defaultAction = "search";		// Папка вьюх		protected $_viewsFolder	= "search";		/**		 * Поиск.		 *		 */		public function actionSearch()		{			extract($_POST);			$this->setTabTitle("Рультаты поиска по запросу «". $text. "»");			$this->addJs("ng-search-app");			# если пустой запрос			$text = trim($text);			$text = str_replace("-", "", $text); // удаляем все тире, чтобы искалось по номеру телефона и так, и так			if (empty($text)) {				$this->render("no_results");				return;			}			$table = Student::$mysql_table;//			error_reporting(E_ALL);//			ini_set("display_errors", 1);						self::_generateCondition($table, $text, [					"first_name", "last_name", "phone", "phone2", "phone3"			], $sql);			self::_generateCondition(Representative::$mysql_table, $text, [					"first_name", "last_name", "phone", "phone2", "phone3"			], $sql);			self::_generateCondition(Request::$mysql_table, $text, [					"phone", "phone2", "phone3"			], $sql);			$search_condition = implode(" OR ", $sql);			$search_results = dbConnection()->query("				SELECT $table.id FROM $table				 LEFT	JOIN ". Representative::$mysql_table 	." on ". Representative::$mysql_table .".id = $table.id_representative				 LEFT	JOIN ". Request::$mysql_table 				." on				 (". Request::$mysql_table .".id_student = $table.id AND ". Request::$mysql_table .".adding = 0)				WHERE $search_condition				GROUP BY $table.id			");			while ($row = $search_results->fetch_row()) {				$student_ids[] = $row[0];			}/*			$Students = Student::findAll([				"condition" => "id IN (". implode(",", $student_ids) .")"			]);*/						$query = dbConnection()->query("				SELECT id, first_name, last_name, middle_name FROM students				WHERE id IN (". implode(",", $student_ids) .")			");						if (!$query->num_rows) {				$this->render("no_results");			} else {				while ($row = $query->fetch_object()) {					$Students[] = $row;				}				$this->render("results", [					"Students" => $Students,				]);			}		}		private function _generateCondition($table, $text, $search_fields, &$sql)		{			foreach ($search_fields as &$search_field) {				$search_field = $table . "." . $search_field;			}			foreach ($search_fields as $search_field) {				$sql[] = "CONVERT($search_field USING utf8) LIKE '%$text%'";			}		}		##################################################		###################### AJAX ######################		##################################################	}