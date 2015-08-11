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