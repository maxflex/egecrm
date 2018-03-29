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
			Socket::trigger('user_' . User::fromSession()->id, 'continue_session', []);
		}

		public function actionDefault()
		{

		}

		// public function actionAjaxCheckSession()
		// {
		//
		// }

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

			if ($grades) {
				$grade_ids = implode(",", $grades);
			}

	        $return = [];
            // @contract-refactored
	        while ($start_date < $end_date) {
		        $start = $start_date->modify('+1 day')->format('Y-m-d'); // переход на новую неделю
	            $end   = $start_date->format('Y-m-d');
	            $return_date = $end;
                // mt = min_table – подключает таблицу вида id_student, year, id_subject, id_contract, min_date
                // другими словами, говорит когда (в каком именно договоре) предмет первый раз появился в рамках года для ученика
                $query = "SELECT COUNT(*) AS cnt FROM contracts c
                    JOIN contract_info ON contract_info.id_contract = c.id_contract
                    LEFT JOIN contract_subjects cs on cs.id_contract = c.id
                    JOIN (SELECT ci.id_student, ci.year, cs2.id_subject, c2.id, MIN(STR_TO_DATE(c2.date, '%d.%m.%Y')) FROM contracts c2
                            JOIN contract_info ci ON ci.id_contract = c2.id_contract
                            LEFT JOIN contract_subjects cs2 on cs2.id_contract = c2.id
                            WHERE cs2.status=3 AND ci.grade <> " . Grades::EXTERNAL . "
                            GROUP BY ci.id_student, ci.year, cs2.id_subject
                    ) mt ON mt.id_student = contract_info.id_student AND mt.year = contract_info.year AND mt.id_subject = cs.id_subject
                    WHERE STR_TO_DATE(c.date, '%d.%m.%Y') = '{$start}' AND cs.status=3 AND contract_info.grade <> " . Grades::EXTERNAL . " AND c.id = mt.id
                    " . ($subjects ? " AND cs.id_subject IN ($subject_ids) " : "") . "
                    " . ($grades ? " AND contract_info.grade IN ({$grade_ids}) " : "") . "
                    " . ($year ? " AND contract_info.year = {$year} " : "");
	            $return[date('d.m.y', strtotime($return_date))] = dbConnection()->query($query)->fetch_object()->cnt;
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

		public function actionAjaxPaymentAdditionalAdd()
		{
			$id = TeacherAdditionalPayment::add($_POST)->id;
			returnJson(TeacherAdditionalPayment::findById($id));
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

		public function actionAjaxDeletePaymentAdditional()
		{
			TeacherAdditionalPayment::deleteById($_POST["id_payment"]);
		}

		public function actionAjaxPaymentEdit()
		{
			extract($_POST);
			returnJson(Payment::updateById($id, $_POST)->dbData());
		}

		public function actionAjaxPaymentAdditionalEdit()
		{
			extract($_POST);
			returnJson(TeacherAdditionalPayment::updateById($id, $_POST)->dbData());
		}



		public function actionAjaxContractSave()
		{
			returnJsonAng(Contract::addNew($_POST));
		}

		public function actionAjaxContractSaveTest()
		{
			returnJsonAng(ContractTest::addNew($_POST));
		}

		public function actionAjaxContractEdit()
		{
			returnJson(Contract::edit($_POST));
		}

		public function actionAjaxContractEditTest()
		{
			returnJson(ContractTest::edit($_POST));
		}

		public function actionAjaxContractDelete()
		{
			extract($_POST);
            Contract::deleteById($id_contract);
		}

		public function actionAjaxContractDeleteTest()
		{
			extract($_POST);
            ContractTest::deleteById($id_contract);
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

			ContractInfo::deleteAll([
				"condition" => "id_student=$id_student"
			]);

			EntityFreetime::deleteAll([
				"condition" => "id_entity=$id_student AND type_entity='student'"
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

			if ($to_teachers == "true") {
				$Teacher = Teacher::findById($Group->id_teacher);
				$msg = $message;

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

            // @schedule-refactored
			$Lessons = VisitJournal::findAll([
				"condition" => "lesson_date='$date'",
			]);

			foreach ($Lessons as &$Lesson) {
				$Lesson->Teacher = Teacher::getLight($Lesson->id_teacher, ['phone']);
				$Lesson->Group = Group::getLight($Lesson->id_group);
				$Lesson->is_unplanned = $Lesson->isUnplanned();
				$Lesson->in_progress = $Lesson->inProgress();
				$Lesson->number = $Lesson->getLessonNumber();

				// общее кол-во уроков
				$Lesson->total_lessons = Group::getLessonCount($Lesson->id_group)->all;

				// @time-refactored @time-checked
                $Lesson->cabinet = Cabinet::getBlock($Lesson->cabinet);
			}

            usort($Lessons, function($a, $b) {
                if ($b->lesson_time == $a->lesson_time)
                    return $a->cabinet['number'] - $b->cabinet['number'];
                else
                    return $b->time - $a->time;
            });

			returnJsonAng($Lessons);
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

			returnJsonAng(User::password($password) == User::fromSession()->password);
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

		public function actionAjaxChangePaymentStatus()
		{
			extract($_POST);
            Student::updateById($id_student, ['payment_status' => $status]);
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

		public function actionAjaxGetLastAccounts()
		{
			extract($_POST);
            $query = dbEgerep()->query("SELECT id, date_end as `date` FROM accounts WHERE tutor_id=" . $id_teacher . " ORDER BY date_end DESC LIMIT 15");
            $return = [];
            while($row = $query->fetch_object()) {
                $row->date = dateFormat($row->date, true);
                $return[] = $row;
            }
			returnJsonAng($return);
		}

        public function actionAjaxEmergency()
        {
            $this->checkRights(Shared\Rights::EMERGENCY_EXIT);
            dbEgerep()->query("UPDATE settings SET `value`=1 WHERE `key`='emergency_exit'");
            Socket::trigger('egerep', 'App\Events\EmergencyExit', [], 'egerep');
        }

		public function actionAjaxUpdateVisitJournal()
		{
			extract($_POST);
			VisitJournal::updateById($id, $_POST);
			returnJsonAng($_POST);
		}

		public function actionAjaxDeleteVisitJournal()
		{
			VisitJournal::deleteById($_POST['id']);
			returnJsonAng($_POST);
		}

		public function actionAjaxSaveVacation()
		{
			extract($_POST);

            if (isset($id)) {
                $response = Vacation::updateById($id, $_POST);
            } else {
                $response = Vacation::add($_POST);
            }
			returnJsonAng($response);
		}

		public function actionAjaxDeleteVacation()
		{
			extract($_POST);

			Vacation::deleteById($id);
		}
	}
