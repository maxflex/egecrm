<?php

	// Контроллер
	class ClientsController extends Controller
	{
		public $defaultAction = "list";

		// Папка вьюх
		protected $_viewsFolder	= "clients";

		public function beforeAction()
		{
			$this->addJs("ng-clients-app");
		}

		public function actionList()
		{
			// не надо панель рисовать
			$this->_custom_panel = true;
			$ang_init_data = angInit([
				'currentPage'	=> $_GET['page'] ? $_GET['page'] : 1,
				'user'	        => User::fromSession(),
			]);

			$this->render("list", [
				"sort"		=> $_GET['sort'],
				"ang_init_data" => $ang_init_data,
			]);
		}

		public function actionSubjects()
		{
			// не надо панель рисовать
			$this->_custom_panel = true;

			$ang_init_data = angInit([
				'currentPage' => $_GET['page'] ? $_GET['page'] : 1,
				'Subjects' => Subjects::$three_letters,
				'Grades' => Grades::$short,
			]);

			$this->render("subjects", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		public function actionAjaxGetStudents()
		{
			extract($_POST);

			returnJsonAng(
				Student::getData($page)
			);
		}

		public function actionAjaxGetSubjects()
		{
			extract($_POST);

			returnJsonAng(
				self::_getSubjects($page)
			);
		}


		private static function _getSubjects($page)
		{
			if (!$page) {
				$page = 1;
			}
			// С какой записи начинать отображение, по формуле
			$start_from = ($page - 1) * 100;

			$search = isset($_COOKIE['clients_subjects']) ? json_decode($_COOKIE['clients_subjects']) : (object)[];

			// получаем данные
			$query = "
				FROM contract_subjects cs
				JOIN contracts c ON cs.id_contract = c.id
				JOIN contract_info ci ON ci.id_contract = c.id_contract
				JOIN students s ON s.id = ci.id_student "
				. (! isBlank($search->year) ? "JOIN student_sums ss ON (ss.id_student = ci.id_student and ss.year = {$search->year})" : "") . "
				WHERE c.current_version = 1 "
				. (! isBlank($search->year) ? " AND ci.year={$search->year} " : '')
				.  (! isBlank($search->status) ? " AND cs.status={$search->status} " : '');

			$count = dbConnection()->query("SELECT COUNT(*) AS cnt " . $query)->fetch_object()->cnt;

			//28/(28+31) * 100300

			$query = "SELECT cs.*, CONCAT(s.last_name, ' ', s.first_name, ' ', s.middle_name) as `student_name`,
				(select sum(`count`) from contract_subjects where contract_subjects.id_contract = cs.id_contract) as `total_count`,
				s.id as `id_student`, ci.grade, c.sum " . (! isBlank($search->year) ? ", ss.sum as deposit" : "")
				. $query;
				// . " LIMIT {$start_from}, 100";

			$result = dbConnection()->query($query);

			$data = [];

			while($row = $result->fetch_object()) {
				if ($row->total_count) {
					$row->subject_sum = round($row->count / $row->total_count * $row->sum);
				} else {
					$row->subject_sum = 0;
				}
				$data[] = $row;
			}

			$visits = [];
			if (! isBlank($search->year)) {
				foreach($data as $d) {
					if (! isset($visits[$d->id_student][$d->id_subject])) {
						$query = dbConnection()->query("SELECT presence, late from visit_journal where year={$search->year} and id_entity={$d->id_student} and type_entity='STUDENT' and id_subject={$d->id_subject}");
						while($row = $query->fetch_object()) {
							if ($row->presence == 2) {
								$visits[$d->id_student][$d->id_subject][] = 1;
							} else {
								if ($row->late > 0) {
									$visits[$d->id_student][$d->id_subject][] = 2;
								} else {
									$visits[$d->id_student][$d->id_subject][] = 3;
								}
							}
						}
					}
				}
			}

			return compact('data', 'count', 'visits');
		}
	}
