<?php
	/**
	 * Статусы заявки
	 */
	class TaskStatuses extends Factory {

		# Список
		const NEWR		       	    = 1;
		// const NEWR_FOR_MAX		    = 2;
		// const NEWR_FOR_SHAM		    = 3;
		const UPLOADED_GITHUB		= 4;
		const UPLOADED_PRODUCTION	= 5;
		const FINISHED		        = 6;
		const DEBUG 		        = 7;
		const CLOSED 	 	        = 8;

		# Все
		static $all = [
			self::NEWR			        => "новая задача",
			// self::NEWR_FOR_MAX			=> "новое для Макса",
			// self::NEWR_FOR_SHAM			=> "новое для Шамшода",
			self::UPLOADED_GITHUB		=> "выгружено на GitHub",
			self::UPLOADED_PRODUCTION	=> "выгружено на Production (тестируется)",
			self::FINISHED              => "выгружено на Production (готово)",
			self::DEBUG 		        => "требует доработки",
			self::CLOSED 		        => "закрыто",
		];

		# Заголовок
		static $title = "статус задачи";

	}
