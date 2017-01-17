<?php

	// Контроллер
	class AjaxController extends Controller
	{
		//
		public $defaultAction = "default";

		// Папка вьюх
		protected $_viewsFolder	= "";

		public static $allowed_users = [User::USER_TYPE, Teacher::USER_TYPE, Student::USER_TYPE];

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
			$days = 45; // отображать статистику за последние n дней
			$date = new DateTime('today');

	        $end_date   = clone $date;
	        $start_date = clone $date->sub(new DateInterval("P{$days}D"));

			if ($subjects) {
				$subject_ids = implode(",", $subjects);
			}

	        $return = [];
            // @contract-refactored
	        while ($start_date < $end_date) {
		        $start = $start_date->modify('+1 day')->format('Y-m-d'); // переход на новую неделю
	            $end   = $start_date->format('Y-m-d');
	            $return_date = $end;
                $cnt = 0;
                if ($subjects) {
                    foreach($subject_ids as $id_subject) {
                        $query = "
                            SELECT COUNT(DISTINCT contract_info.id_student) AS cnt FROM contracts c
                            JOIN contract_info ON contract_info.id_contract = c.id_contract
                            LEFT JOIN contract_subjects cs on cs.id_contract = c.id
                            WHERE STR_TO_DATE(c.date, '%d.%m.%Y') = '{$start}' AND c.id=c.id_contract AND cs.status=3 AND c.external=0
                            AND cs.id_subject = {$id_subject}
                            " . ($grade ? " AND contract_info.grade = {$grade} " : "") . "
                            " . ($year ? " AND contract_info.year = {$year} " : "");
                        $cnt += dbConnection()->query($query)->fetch_object()->cnt;
                    }
                } else {
                    $query = "
                        SELECT COUNT(*) AS cnt FROM contracts c
                        JOIN contract_info ON contract_info.id_contract = c.id_contract
                        LEFT JOIN contract_subjects cs on cs.id_contract = c.id
                        WHERE STR_TO_DATE(c.date, '%d.%m.%Y') = '{$start}' AND c.id=c.id_contract AND cs.status=3 AND c.external=0
                        " . ($subjects ? " AND cs.id_subject IN ($subject_ids) " : "") . "
                        " . ($grade ? " AND contract_info.grade = {$grade} " : "") . "
                        " . ($year ? " AND contract_info.year = {$year} " : "");
                    $cnt = dbConnection()->query($query)->fetch_object()->cnt;
                }
	            $return[date('d.m.y', strtotime($return_date))] = $cnt;

	        }

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
            returnJsonAng($Comment);
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
			returnJson(Payment::updateById($id, $_POST)->dbData());
		}



		public function actionAjaxContractSave()
		{
			returnJson(Contract::addNew($_POST));
		}

		public function actionAjaxContractEdit()
		{
			returnJson(Contract::edit($_POST));
		}

		public function actionAjaxContractDelete()
		{
			extract($_POST);
            Contract::deleteById($id_contract);
		}

//		public function actionAjaxContractDeleteHistory()
//		{
//			extract($_POST);
//
//			Contract::deleteAll([
//				"condition" => "id=$id_contract"
//			]);
//		}

		public function actionAjaxChangeRequestUser()
		{
			extract($_POST);

			$Request = Request::findById($id_request);
			$Request->id_user = $id_user_new;
			$Request->save();
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

			$Group = Group::findById($groupId);

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
				$return[] = $User->dbData();
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

		public function actionAjaxLoadStatsSchedule()
		{
			extract($_POST);
			$Schedule = GroupSchedule::findAll([
				"condition" => "date='$date' AND id_group!=0",
				"group"		=> "id_group",
			]);

			foreach ($Schedule as &$S) {
				$S->Group = Group::findById($S->id_group);
				$S->Group->Teacher = Teacher::getLight($S->Group->Teacher->id, ['phone']);
				$S->is_unplanned = $S->isUnplanned();

				// номер урока
				$S->lesson_number = GroupSchedule::count([
					"condition" => "id_group={$S->id_group} AND date <= '{$date}' AND cancelled = 0"
				]);

				// общее кол-во уроков
				$S->total_lessons = GroupSchedule::count([
					"condition" => "id_group={$S->id_group} AND cancelled = 0"
				]);

				// данные по прошедшему занятию из журнала
				if ($S->was_lesson) {
					$S->Lesson = VisitJournal::find(["condition" => "id_group={$S->id_group} AND lesson_date='{$S->date}'"]);
					if ($S->Lesson->cabinet) {
	                    $S->Lesson->cabinet = Cabinet::getBlock($S->Lesson->cabinet);
	                }
	                $S->Lesson->Teacher = Teacher::getLight($S->Lesson->id_teacher, ['phone']);
				}

				// @time-refactored @time-checked
				if ($S->cabinet) {
                    $S->cabinet = Cabinet::getBlock($S->cabinet);
                }
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

		// @time-refactored @time-checked
		public function actionAjaxAddFreetime()
		{
			extract($_POST);
			EntityFreetime::add($_POST);
		}

		// @time-refactored @time-checked
		public function actionAjaxDeleteFreetime()
		{
			extract($_POST);
			EntityFreetime::remove($id_entity, $type_entity, $id_time);
		}
	}
