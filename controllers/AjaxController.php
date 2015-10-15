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
				"confirmed" => $confirmed
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




		# TEACHER PAYMENTS
		public function actionAjaxTeacherPaymentAdd()
		{
			echo TeacherPayment::add($_POST)->id;
		}
		
		public function actionAjaxConfirmTeacherPayment()
		{
			extract($_POST);
			
			TeacherPayment::updateById($id, [
				"confirmed" => $confirmed
			]);
		}
		
		public function actionAjaxDeleteTeacherPayment()
		{
			TeacherPayment::deleteById($_POST["id_payment"]);
		}

		public function actionAjaxTeacherPaymentEdit()
		{
			$Payment = new TeacherPayment($_POST);
			$Payment->save();
		}
		# / TEACHER PAYMENTS
		
		
		
		public function actionAjaxContractSave()
		{
			returnJson(Contract::addNewAndReturn($_POST));
		}

		public function actionAjaxContractEdit()
		{
			returnJson(Contract::edit($_POST));
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
			} else {
				// иначе удаляем заявку вместе с учеником
				$_POST["id_student"] = $Request->id_student;
				self::actionAjaxDeleteStudent();
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
		
		public function actionAjaxSendGroupSms()
		{
			extract($_POST);
			
			$Group = Group::findById($id_place);
				
			$Students = $Group->getStudents();
			
			if ($to_students == "true") {
				foreach ($Students as $Student) {
					foreach (Student::$_phone_fields as $phone_field) {
						$number = $Student->{$phone_field};
						
						if (!empty(trim($number))) {
							$msg = $message;
							if ($Student->login && $Student->password) {
								$msg = str_replace('{entity_login}', $Student->login, $msg);
								$msg = str_replace('{entity_password}', $Student->password, $msg);
							}
							$messages[] = [
								"type"      => "Ученику #" . $Student->id,
								'number' 	=> $number,
								'message'	=> $msg,
							];
						}
					}
				}
			}
			
			if ($to_representatives == "true") {
				foreach ($Students as $Student) {
					if ($Student->Representative) {
						foreach (Student::$_phone_fields as $phone_field) {
							$number = $Student->Representative->{$phone_field};
							
							if (!empty(trim($number))) {
								$msg = $message;
								if ($Student->login && $Student->password) {
									$msg = str_replace('{entity_login}', $Student->login, $msg);
									$msg = str_replace('{entity_password}', $Student->password, $msg);
								}
								$messages[] = [
									"type"      => "Представителю #" . $Student->Representative->id,
									'number' 	=> $number,
									'message'	=> $msg,
								];
							}
						}
					}
				}
			}
			
			if ($to_teacher == "true") {
				$Teacher = Teacher::findById($Group->id_teacher);
				$msg = $message;
				if ($Teacher->login && $Teacher->password) {
					$msg = str_replace('{entity_login}', $Teacher->login, $msg);
					$msg = str_replace('{entity_password}', $Teacher->password, $msg);
				}
				
				foreach (Student::$_phone_fields as $phone_field) {
					$number = $Teacher->{$phone_field};
					
					if (!empty(trim($number))) {				
						$messages[] = [
							"type"      => "Учителю #" . $Teacher->id,
							'number' 	=> $number,
							'message'	=> $msg,
						];
					}
				}
			}
			
			$sent_to = [];
			foreach ($messages as $message) {
				if (!in_array($message['number'], $sent_to)) {
					SMS::send($message['number'], $message['message'], $_POST + ["additional" => 3]);
					$sent_to[] = $message['number'];
					
					// debug
					$body .= "<h3>" . $message["type"] . "</h3>";
					$body .= "<b>Номер: </b>" . $message['number']."<br><br>";
					$body .= "<b>Сообщение: </b>" . $message['message']."<hr>";
				}
			}

			Email::send("makcyxa-k@yandex.ru", "Групповое СМС", $body);
			
//			SMS::sendToNumbers($numbers, $message, $_POST + ["additional" => $additional]);
			
			returnJSON(count($sent_to));
/*
			$SMS = SMS::send($number, $message);
			$SMS->getCoordinates();
			
			returnJSON($SMS);
*/
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
		
		public function actionAjaxSendEmail()
		{
			extract($_POST);
			
			// Group send
			if ($mode == 2) {
				$Group = Group::findById($id_place);
				
				$Students = $Group->getStudents();
				
				if ($to_students == "true") {
					foreach ($Students as $Student) {
						if (!empty($Student->email)) {
							$email[] = $Student->email;
						}
					}
					$additional = 1;
				}
				
				if ($to_representatives == "true") {
					foreach ($Students as $Student) {
						if ($Student->Representative) {
							if (!empty($Student->Representative->email)) {
								$email[] = $Student->Representative->email;
							}
						}
					}
					// 0 if nothing selected
					// 1 if only to students
					// 2 if only to representatives
					// 3 if to both
					$additional += 2; 
				}
			}
			
			$Email = Email::send($email, $subject, $message, $files, $place, $id_place, $additional);
			$Email->getCoordinates();
			
			returnJSON($Email);
		}
		
		public function actionAjaxEmailHistory() {
			extract($_POST);
			
			$History = Email::findAll([
				"condition" => $email ? "email='$email'" : "place='$place' AND id_place=$id_place",
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
		
		public function actionAjaxGetFile()
		{
			extract($_GET);
			
			if (file_exists($file_name)) {
				// указываем размер
				$size = round(filesize($file_name) / 1000000, 3); // в мегабайтах, 1 цифра после запятой
				
				// если размер меньше мегабайта, отобразить в киллобайтах
				if ($size < 1) {
					$size = round($size * 1000) . " Кб";
				} else {
					$size = round($size, 1) . " Мб";
				}
				
				returnJson($size);
			} else {
				returnJson(false);	
			}
		}
		
		public function actionAjaxBranchLoadAdd()
		{
			BranchLoad::add($_POST);
		}
		
		public function actionAjaxBranchLoadChange()
		{
			extract($_POST);
			
			$BranchLoad = BranchLoad::findAll([
				"condition" => "id_branch=$id_branch AND id_subject IS NULL AND grade IS NULL",
				"limit"		=> "$index, 1"
			])[0];
			
			if ($BranchLoad->color == 3) {
				$BranchLoad->delete();
			} else {
				$BranchLoad->color++;
				$BranchLoad->save('color');
			}
						
		}
		
		public function actionAjaxBranchLoadChangeFull()
		{
			extract($_POST);
			
			$BranchLoad = BranchLoad::findAll([
				"condition" => "id_branch=$id_branch AND id_subject=$id_subject AND grade=$grade",
				"limit"		=> "$index, 1"
			])[0];
			
			if ($BranchLoad->color == 3) {
				$BranchLoad->delete();
			} else {
				$BranchLoad->color++;
				$BranchLoad->save('color');
			}
						
		}
		
		public function actionAjaxClientsMap()
		{
			extract($_GET);
			
// 			preType($_GET);
			
			if (!$branches_invert && !$branches && !$grades && !$subjects) {
				returnJsonAng(false);
			}
			
			if ($branches_invert) {
				foreach ($branches_invert as $id_branch) {
					$condition_branches_invert[] = "CONCAT(',', CONCAT(s.branches, ',')) NOT LIKE '%,{$id_branch},%'";
				}
				$condition[] = "(".implode(" AND ", $condition_branches_invert).")";
			}
			
			if ($branches) {
				foreach ($branches as $id_branch) {
					$condition_branches[] = "CONCAT(',', CONCAT(s.branches, ',')) LIKE '%,{$id_branch},%'";
				}
				$condition[] = "(".implode(" OR ", $condition_branches).")";
			}
			
			if ($subjects) {
				$condition_subjects = "(cs.id_subject IN (". implode(",", $subjects) ."))";
				$condition[] = $condition_subjects;
			}
			
			if ($grades) {
				$condition_grades = "(c.grade IN (". implode(",", $grades) ."))";
				$condition[] = $condition_grades;
			}
			
			$query = dbConnection()->query("
				SELECT s.id FROM contracts c
				LEFT JOIN students s ON s.id = c.id_student
				LEFT JOIN contract_subjects cs ON cs.id_contract = c.id
				WHERE (". implode(" AND ", $condition) .")
					AND (c.id_contract=0 OR c.id_contract IS NULL) AND c.cancelled=0 GROUP BY s.id");
			
/*
			ECHO("
				SELECT s.id FROM contracts c
				LEFT JOIN students s ON s.id = c.id_student
				LEFT JOIN contract_subjects cs ON cs.id_contract = c.id
				WHERE (". implode(" AND ", $condition) .")
					AND (c.id_contract=0 OR c.id_contract IS NULL) GROUP BY s.id");		
*/

			while ($row = $query->fetch_array()) {
				if ($row["id"]) {
					$ids[] = $row["id"];
				}
			}
			
			$Students = Student::findAll([
				"condition"	=> "id IN (". implode(",", $ids) .")"
			]);
			
			foreach ($Students as &$Student) {
				$Student->Contract 	= $Student->getLastContract();
				
				if ($Student->Contract->cancelled) {
					unset($Students[$index]);
					continue;
				}
				
				if ($Student->Contract->subjects) {
					foreach ($Student->Contract->subjects as $subject) {
						$Student->subjects_string[] = $subject['count'] > 40 
							? "<span class='text-danger bold'>" . Subjects::$short[$subject['id_subject']] . "</span>" : Subjects::$short[$subject['id_subject']];
					}
					$Student->subjects_string = implode("+", $Student->subjects_string);
				}
				
				$Student->markers = $Student->getMarkers();
				
				if ($Student->branches) {
					foreach ($Student->branches as $id_branch) {
						if (!$id_branch) {
							continue;
						}
						$Student->branches_string[] = Branches::getName($id_branch);
					}
					
					$Student->branches_string = implode("<br>", $Student->branches_string);
				}
			}
			
			returnJsonAng($Students);
		}
		
		public function actionAjaxPreCancel()
		{
			extract($_POST);
			
			$Contract = Contract::findById($id_contract);
			$Contract->pre_cancelled = $pre_cancelled;
			$Contract->save("pre_cancelled");
		}
		
		public function actionAjaxSmsCheckOk()
		{
			extract($_POST);
			dbConnection()->query("UPDATE sms SET force_ok = 1 WHERE id=$id");
		}
		
		public function actionAjaxSmsCheckDelete()
		{
			extract($_POST);
			dbConnection()->query("UPDATE sms SET force_ok = 0 WHERE id=$id");
		}
	}
