<?php


	/**
	 * Класс статической коллекции. Типа классов, филиалов, статусов заявки.
	 */
	class Factory {

		// Все записи коллекции
		static $all = false;

		// Заголовок коллекции
		static $title = false;

		# удаленные записи коллекции
		static $deleted = array();

		/**
		 * Построить селектор из всех записей.
		 * $selcted - что выбрать по умолчанию
		 * $name 	– имя селектора, по умолчанию имя класса
		 * $attrs	– остальные атрибуты
		 *
		 */
		public static function buildSelector($selcted = false, $name = false, $attrs = false)
		{
			$class_name = strtolower(get_called_class());
			echo "<select class='form-control' id='".$class_name."-select' name='".($name ? $name : $class_name)."' ".Html::generateAttrs($attrs).">";
			if (static::$title) {
				echo "<option selected value=''>". static::$title ."</option>";
				echo "<option disabled value=''>──────────────</option>";
			}
			foreach (static::$all as $id => $value) {
				// удаленные записи коллекции отображать только в том случае, если они уже были выбраны
				// (т.е. были использованы ранее, до удаления)
				if (!in_array($id, static::$deleted) || ($id == $selcted)) {
					echo "<option value='$id' ".($id == $selcted ? "selected" : "").">$value</option>";
				}
			}
			echo "</select>";
		}


		/**
		 * Создать для ng-options ануляра.
		 *
		 */
		public static function angInit()
		{
			return angInit(strtolower(get_called_class()), static::$all);
		}



		/**
		 * Получить название по ID.
		 *
		 */
		public static function getById($id)
		{
			return static::$all[$id];
		}

		/**
		 * Получить с названиями констат фактории, c учетом удалений
		 *
		 */
		public static function get()
		{
			$A = new ReflectionClass(get_called_class());

			// получаем названия констант
			$constants = $A->getConstants();

			foreach ($constants as $name => $value) {
				// не показывать удаленные
				if (!in_array($value, static::$deleted)) {
					$return[] = [
						"id" 		=> $value,
						"constant"	=> $name,
						"name"		=> static::$all[$value],
					];
				}
			}
			
			return $return;
		}
	}
