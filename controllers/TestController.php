<?php

	// Контроллер
	class TestController extends Controller
	{
		public $defaultAction = "test";

		// Папка вьюх
		protected $_viewsFolder	= "";

		
		// Перевести номера телефонов из форматированных
		public function actionUpdatePhones()
		{
			$Requests = Request::findAll([
				"condition" => "adding = 0 && (phone !='' OR phone2 != '' OR phone3 != '')",
			]);
			
			$Students = Student::findAll([
				"condition" => "phone !='' OR phone2 != '' OR phone3 != ''"
			]);
			
			$Representatives = Representative::findAll([
				"condition" => "phone !='' OR phone2 != '' OR phone3 != ''"
			]);
			
			foreach ($Requests as &$Request) {
				foreach (Request::$_phone_fields as $phone_field) {
					if ($Request->{$phone_field} != "") {
						$Request->{$phone_field} = cleanNumber($Request->{$phone_field});
						$Request->save($phone_field);
					}	
				}
			}
			
			foreach ($Students as &$Student) {
				foreach (Request::$_phone_fields as $phone_field) {
					if ($Student->{$phone_field} != "") {
						$Student->{$phone_field} = cleanNumber($Student->{$phone_field});
						$Student->save($phone_field);
					}	
				}
			}
			
			foreach ($Representatives as &$Representative) {
				foreach (Request::$_phone_fields as $phone_field) {
					if ($Representative->{$phone_field} != "") {
						$Representative->{$phone_field} = cleanNumber($Representative->{$phone_field});
						$Representative->save($phone_field);
					}	
				}
			}
		}

		##################################################
		###################### AJAX ######################
		##################################################
	
	
	}
