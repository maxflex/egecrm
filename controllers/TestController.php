<?php

	// Контроллер
	class TestController extends Controller
    {
        public $defaultAction = "test";
        // Папка вьюх
        protected $_viewsFolder = "test";


    //        public function beforeAction()
    //        {
    //            ini_set("display_errors", 1);
    //            error_reporting(E_ALL);
    //        }

        /**
         * Обновление статусов задач
         */
        public function actionUpdateTasksStatuses()
        {
            $Tasks = Task::findAll();
            /* @var $Tasks Group[] */
            foreach ($Tasks as $Task) {
                switch ($Task->id_status) {
                    case 2: // выполнено  => выгружен в гитхаб
                        $Task->id_status = 4;
                        break;
                    case 3: // требует доработки
                        $Task->id_status = 7;
                        break;
                    case 4: // Закрыто
                        $Task->id_status = 8;
                        break;
                }
                $Task->save('id_status');
            }
        }

		/**
		 * Проверка отправки смс при отмене уроков
		 */
		public function actionTestCancelLesson()
		{
			$tomorrow_month = date("n", strtotime("tomorrow"));
			$tomorrow_month = russian_month($tomorrow_month);

			$tomorrow = date("j", strtotime("tomorrow")) . " " . $tomorrow_month;

			// все отмененные завтрашние занятия
			$GroupSchedule = GroupSchedule::findAll([
				"condition" => "date='" . date("Y-m-d", strtotime("tomorrow")) . "' AND cancelled = 1 ",
				"group"		=> "id_group",
			]);

			$group_ids = [];
			foreach ($GroupSchedule as $GS) {
				$group_ids[] = $GS->id_group;
			}

			$Groups = Group::findAll([
				"condition" => "id IN (" . implode(",", $group_ids) . ")"
			]);

			foreach($Groups as $Group) {
				if ($Group->id_teacher) {
					$Teacher = Teacher::findById($Group->id_teacher);
					if ($Teacher) {
						foreach (Student::$_phone_fields as $phone_field) {
							$teacher_number = $Teacher->{$phone_field};
							if (!empty($teacher_number)) {
								$messages[] = [
									"type"      => "Учителю #" . $Teacher->id,
									"number" 	=> $teacher_number,
									"message"	=> CronController::_generateCancelledMessage($Group, $Teacher, $tomorrow),
								];
							}
						}
					}
				}
				foreach ($Group->students as $id_student) {
					$Student = Student::findById($id_student);
					if (!$Student) {
						continue;
					}

					foreach (Student::$_phone_fields as $phone_field) {
						$student_number = $Student->{$phone_field};
						if (!empty($student_number)) {
							$messages[] = [
								"type"      => "Ученику #" . $Student->id,
								"number" 	=> $student_number,
								"message"	=> CronController::_generateCancelledMessage($Group, $Student, $tomorrow),
							];
						}

						if ($Student->Representative) {
							$representative_number = $Student->Representative->{$phone_field};
							if (!empty($representative_number)) {
								$messages[] = [
									"type"      => "Представителю #" . $Student->Representative->id,
									"number" 	=> $representative_number,
									"message"	=> CronController::_generateCancelledMessage($Group, $Student, $tomorrow),
								];
							}
						}
					}
				}
			}

			$sent_to = [];
			foreach ($messages as $message) {
				if (!in_array($message['number'], $sent_to)) {
					//SMS::send($message['number'], $message['message'], ["additional" => 3]);
//					$sent_to[] = $message['number'];

					// debug
					$body .= "<h3>" . $message["type"] . "</h3>";
					$body .= "<b>Номер: </b>" . $message['number']."<br><br>";
					$body .= "<b>Сообщение: </b>" . $message['message']."<hr>";
				}
			}

			Email::send("shamik1551@mail.ru", "СМС о отмененных занятиях завтра", $body);
		}

        /**
         * update user cache
         */
        public function actionClearUserCache()
        {
            User::updateCache();
        }

		/**
		 * Обновление кеша полей таблиц.
		 */
		public function actionClearColumnCache()
		{
			$Tables = dbConnection()->query("SHOW TABLES");

			while ($Table = $Tables->fetch_assoc())
			{
				$table_name = $Table["Tables_in_".DB_PREFIX."egecrm"];
				memcached()->delete($table_name."Columns");

				$Query = dbConnection()->query("SHOW COLUMNS FROM `".$table_name."`");
				$mysql_vars = [];
				while ($data = $Query->fetch_assoc()) {
					$mysql_vars[] = $data["Field"];
				}
				memcached()->set($table_name."Columns", $mysql_vars, 3600 * 24);
			}
		}

		/**
		 * Сравниние на предмет полного соответствия филиала и кабинета в журнале посещений и в расписании занятий,
		 * которые уже прошли.
		 */
		public function actionJournalScheduleConsistency()
		{
			/* VisitJournal[] несоответствующие элементы */
			$discrepancy = [];
			$checkCnt = 0;

			$GroupSchedules = GroupSchedule::findAll();
			if ($GroupSchedules) {
				/* @var $GroupSchedules GroupSchedule[] */
				foreach($GroupSchedules as $GroupSchedule) {
					$VisitJournal = VisitJournal::find([
												'condition' => "id_group={$GroupSchedule->id_group} ".
															   "AND lesson_date='{$GroupSchedule->date}' ".
															   "AND lesson_time='{$GroupSchedule->time}' "
									]);
					if ($VisitJournal) {
						if ($GroupSchedule->id_branch != $VisitJournal->id_branch || $GroupSchedule->cabinet != $VisitJournal->cabinet) {
							$discrepancy[] = [$GroupSchedule, $VisitJournal];
						} else {
							$checkCnt++;
						}
					}
				}

				if (empty($discrepancy)) {
					echo 'Все записи журнала и расписания соответствуют по параметру филиал/кабинет';
				} else {
					$f = fopen('files/discrepancy.txt', 'w+');
					fwrite($f, "Количество несоответствий ".count($discrepancy)."\n");
					foreach ($discrepancy as $elem) {
						fwrite($f, "Занятие {$elem[0]->date} {$elem[0]->time} в группе № {$elem[0]->id_group} (не соответстует кабинет.)\n");
					}
				}
			} else {
				echo 'No visits';
			}
		}


		/**
         * Updating old group schedule records.
         * Sets group id for records.
         */
        public function actionTransferSchedule()
        {
            $Groups = Group::findAll();
            if ($Groups) {
                /* @var $Groups Group[] */
                foreach($Groups as $Group) {
                    $GroupSchedules = GroupSchedule::findAll(['condition' => 'id_group='.$Group->id]);
                    if ($GroupSchedules) {
                        /* @var $GroupSchedules GroupSchedule[] */
                        foreach ($GroupSchedules as $GroupSchedule) {
                        	$data = [];
                        	if ($Group->id_branch) {
                        		$data['id_branch'] = $Group->id_branch;
//                        		if ($Group->cabinet) {
//                        			$data['cabinet'] = $Group->cabinet;
//                        		}
                        	}
                        	if (!empty($data)) {
								$GroupSchedule->update($data);
							}
                        }
                    }
                }
            } else {
                echo 'No group schedule updated';
            }
        }

        public function actionTeacherLikes()
		{
			$Students = Student::getWithContract();

			foreach ($Students as $Student) {
				$VisitJournal = Student::getExistedTeachers($Student->id);
				foreach ($VisitJournal as $VJ) {
					$Like = GroupTeacherLike::find([
						'condition' => "id_status > 0 AND id_student={$VJ->id_entity} AND id_teacher={$VJ->id_teacher}"
					]);

					if ($Like) {
						switch($Like->id_status) {
							case 1: {
								$new_rating = 5;
								break;
							}
							case 2: {
								$new_rating = 4;
								break;
							}
							case 3: {
								$new_rating = 3;
								break;
							}
						}

						$TeacherReview = TeacherReview::find([
							'condition' => "id_teacher={$VJ->id_teacher} AND id_student={$VJ->id_entity} AND id_subject={$VJ->id_subject}"
						]);

						if ($TeacherReview) {
							$TeacherReview->admin_rating = $new_rating;
							$TeacherReview->save('admin_rating');
						} else {
							TeacherReview::add([
								'id_student' => $Like->id_student,
								'id_teacher' => $Like->id_teacher,
								'id_subject' => $VJ->id_subject,
								'admin_rating' => $new_rating,
							]);
						}
					}
				}
			}








			// $TeacherLikes = GroupTeacherLike::findAll([
			// 	'condition' => 'id_status > 0'
			// ]);
			//
			// foreach ($TeacherLikes as $TeacherLike) {
			// 	switch($TeacherLike->id_status) {
			// 		case 1: {
			// 			$new_rating = 5;
			// 			break;
			// 		}
			// 		case 2: {
			// 			$new_rating = 4;
			// 			break;
			// 		}
			// 		case 3: {
			// 			$new_rating = 3;
			// 			break;
			// 		}
			// 	}
			//
			// 	$VisitJournal = Student::getExistedTeachers($TeacherLike->id_student);
			//
			//
			// 	foreach ($VisitJournal as $VJ) {
			// 		$TeacherReview = TeacherReview::find([
			// 			'condition' => "id_teacher={$VJ->id_teacher} AND id_student={$VJ->id_entity} AND id_subject={$VJ->id_subject}"
			// 		]);
			//
			// 		if ($TeacherReview) {
			// 			$TeacherReview->admin_rating = $new_rating;
			// 			$TeacherReview->save('admin_rating');
			// 		} else {
			// 			TeacherReview::add([
			// 				'id_student' => $TeacherLike->id_student,
			// 				'id_teacher' => $TeacherLike->id_teacher,
			// 				'id_subject' => $VJ->id_subject,
			// 				'admin_rating' => $new_rating,
			// 			]);
			// 		}
			// 	}
			// }
		}

		public function actionMango()
		{
			Mango::call();
		}

		public function actionTestyTest()
		{
			$Students = Student::getWithoutGroupErrors();

			h1(count($Students));

			preType($Students);
		}

		public function actionOnlyTeacherSms()
		{
			$teacher_ids = Group::getTeacherIds();

			$Teachers = Teacher::findAll([
				"condition" => "id IN (" . implode(',', $teacher_ids) . ")"
			]);

			$message = "Уважаемые преподаватели, пожалуйста, не используйте мобильные телефоны на занятиях. Администрация ЕГЭ-Центра по просьбам учеников.";

			foreach ($Teachers as $Teacher) {
				foreach (Student::$_phone_fields as $phone_field) {
					$phone_number = $Teacher->{$phone_field};
					if (!empty($phone_number)) {
						$messages[] = [
							"type"      => "Учителю #" . $Teacher->id,
							"number" 	=> $phone_number,
							"message"	=> $message,
						];
					}
				}
			}

			$sent_to = [];
			foreach ($messages as $message) {
				if (!in_array($message['number'], $sent_to)) {
					SMS::send($message['number'], $message['message'], ["additional" => 3]);
					$sent_to[] = $message['number'];

					// debug
					$body .= "<h3>" . $message["type"] . "</h3>";
					$body .= "<b>Номер: </b>" . $message['number']."<br><br>";
					$body .= "<b>Сообщение: </b>" . $message['message']."<hr>";
				}
			}

			Email::send("makcyxa-k@yandex.ru", "СМС", $body);
		}

		public function actionTestingSms()
		{
			$Students = Student::getWithContract();

			foreach ($Students as $Student) {
				if ($Student->grade == 11) {
					$message = "ЕГЭ-Центр информирует: в ЕГЭ-Центре-Тургеневская можно пройти пробное тестирование ЕГЭ на официальных бланках. Записаться можно из личного кабинета (логин: {$Student->login}, пароль: {$Student->password}) либо по телефону (495) 646-85-92. Администрация.";

					foreach (Student::$_phone_fields as $phone_field) {
						$student_number = $Student->{$phone_field};
						if (!empty($student_number)) {
							$messages[] = [
								"type"      => "Ученику #" . $Student->id,
								"number" 	=> $student_number,
								"message"	=> $message,
							];
						}

						if ($Student->Representative) {
							$representative_number = $Student->Representative->{$phone_field};
							if (!empty($representative_number)) {
								$messages[] = [
									"type"      => "Представителю #" . $Student->Representative->id,
									"number" 	=> $representative_number,
									"message"	=> $message,
								];
							}
						}
					}
				}
			}

			$sent_to = [];
			foreach ($messages as $message) {
				if (!in_array($message['number'], $sent_to)) {
					SMS::send($message['number'], $message['message'], ["additional" => 3]);
					$sent_to[] = $message['number'];

					// debug
					$body .= "<h3>" . $message["type"] . "</h3>";
					$body .= "<b>Номер: </b>" . $message['number']."<br><br>";
					$body .= "<b>Сообщение: </b>" . $message['message']."<hr>";
				}
			}

			Email::send("makcyxa-k@yandex.ru", "СМС о тестировании", $body);
		}

		public function actionUpdateSearchData()
		{
			$Students = Student::getWithContract();

			foreach ($Students as $Student) {
				$text = "";
				$Requests = $Student->getRequests();
				foreach ($Requests as $Request) {
					$text .= $Request->name;
					$text .= self::_getPhoneNumbers($Request);
				}
				// Имя, телефоны ученика и представителя
				$text .= $Student->name();
				$text .= self::_getPhoneNumbers($Student);
				$text .= $Student->email;

				if ($Student->Passport) {
					$text .= $Student->Passport->series;
					$text .= $Student->Passport->number;
				}

				if ($Student->Representative) {
					$text .= $Student->Representative->name();
					$text .= self::_getPhoneNumbers($Student->Representative);
					$text .= $Student->Representative->email;
					$text .= $Student->Representative->address;

					if ($Student->Representative->Passport) {
						$text .= $Student->Representative->Passport->series;
						$text .= $Student->Representative->Passport->number;
						$text .= $Student->Representative->Passport->issued_by;
						$text .= $Student->Representative->Passport->address;
					}
				}

				// Последние 4 цифры номер карты
				$Payments = Payment::findAll([
					"condition" => "id_status=" . Payment::PAID_CARD . " AND id_student=" . $Student->id . " AND card_number!=''"
				]);
				foreach ($Payments as $Payment) {
					$text .= $Payment->card_number;
				}

				$return[$Student->id] = $text;
			}

			dbConnection()->query("TRUNCATE TABLE search_students");

			foreach ($return as $id_student => $text) {
				$values[] = "($id_student, '" . $text . "')";
			}

			dbConnection()->query("INSERT INTO search_students (id_student, search_text) VALUES " . implode(",", $values));
		}

		private static function _getPhoneNumbers($Object)
		{
			$text = "";
			foreach (Student::$_phone_fields as $phone_field) {
				$phone = $Object->{$phone_field};
				if (!empty($phone)) {
					$text .= $phone;
				}
			}
			return $text;
		}

		public function actionStudentsWithoutGrade()
		{
			$Students = Student::getWithContract();

			$student_ids = [];
			foreach ($Students as $Student) {
				if (!$Student->grade) {
					$student_ids[] = $Student->id;
				}
			}

			echo implode(", ", $student_ids);
		}

		public function actionCalculateRemainder()
		{
			$Students = Student::getWithContract(true);

			$student_ids = [];
			foreach ($Students as $Student) {
				$Contract = $Student->getContracts()[0];
				$Payments = $Student->getPayments();

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

				PaymentRemainder::add([
					"id_student"	=> $Student->id,
					"remainder"		=> $remainder,
				]);
			}
		}

		public function actionEgecentr()
		{
			$this->addJs("ng-test-app");

			$date_start = "2013-09-01";
			$date_end = "2014-05-31";


			do {
				$dates[] = $date_start;
				$date_start = date("Y-m-d", strtotime("$date_start + 1 day"));
			} while ($date_start <= $date_end);

			$ang_init_data = angInit([
				"dates" => $dates,
			]);

			$this->setTabTitle("test");
			$this->render("egecentr", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		public function actionAgash()
		{
			$id_branch 	= 1;
			$subjects	= [1, 2];
			$grade 		= 10;

			$subjects_ids = implode(",", $subjects);

			foreach(range(0, 7) as $day) {
				$count = 0;
				$date = date("Y-m", strtotime("-$day months"));

				$Contracts = Contract::findAll([
					"condition" => "STR_TO_DATE(date, '%d.%m.%Y') >= '$date-01'
						AND STR_TO_DATE(date, '%d.%m.%Y') <= '$date-31'
						AND cancelled=0 " . Contract::ZERO_OR_NULL_CONDITION
				]);

				foreach ($Contracts as $Contract) {
					$ContractSubjects = ContractSubject::findAll([
						"condition" => "id_contract=" . $Contract->id . ($id_subject ? " AND id_subject IN ($subjects_ids)" : "")
					]);

					if ($ContractSubjects) {
						foreach ($ContractSubjects as $Subject) {
							// Находим группу по параметрам
							$Group = Group::count([
								"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$Contract->id_student},%'
									AND id_subject = {$Subject->id_subject}
									AND grade = {$grade}
									AND id_branch={$id_branch}"
							]);

							if ($Group) {
								$count++;
							}
						}
					}
				}


				$return[] = [
					"month" => date("F", strtotime("-$day months")),
					"count"	=> $count,
				];
			}

			$return = array_reverse($return);

			preType($return);
		}

		public function actionSwitchTest()
		{
			$this->addCss("bs-slider");
			$this->addJs("bs-slider");
			$this->setTabTitle("test");
			$this->render("test");
		}

		public function actionSLessons()
		{
			$Student = Student::findById(288);

			$Data = $Student->getVisits();

			preType($Data);
		}

		public function actionPhpExcel()
		{
/*
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="расписание.xls"');
			header('Cache-Control: max-age=0');
*/

			$objPHPExcel = new PHPExcel();

			$objPHPExcel->setActiveSheetIndex(0);

			$objPHPExcel->getActiveSheet()->SetCellValue('B3', 'ПОНЕДЕЛЬНИК');
			$objPHPExcel->getActiveSheet()->mergeCells('B3:C3');

			$objPHPExcel->getActiveSheet()->SetCellValue('B4', '16:15');
			$objPHPExcel->getActiveSheet()->SetCellValue('C4', '18:40');

			$objPHPExcel->getActiveSheet()->SetCellValue('D3', 'ВТОРНИК');
			$objPHPExcel->getActiveSheet()->mergeCells('D3:E3');

			$objPHPExcel->getActiveSheet()->SetCellValue('D4', '16:15');
			$objPHPExcel->getActiveSheet()->SetCellValue('E4', '18:40');

			$objPHPExcel->getActiveSheet()->SetCellValue('F3', 'СРЕДА');
			$objPHPExcel->getActiveSheet()->mergeCells('F3:G3');

			$objPHPExcel->getActiveSheet()->SetCellValue('F4', '16:15');
			$objPHPExcel->getActiveSheet()->SetCellValue('G4', '18:40');

			$objPHPExcel->getActiveSheet()->SetCellValue('H3', 'ЧЕТВЕРГ');
			$objPHPExcel->getActiveSheet()->mergeCells('H3:I3');

			$objPHPExcel->getActiveSheet()->SetCellValue('H4', '16:15');
			$objPHPExcel->getActiveSheet()->SetCellValue('I4', '18:40');

			$objPHPExcel->getActiveSheet()->SetCellValue('J3', 'ПЯТНИЦА');
			$objPHPExcel->getActiveSheet()->mergeCells('J3:K3');

			$objPHPExcel->getActiveSheet()->SetCellValue('J4', '16:15');
			$objPHPExcel->getActiveSheet()->SetCellValue('K4', '18:40');


			$Cabinets = Cabinet::findAll([
				"condition" => "id_branch=" . Branches::TRG,
			]);


			$row = 5;
			$col = 'A';

			foreach ($Cabinets as $Cabinet) {
				$objPHPExcel->getActiveSheet()->SetCellValue($col.$row, 'Кабинет ' . $Cabinet->number);
				$row++;

				// Cabinet groups
				$Groups = Group::findAll([
					"condition" => "cabinet=" . $Cabinet->id
				]);

				preType($Groups, 1);
			}

			exit();
			$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
			$objWriter->save('php://output');
		}

		public function actionReviewCount()
		{
			$Groups = Group::findAll();

			foreach ($Groups as $Group) {
				foreach ($Group->students as $id_student) {
					$Student = Student::findById($id_student);
					$Teacher = Teacher::findById($Group->id_teacher);

					$Student->already_had_lesson	= $Student->alreadyHadLesson($Group->id);

					$Student->review_status	= $Group->student_statuses[$Student->id]['review_status'];

					if ($Student->already_had_lesson) {
						$total_count++;
						if (!$Student->review_status) {
							$gray_count++;
							$data[] = [
								'sort'		=> 0,
								'class' 	=> 'not-collected',
								'Teacher'	=> $Teacher,
								'Student'	=> $Student,
								'id_group'	=> $Group->id,
							];
							//echo "GROUP ID: {$Group->id} | STUDENT ID: {$Student->id} <br>";
						} else {
							switch ($Student->review_status) {
								case 1: {
									$green_count++;
									$data[] = [
										'sort'		=> 1,
										'class' 	=> 'collected',
										'Teacher'	=> $Teacher,
										'Student'	=> $Student,
										'id_group'	=> $Group->id,
									];
									break;
								}
								case 2: {
									$orange_count++;
									$data[] = [
										'sort'		=> 2,
										'class' 	=> 'orange',
										'Teacher'	=> $Teacher,
										'Student'	=> $Student,
										'id_group'	=> $Group->id,
									];
									break;
								}
								case 3: {
									$red_count++;
									$data[] = [
										'sort'		=> 3,
										'class' 	=> 'red',
										'Teacher'	=> $Teacher,
										'Student'	=> $Student,
										'id_group'	=> $Group->id,
									];
									break;
								}
							}
						}
					}
				}
			}

			usort($data, function($a, $b) {
				return $a['sort'] - $b['sort'];
			});

			$this->setTabTitle("Количество отзывов");

			$this->render("review_count", [
				"data" => $data,
				"gray_count" => $gray_count,
				"green_count" => $green_count,
				"orange_count" => $orange_count,
				"red_count"	=> $red_count,
				"total_count" => $total_count,
			]);

// 			echo "GRAY: $gray_count | GREEN: $green_count | ORANGE: $orange_count | RED: $red_count <br> TOTAL: $total_count";
		}


		/**
		 * у которых договор есть, но нет ни одного посещения ни в одной группе.
		 *
		 * @access public
		 * @return void
		 */
		public function actionWithContractButNoLessons()
		{
			$Students = Student::getWithContract();

			$student_ids = [];
			foreach ($Students as $Student) {
				$result = dbConnection()->query("
					SELECT COUNT(*) as cnt FROM visit_journal
					WHERE id_entity={$Student->id} AND type_entity='STUDENT'
				");

				if ($result->fetch_object()->cnt == 0) {
					$student_ids[] = $Student->id;
				}
			}

			echo implode(", ", $student_ids);
		}

		/**
		 * у которых есть хоть одна группа, в которой ученики прекратили занятия.
		 *
		 */
		public function actionStoppedGroup()
		{
			$Students = Student::getWithContract();

			$student_ids = [];
			foreach ($Students as $Student) {
				$group_ids = Group::getIds([
					"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$Student->id},%'"
				]);

				$result = dbConnection()->query("
					SELECT id_group FROM visit_journal
					WHERE id_entity={$Student->id} AND type_entity='STUDENT'
					GROUP BY id_group
				");

				$group_ids2 = [];
				while ($row = $result->fetch_object()) {
					$group_ids2[] = $row->id_group;
				}

				foreach ($group_ids2 as $id_group) {
					if (!in_array($id_group, $group_ids)) {
						$student_ids[] = $Student->id;
						break;
					}
				}

/*
				$diff = array_diff($group_ids, $group_ids2);

				if (count($diff) > 0) {
					h1($Student->id);
					preType([
						$group_ids, $group_ids2
					]);
					$student_ids[] = $Student->id;
				}
*/
			}

			echo implode(", ", $student_ids);
		}

		public function actionHasGreenOrYellowSubjectInOriginal()
		{
			$result = dbConnection()->query("
				SELECT c.id_student FROM contract_subjects cs
				LEFT JOIN contracts c ON c.id = cs.id_contract
				WHERE cs.status IN (1, 2)
				GROUP BY c.id_student
			");

			while ($row = $result->fetch_object()) {
				$student_ids[] = $row->id_student;
			}

			echo implode(", ", $student_ids);
		}

		public function actionHasGreenOrYellowSubjectInVersion()
		{
			$result = dbConnection()->query("
				SELECT c.id_contract FROM contract_subjects cs
				LEFT JOIN contracts c ON c.id = cs.id_contract
				WHERE cs.status IN (1, 2) AND (c.id_student IS NULL or c.id_student=0) AND c.id_contract > 0
				GROUP BY c.id_contract
			");

			while ($row = $result->fetch_object()) {
				$contract_ids[] = $row->id_contract;
			}
			$contract_ids_string = implode(", ", $contract_ids);

			$result = dbConnection()->query("
				SELECT id_student FROM contracts WHERE id IN ({$contract_ids_string})
			");

			while ($row = $result->fetch_object()) {
				$student_ids[] = $row->id_student;
			}


			echo implode(", ", $student_ids);
		}

		public function actionTwoOrMoreVersions()
		{
			$Students = Student::getWithContract();

			$student_ids = [];
			foreach ($Students as $Student) {
				$Student->Contract = $Student->getLastContract();

				if (!$Student->Contract->isOriginal()) {
					$student_ids[] = $Student->id;
				}
			}

			echo implode(", ", $student_ids);
		}

		public function actionTwoOrMoreContracts()
		{
			$Students = Student::getWithContract();

			$student_ids = [];
			foreach ($Students as $Student) {
				$count = Contract::count([
					"condition" => "id_student={$Student->id} ".Contract::ZERO_OR_NULL_CONDITION
				]);

				if ($count > 1) {
					$student_ids[] = $Student->id;
				}
			}

			echo implode(", ", $student_ids);
		}

		public function actionSameNumber()
		{
			$Requests = Request::findAll([
				"condition" => "adding=0",
				"limit"		=> "100 OFFSET 100",
			]);

			$request_ids = [];
			foreach ($Requests as $Request) {
				foreach (Student::$_phone_fields as $phone_field) {
					$request_phone = $Request->{$phone_field};
					if (!empty($request_phone)) {
						if (isDuplicate($request_phone, $Request->id)) {
							$request_ids[] = $Request->id;
							break;
						}
					}

					$student_phone = $Request->Student->{$phone_field};
					if (!empty($student_phone)) {
						if (isDuplicate($student_phone, $Request->id)) {
							$request_ids[] = $Request->id;
							break;
						}
					}

					if ($Request->Student->Representative) {
						$representative_phone = $Request->Student->Representative->{$phone_field};
						if (!empty($representative_phone)) {
							if (isDuplicate($representative_phone, $Request->id)) {
								$request_ids[] = $Request->id;
								break;
							}
						}
					}
				}
			}

			preType($request_ids);
		}

		public function actionGroupContractCacnelled()
		{
			$Students = Student::getWithContract();
			foreach ($Students as $Student) {
				$Student->Contract = $Student->getLastContract();
				$subject_ids = [];
				foreach ($Student->Contract->subjects as $subject) {
					$subject_ids[] = $subject['id_subject'];
				}
				// preType($Student->Contract->subjects);

				if (count($subject_ids)) {
					$count = Group::count([
						"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$Student->id},%' AND id_subject NOT IN (" . implode(",", $subject_ids) . ")"
					]);
					if ($count > 0) {
						h1($Student->id);
					}
				}
			}
		}

		public function actionSameDay()
		{
			$Students = Student::getWithContract();
			foreach ($Students as $Student) {
				$Groups = $Student->getGroups();
				foreach ($Groups as $Group) {
					foreach ($Group->day_and_time as $day => $time_data) {
						foreach ($time_data as $time) {
							$result = dbConnection()->query("
								SELECT COUNT(*) AS cnt FROM groups g
									LEFT JOIN group_time gt ON gt.id_group = g.id
									WHERE CONCAT(',', CONCAT(g.students, ',')) LIKE '%,{$Student->id},%' AND gt.day = {$day} AND gt.time = '{$time}'
							");
							$count = $result->fetch_object()->cnt;
							if ($count > 1) {
								h1($Student->id);
							}
						}
					}
				}
			}
		}
/*

		public function actionOneSubject()
		{
			$Students = Student::getWithContract();


			foreach ($Students as $Student) {
				$Groups = $Student->getGroups();

				foreach ($Groups as $Group) {
					$count = Group::count([
						"condition" => "CONCAT(',', CONCAT(students, ',')) LIKE '%,{$Student->id},%' AND id_subject={$Group->id_subject}"
					]);

					if ($count > 1) {
						h1($Student->id);
					}
				}
		}

*/
		public function actionMatch()
		{
			$Group = Group::findById(73);

			var_dump($Group->lessonDaysMatch());
		}


		public function actionSmsCheckLate()
		{
			$result = dbConnection()->query("select * from sms where message LIKE '%опоздал%' or  message LIKE '%отсутствовал%'");

			while ($row = $result->fetch_object())
			{
				$all_sms[] = $row;
			}


			foreach ($all_sms as &$sms) {
				$phone = $sms->number;

				$sms->message = preg_replace('!\s+!', ' ', $sms->message);
				preg_match("/информирует: ([\d]+) ([\w]+) ([\w-]+[\s]*[\w-]+) ([\w]+)/u", $sms->message, $matches);

				$presence = false;

				if (strpos($matches[4], "отсутствовал") !== false) {
					$presence = 2;
				} else
				if (strpos($matches[4], "опоздал") !== false) {
					$presence = 1;
				}

				if ($presence) {
					$month = russian_month_id_by_name($matches[2]);
					if ($month < 10) {
						$month = "0" . $month;
					}
					$date = "2015-{$month}-{$matches[1]}";

					list($last_name, $first_name) = explode(" ", $matches[3]);

					$result = dbConnection()->query("
						SELECT * FROM visit_journal vj
						LEFT JOIN students s on s.id = vj.id_entity
						WHERE vj.type_entity = 'STUDENT' AND s.first_name = '{$first_name}'
							AND s.last_name = '{$last_name}' AND vj.lesson_date = '$date' AND ". ($presence == 2 ? "vj.presence=2" : "(vj.presence=1 AND vj.late > 0)") ."
					");

					$count_all++;

					if ($result->num_rows) {
						$count_correct++;
					}

// 					h1($result->num_rows);
				}

// 				h1($matches[4] . "-" . $presence);
// 				preType($matches);
			}
			echo "ALL: $count_all | CORRECT: $count_correct";
// 			preType($all_sms);

/*
			$this->setTabTitle("Проверка СМС");

			$this->render("sms_check", [
				"all_sms" => $all_sms
			]);
*/
		}



		public function actionSmsCheck()
		{
			$result = dbConnection()->query("select * from sms where message LIKE '%ожидается%'");

			while ($row = $result->fetch_object())
			{
				$all_sms[] = $row;
			}


			foreach ($all_sms as &$sms) {
				$phone = $sms->number;

				$sms->message = preg_replace('!\s+!', ' ', $sms->message);
				preg_match("/ченик ([\w-]+[\s]*[\w-]+)[\s]*ожидается на первое занятие по ([\w]+[\s]?[\w]*) в ЕГЭ-Центр-([\w]+) ([\d]+) ([\w]+)[\s]*[в]?[\s]*([\d:]+)?. Кабинет ([\d]+)./u", $sms->message, $matches);

				$id_subject = array_search($matches[2], Subjects::$dative);
				$id_branch 	= array_search($matches[3], Branches::$all);

				$Student = dbConnection()->query("
					select s.id, s.first_name, s.last_name from students s
					left join representatives r on s.id_representative = r.id
					where (s.phone = '$phone' OR s.phone2 = '$phone' OR s.phone3 = '$phone')
					or (r.phone = '$phone' OR r.phone2 = '$phone' OR r.phone3 = '$phone')
					LIMIT 1
				")->fetch_object();

				$id_student = $Student->id;

				// если студент не найден
				if (!$id_student) {
					$sms->status 		= 0;
					$sms->status_text 	= "УЧЕНИКА С ТАКИМ НОМЕРОМ НЕ НАЙДЕНО";
					continue;
				}

				if ($id_subject && $id_branch && $id_student) {
					$Group = Group::find([
						"condition" => "id_branch=$id_branch AND id_subject=$id_subject AND CONCAT(',', CONCAT(students, ',')) LIKE '%,{$id_student},%'"
					]);
					if ($Group) {
						// проверка даты и времени первого занятия
						$Group->first_schedule = $Group->getFirstSchedule(false);

						// проверка имени и фамилии студента
						$name = explode(" ", $matches[1]);
						if (strcmp(trim($name[0]), trim($Student->last_name)) !== 0 || strcmp(trim($name[1]), trim($Student->first_name))) {
							$sms->status 		= 0;
							$sms->status_text 	= "ИМЯ НЕ СОВПАДАЕТ ({$Student->last_name} {$Student->first_name} | {$name[0]} {$name[1]})";
							continue;
						}

						// проверка статуса согласия студента
						if (!$Group) {
							$sms->status 		= 0;
							$sms->status_text 	= "ГРУППА НЕ НАЙДЕНА ($id_branch | $id_subject | $id_student)";
							continue;
						}


						$Status = GroupStudentStatuses::find([
							"condition" => "id_student=$id_student AND id_group={$Group->id}"
						]);

						if ($Status->id_status != GroupStudentStatuses::AGREED) {
							$sms->not_agreed = true;
						}

						if ($Status->notified != 1) {
//							echo $Group->id . " | " . $Student->id . "<br>";
//							$Status->notified = 1;
//							$Status->save("notified");
							$sms->not_notified = true;
						}

						$date_day = date("j", strtotime($Group->first_schedule->date));

						if ($date_day != $matches[4]) {
							$sms->status 		= 0;
							$sms->status_text 	= "НЕПРАВИЛЬНАЯ ДАТА ($date_day | {$matches[4]})";
							continue;
						}

						if (mb_strimwidth($Group->first_schedule->time, 0, 5) != $matches[6]) {
							$sms->status 		= 0;
							$sms->status_text 	= "НЕПРАВИЛЬНОЕ ВРЕМЯ (" . mb_strimwidth($Group->first_schedule->time, 0, 5) . " | {$matches[6]})";
							continue;
						}

						$cabinet_number = Cabinet::findById($Group->cabinet)->number;

						if ($cabinet_number != $matches[7]) {
							$sms->status 		= 0;
							$sms->status_text 	= "КАБИНЕТЫ НЕ СОВПАДАЮТ";
							continue;
						}

						$sms->status 		= 1;
						$sms->status_text 	= "ОК";
						continue;

					} else {
						$sms->status 		= 0;
						$sms->status_text 	= "ГРУППА НЕ НАЙДЕНА ($id_branch | $id_subject | $id_student)";
					}
				} else {
					$sms->status 		= 0;
					$sms->status_text 	= "НЕ ПОДХОДИТ ПОД РЕГУЛЯРНОЕ ВЫРАЖЕНИЕ";
				}
			}

//			preType($all_sms);

			$this->setTabTitle("Проверка СМС");

			$this->render("sms_check", [
				"all_sms" => $all_sms
			]);
		}

		public function actionGo()
		{
			$Groups = Group::findAll([
				"condition" => "id_branch=" . Branches::PVN,
			]);

// 			$add_students = [1851, 2111, 1910, 2051];

			foreach ($Groups as $Group) {
				if ($Group->id_teacher) {
					$Teacher = Teacher::findById($Group->id_teacher);
					foreach (Student::$_phone_fields as $phone_field) {
						$teacher_number = $Teacher->{$phone_field};
						if (!empty($teacher_number)) {
							$messages[] = [
								"type"      => "Учителю #" . $Teacher->id,
								"number" 	=> $teacher_number,
								"message"	=> self::_generateMessage($Teacher),
							];
						}
					}
				}
				foreach ($Group->students as $id_student) {
					if (!in_array($id_student, $add_students)) {
						continue;
					}
					$Student = Student::findById($id_student);

					foreach (Student::$_phone_fields as $phone_field) {
						$student_number = $Student->{$phone_field};
						if (!empty($student_number)) {
							$messages[] = [
								"type"      => "Ученику #" . $Student->id,
								"number" 	=> $student_number,
								"message"	=> self::_generateMessage($Student),
							];
						}

						if ($Student->Representative) {
							$representative_number = $Student->Representative->{$phone_field};
							if (!empty($representative_number)) {
								$messages[] = [
									"type"      => "Представителю #" . $Student->Representative->id,
									"number" 	=> $representative_number,
									"message"	=> self::_generateMessage($Student),
								];
							}
						}
					}
				}
			}

			$sent_to = [];
			$final = [];
			foreach ($messages as $message) {
				if (!in_array($message['number'], $sent_to)) {
// 					SMS::send($message['number'], $message['message'], ["additional" => 3]);
					$sent_to[] = $message['number'];
					$final[] = $message;
					// debug
					$body .= "<h3>" . $message["type"] . "</h3>";
					$body .= "<b>Номер: </b>" . $message['number']."<br><br>";
					$body .= "<b>Сообщение: </b>" . $message['message']."<hr>";
				}
			}

//			Email::send("makcyxa-k@yandex.ru", "Уведомление о личном кабинете, Калужская", $body);
			preType($final);
		}

		private function _generateMessage($Entity)
		{
			return "ЕГЭ-Центр информирует: доступ в личный кабинет (на сайте ЕГЭ-Центра ссылка вверху справа) логин – {$Entity->login}, пароль {$Entity->password}";
		}

		public function actionSetTeacherLogin()
		{
			ini_set('display_errors', 1);
			error_reporting(E_ALL);
			$Teachers = Teacher::findAll();

			foreach ($Teachers as $Teacher) {
				$Teacher->login 	= $Teacher->_generateLogin();
				$Teacher->password	= $Teacher->_generatePassword();

				User::add([
					"login" 		=> $Teacher->login,
					"password"		=> $Teacher->password,
					"first_name"	=> $Teacher->first_name,
					"last_name"		=> $Teacher->last_name,
					"middle_name"	=> $Teacher->middle_name,
					"type"			=> Teacher::USER_TYPE,
					"id_entity"		=> $Teacher->id
				]);

				$Teacher->save();

				preType($Teacher);
			}
		}

		public function actionSetStudentLogin()
		{
			$Students = Student::getWithContract();
/*
			$Students = Student::findAll([
				"condition" => "id IN (1851, 2111, 1910, 2051)"
			]);
*/

			foreach ($Students as $Student) {
				if (!$Student->login) {
					echo $Student->id . "<br>";
				}
/*
				$Student->Contract 	= $Student->getLastContract();
				$Student->login 	= $Student->Contract->id;
				$Student->password	= mt_rand(10000000, 99999999);
				$Student->save();

				User::add([
					"login" 	=> $Student->login,
					"password"	=> $Student->password,
					"first_name"	=> $Student->first_name,
					"last_name"		=> $Student->last_name,
					"middle_name"	=> $Student->middle_name,
					"type"			=> Student::USER_TYPE,
					"id_entity"		=> $Student->id
				]);
*/
			}
		}

		public function actionSetStudentCode()
		{
			$Students = Student::getWithContract();

			foreach ($Students as $Student) {
				// if (!$Student->code) {
					$Student->code = Contract::_generateCode();
					$Student->save("code");
				//}
			}
		}

/*
		public function action()
		{
			$Students = Student::getWithContract(true);

			$nc = new NCLNameCaseRu();

			$messages = [];
			foreach ($Students as $Student) {
				$student_gender = $nc->genderDetect($Student->last_name . " "
							. $Student->first_name . " " . $Student->middle_name);
				foreach (Student::$_phone_fields as $phone_field) {
					$student_number = $Student->{$phone_field};
					if (!empty($student_number)) {
						$messages[] = [
							"number" 	=> $student_number,
							"message"	=> ($student_gender == 1 ? "Уважаемый" : "Уважаемая") . " {$Student->first_name} {$Student->middle_name}, ЕГЭ-Центр активно формирует группы и расписание. Ежегодно, как и в этом году, занятия начинаются с 15 по 30 сентября. Перед началом занятий мы обязательно с Вами свяжемся. Спасибо за понимание. Администрация ЕГЭ-Центра."
						];
					}

					if ($Student->Representative) {
						$representative_gender = $nc->genderDetect($Student->Representative->last_name . " "
							. $Student->Representative->first_name . " " . $Student->Representative->middle_name);
						$representative_number = $Student->Representative->{$phone_field};
						if (!empty($representative_number)) {
							$messages[] = [
								"number" 	=> $representative_number,
								"message"	=> ($representative_gender == 1 ? "Уважаемый" : "Уважаемая") . " {$Student->Representative->first_name} {$Student->Representative->middle_name}, ЕГЭ-Центр активно формирует группы и расписание. Ежегодно, как и в этом году, занятия начинаются с 15 по 30 сентября. Перед началом занятий мы обязательно с Вами свяжемся. Спасибо за понимание. Администрация ЕГЭ-Центра."
							];
						}
					}
				}
			}

			$sent_to = ['79670270752', '79031231801', '79037457698', '79251285692', '79163301472', '79164306272', '79654492601', '79096451438', '79175883100', '79153460947', '74953142024', '79055555825', '79099526366', '79853550349', '79037755318', '79852699043', '79653994501', '79152446686', '79857808032', '79030155035', '79169291117', '79257318384', '79175630479', '79166259015', '79166976092', '79166059905', '79169901330', '79652064827', '79032828225', '79152191898', '79104049172', '79152690638', '79165339308', '79852792608', '79055907327', '79060568266', '79166705602', '79150075824', '79164521239', '79859780281', '79152381922', '79680619395', '79629271004', '79165480965', '79161710291', '79152330527', '79150072764', '79636724119', '79057799915', '79859659477', '79162310335', '79160305385', '79166161406', '79035071873', '79166390009', '79175605578', '79030101149', '79037805451', '79151887521', '79166878687', '79998262848', '79263414810', '79169558280', '79168509009', '79168153574', '79175658506', '79197789113', '79653132515', '79032874994', '79032767814', '79167437499', '79163102484', '79175776664', '79163947757', '79637896620', '79165035329', '79096478884', '79629238497', '79060663807', '79055039481', '79067895132', '79037842820', '79168793353', '79859228141', '79269127329', '79684492848', '79060535047', '79036157176', '79037335976', '79264542105', '79853899036', '79153272754', '79169536667', '79683364322', '79859952254', '79164741454', '79163533037', '79168790332', '79160703690', '79037533242', '79037413040', '79672915048', '79035242404', '79647834373', '79036119530', '79251908463', '79636488591', '79639245523', '79267080163', '79268279111', '79299623626', '79295184080', '79262489217', '79264291913', '79031778317', '79685148381', '79168238741', '79853047100', '79160446718', '79164911512', '79154670326', '79168086738', '79689657517', '79851988946', '79175548554', '79629381537', '79031218805', '79672582855', '79161572919', '79096595455', '79636058802', '79250298355', '79263374369', '79035716447', '79265381197', '79035716467', '79036819268', '79647993371', '79194100124', '79265728638', '79036710491', '79036710489', '79162612220', '79166661129', '79153866202', '79163349589', '79160412379', '79175404446', '79150367931', '79104281257', '79851408180', '79165050858', '79250236096', '79262720500', '79055347193', '79037779277', '79150774972', '79153978881', '79636661438', '79166233805', '79652195014', '79647058120', '79685286554', '79037759411', '79266989106', '79261936833', '79851786672', '79168468245', '79168334676', '79161354015', '79099566952', '79035400133', '79253709094', '79165054682', '79261542040', '79266965344', '79150190567', '79199675935', '79854580882', '79104880192', '79670763053', '79096831152', '79035260414', '79167469551', '79260140057', '79295798008', '79035158899', '79037757657', '79165366325', '79036175095', '79854310778', '79162005025', '79091569166', '79036856688', '79150318065', '79166202089', '79036816083', '79636444830'];

			$sent_to_new = [];
			foreach ($messages as $message) {
				if (!in_array($message['number'], $sent_to)) {
// 					SMS::send($message['number'], $message['message'], ["additional" => 3]);
					$sent_to[] = $message['number'];
					$sent_to_new[] = $message['number'];
				}
			}

			preType($sent_to_new);
			echo "<hr>";
			echo count($sent_to_new);
		}
*/

		public function actionCabinetsCheck()
		{
			$r = Cabinet::getCabinetGroups(1);

			preType($r);
		}

		public function actionSetCabinet()
		{
			$Groups = Group::findAll();

			foreach ($Groups as $Group) {
				$Cabinets = Cabinet::findAll([
					"condition" => "id_branch=" . $Group->id_branch
				]);

				if ($Cabinets) {
					$Group->cabinet = $Cabinets[0]->id;
					$Group->save("cabinet");
				}
			}
		}

		public function actionStudentFreetime()
		{
			$Student = Student::findById(473);
			$ft = $Student->getGroupFreetime(208);
			preType($ft);
		}

		public function actionTeacherFreetime()
		{
			$Teachers = Teacher::findAll();

			foreach ($Teachers as $Teacher) {
				foreach ($Teacher->branches as $id_branch) {
					if (!$id_branch) {
						continue;
					}

					dbConnection()->query("
						INSERT INTO teacher_freetime
							(id_teacher, id_branch, day, time)
						VALUES
							({$Teacher->id}, $id_branch, 1, '16:15'),
							({$Teacher->id}, $id_branch, 1, '18:40'),

							({$Teacher->id}, $id_branch, 2, '16:15'),
							({$Teacher->id}, $id_branch, 2, '18:40'),

							({$Teacher->id}, $id_branch, 3, '16:15'),
							({$Teacher->id}, $id_branch, 3, '18:40'),

							({$Teacher->id}, $id_branch, 4, '16:15'),
							({$Teacher->id}, $id_branch, 4, '18:40'),

							({$Teacher->id}, $id_branch, 5, '16:15'),
							({$Teacher->id}, $id_branch, 5, '18:40'),

							({$Teacher->id}, $id_branch, 6, '11:00'),
							({$Teacher->id}, $id_branch, 6, '13:30'),
							({$Teacher->id}, $id_branch, 6, '16:00'),
							({$Teacher->id}, $id_branch, 6, '18:30'),

							({$Teacher->id}, $id_branch, 7, '11:00'),
							({$Teacher->id}, $id_branch, 7, '13:30'),
							({$Teacher->id}, $id_branch, 7, '16:00'),
							({$Teacher->id}, $id_branch, 7, '18:30')
					");

					echo ("
						INSERT INTO teacher_freetime
							(id_teacher, id_branch, day, time)
						VALUES
							({$Teacher->id}, $id_branch, 1, '16:15'),
							({$Teacher->id}, $id_branch, 1, '18:40'),

							({$Teacher->id}, $id_branch, 2, '16:15'),
							({$Teacher->id}, $id_branch, 2, '18:40'),

							({$Teacher->id}, $id_branch, 3, '16:15'),
							({$Teacher->id}, $id_branch, 3, '18:40'),

							({$Teacher->id}, $id_branch, 4, '16:15'),
							({$Teacher->id}, $id_branch, 4, '18:40'),

							({$Teacher->id}, $id_branch, 5, '16:15'),
							({$Teacher->id}, $id_branch, 5, '18:40'),

							({$Teacher->id}, $id_branch, 6, '11:00'),
							({$Teacher->id}, $id_branch, 6, '13:30'),
							({$Teacher->id}, $id_branch, 6, '16:00'),
							({$Teacher->id}, $id_branch, 6, '18:30'),

							({$Teacher->id}, $id_branch, 7, '11:00'),
							({$Teacher->id}, $id_branch, 7, '13:30'),
							({$Teacher->id}, $id_branch, 7, '16:00'),
							({$Teacher->id}, $id_branch, 7, '18:30')
					")."<br><br>";

					echo dbConnection()->error . "<hr>";
				}
			}
		}

		/**
		 * @deprecated
		 */
		public function actionDeleteCache()
		{
			foreach (Branches::$all as $id_branch => $name) {
				memcached()->delete("Rating[$id_branch]");
				memcached()->delete("UniqueRating[$id_branch]");
				memcached()->delete("MaxRating[$id_branch]");
			}
//			memcached()->delete("Rating");
		//	memcached()->delete("SumRating");
		}


		public function actionTransferDayAndTime()
		{
			$Groups = Group::findAll([
				"condition" => "start!='' AND start IS NOT NULL"
			]);

			foreach ($Groups as $Group) {
				$GroupTime = new GroupTime([
					"id_group" 	=> $Group->id,
					"day"		=> $Group->day,
					"time"		=> $Group->start,
				]);

				$GroupTime->save();
			}
		}


		private static function addFreetime($Student, $day, $time) {
			foreach ($Student->branches as $id_branch) {
				if (!$id_branch) {
					continue;
				}
				$FreetimeNew = new FreetimeNew([
					"id_student"	=> $Student->id,
					"id_branch"		=> $id_branch,
					"day"			=> $day,
					"time"			=> $time,
				]);
				$FreetimeNew->save();
			}
		}

		public function actionTesty()
		{
			$Students = Student::getWithContract(true);
			h1(count($Students));


			// Добавляем догавары к студентам
			foreach ($Students as $index => $Student) {
// 				echo $index . ") " . $Student->last_name .  "<br>";

				$Students[$index]->Contract 	= $Student->getLastContract();
				foreach ($Student->branches as $id_branch) {
					if (!$id_branch) {
						continue;
					}
					$Students[$index]->branch_short[$id_branch] = Branches::getShortColoredById($id_branch);
				}
			}

// 			preType($Students);
//			preType($Students[326]);

			echo "<hr>";

			// Формируем по классам, всех студентов, кто не принадлежит группам
			foreach ($Students as $index => $Student) {
//				echo $index . ") " . $Student->last_name .  "<br>";
				$GroupsGrade[$Student->Contract->grade][] = $Student;
			}


			// Формируем по предметам
			foreach ($GroupsGrade as $grade => $GS)
			{
				foreach ($GS as $Student) {
					foreach ($Student->Contract->subjects as $subject) {
						foreach ($Student->branches as $id_branch) {
							if (!$id_branch) {
								continue;
							}
							$GroupStudents[$grade][$subject['id_subject']][$id_branch][] = $Student;
						}
					}
				}
			}

			// Формируем отдельные группы из массива (до примыкания к филиалу)
			foreach ($GroupStudents as $_grade => $_SubjectBranch) {
				foreach ($_SubjectBranch as $_subject => $_Branch) {
					foreach ($_Branch as $_branch => $BS) {
						foreach ($BS as $index => $S) {
							if ($S->inOtherGradeSubjectGroup($grade, $subject)) {
								unset($BS[$index]);
							}
						}
						// если есть ученики в группе
						if (count($BS)) {
							$GroupsFull[] = [
								"grade"		=> $_grade,
								"subject"	=> $_subject,
								"branch"	=> $_branch,
								"branch_svg"=> Branches::getName($_branch),
								"count"		=> count($BS),
								"Students"	=> $BS,
							];
						}
					}
				}
			}

			preType($GroupsFull);

/*

			// Сортируем по количеству учеников
			usort($GroupsFull, function($a, $b) {
				return ($a['count'] > $b['count'] ? -1 : 1);
			});

*/
// 			preType($GroupsFull);

			h1(count($Students));
		}

		public function actionDeleteUsersCache()
		{
			memcached()->delete("Users");
		}

		public function actionClientsMap()
		{
			$this->setTabTitle("Карта клиентов");

			$this->addJs("//maps.google.ru/maps/api/js?libraries=places", true);
			$this->addJs("maps.controller, ng-test-app");

			$ang_init_data = angInit([
				"Branches"	=> Branches::$all,
				"Grades"	=> Grades::$all,
				"Subjects"	=> Subjects::$all,
			]);

			$this->render("clientsmap", [
				"ang_init_data" => $ang_init_data,
			]);
		}

		public function actionBranchesDelete()
		{
			error_reporting(E_ALL);
			ini_set("display_errors", 1);


			$branch_str = Branches::STR;
			$branch_prr = Branches::PRR;

			$RequestsStr = Request::findAll([
				"condition" => "id_branch=$branch_str"
			]);

			$RequestsPrr = Request::findAll([
				"condition" => "id_branch=$branch_prr"
			]);

			$StudentsStr = Student::findAll([
				"condition" => "CONCAT(',', CONCAT(branches, ',')) LIKE '%,{$branch_str},%'"
			]);

			$StudentsPrr = Student::findAll([
				"condition" => "CONCAT(',', CONCAT(branches, ',')) LIKE '%,{$branch_prr},%'"
			]);

			preType($RequestsStr);

/*
			foreach ($RequestsStr as $Request) {
				$Request->id_branch = Branches::MLD;
				$Request->save("id_branch");
			}

			foreach ($RequestsPrr as $Request) {
				$Request->id_branch = Branches::VLD;
				$Request->save("id_branch");
			}
*/
		}

		public function actionMap()
		{
			$this->setTabTitle("Тестирование алгоритма метро");

			$this->addJs("//maps.google.ru/maps/api/js?libraries=places", true);
			$this->addJs("maps.controller, ng-test-app");
			$this->render("map");
		}

		public function actionImap()
		{
			error_reporting(E_ALL);
			ini_set("display_errors", 1);

			$mailbox = new PhpImap\Mailbox('{imap.yandex.ru:993/imap/ssl}', 'info@ege-centr.ru', 'kochubey1981');

//			$mailbox->statusMailbox();
//			$mailbox->testy();

//			$t = $mailbox->statusMailbox();

			$mailsIds = $mailbox->searchMailBox('ANSWERED');

			foreach ($mailsIds as $id_mail) {
				$mail = $mailbox->getMail($id_mail);
				preType($mail);
			}

//			var_dump($mailsIds);
//			preType($mailbox);
		}

		public function actionRating()
		{
			$Students = Student::findAll([
				"condition" => "branches != ''"
			]);

			foreach ($Students as &$Student) {
				$Student->Contract = $Student->getLastContract();
			}


			foreach ($Students as $Student) {
				foreach ($Student->branches as $id_branch) {
					$rating[$id_branch]++;
					if ($Student->Contract) {
						$rating[$id_branch] += count($Student->Contract->subjects);
					}
				}
			}

			asort($rating);
			$rating = array_reverse($rating, true);

			foreach ($rating as $id_branch => $score) {
				echo Branches::$all[$id_branch].": ".$score;
				echo "<br>";
			}
		}

		public function actionRatingCache()
		{
			$Rating = memcached()->get("Rating");

			preType($Rating);
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

		##################################################
		###################### AJAX ######################
		##################################################


	}
