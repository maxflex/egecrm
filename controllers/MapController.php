<?php

    class MapController extends Controller
    {
        public $defaultAction = "index";

        public static $allowed_users = [Admin::USER_TYPE];

        // Папка вьюх
        protected $_viewsFolder	= "map";

        public function beforeAction()
        {
            $this->addJs("//maps.google.ru/maps/api/js?key=AIzaSyAXXZZwXMG5yNxFHN7yR4GYJgSe9cKKl7o&libraries=places&language=ru", true);
            $this->addJs('maps.controller, ng-map-app');
        }

        public function actionIndex()
        {
            $this->setTabTitle("Карта");


            $ang_init_data = angInit([
                "Subjects" 		=> Subjects::$three_letters,
                "Grades"		=> Grades::$all,
            ]);

            $this->render("index", [
                "ang_init_data" => $ang_init_data,
            ]);
        }

        public function actionMarkers()
        {
            $search = isset($_COOKIE['map']) ? json_decode($_COOKIE['map']) : (object)[];

            $conditions = [];
            if (isset($search->subjects) && count($search->subjects)) {
                $subject_conditions = [];
                foreach($search->subjects as $id_subject) {
                    $subject_conditions[] = "cs.id_subject={$id_subject}";
                }
                $conditions[] = "(" . implode(" OR ", $subject_conditions) . ")";
            }

            if (isset($search->grades) && count($search->grades)) {
                $grade_conditions = [];
                foreach($search->grades as $grade) {
                    $grade_conditions[] = "ci.grade={$grade}";
                }
                $conditions[] = "(" . implode(" OR ", $grade_conditions) . ")";
            }

            if (isset($search->include_branches) && count($search->include_branches)) {
                $include_branches_conditions = [];
                foreach($search->include_branches as $branch) {
                    $include_branches_conditions[] = "FIND_IN_SET({$branch}, s.branches)";
                }
                $conditions[] = "(" . implode(" OR ", $include_branches_conditions) . ")";
            }

            if (isset($search->exclude_branches) && count($search->exclude_branches)) {
                $exclude_branches_conditions = [];
                foreach($search->exclude_branches as $branch) {
                    $exclude_branches_conditions[] = "NOT FIND_IN_SET({$branch}, s.branches)";
                }
                $conditions[] = "(" . implode(" AND ", $exclude_branches_conditions) . ")";
            }

            $conditions = count($conditions) ? " AND " . implode(" AND ", $conditions) : '';

            $query = "
				SELECT m.*, s.first_name, s.last_name, s.middle_name
				FROM students s
                JOIN markers m ON (m.id_owner = s.id AND m.owner = 'STUDENT')
				JOIN contract_info ci on (ci.id_student = s.id and ci.id_contract = (
					select max(id_contract)
					from contract_info ci2
					where ci2.year = ci.year and ci.id_student = ci2.id_student
				))
				JOIN contracts c on c.id_contract = ci.id_contract
				LEFT JOIN contract_subjects cs on cs.id_contract = c.id
				WHERE c.current_version=1 AND cs.id_subject > 0 AND cs.status > 1
                    {$conditions}
					AND ci.year={$search->year}
			";



            $result = dbConnection()->query($query);

            $markers = [];
            while ($row = $result->fetch_object()) {
                $markers[] = $row;
            }

            returnJsonAng(
				compact('markers')
			);
        }
    }
