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
		
		public static function actionUpdateSearchData()
		{
			$Students = Student::findAll();
			
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
		
		
		
		
		/**
		 * Сообщить о незапланированных занятиях.
		 * 
		 */
		public static function actionNotifyUnplannedLessons()
		{
			$tomorrow_month = date("n", strtotime("tomorrow"));
			$tomorrow_month = russian_month($tomorrow_month);
			
			$tomorrow = date("j", strtotime("tomorrow")) . " " . $tomorrow_month;
			
			// все завтрашние занятия
			$GroupSchedule = GroupSchedule::findAll([
				"condition" => "date='" . date("Y-m-d", strtotime("tomorrow")) . "'",
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
				$days = array_keys($Group->day_and_time);
				
				// sunday in mysql is 0
				foreach ($days as &$day) {
					if ($day == 7) {
						$day = 0;
					}
				}
				
				// дни совпали
				$days_match = GroupSchedule::count([
					"condition" => "id_group={$Group->id} AND DATE_FORMAT('" . date("Y-m-d", strtotime("tomorrow")) . "', '%w') NOT IN (" . implode(',', $days) . ")"
				]) > 0 ? false : true;
				
				if (!$days_match) {
					if ($Group->id_teacher) {
						$Teacher = Teacher::findById($Group->id_teacher);
						if ($Teacher) {
							foreach (Student::$_phone_fields as $phone_field) {
								$teacher_number = $Teacher->{$phone_field};
								if (!empty($teacher_number)) {
									$messages[] = [
										"type"      => "Учителю #" . $Teacher->id,
										"number" 	=> $teacher_number,
										"message"	=> self::_generateMessage2($Group, $Teacher, $tomorrow),
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
									"message"	=> self::_generateMessage2($Group, $Student, $tomorrow),
								];
							}
							
							if ($Student->Representative) {
								$representative_number = $Student->Representative->{$phone_field};
								if (!empty($representative_number)) {
									$messages[] = [
										"type"      => "Представителю #" . $Student->Representative->id,
										"number" 	=> $representative_number,
										"message"	=> self::_generateMessage2($Group, $Student, $tomorrow),
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
					SMS::send($message['number'], $message['message'], ["additional" => 3]);
					$sent_to[] = $message['number'];
					
					// debug
					$body .= "<h3>" . $message["type"] . "</h3>";
					$body .= "<b>Номер: </b>" . $message['number']."<br><br>";
					$body .= "<b>Сообщение: </b>" . $message['message']."<hr>";
				}
			}
			
			Email::send("makcyxa-k@yandex.ru", "СМС о внеплановых занятиях завтра", $body);
		}
		
		private function _generateMessage2($Group, $Entity, $tomorrow)
		{
			$GroupSchedule = GroupSchedule::find([
				"condition" => "id_group={$Group->id} AND date='" . date("Y-m-d", strtotime("tomorrow")) ."'"
			]);
			return Template::get(10, [
				'tomorrow'		=> $tomorrow,
				'time'			=> $GroupSchedule->time,
				'subject'		=> Subjects::$dative[$Group->id_subject],
				'address'		=> Branches::$address[$Group->id_branch],
				'branch' 		=> Branches::$all[$Group->id_branch],
				'cabinet'		=> trim(Cabinet::findById($GroupSchedule->cabinet)->number),
				'entity_login'	=> $Entity->login,
				'entity_password' => $Entity->password,
			]);
		}
		
		/**
		 * Обновить отсутствующие фактически занятия, но присутствующие в расписании.
		 * 
		 */
		public static function actionUpdateJournalMiss()
		{
			// Высчитываем полностью отсутствующие занятия
			$Groups = Group::findAll();
			
			// для подсчета цифры ВСЕГО ОТСУТСТВУЮЩИХ ГРУПП
			// (будет отображаться кружочком в меню)
			$count = [];
			foreach ($Groups as $Group) {
				$PastSchedule = $Group->getPastScheduleBeforeEnd();
				
				foreach ($PastSchedule as $Schedule) {
					// Проверяем было ли это занятие
					$was_lesson = VisitJournal::find([
						"condition" => "lesson_date = '" . $Schedule->date . "' AND id_group=" . $Schedule->id_group
					]);
					
					// если занятия не было, добавляем в ошибки
					if (!$was_lesson) {
						// с начала занятия должно пройти полчаса
/*
						$datetime1 = time();
						$datetime2 = strtotime("{$Schedule->lesson_date} {$Schedule->lesson_time}:00");
						$interval  = $datetime1 - $datetime2;
						$minutes   = round($interval / 60);
*/						
 						$return[$Schedule->date][] = $Schedule->id_group;
						
						if (!in_array($Schedule->id_group, $count)) {
							$count[] = $Schedule->id_group;
						}
					}
				}
			}
			
			if (!LOCAL_DEVELOPMENT) {
// 				memcached()->set("JournalErrorsCount", count($count), 3600 * 24);
				memcached()->set("JournalErrors", $return, 3600 * 24);
			} 
			// var_dump(count($count));
			return count($count);
		}
		
		/**
		 * Уведомить учителя об отсутствии записи в журнале
		 * 
		 */
		public static function actionTeacherNotifyJournalMiss()
		{
			// Высчитываем полностью отсутствующие занятия
			$Groups = Group::findAll();
			
			foreach ($Groups as $Group) {
				if (!$Group->Teacher) {
					continue;
				}
				$PastSchedule = $Group->getPastScheduleTeacherReport();
				
				foreach ($PastSchedule as $Schedule) {
					// Проверяем было ли это занятие
					$was_lesson = VisitJournal::find([
						"condition" => "lesson_date = '" . $Schedule->date . "' AND id_group=" . $Schedule->id_group
					]);
					
					// если занятия не было, отправляем смс
					if (!$was_lesson) {
						$message = Template::get(9, [
							"time" 			=> $Schedule->time,
							"teacher_name"	=> $Group->Teacher->first_name ." " .$Group->Teacher->middle_name
						]);
						if (!empty($Group->Teacher->phone)) {
							SMS::send($Group->Teacher->phone, $message);
						//	echo $message . "<hr>";
							Email::send("makcyxa-k@yandex.ru", "Отсутствие занятий у учителя", $message);
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
		
		public static function actionNotifyGroupsFirstLesson()
		{
			// все завтрашние занятия
			$GroupSchedule = GroupSchedule::findAll([
				"condition" => "date='" . date("Y-m-d", strtotime("tomorrow")) . "'",
				"group"		=> "id_group",
			]);
			
			$group_ids = [];
			foreach ($GroupSchedule as $GS) {
				$group_ids[] = $GS->id_group;
			}
			
			$Groups = Group::findAll([
				"condition" => "id IN (" . implode(",", $group_ids) . ")"
			]);
			
			$tomorrow_month = date("n", strtotime("tomorrow"));
			$tomorrow_month = russian_month($tomorrow_month);
			
			$tomorrow = date("j", strtotime("tomorrow")) . " " . $tomorrow_month;
			
			foreach ($Groups as $Group) {
				if ($Group->id_teacher) {
					// Было ли занятие уже у учителя?
					$teacher_already_had_lesson = VisitJournal::count([
						"condition" => "id_entity=" . $Group->id_teacher . " AND type_entity='" . Teacher::USER_TYPE . "' 
							AND id_group={$Group->id}"
					]) > 0 ? true : false;
					
					if (!$teacher_already_had_lesson) {
						$Teacher = Teacher::findById($Group->id_teacher);
						if ($Teacher) {
							foreach (Student::$_phone_fields as $phone_field) {
								$teacher_number = $Teacher->{$phone_field};
								if (!empty($teacher_number)) {
									$messages[] = [
										"type"      => "Учителю #" . $Teacher->id,
										"number" 	=> $teacher_number,
										"message"	=> self::_generateMessage($Group, $Teacher, $tomorrow),
									];
								}
							}
						}						
					}
				}
				foreach ($Group->students as $id_student) {
					$Student = Student::findById($id_student);
					if (!$Student) {
						continue;
					}
					// Проверяем было ли занятие у ученика
					$already_had_lesson = VisitJournal::count([
						"condition" => "id_entity=" . $Student->id . " AND type_entity='" . Student::USER_TYPE . "' 
							AND id_group={$Group->id} AND presence=1"
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
								"message"	=> self::_generateMessage($Group, $Student, $tomorrow),
							];
						}
						
						if ($Student->Representative) {
							$representative_number = $Student->Representative->{$phone_field};
							if (!empty($representative_number)) {
								$messages[] = [
									"type"      => "Представителю #" . $Student->Representative->id,
									"number" 	=> $representative_number,
									"message"	=> self::_generateMessage($Group, $Student, $tomorrow),
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
			
			Email::send("makcyxa-k@yandex.ru", "СМС о занятиях завтра", $body);
		}
		
		private function _generateMessage($Group, $Entity, $tomorrow)
		{
			$GroupSchedule = GroupSchedule::find([
				"condition" => "id_group={$Group->id} AND date='" . date("Y-m-d", strtotime("tomorrow")) ."'"
			]);
			return Template::get(5, [
				'tomorrow'		=> $tomorrow,
				'time'			=> $GroupSchedule->time,
				'subject'		=> Subjects::$dative[$Group->id_subject],
				'address'		=> Branches::$address[$Group->id_branch],
				'branch' 		=> Branches::$all[$Group->id_branch],
				'cabinet'		=> trim(Cabinet::findById($GroupSchedule->cabinet)->number),
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
					}
					// "Изменение статуса. Сообщение: $sms_id. Новый статус: $sms_status";
					// Здесь вы можете уже выполнять любые действия над этими данными.
				}
			}
			exit("100"); /* Важно наличие этого блока, иначе наша система посчитает, что в вашем обработчике сбой */
		}
	}