<?php
	/**
	 * Статусы заявки
	 */
	class TaskStatuses extends Factory {

		# Список
		const NEWR			= 1;
		const FINISHED		= 2;


		# Все
		static $all = [
			self::NEWR			=> "невыполненные",
			self::FINISHED		=> "выполненные",
		];

		# Заголовок
		static $title = "статус задачи";

	}
