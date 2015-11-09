<?php
	class AsController extends Controller
	{
		public static $allowed_users = [User::USER_TYPE, Teacher::USER_TYPE, Student::USER_TYPE];
		
		public function actionMain()
		{
			$type 	= strtoupper($_GET['type']);
			$id		= $_GET['id'];
			
			if (!empty($type) && !empty($id)) {
				User::fromSession()->type 		= $type;
				User::fromSession()->id_entity 	= $id;
				
				// запоминаем url с которого ушли в режим просмотра, чтобы потом на него вернуться
				User::fromSession()->AsUrl = $_SERVER['HTTP_REFERER'];
				
				User::fromSession()->AsUser = User::find([
					"condition" => "type='$type' AND id_entity=$id"
				]);
			}
			
			$this->redirect("groups", true);
		}
		
		public function actionCancel()
		{
			$User = User::findById(User::fromSession()->id);

			User::fromSession()->type 		= $User->type;
			User::fromSession()->id_entity	= 0;
			
			unset(User::fromSession()->AsUser);
			
			$this->redirect(User::fromSession()->AsUrl, false);
		}
	}