<?php

	// Контроллер
	class AjaxController extends Controller
	{
		//
		public $defaultAction = "default";

		// Папка вьюх
		protected $_viewsFolder	= "";



		##################################################
		###################### AJAX ######################
		##################################################
		
		public function actionAjaxTest()
		{
			$Request = new Request([
				"phone" => "+7 (911) 111-11-11"
			]);
			
			$Request->processIncoming();
			
			preType($Request);
			
			exit("here");
		}

		/**
		 * Добавление комментария.
		 *
		 */
		public function actionAjaxAddComment()
		{
			$Comment = Comment::add($_POST);

			// Возвращаем форматированную дату и ник пользователя
			toJson([
				"date"			=> date("d.m.y в H:i", time()),
				"user"			=> User::fromSession()->login,
				"id" 			=> $Comment->id,
				"coordinates"	=> $Comment->getCoordinates(),
			]);
		}

		/**
		 * Редактирование комментария.
		 *
		 */
		public function actionAjaxEditComment()
		{
			extract($_POST);

			$Comment = Comment::findById($id);
			$Comment->comment = $comment;
			$Comment->save("comment");
		}
		
		
		public function actionAjaxTestDeleteStudent()
		{
			Student::fullDelete(651);
		}

		/**
		 * Удалить комментарий.
		 *
		 */
		public function actionAjaxDeleteComment()
		{
			Comment::findById($_POST["id"])->delete();
		}

		public function actionAjaxPaymentAdd()
		{
			echo Payment::add($_POST)->id;
		}
		
		public function actionAjaxConfirmPayment()
		{
			extract($_POST);
			
			Payment::updateById($id, [
				"confirmed" => 1
			]);
		}

		public function actionAjaxDeletePayment()
		{
			Payment::deleteById($_POST["id_payment"]);
		}

		public function actionAjaxPaymentEdit()
		{
			$Payment = new Payment($_POST);
			$Payment->save();
		}


		public function actionAjaxContractSave()
		{
			returnJson(Contract::addNewAndReturn($_POST));
		}

		public function actionAjaxContractEdit()
		{
			Contract::edit($_POST);
		}

		public function actionAjaxUploadFiles()
		{
			$Contract = Contract::findById($_POST["id_contract"]);
			$Contract->files = $_POST["files"];
			$Contract->uploadFile();
		}

		public function actionAjaxContractDelete()
		{
			extract($_POST);

			Contract::deleteAll([
				"condition" => "id=$id_contract OR id_contract=$id_contract"
			]);
		}

		public function actionAjaxContractDeleteHistory()
		{
			extract($_POST);

			Contract::deleteAll([
				"condition" => "id=$id_contract"
			]);
		}

		public function actionAjaxChangeRequestUser()
		{
			extract($_POST);

			$Request = Request::findById($id_request);
			$Request->id_user = $id_user_new;
			$Request->save();

/*
			Request::updateById($id_request, [
				"id_user" => $id_user_new,
			]);
*/
		}

		public function actionAjaxDeleteRequest()
		{
			extract($_POST);
			
			// нельзя удалять, если меньше одной заявки
			$Request = Request::findById($id_request);
			$RequestDuplicates = $Request->getDuplicates();
			
			// удаляем заявку только если есть дубликаты (если она не единственная)
			if ($RequestDuplicates) {
				Request::deleteById($id_request);
			}
		}


		public function actionAjaxDeleteStudent()
		{
			extract($_POST);

			// удаляем все заявки ученика
			Request::deleteAll([
				"condition" => "id_student=$id_student"
			]);

			Payment::deleteAll([
				"condition" => "id_student=$id_student"
			]);

			Contract::deleteAll([
				"condition" => "id_student=$id_student"
			]);

			Freetime::deleteAll([
				"condition" => "id_student=$id_student"
			]);

			Marker::deleteAll([
				"condition" => "owner='STUDENT' AND id_owner=$id_student"
			]);

			Comment::deleteAll([
				"condition" => "id_place=$id_student AND place='". Comment::PLACE_STUDENT ."'"
			]);

			Student::deleteById($id_student);
		}

		public function actionAjaxMinimizeStudent()
		{
			extract($_POST);

			$Student = Student::findById($id_student);
			$Student->minimized = $minimized;
			$Student->save("minimized");
		}

		
		/**
		 * Эта функция вынесена в отдельную функцию в isDuplicate (functions.php).
		 * 
		 */
		public function actionAjaxCheckPhone()
		{
			extract($_POST);
			
			returnJSON(isDuplicate($phone, $id_request));
		}
		
		public function actionAjaxSendSms()
		{
			extract($_POST);
			
			$SMS = SMS::send($number, $message);
			$SMS->getCoordinates();
			
			returnJSON($SMS);
		}
		
		public function actionAjaxSmsHistory() {
			extract($_POST);
			
			$number = cleanNumber($number);
			
			$History = SMS::findAll([
				"condition" => "number='$number'",
				"order"		=> "date DESC",
			]);
			
			returnJSON($History);
		}
		
		public function actionAjaxUpdateUserCache()
		{
			$Users = User::findAll();
							
			foreach ($Users as $User) {
				$return[$User->id] = $User->dbData();
			}
			
			$Users = $return;
			memcached()->set("Users", $Users, 2 * 24 * 3600); // кеш на 2 дня
		}
	}
