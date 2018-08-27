<?php
	// Контроллер
	class GoogleIdsController extends Controller
	{
		// Папка вьюх
		protected $_viewsFolder	= "google-ids";

		public static $allowed_users = [Admin::USER_TYPE];

		public function beforeAction()
		{
			$this->addJs("ng-google-ids");
		}

		public function actionMain()
		{
			$ang_init_data = angInit(compact('id_group'));

			$this->setTabTitle('Google IDS');
            $this->render("index", compact('ang_init_data'));
		}

		public function actionAjaxShow()
		{
			extract($_POST);
			$google_ids = explode(' ', $google_ids);

			$result = [];
			foreach($google_ids as $id_google) {
				if (isset($result[$id_google])) {
					continue;
				} else {
					$result[$id_google] = [
						'requests' => [],
						'students' => [],
						'payments' => []
					];
				}

				$query = dbConnection()->query("SELECT id, id_student FROM requests WHERE id_google='{$id_google}'");

				if ($query->num_rows) {
					while($row = $query->fetch_object()) {
						$result[$id_google]['requests'][] = $row->id;
						if (! in_array($row->id_student, $result[$id_google]['students'])) {
							$result[$id_google]['students'][] = $row->id_student;
						}
					}
					foreach($result[$id_google]['students'] as $id_student) {
						$payments = Payment::getByStudentId($id_student);
						if ($payments) {
							$result[$id_google]['payments'] = array_merge($payments, $result[$id_google]['payments']);
						}
					}
				} else {
					$result[$id_google] = null;
				}
			}
			returnJsonAng($result);
		}
	}
