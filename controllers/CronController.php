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
		
		public static function actionNotifyGroupsFirstLesson()
		{
			$result = dbConnection()->query("
				SELECT id_group, MIN(date) as first_lesson_date FROM group_schedule
				GROUP BY id_group
				HAVING first_lesson_date = '" . date("Y-m-d", strtotime("tomorrow")) . "'
			");
			
			$tomorrow_group_ids = [];
			while ($row = $result->fetch_object()) {
				$tomorrow_group_ids[] = $row->id_group;
			}
			
			$Groups = Group::findAll([
				"condition" => "id IN (". implode(",", $tomorrow_group_ids) .")"
			]);
			
			$tomorrow_month = date("n", strtotime("tomorrow"));
			$tomorrow_month = russian_month($tomorrow_month);
			
			$tomorrow = date("d", strtotime("tomorrow")) . " " . $tomorrow_month;
			
			
			foreach ($Groups as $Group) {
				if ($Group->id_teacher) {
					$Teacher = Teacher::findById($Group->id_teacher);
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
				foreach ($Group->students as $id_student) {
					$Student = Student::findById($id_student);
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
//					SMS::send($message['number'], $message['message'], ["additional" => 3]);
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
			return "ЕГЭ-Центр информирует: завтра, {$tomorrow}, состоится занятие по " . Subjects::$dative[$Group->id_subject] . " (". Branches::$all[$Group->id_branch] ."). Подробности в личном кабинете на сайте ege-centr.ru (ссылка вверху справа). Ваш логин: {$Entity->login}, пароль {$Entity->password}. Администрация ЕГЭ-Центра";
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