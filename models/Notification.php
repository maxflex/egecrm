<?php
	class Notification  extends Model
	{
		// На эти номера телефонов отправлять уведомления
		static $numbers = [
			"9252727210", // Максим
			"9265292158", // Костя
			"9268289232", // Таня
		];
	
		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/

		public static $mysql_table	= "notifications";
		
		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/
		
		public function __construct($array)
		{
			parent::__construct($array);
			
			// Добавляем таймстемп
			$this->timestamp = strtotime($this->date." ".$this->time);
			
			// Добавляем id_request в напоминание
			// $this->getRelation("Request");
		}
		
		/*====================================== СТАТИЧЕСКИЕ ФУНКЦИИ ======================================*/
		
		/*
		 * Получаем все записи
		 * $params - дополнительные параметры (condition - дополнительное условие, order - параметры сортировки)
		 */
		public static function findAll($params = array())
		{	
			// Получаем все данные из таблицы + доп условие, если есть
			$result = static::dbConnection()->query("
				SELECT ".static::$mysql_table.".*, ". Request::$mysql_table .".id as id_request FROM ".static::$mysql_table." 
				JOIN ".Request::$mysql_table." ON ".Request::$mysql_table.".id_notification = ".static::$mysql_table.".id 
				WHERE true ".(!empty($params["condition"]) ? " AND ".$params["condition"] : "") // Если есть дополнительное условие выборки
				.(!empty($params["order"]) ? " ORDER BY ".$params["order"] : "")				// Если есть условие сортировки
				.(!empty($params["limit"]) ? " LIMIT ".$params["limit"] : "")					// Если есть условие лимита
			);

			// Если успешно получили и (что-то есть или нужно просто подсчитать)
			if ($result && $result->num_rows) {
				// Создаем массив объектов
				while ($array = $result->fetch_assoc()) {
					$return[] = new self($array);
				}
				
				// Возвращаем массив объектов
				return $return;
			}
			else {
				return false;
			}
		}
		
		/*====================================== ФУНКЦИИ КЛАССА ======================================*/

		// Уведомить
		public function notify()
		{
			$message = "Напоминание: заявка #" . $this->id_request . ", " .  NotificationTypes::getById($this->id_type) .", завтра в " . $this->time;
			
			// Отправляем СМС админам
			SMS::sendToNumbers(static::$numbers, $message);
		
			// Если встреча, то отправить напоминание по заявке
			if ($this->id_type == NotificationTypes::MEETING) {
				// Получаем заявку
				$Request = Request::findById($this->id_request);
			
				// Отправляем по номеру
				$message = ($Request->name ? $Request->name . ", напоминаем" : "Напоминаем") . " вам о встрече завтра в " . $this->time;
				SMS::send($Request->phone, $message);
			}
			
			// уведомили уже.
			$this->noted = 1;
			$this->save("noted");
		}
	}