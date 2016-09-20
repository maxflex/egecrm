<?php
	class EntityFreetime extends Model
	{

		/*====================================== ПЕРЕМЕННЫЕ И КОНСТАНТЫ ======================================*/
		const STUDENT = 'student';
		const TEACHER = 'teacher';

		public static $mysql_table	= "freetime";


		/*====================================== СИСТЕМНЫЕ ФУНКЦИИ ======================================*/

		public function __construct($array)
		{
			parent::__construct($array);
		}

		/**
		 * @refactored
		 */
		public static function remove($id_entity, $type_entity, $id_time)
		{
			static::deleteAll([
				'condition' => "id_entity=$id_entity AND type_entity='$type_entity' AND id_time=$id_time"
			]);
		}

		/*
		 * В этот день свободно
		 * @refactored
		 */
		public static function hasFreetime($id_entity, $type_entity, $id_time)
		{
			return static::count([
				'condition' => "id_entity=$id_entity AND type_entity='$type_entity' AND id_time=$id_time"
			]);
		}

	}
