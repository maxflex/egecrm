<?php

	// Контроллер
	class CronController extends Controller
	{
		/**
		 * Очищает временные данные.
		 *
		 */
		public static function actionClean()
		{
			#
			# Удалить добавляемые задачи.
			#

			$Requests = Request::findAll([
				"condition" => "adding=1"
			]);

			foreach ($Requests as $Request) {
				Student::fullDelete($Request->id_student);
				$Request->delete();
			}
		}

		public static function actionUpdateYellowLoss()
		{
			memcached()->set("YellowLoss", StatsController::calculateYellowLoss(), 3600 * 24 * 30);
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




		/**
		 * Сообщить о незапланированных занятиях.
		 * @have-to-refactor !!!
		 * @refactored !!!
		 * @schedule-refactored
		 */
		public static function actionNotifyUnplannedLessons()
		{
			$tomorrow_month = date("n", strtotime("tomorrow"));
			$tomorrow_month = russian_month($tomorrow_month);
			$tomorrow = date("j", strtotime("tomorrow")) . " " . $tomorrow_month;

			// @refactored @schedule-refactored
			// все завтрашние занятия
			$TomorrowLessons = VisitJournal::findAll([
				"condition" => "lesson_date='" . date("Y-m-d", strtotime("tomorrow")) . "' AND cancelled=0 AND " . VisitJournal::PLANNED_CONDITION,
			]);

			foreach ($TomorrowLessons as $Lesson) {
				if ($Lesson->isUnplanned() || Group::getLight($Lesson->id_group)->is_unplanned) {
                    $Lesson->Group = Group::getLight($Lesson->id_group);
                    if ($Lesson->id_teacher) {
                        $Teacher = Teacher::getLight($Lesson->id_teacher);
                        if ($Teacher) {
                            foreach (Student::$_phone_fields as $phone_field) {
                                $teacher_number = $Teacher->{$phone_field};
                                if (!empty($teacher_number)) {
                                    $messages[] = [
                                        "type"      => "Учителю #" . $Teacher->id,
                                        "number" 	=> $teacher_number,
                                        "message"	=> self::_generateMessage2($Lesson, $Teacher, $tomorrow),
                                    ];
                                }
                            }
                        }
                    }
                    foreach ($Lesson->Group->students as $id_student) {
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
                                    "message"	=> self::_generateMessage2($Lesson, $Student, $tomorrow),
                                ];
                            }

                            if ($Student->Representative) {
                                $representative_number = $Student->Representative->{$phone_field};
                                if (!empty($representative_number)) {
                                    $messages[] = [
                                        "type"      => "Представителю #" . $Student->Representative->id,
                                        "number" 	=> $representative_number,
                                        "message"	=> self::_generateMessage2($Lesson, $Student, $tomorrow),
                                    ];
                                }
                            }
                        }
                    }
                }
            }

            $sent_to = [];
			foreach ($messages as $message) {
				// if (!in_array($message['number'], $sent_to)) {
					SMS::send($message['number'], $message['message']);
					$sent_to[] = $message['number'];

					// debug
					$body .= "<h3>" . $message["type"] . "</h3>";
					$body .= "<b>Номер: </b>" . $message['number']."<br><br>";
					$body .= "<b>Сообщение: </b>" . $message['message']."<hr>";
				// }
			}
		}


		/**
		 * Сообщить об отмененных занятиях.
		 */
		public static function actionNotifyCancelledLessons()
		{
			$tomorrow_month = date("n", strtotime("tomorrow"));
			$tomorrow_month = russian_month($tomorrow_month);

			$tomorrow = date("j", strtotime("tomorrow")) . " " . $tomorrow_month;

			// все отмененные завтрашние занятия
			$Lessons = VisitJournal::findAll([
				"condition" => "lesson_date='" . date("Y-m-d", strtotime("tomorrow")) . "' AND cancelled = 1 ",
			]);

			foreach($Lessons as $Lesson) {
				$Lesson->Group = Group::getLight($Lesson->id_group);
				if ($Lesson->id_teacher) {
					$Teacher = Teacher::findById($Lesson->id_teacher);
					if ($Teacher) {
						foreach (Student::$_phone_fields as $phone_field) {
							$teacher_number = $Teacher->{$phone_field};
							if (! empty($teacher_number)) {
								$messages[] = [
									"type"      => "Учителю #" . $Teacher->id,
									"number" 	=> $teacher_number,
									"message"	=> self::_generateCancelledMessage($Lesson, $Teacher, $tomorrow),
								];
							}
						}
					}
				}
				foreach ($Lesson->Group->students as $id_student) {
					$Student = Student::findById($id_student);
					if (! $Student) {
						continue;
					}

					foreach (Student::$_phone_fields as $phone_field) {
						$student_number = $Student->{$phone_field};
						if (! empty($student_number)) {
							$messages[] = [
								"type"      => "Ученику #" . $Student->id,
								"number" 	=> $student_number,
								"message"	=> self::_generateCancelledMessage($Lesson, $Student, $tomorrow),
							];
						}

						if ($Student->Representative) {
							$representative_number = $Student->Representative->{$phone_field};
							if (! empty($representative_number)) {
								$messages[] = [
									"type"      => "Представителю #" . $Student->Representative->id,
									"number" 	=> $representative_number,
									"message"	=> self::_generateCancelledMessage($Lesson, $Student, $tomorrow),
								];
							}
						}
					}
				}
			}

			$sent_to = [];
			foreach ($messages as $message) {
				// if (! in_array($message['number'], $sent_to)) {
					SMS::send($message['number'], $message['message']);
					$sent_to[] = $message['number'];
					// debug
					// $body .= "<h3>" . $message["type"] . "</h3>";
					// $body .= "<b>Номер: </b>" . $message['number']."<br><br>";
					// $body .= "<b>Сообщение: </b>" . $message['message']."<hr>";
				// }
			}

		}

        // @schedule-refactored
		private function _generateMessage2($Lesson, $Entity, $tomorrow)
		{
			// @time-refactored @time-checked
			// @sms-checked
			return Template::get(10, [
				'tomorrow'		=> $tomorrow,
				'time'			=> $Lesson->lesson_time,
				'subject'		=> Subjects::$dative[$Lesson->id_subject],
				'address'		=> Branches::getField(Cabinet::getField($Lesson->cabinet), 'address'),
				'branch' 		=> Branches::getField(Cabinet::getField($Lesson->cabinet), 'full'),
				'cabinet'		=> trim(Cabinet::getField($Lesson->cabinet, 'number'))
			]);
		}

        // @schedule-refactored
		public function _generateCancelledMessage($Lesson, $Entity, $tomorrow)
		{
			// @time-refactored @time-checked
			// @sms-checked
			return Template::get(12, [
				'tomorrow'		=> $tomorrow,
				'time'			=> $Lesson->lesson_time,
				'subject'		=> Subjects::$dative[$Lesson->Group->id_subject],
				'address'		=> Branches::getField(Cabinet::getField($Lesson->cabinet), 'address'),
				'branch' 		=> Branches::getField(Cabinet::getField($Lesson->cabinet), 'full'),
				'cabinet'		=> trim(Cabinet::getField($Lesson->cabinet, 'number')),
			]);
		}


		/**
		 * Уведомить учителя об отсутствии записи в журнале
		 * Берем сегодняшние занятия, если нет в журнале записи, отправляем смс.
		 */
		public static function actionTeacherNotifyJournalMiss()
		{
            $date = date('Y-m-d', strtotime('today'));  // потому что проверяется сегодня в 21:05

            $Lessons = VisitJournal::findAll([
                "condition" => "lesson_date='$date' AND cancelled=0 AND " . VisitJournal::PLANNED_CONDITION
            ]);

            foreach ($Lessons as $Lesson) {
	            if ($Lesson->is_planned) {
					$Lesson->Group = Group::getLight($Lesson->id_group);
					$Teacher = Teacher::findById($Lesson->Group->id_teacher);
					if ($Teacher) {
						$message = Template::get(9, [
                            "time" 			=> $Lesson->lesson_time,
                            "teacher_name"	=> $Teacher->first_name . " " .$Teacher->middle_name
                        ]);
                        foreach (Student::$_phone_fields as $phone_field) {
							$teacher_number = $Teacher->{$phone_field};
							if (!empty($teacher_number)) {
								SMS::send($teacher_number, $message);
							}
						}
					}
				}
            }
		}

		/**
		 * Выгружает количества групп на ЕГЭ-ЦЕНТР в переменны {inf_11} и тд.
		 *
		 */
		public static function actionEgeCentrUpdateGroupsCount()
		{
			for ($i = 9; $i <= 11; $i += 2) {
				foreach (Subjects::$short_eng as $id_subject => $subject) {
					// @refactored
					$count = Group::count([
						"condition" => "id_subject=$id_subject AND grade=$i"
					]);
					$return[] = [
						'subject' 	=> $subject . "_" . $i,
						'count'		=> $count,
					];
				}
			}
			// соединение с базой ЕЦ
			$ec_connection = new mysqli('mysql.aperspek.mass.hc.ru', 'aperspek_ec', 'xu6Sonu4', "wwwaperspektivar_ec");
			$ec_connection->set_charset("utf8");

			$ec_connection->query("DELETE FROM groups_count");

			foreach ($return as $data) {
				$values[] = "('{$data['subject']}', {$data['count']})";
			}

			$ec_connection->query("INSERT INTO groups_count (name, value) VALUES " . implode(",", $values));
		}

        /*
         * Шаблон: занятие завтра
         *
         */
		public static function actionNotifyGroupsFirstLesson()
		{
			// все завтрашние занятия
			// @refactored @schedule-refactored
			$Lessons = VisitJournal::findAll([
				"condition" => "lesson_date='" . date("Y-m-d", strtotime("tomorrow")) . "' AND cancelled = 0",
			]);

			$tomorrow_month = date("n", strtotime("tomorrow"));
			$tomorrow_month = russian_month($tomorrow_month);

			$tomorrow = date("j", strtotime("tomorrow")) . " " . $tomorrow_month;

			foreach ($Lessons as $Lesson) {
                $Lesson->Group = Group::getLight($Lesson->id_group);
				if ($Lesson->Group->id_teacher) {
					// Было ли занятие уже у учителя?
					$teacher_already_had_lesson = VisitJournal::count([
						"condition" => "id_entity=" . $Lesson->Group->id_teacher . " AND type_entity='" . Teacher::USER_TYPE . "'
							AND id_group={$Lesson->Group->id}"
					]) > 0 ? true : false;

					if (! $teacher_already_had_lesson) {
						$Teacher = Teacher::findById($Lesson->Group->id_teacher);
						if ($Teacher) {
							foreach (Student::$_phone_fields as $phone_field) {
								$teacher_number = $Teacher->{$phone_field};
								if (!empty($teacher_number)) {
									$messages[] = [
										"type"      => "Учителю #" . $Teacher->id,
										"number" 	=> $teacher_number,
										"message"	=> self::_generateMessage($Lesson, $Teacher, $tomorrow),
									];
								}
							}
						}
					}
				}
				foreach ($Lesson->Group->students as $id_student) {
					$Student = Student::findById($id_student);
					if (!$Student) {
						continue;
					}
					// Проверяем было ли занятие у ученика
					$already_had_lesson = VisitJournal::count([
						"condition" => "id_entity=" . $Student->id . " AND type_entity='" . Student::USER_TYPE . "'
							AND id_group={$Lesson->Group->id} AND presence=1"
					]) > 0 ? true : false;

					// Если занятие у ученика уже было – отправлять СМС не надо
					if ($already_had_lesson) {
						continue;
					}

					foreach (Student::$_phone_fields as $phone_field) {
						$student_number = $Student->{$phone_field};
						if (!empty($student_number)) {
							$messages[] = [
								"type"      => "Ученику #" . $Student->id,
								"number" 	=> $student_number,
								"message"	=> self::_generateMessage($Lesson, $Student, $tomorrow),
							];
						}

						if ($Student->Representative) {
							$representative_number = $Student->Representative->{$phone_field};
							if (!empty($representative_number)) {
								$messages[] = [
									"type"      => "Представителю #" . $Student->Representative->id,
									"number" 	=> $representative_number,
									"message"	=> self::_generateMessage($Lesson, $Student, $tomorrow),
								];
							}
						}
					}
				}
			}

			$sent_to = [];
			foreach ($messages as $message) {
				// if (!in_array($message['number'], $sent_to)) {
					SMS::send($message['number'], $message['message']);
					$sent_to[] = $message['number'];

					// debug
					$body .= "<h3>" . $message["type"] . "</h3>";
					$body .= "<b>Номер: </b>" . $message['number']."<br><br>";
					$body .= "<b>Сообщение: </b>" . $message['message']."<hr>";
				// }
			}
		}

		private function _generateMessage($Lesson, $Entity, $tomorrow)
		{
			return Template::get(5, [
				'tomorrow'		=> $tomorrow,
				'time'			=> $Lesson->lesson_time,
				'subject'		=> Subjects::$dative[$Lesson->Group->id_subject],
				'address'		=> Branches::getField(Cabinet::getField($Lesson->cabinet), 'address'),
				'branch' 		=> Branches::getField(Cabinet::getField($Lesson->cabinet), 'full'),
				'cabinet'		=> trim(Cabinet::getField($Lesson->cabinet, 'number')),
			]);
		}


        /**
         * Обновить таблицу reports_helper
         */
        public function actionReportsHelper()
        {
            ReportHelper::recalc();
        }

		/**
		 * Отложенные задачи
		 */
		public function actionDelayedJobs()
		{
			// получить все текущие задачи
			$current_jobs = Job::findAll([
				'condition' => "DATE_FORMAT(run_at, '%Y-%m-%d %H:%i')='" . date('Y-m-d H:i') ."'"
			]);

			foreach($current_jobs as $job) {
				$job_class = new $job->class;
				$job_class->handle($job->params);
				$job->delete();
			}
		}

		public function actionMasteredSum()
		{
			dbConnection()->query("TRUNCATE TABLE student_sums");

			$prices = Prices::get();

			foreach([2015, 2016, 2017] as $year) {
				$query = dbConnection()->query("SELECT max(id) as id_contract, ci.id_student FROM contracts c
	                JOIN contract_info ci ON ci.id_contract = c.id_contract
	                WHERE c.current_version=1 AND ci.year={$year}
					GROUP BY ci.id_student
	                ORDER BY id DESC
	            ");
				while ($row = $query->fetch_object()) {
					$payment_sum = dbConnection()->query("SELECT sum(`sum`) as s from payments where id_type=1 and entity_id={$row->id_student} and year={$year} and entity_type='STUDENT'")->fetch_object()->s;
					$returns_sum = dbConnection()->query("SELECT sum(`sum`) as s from payments where id_type=2 and entity_id={$row->id_student} and year={$year} and entity_type='STUDENT'")->fetch_object()->s;


					$active_contract = dbConnection()->query("SELECT 1 from contract_subjects where status > 1 and id_contract=" . $row->id_contract)->num_rows;

					if ($active_contract) {
						$contract 		= dbConnection()->query("SELECT * from contracts where id=" . $row->id_contract)->fetch_object();
						// $subject_count 	= dbConnection()->query("SELECT count(*) as cnt from contract_subjects where id_contract=" . $row->id_contract)->fetch_object()->cnt;
						$grade 			= dbConnection()->query("SELECT grade from contract_info where id_contract=" . $contract->id_contract)->fetch_object()->grade;
						$lesson_count   = dbConnection()->query("SELECT count(*) as cnt from visit_journal where id_entity={$row->id_student} and type_entity='STUDENT' and year={$year}")->fetch_object()->cnt;

						$price = $prices[$grade];
						if ($contract->discount) {
							$price = $price * ((100 - $contract->discount) * 0.01);
						}
						$sum = $payment_sum - $returns_sum - ($lesson_count * $price);

						dbConnection()->query("INSERT INTO student_sums (id_student, year, sum) VALUES ({$row->id_student}, {$year}, {$sum})");
					}
				}
			}
		}
	}
