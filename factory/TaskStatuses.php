<?php
	/**
	 * Статусы заявки
	 */
	class TaskStatuses extends Factory {

		# Список
		const NEWR			= 1;
		const FINISHED		= 2;
		const DEBUG 		= 3;
		const CLOSED 	 	= 4;


		# Все
		static $all = [
			self::NEWR			=> "новая задача",
			self::FINISHED		=> "выполнено",
			self::DEBUG 		=> "требует доработки",
			self::CLOSED 		=> "закрыто",
		];

		# Заголовок
		static $title = "статус задачи";

	}
