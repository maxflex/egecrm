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
			return Template::get(5, [
				'tomorrow'		=> $tomorrow,
				'time'			=> $Group->getFirstSchedule(false)->time,
				'subject'		=> Subjects::$dative[$Group->id_subject],
				'branch' 		=> Branches::$all[$Group->id_branch],
				'cabinet'		=> Cabinet::findById($Group->cabinet)->number,
				'entity_login'	=> $Entity->login,
				'entity_password' => $Entity->password,
			]);
		}
		
		/**
		 * Разослать уведомления
		 * 
		 */
		public static function actionNotify()
		{
			// Получаем завтрашние уведомления
			$Notifications = Notification::findAll([
				"condition" => Notification::$mysql_table . ".date='". dateFormat("tomorrow", true) ."' AND ". Notification::$mysql_table . ".noted=0",
			]);
			
			// Отсылаем СМСки по уведомлениям
			foreach ($Notifications as $Notification) {
				$Notification->notify();
			}
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