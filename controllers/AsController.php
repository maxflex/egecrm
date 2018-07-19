<?php
	class AsController extends Controller
	{
		public static $allowed_users = [Admin::USER_TYPE, Teacher::USER_TYPE, Student::USER_TYPE];
		
		public function actionMain()
		{
			$type 	= strtoupper($_GET['type']);
			$id		= $_GET['id'];
			
			User::enterViewMode($id, $type);
			
			$this->redirect(strtolower($type) . "s/groups", true);
		}
		
		public function actionCancel()
		{
			User::quitViewMode();			
			$this->redirect($_SESSION["view_mode_url"], false);
		}
	}