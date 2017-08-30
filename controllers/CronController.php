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
			$GroupSchedule = GroupSchedule::findAll([
				"condition" => "date='" . date("Y-m-d", strtotime("tomorrow")) . "' AND cancelled  = 0",
			]);

			foreach ($GroupSchedule as $GS) {
				if ($GS->isUnplanned()) {
                    $GS->getGroup();
                    if ($GS->Group->id_teacher) {
                        $Teacher = Teacher::findById($GS->Group->id_teacher);
                        if ($Teacher) {
                            foreach (Student::$_phone_fields as $phone_field) {
                                $teacher_number = $Teacher->{$phone_field};
                                if (!empty($teacher_number)) {
                                    $messages[] = [
                                        "type"      => "Учителю #" . $Teacher->id,
                                        "number" 	=> $teacher_number,
                                        "message"	=> self::_generateMessage2($GS, $Teacher, $tomorrow),
                                    ];
                                }
                            }
                        }
                    }
                    foreach ($GS->Group->students as $id_student) {
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
                                    "message"	=> self::_generateMessage2($GS, $Student, $tomorrow),
                                ];
                            }

                            if ($Student->Representative) {
                                $representative_number = $Student->Representative->{$phone_field};
                                if (!empty($representative_number)) {
                                    $messages[] = [
                                        "type"      => "Представителю #" . $Student->Representative->id,
                                        "number" 	=> $representative_number,
                                        "message"	=> self::_generateMessage2($GS, $Student, $tomorrow),
                                    ];
                                }
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
		}


		/**
		 * Сообщить об отмененных занятиях.
		 * @schedule-refactored
		 */
		public static function actionNotifyCancelledLessons()
		{
			$tomorrow_month = date("n", strtotime("tomorrow"));
			$tomorrow_month = russian_month($tomorrow_month);

			$tomorrow = date("j", strtotime("tomorrow")) . " " . $tomorrow_month;

			// все отмененные завтрашние занятия
			// @refacored @schedule-refactored
			$GroupSchedule = GroupSchedule::findAll([
				"condition" => "date='" . date("Y-m-d", strtotime("tomorrow")) . "' AND cancelled = 1 ",
			]);

			foreach($GroupSchedule as $GS) {
                $GS->getGroup();
				if ($GS->Group->id_teacher) {
					$Teacher = Teacher::findById($GS->Group->id_teacher);
					if ($Teacher) {
						foreach (Student::$_phone_fields as $phone_field) {
							$teacher_number = $Teacher->{$phone_field};
							if (! empty($teacher_number)) {
								$messages[] = [
									"type"      => "Учителю #" . $Teacher->id,
									"number" 	=> $teacher_number,
									"message"	=> self::_generateCancelledMessage($GS, $Teacher, $tomorrow),
								];
							}
						}
					}
				}
				foreach ($GS->Group->students as $id_student) {
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
								"message"	=> self::_generateCancelledMessage($GS, $Student, $tomorrow),
							];
						}

						if ($Student->Representative) {
							$representative_number = $Student->Representative->{$phone_field};
							if (! empty($representative_number)) {
								$messages[] = [
									"type"      => "Представителю #" . $Student->Representative->id,
									"number" 	=> $representative_number,
									"message"	=> self::_generateCancelledMessage($GS, $Student, $tomorrow),
								];
							}
						}
					}
				}
			}

			$sent_to = [];
			foreach ($messages as $message) {
				if (! in_array($message['number'], $sent_to)) {
					SMS::send($message['number'], $message['message']);
					$sent_to[] = $message['number'];
					// debug
					// $body .= "<h3>" . $message["type"] . "</h3>";
					// $body .= "<b>Номер: </b>" . $message['number']."<br><br>";
					// $body .= "<b>Сообщение: </b>" . $message['message']."<hr>";
				}
			}

		}

        // @schedule-refactored
		private function _generateMessage2($GroupSchedule, $Entity, $tomorrow)
		{
			// @time-refactored @time-checked
			// @sms-checked
			return Template::get(10, [
				'tomorrow'		=> $tomorrow,
				'time'			=> $GroupSchedule->time,
				'subject'		=> Subjects::$dative[$GroupSchedule->Group->id_subject],
				'address'		=> Branches::getField(Cabinet::getField($GroupSchedule->cabinet), 'address'),
				'branch' 		=> Branches::getField(Cabinet::getField($GroupSchedule->cabinet), 'full'),
				'cabinet'		=> trim(Cabinet::getField($GroupSchedule->cabinet, 'number')),
				'entity_login'	=> $Entity->login,
				'entity_password' => $Entity->password,
			]);
		}

        // @schedule-refactored
		public function _generateCancelledMessage($GroupSchedule, $Entity, $tomorrow)
		{
			// @time-refactored @time-checked
			// @sms-checked
			return Template::get(12, [
				'tomorrow'		=> $tomorrow,
				'time'			=> $GroupSchedule->time,
				'subject'		=> Subjects::$dative[$GroupSchedule->Group->id_subject],
				'address'		=> Branches::getField(Cabinet::getField($GroupSchedule->cabinet), 'address'),
				'branch' 		=> Branches::getField(Cabinet::getField($GroupSchedule->cabinet), 'full'),
				'cabinet'		=> trim(Cabinet::getField($GroupSchedule->cabinet, 'number')),
				'entity_login'	=> $Entity->login,
				'entity_password' => $Entity->password,
			]);
		}


		/**
		 * Уведомить учителя об отсутствии записи в журнале
		 * Берем сегодняшние занятия, если нет в журнале записи, отправляем смс.
		 * @schedule-refactored
		 */
		public static function actionTeacherNotifyJournalMiss()
		{
            $date = date('Y-m-d', strtotime('today'));  // потому что проверяется сегодня в 21:05

            // @schedule-refactored
            $GroupSchedule = GroupSchedule::findAll([
                "condition" => "date='$date' AND id_group > 0 AND cancelled=0"
            ]);

            foreach ($GroupSchedule as $Schedule) {
	            if (! $Schedule->was_lesson) {
					$Schedule->getGroup();
					if ($Schedule->Group) {
						$Teacher = Teacher::findById($Schedule->Group->id_teacher);
						if ($Teacher) {
							$message = Template::get(9, [
	                            "time" 			=> $Schedule->time,
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
			$GroupSchedule = GroupSchedule::findAll([
				"condition" => "date='" . date("Y-m-d", strtotime("tomorrow")) . "' AND cancelled = 0",
			]);

			$tomorrow_month = date("n", strtotime("tomorrow"));
			$tomorrow_month = russian_month($tomorrow_month);

			$tomorrow = date("j", strtotime("tomorrow")) . " " . $tomorrow_month;

			foreach ($GroupSchedule as $GS) {
                $GS->getGroup();
				if ($GS->Group->id_teacher) {
					// Было ли занятие уже у учителя?
					$teacher_already_had_lesson = VisitJournal::count([
						"condition" => "id_entity=" . $GS->Group->id_teacher . " AND type_entity='" . Teacher::USER_TYPE . "'
							AND id_group={$GS->Group->id}"
					]) > 0 ? true : false;

					if (! $teacher_already_had_lesson) {
						$Teacher = Teacher::findById($GS->Group->id_teacher);
						if ($Teacher) {
							foreach (Student::$_phone_fields as $phone_field) {
								$teacher_number = $Teacher->{$phone_field};
								if (!empty($teacher_number)) {
									$messages[] = [
										"type"      => "Учителю #" . $Teacher->id,
										"number" 	=> $teacher_number,
										"message"	=> self::_generateMessage($GS->Group, $Teacher, $tomorrow),
									];
								}
							}
						}
					}
				}
				foreach ($GS->Group->students as $id_student) {
					$Student = Student::findById($id_student);
					if (!$Student) {
						continue;
					}
					// Проверяем было ли занятие у ученика
					$already_had_lesson = VisitJournal::count([
						"condition" => "id_entity=" . $Student->id . " AND type_entity='" . Student::USER_TYPE . "'
							AND id_group={$GS->Group->id} AND presence=1"
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
								"message"	=> self::_generateMessage($GS->Group, $Student, $tomorrow),
							];
						}

						if ($Student->Representative) {
							$representative_number = $Student->Representative->{$phone_field};
							if (!empty($representative_number)) {
								$messages[] = [
									"type"      => "Представителю #" . $Student->Representative->id,
									"number" 	=> $representative_number,
									"message"	=> self::_generateMessage($GS->Group, $Student, $tomorrow),
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
		}

        // @schedule-refactored
		private function _generateMessage($GroupSchedule, $Entity, $tomorrow)
		{
			// @time-refactored @time-checked
			// @sms-checked
			return Template::get(5, [
				'tomorrow'		=> $tomorrow,
				'time'			=> $GroupSchedule->time,
				'subject'		=> Subjects::$dative[$GroupSchedule->Group->id_subject],
				'address'		=> Branches::getField(Cabinet::getField($GroupSchedule->cabinet), 'address'),
				'branch' 		=> Branches::getField(Cabinet::getField($GroupSchedule->cabinet), 'full'),
				'cabinet'		=> trim(Cabinet::getField($GroupSchedule->cabinet, 'number')),
				'entity_login'	=> $Entity->login,
				'entity_password' => $Entity->password,
			]);
		}


		/**
		 * Обновить статусы СМС. На самом деле запускается не кроном, а сервисом sms.ru
		 *
		 */
		public static function actionUpdateSmsStatus()
		{
			foreach ($_POST["data"] as $entry) {
				$lines = explode("\n",$entry);
				if ($lines[0] == "sms_status") {

					$sms_id 	= $lines[1];
					$sms_status = $lines[2];

					$SMS = SMS::find([
						"condition" => "id_smsru='". $sms_id ."'"
					]);

					if ($SMS) {
                        $SMS->id_status = $sms_status;
                        $SMS->save("id_status");
                        SMS::notifyStatus($SMS);
					}
					// "Изменение статуса. Сообщение: $sms_id. Новый статус: $sms_status";
					// Здесь вы можете уже выполнять любые действия над этими данными.
				}
			}
			exit("100"); /* Важно наличие этого блока, иначе наша система посчитает, что в вашем обработчике сбой */
		}

        /**
         * Обновить таблицу reports_helper
         */
        public function actionReportsHelper()
        {
            ReportHelper::recalc();
        }
	}
