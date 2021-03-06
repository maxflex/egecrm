<?php

	// Контроллер
	class TendencyController extends Controller
	{
		public $defaultAction = "index";

		public static $allowed_users = [Admin::USER_TYPE, Teacher::USER_TYPE, Student::USER_TYPE];

		// Папка вьюх
		protected $_viewsFolder	= "tendency";

		public function actionIndex()
		{
			$this->setTabTitle("Тенденции");
            $this->addJs('ng-tendency-app');
            $this->render('index');

            $ang_init_data = angInit([
                "Subjects" 		=> Subjects::$three_letters,
                "Grades"		=> Grades::$all,
                "GroupLevels"	=> GroupLevels::$short,
                "Branches"		=> Branches::getAll(),
            ]);

            $this->render("index", [
                "ang_init_data" => $ang_init_data
            ]);
		}

        public function actionAjaxSearch()
        {
            extract($_POST['search']);

            if (isset($subjects)) {
                $subjects_condition = [];
                foreach($subjects as $id_subject) {
                    $subjects_condition[] = "FIND_IN_SET({$id_subject}, subjects)";
                }
            }
            if (isset($grades)) {
                $grades_condition = [];
                foreach($grades as $grade) {
                    $grades_condition[] = "grade={$grade}";
                }
            }
            if (isset($branches)) {
                $branches_condition = [];
                foreach($branches as $id_branch) {
                    $branches_condition[] = "FIND_IN_SET({$id_branch}, branches)";
                }
            }

            $query = dbConnection()->query("SELECT id_student FROM requests WHERE adding=0 AND date>='2016-05-01' "
                . (isset($subjects) ? ' AND (' . implode(' OR ', $subjects_condition) . ')' : "")
                . (isset($grades) ? ' AND (' . implode(' OR ', $grades_condition) . ')' : "")
                . (isset($branches) ? ' AND (' . implode(' OR ', $branches_condition) . ')' : ""));

            $return['count'] = $query->num_rows;

            $student_ids = [];
            while($row = $query->fetch_object()) {
                if (! in_array($row->id_student, $student_ids)) {
                    $student_ids[] = $row->id_student;
                }
            }

            $contracts_count = 0;
            $payments_sum = 0;
            foreach($student_ids as $id_student) {
                // есть ли договор на 16-17 год?
                $query = dbConnection()->query("SELECT 1 FROM contracts c
                    JOIN contract_info ci ON ci.id_contract = c.id_contract
                    WHERE ci.id_student={$id_student} AND ci.year=2016");
                if ($query->num_rows) {
                    $contracts_count++;
                }

                $query = dbConnection()->query("SELECT id_type, sum FROM payments WHERE entity_id = {$id_student} AND entity_type='STUDENT' AND year=2016");
                while($row = $query->fetch_object()) {
                    if ($row->id_type == 2) {
                        $payments_sum -= $row->sum;
                    } else {
                        $payments_sum += $row->sum;
                    }
                }
            }

            $return['contracts_count'] = $contracts_count;
            $return['payments_sum'] = $payments_sum;

            returnJsonAng($return);
        }

	}
