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

			ExamDay::addData($exam_days, $year);
		}

		public function actionAjaxContinueSession()
		{
			# ничего не надо, пустая функция для обновления сессии
		}

		public function actionAjaxCheckLogout()
		{
			// если в режиме просмотра, не делаем логаут
			if (User::inViewMode()) {
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


			foreach(range(0, 6) as $month) {
				$contract_count = 0;
// 				$messages = [];

                // @link http://php.net/manual/ru/function.strtotime.php#107331
                $month_timestamp = strtotime("first day of -$month months");

				$date = date("Y-m", $month_timestamp);

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
					GROUP BY c.id
				");

				while ($row = $result->fetch_object()) {
					$Contract = Contract::findById($row->id);

					// Если договор оригинальный, то прибавляем + все предметы в количество
					if ($Contract->isOriginal()) {
						$contract_count += count($Contract->subjects);
// 						$messages[] = "Original contract №" . $Contract->id . ": +" . count($Contract->subjects);
					} else {
						// если это версия договора
						$PreviousContract = $Contract->getPreviousVersion();

						// разница в предметах = кол-во новых договоров
						$contract_count += count($Contract->subjects) - count($PreviousContract->subjects);

/*
						$cnt = count($Contract->subjects) - count($PreviousContract->subjects);
						if ($cnt > 0) {
							$messages[] = "Contract №" . $Contract->id . ": +" . $cnt;
						}
*/
					}
				}

				$request_count = Request::count([
					"condition" => "date >= '$date-01' AND date <= '$date-31'
						" . ($subjects ? " AND (" . implode(' OR ', $subject_condition) . ")" : "") . "
						" . ($grade ? " AND grade = {$grade} " : "") . "
						AND id_status!=" . RequestStatuses::DUPLICATE . " AND id_status!=" . RequestStatuses::SPAM
				]);

				$return[] = [
					"month" => date("n", $month_timestamp),
					"contract_count"	=> $contract_count,
					"request_count"		=> $request_count,
// 					"messages"			=> $messages,
				];
			}

			$return = array_reverse($return);

			returnJsonAng($return);
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
			returnJson(Payment::add($_POST)->dbData());
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
			extract($_POST);
			Payment::updateById($id, $_POST);
		}

		# TEACHER PAYMENTS
			# теперь все через обычний пэймент
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
				"condition" => "entity_id=$id_student and entity_type='".Student::USER_TYPE."'"
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
					SMS::send($message['number'], $message['message']);
					$sent_to[] = $message['number'];

					// debug
					$body .= "<h3>" . $message["type"] . "</h3>";
					$body .= "<b>Номер: </b>" . $message['number']."<br><br>";
					$body .= "<b>Сообщение: </b>" . $message['message']."<hr>";
				}
			}

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
					SMS::send($message['number'], $message['message']);
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

		public function actionAjaxSendGroupSmsClients()
		{
			extract($_POST);

			$student_ids = implode(",", Student::getData(-1)['data']);

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
					SMS::send($message['number'], $message['message']);
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
				}

				if ($to_representatives == "true") {
					foreach ($Students as $Student) {
						if ($Student->Representative) {
							if (!empty($Student->Representative->email)) {
								$email[] = $Student->Representative->email;
							}
						}
					}
				}
			}

			$Email = Email::send($email, $subject, $message, $files);
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
					AND (c.id_contract=0 OR c.id_contract IS NULL) AND cs.status > 1 GROUP BY s.id");

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
					"condition" => "id_group={$S->id_group} AND date <= '{$date}' AND cancelled = 0"
				]);

				// общее кол-во уроков
				$S->total_lessons = GroupSchedule::count([
					"condition" => "id_group={$S->id_group} AND cancelled = 0"
				]);

                $Cabinet = Cabinet::findById($S->cabinet);
				$S->branch = Branches::getShortColoredById($S->id_branch,
					($S->cabinet ? "-".$Cabinet->number : "")
				);
                $S->cabinetNumber = $Cabinet->number;
			}

            /*
                проверка на признак наслоений. если найдено то ищем студентов на чьих расписаниях
                оно появилось. насловение кабинетов на фронтэнде проверяется.

                фор, а не форич, чтобы не проверить уже проверенные пары  (s2,s1) = (s1,s2)
            */
            if($Schedule) {
                for ($i = 0; $i < count($Schedule); $i++) {
                    $S1 = &$Schedule[$i];
                    for ($j = $i + 1; $j < count($Schedule); $j++) {
                        $S2 = &$Schedule[$j];

                        if ($S1->id != $S2->id && $S1->time == $S2->time) {
                            /* если найдены общие студенты, запоминаем их фамилии */
                            if ($layerData = array_intersect($S1->Group->students, $S2->Group->students)) {
                                $Students = Student::findAll([
                                    "condition" => "id IN (" . implode(",", $layerData) . ")"
                                ]);

                                foreach ($Students as $Student) {
                                    /* чтобы одного и того же студента не добавить 2 раза */
                                    if (!in_array($Student->id, $S1->layerData)) {
                                        $S1->studentLayered .= $S1->studentLayered ? ', ' : '';
                                        $S1->studentLayered .= $Student->last_name . ' ' . $Student->first_name;
                                    }

                                    if (!in_array($Student->id, $S2->layerData)) {
                                        $S2->studentLayered .= $S2->studentLayered ? ', ' : '';
                                        $S2->studentLayered .= $Student->last_name . ' ' . $Student->first_name;
                                    }
                                }

                                $S1->layerData = array_merge($S1->layerData ? $S1->layerData : [], $layerData);
                                $S2->layerData = array_merge($S2->layerData ? $S2->layerData : [], $layerData);
                            }
                        }
                    }
                }
            }

            usort($Schedule, function($a, $b) {
                if ($b->time == $a->time)
                    return $a->cabinetNumber - $b->cabinetNumber;
                else
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


		public function actionAjaxSaveTeacherFaq()
		{
			extract($_POST);
			Settings::set('teachers_faq', $html);
		}

		public function actionAjaxGetReviews()
		{
			extract($_POST);

			returnJsonAng(
				TeacherReview::getData($page, $teachers, $id_student)
			);
		}

		public function actionAjaxUpdateStudentReviewUser()
		{
			extract($_POST);
            Student::updateById($id_student, ['id_user_review' => $id_user_new]);
		}
		
		public function actionAjaxAddStudentFreetime()
		{
			extract($_POST);
			dbConnection()->query("INSERT INTO students_freetime (id_student, day, time_id) VALUES ({$id_student}, {$day}, {$time_id})");
		}
		
		public function actionAjaxDeleteStudentFreetime()
		{
			extract($_POST);
			dbConnection()->query("DELETE FROM students_freetime WHERE id_student={$id_student} AND day={$day} AND time_id={$time_id}");
		}
	}
