<?php

	// Контроллер
	class TestController extends Controller
	{
		public $defaultAction = "test";

		// Папка вьюх
		protected $_viewsFolder	= "test";
		
		public function actionBeforeAction()
		{
// 			$this->setTabTitle("Тест");
		}
		
		public function actionImap()
		{
			$mailbox = new PhpImap\Mailbox('{imap.yandex.ru:993/imap/ssl}', 'makcyxa-k', 'rrn1840055');
			
			$mailsIds = $mailbox->searchMailBox('FROM maksim@kolyaidn.com');
			
//			$inboxMail = $mailbox->lecplay();
			
			var_dump($mailbox);
		}
		
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
		
		public function actionMap()
		{
			$this->setTabTitle("Тестирование алгоритма метро");
			
			$this->addJs("//maps.google.ru/maps/api/js?libraries=places", true);
			$this->addJs("maps.controller, ng-test-app");
			
			$this->render("map");
		}
		
		public function actionMailer()
		{
			$mail = initMailer();
			
			$mail->addAddress("makcyxa-k@yandex.ru");
			$mail->Body = "Замалым";
			$mail->Subject = 'Here is the subject';

			if(!$mail->send()) {
			    echo 'Message could not be sent.';
			    echo 'Mailer Error: ' . $mail->ErrorInfo;
			} else {
			    echo 'Message has been sent';
			}			
		}
		
		public function actionDuplicate()
		{
			$res = isDuplicate("79205556776", 19);
			var_dump($res);
		}
		
		function actionSwc()
		{
			$q = Student::getWithoutContract();
			
			var_dump($q);
		}
		
		function actionAddTask()
		{
// 			$this->addJs("//cdn.ckeditor.com/4.5.2/full-all/ckeditor.js", true);
			$this->setTabTitle("Редактирование задачи");
			$this->render("add_task");
		}
		
		##################################################
		###################### AJAX ######################
		##################################################
	
	
	}
