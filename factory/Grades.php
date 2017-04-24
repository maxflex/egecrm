<?php
	/**
	 * Классы
	 */
	class Grades extends Factory {

		# Список
		const FIRST 	= 1;
		const SECOND 	= 2;
		const THIRD 	= 3;
		const FOURTH	= 4;
		const FIFTH		= 5;
		const SIXTH		= 6;
		const SEVENTH	= 7;
		const EIGHTH	= 8;
		const NINETH	= 9;
		const TENTH		= 10;
		const ELEVENTH	= 11;
		const STUDENTS	= 12;
		const OTHERS	= 13;
		const EXTERNAL	= 14;

		# Класс (для формирования надписи)
		const GRADE 	= "класс";

		# Все
		static $all  = [
			self::FIRST 	=> self::FIRST	." ".self::GRADE,
			self::SECOND 	=> self::SECOND	." ".self::GRADE,
			self::THIRD 	=> self::THIRD	." ".self::GRADE,
			self::FOURTH	=> self::FOURTH	." ".self::GRADE,
			self::FIFTH		=> self::FIFTH	." ".self::GRADE,
			self::SIXTH		=> self::SIXTH	." ".self::GRADE,
			self::SEVENTH	=> self::SEVENTH." ".self::GRADE,
			self::EIGHTH	=> self::EIGHTH	." ".self::GRADE,
			self::NINETH	=> self::NINETH	." ".self::GRADE,
			self::TENTH		=> self::TENTH	." ".self::GRADE,
			self::ELEVENTH	=> self::ELEVENTH." ".self::GRADE,
			self::STUDENTS	=> 'студенты',
			self::OTHERS	=> 'остальные',
			self::EXTERNAL	=> 'экстернат',
		];

        const LIGHT = [self::NINETH, self::TENTH, self::ELEVENTH, self::EXTERNAL];

		# Заголовок
		static $title = "класс";

		public static function json()
		{
			return json_encode(static::$all);
		}

        public static function buildSelector($selcted = false, $name = false, $attrs = false, $light = false)
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
                    // лёкгая версия
                    if (!$light || in_array($id, static::LIGHT)) {
                        echo "<option value='$id' ".($id == $selcted ? "selected" : "").">$value</option>";					
                    }
                }
            }
            echo "</select>";
        }

		/**
		 * Построить селектор с кружочками метро
		 * $multiple - множественный выбор
		 */
		public static function buildMultiSelector($selected = false, $attrs)
		{
			$multiple = true;
			echo "<select ".($multiple ? "multiple" : "")." class='form-control' ".Html::generateAttrs($attrs).">";

			// Заголовок
			if (!$multiple) {
				echo "<option selected style='cursor: default; outline: none' value=''>". static::$title ."</option>";
				echo "<option disabled style='cursor: default' value=''>──────────────</option>";
			}

			foreach (static::$all as $id_subject => $name) {
				// если это массив выбранных элементов (при $multiple = true)
				$option_selected = in_array($id_subject, $selected);

				// если опция не удалена (если удалена, то отображается только в том случае, если удаленный вариант был выбран ранее)
				if (!in_array($id_subject, self::$deleted) || ($option_selected)) {
					echo "<option ".($option_selected ? "selected" : "")." value='{$id_subject}'>{$name}</option>";
				}
			}
			echo "</select>";
		}
	}
