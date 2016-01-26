<?php

	// Контроллер
	class AjaxController extends Controller
	{
		//
		public $defaultAction = "default";

		// Папка вьюх
		protected $_viewsFolder	= "";

		public static $allowed_users = [User::USER_TYPE, User::SEO_TYPE, Teacher::USER_TYPE, Student::USER_TYPE];

		##################################################
		###################### AJAX ######################
		##################################################
		
		public function actionAjaxSaveExamDays()
		{
			extract($_POST);
			
			ExamDay::addData($exam_days);
		}
		
		public function actionAjaxDeleteRemainder()
		{
			PaymentRemainder::deleteById($_POST['id']);
		}
		
		public function actionAjaxUpdateRemainder()
		{
			extract($_POST);
			
			PaymentRemainder::updateById($id, [
				"remainder" => $remainder
			]);
		}
		
		public function actionAjaxAddRemainder()
		{
			extract($_POST);
			
			$Student	= Student::findById($id_student);
			$Contract 	= $Student->getContracts()[0];
			$Payments 	= $Student->getPayments();
			
			// сумма последней версии текущего договора минус сумма платежей и плюс сумма возвратов
			$remainder = $Contract->sum;
			
			foreach ($Payments as $Payment) {
				if ($Payment->id_type == PaymentTypes::PAYMENT) {
					$remainder -= $Payment->sum;
				} else
				if ($Payment->id_type == PaymentTypes::RETURNN) {
					$remainder += $Payment->sum;
				}
			}
			
			$PaymentRemainder = PaymentRemainder::add([
				"id_student"	=> $Student->id,
				"remainder"		=> $remainder,
			]);
			
			returnJsonAng($PaymentRemainder);
		}
		
		public function actionAjaxContinueSession()
		{
			# ничего не надо, пустая функция для обновления сессии
		}
		
		public function actionAjaxCheckLogout()
		{
			// если в режиме просмотра, не делаем логаут
			if (isset(User::fromSession()->AsUser)) {
				returnJsonAng(0);	
			}
			
			if (User::fromSession()->type == User::USER_TYPE) {
				$minutes_limit = 40; // 40 минут для пользователей			
			} else {
				$minutes_limit = 15; // 15 минут
			}
			
			// разница во времени между последним действием и сейчас
			// (сколько минут назад было последнее действие)
			$time_diff = (time() - User::fromSession()->last_action_time) / 60;
			
// 			returnJsonAng($time_diff);
			
			// одна минута до выброса из сессии
			if ($time_diff >= ($minutes_limit - 1) && $time_diff <= $minutes_limit) {
				returnJsonAng(2);
			}
			
			if ($time_diff >= $minutes_limit) {
				// Удаляем сессию
				session_destroy();
				session_unset();
				
				// Очищаем куку залогиненного пользователя
				removeCookie("egecrm_token");
				
				// Очищаем куку сессии PHP
				removeCookie("PHPSESSID", "/"); 
				//setcookie("PHPSESSID","",time()-3600,"/"); // delete session cookie
				
				returnJsonAng(1);	
			}
			
			returnJsonAng(0);
		}
		
		public function actionAjaxEgecentr()
		{
			$data = egeCentrData();
			
			foreach ($data as $d) {
				$return[$d['student_id'] . "_" . $d["subjects"] . "_" . $d["grade"]][$d['date']] = $d['status'];
			}
			
			returnJsonAng($return);
		}
		
		public function actionAjaxGroupCreateHelper()
		{
			extract($_POST);
			
			if ($subjects) {
				$subjects_ids = implode(",", $subjects);
				
				$subject_condition = [];
				foreach ($subjects as $id_subject) {
					$subject_condition[] = "CONCAT(',', CONCAT(subjects, ',')) LIKE '%,{$id_subject},%' ";
				}
			}
			
			foreach(range(0, 6) as $day) {
				$count = 0;
				$date = date("Y-m", strtotime("-$day months"));
				
/*
				$Contracts = Contract::findAll([
					"condition" => "STR_TO_DATE(date, '%d.%m.%Y') >= '$date-01' 
						AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date-31' 
						AND cancelled=0 " . Contract::ZERO_OR_NULL_CONDITION
				]);
*/
				
/*
				$contract_count = Contract::count([
					"condition" => "STR_TO_DATE(date, '%d.%m.%Y') >= '$date-01' 
						AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date-31' 
						" . ($grade ? " AND grade = {$grade} " : "") . "
						AND cancelled=0 " . Contract::ZERO_OR_NULL_CONDITION
				]);
*/
				
				$result = dbConnection()->query("
					SELECT c.id FROM contracts c 
						LEFT JOIN contract_subjects cs ON cs.id_contract = c.id
						LEFT JOIN students s ON s.id = c.id_student
						LEFT JOIN requests r ON r.id_student = s.id
					WHERE STR_TO_DATE(c.date, '%d.%m.%Y') >= '$date-01' 
						AND STR_TO_DATE(c.date, '%d.%m.%Y') <= '$date-31' 
						" . ($subjects ? " AND cs.id_subject IN ($subjects_ids) " : "") . "
						" . ($id_branch ? " AND CONCAT(',', CONCAT(s.branches, ',')) LIKE '%,{$id_branch},%' " : "") . "
						" . ($grade ? " AND c.grade = {$grade} " : "") . "
						AND c.cancelled=0 AND (c.id_contract=0 OR c.id_contract IS NULL)
					GROUP BY c.id
				");		
				
				//$contract_count = $result->fetch_object()->cnt;
				$contract_count = $result->num_rows;
				
				$request_count = Request::count([
					"condition" => "date >= '$date-01' AND date <= '$date-31' 
						" . ($subjects ? " AND (" . implode(' OR ', $subject_condition) . ")" : "") . "
						" . ($grade ? " AND grade = {$grade} " : "") . "
						AND id_status!=" . RequestStatuses::DUPLICATE . " AND id_status!=" . RequestStatuses::SPAM
				]);
				
// 				" . ($id_branch ? " AND CONCAT(',', CONCAT(branches, ',')) LIKE '%,{$id_branch},%' " : "") . "
				
/*
				if (User::fromSession()->id == 69) {
					echo("date >= '$date-01' AND date <= '$date-31' 
							" . ($subjects ? " AND (" . implode(' OR ', $subject_condition) . ")" : "") . "
							" . ($id_branch ? " AND CONCAT(',', CONCAT(branches, ',')) LIKE '%,{$id_branch},%' " : "") . "
							" . ($grade ? " AND grade = {$grade} " : "") . "
							AND id_status!=" . RequestStatuses::DUPLICATE . " AND id_status!=" . RequestStatuses::SPAM
					);
					exit();
				}
*/
/*
				foreach ($Contracts as $Contract) {
					// если предметы указаны
					$ContractSubjects = ContractSubject::findAll([
						"condition" => "id_contract=" . $Contract->id . ($subjects ? " AND id_subject IN ($subjects_ids)" : "")
					]);
					
					if ($ContractSubjects) {
						foreach ($ContractSubjects as $Subject) {
							// Находим группу по параметрам
							$Group = Group::count([
								"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$Contract->id_student},%' 
									AND id_subject = {$Subject->id_subject}
									" . ($id_branch ? " AND id_branch={$id_branch}" : "") . "
									" . ($grade ? " AND grade = {$grade}" : "")
							]);
							
							if ($Group) {
								$count++;
							}
						} 
					}
				}
*/
					
				
				$return[] = [
					"month" => date("n", strtotime("-$day months")),
//					"count"	=> $count,
					"contract_count"	=> $contract_count,
					"request_count"		=> $request_count,
				];
			}
			
			$return = array_reverse($return);
			
			returnJsonAng($return);
		}
		
		public function actionAjaxMissingNoteToggle()
		{
			extract($_POST);
			
			$GroupNote = GroupNote::find([
				"condition" => "id_group=$id_group AND id_student=$id_student AND date='$date'"
			]);
			
			if ($GroupNote) {
				$GroupNote->delete();
				returnJsonAng(false);
			} else {
				GroupNote::add($_POST);
				returnJsonAng(true);
			}
		}
				
		public function actionAjaxToggleGroupAgreement()
		{
			GroupAgreement::addData($_POST);
		}
		
		public function actionAjaxToggleTeacherLike()
		{
			GroupTeacherLike::addData($_POST);
		}
		
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
				"User"			=> User::fromSession()->dbData(),
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
		
		public function actionAjaxSendGroupSmsTeachers()
		{
			extract($_POST);
			
			$Teachers = Teacher::findAll();
			
			foreach ($Teachers as $Teacher) {
				foreach (Student::$_phone_fields as $phone_field) {
					$number = $Teacher->{$phone_field};
					
					if (!empty(trim($number))) {
						$msg = $message;
						if ($Teacher->login && $Teacher->password) {
							$msg = str_replace('{entity_login}', $Teacher->login, $msg);
							$msg = str_replace('{entity_password}', $Teacher->password, $msg);
						}
						$messages[] = [
							"type"      => "Преподавателю #" . $Teacher->id,
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
			
			returnJSON(count($sent_to));
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
		
		public function actionAjaxSendGroupSmsClients()
		{
			extract($_POST);
			
			
			$student_ids = implode(",", $student_ids);
			
			$Students = Student::findAll([
				"condition" => "id IN ($student_ids)"
			]);

			
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
			
			returnJSON(count($sent_to));
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
		
		public function actionAjaxLoadStatsSchedule()
		{
			extract($_POST);
			
			$Schedule = GroupSchedule::findAll([
				"condition" => "date='$date' AND id_group!=0",
				"group"		=> "id_group",
			]);
			
			foreach ($Schedule as &$S) {
				$S->Group = Group::findById($S->id_group);
				$S->is_unplanned = $S->isUnplanned();
				
				// номер урока
				$S->lesson_number = GroupSchedule::count([
					"condition" => "id_group={$S->id_group} AND date <= '{$date}'"
				]);
				
				$S->branch = Branches::getShortColoredById($S->Group->id_branch, 
					($S->cabinet ? "-".Cabinet::findById($S->cabinet)->number : "")
				);
			}
			
			usort($Schedule, function($a, $b) {
				return $b->time - $a->time;
			});
			
			returnJsonAng($Schedule);
		}
		
		public function actionAjaxPlusDays()
		{
			extract($_POST);
			
			$return = StatsController::plusDays($day);
			
			returnJsonAng($return);
		}
		
		public function actionAjaxCheckTeacherPass()
		{
			extract($_POST);
			
			$Teacher = Teacher::find([
				"condition" => "id=" . User::fromSession()->id_entity
			]);
			
			returnJsonAng($password == $Teacher->password);
		}
		
		public function actionAjaxStudentsWithNoGroup()
		{
			$Students = Student::getWithoutGroup();
			
			// preType($Students, true);
			
			returnJsonAng($Students);
		}
	}