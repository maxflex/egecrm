<?php

	// Контроллер
	class CronController extends Controller
	{
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
	}