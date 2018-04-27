<?php

	// Контроллер
	class StudentsController extends Controller
	{
		public static $allowed_users = [User::USER_TYPE];

		public $defaultAction = "findById";

		// Папка вьюх
		protected $_viewsFolder	= "request";

		/**
		 * Перенаправить на заявку студента по ID.
		 *
		 */
		public function actionFindById()
		{
			$id_student = $_GET["id_student"];
			$Request = Request::findByStudent($id_student);
			$_GET["id"] = $Request->id;

			$controller = new RequestController();
			$controller->beforeAction();
			$controller->actionEdit($id_student);
// 			$this->redirect("requests/edit/" . $Request->id, true);
		}

		##################################################
		###################### AJAX ######################
		##################################################



	}
