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

			Request::deleteById($id_request);
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


		public function actionAjaxCheckPhone()
		{
			extract($_POST);

			// Находим оригинальную заявку
			$OriginalRequest = Request::findById($id_request);

			// Находим заявку с таким номером
			# Ищем заявку с таким же номером телефона
			$Request = Request::find([
				"condition"	=> "(phone='".$phone."' OR phone2='".$phone."' OR phone3='".$phone."') AND id_student!=".$OriginalRequest->id_student
			]);

			// Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
			if ($Request) {
				returnJson($Request->Student->id);
			}

			# Ищем ученика с таким же номером телефона
			$Student = Student::find([
				"condition"	=> "(phone='".$phone."' OR phone2='".$phone."' OR phone3='".$phone."') AND id_student!=".$OriginalRequest->id_student
			]);

			// Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
			if ($Student) {
				returnJson($Student->id);
			}

			# Ищем представителя с таким же номером телефона
			$Representative = Representative::find([
				"condition"	=> "(phone='".$phone."' OR phone2='".$phone."' OR phone3='".$phone."')"
			]);

			// Если заявка с таким номером телефона уже есть, подхватываем ученика оттуда
			if ($Representative) {
				returnJson($Representative->getStudent()->id);
			}



			// возвращается, если номера нет в базе
			returnJson(null);
		}
	}
