<?php
	/**
	 * Данные по предметам.
	 */
	class Subjects extends Factory {
		
		# Список предметов
		const MATH 		= 1;
		const PHYSICS	= 2;
		const CHEMISTRY	= 3;
		const BIOLOGY	= 4;
		const COMPUTER	= 5;
		const RUSSIAN	= 6;
		const LITERATURE= 7;
		const SOCIETY	= 8;
		const HISTORY	= 9;
		const ENGLISH	= 10;
		
		# Все предметы
		static $all = [
			self::MATH 		=> "математика",
			self::PHYSICS	=> "физика",
			self::RUSSIAN	=> "русский",
			self::LITERATURE=> "литература",
			self::ENGLISH	=> "английский",
			self::HISTORY	=> "история",
			self::SOCIETY	=> "обществознание",
			self::CHEMISTRY	=> "химия",
			self::BIOLOGY	=> "биология",
			self::COMPUTER	=> "информатика",
		];
		
		# Все предметы
		static $full = [
			self::MATH 		=> "Математика",
			self::PHYSICS	=> "Физика",
			self::RUSSIAN	=> "Русский язык",
			self::LITERATURE=> "Литература",
			self::ENGLISH	=> "Английский язык",
			self::HISTORY	=> "История",
			self::SOCIETY	=> "Обществознание",
			self::CHEMISTRY	=> "Химия",
			self::BIOLOGY	=> "Биология",
			self::COMPUTER	=> "Информатика",
		];
		
		static $dative = [
			self::MATH 		=> "математике",
			self::PHYSICS	=> "физике",
			self::RUSSIAN	=> "русскому языку",
			self::LITERATURE=> "литературе",
			self::ENGLISH	=> "английскому языку",
			self::HISTORY	=> "истории",
			self::SOCIETY	=> "обществознанию",
			self::CHEMISTRY	=> "химии",
			self::BIOLOGY	=> "биологии",
			self::COMPUTER	=> "информатике",
		];
		
		static $short = [
			self::MATH 		=> "М",
			self::PHYSICS	=> "Ф",
			self::RUSSIAN	=> "Р",
			self::LITERATURE=> "Л",
			self::ENGLISH	=> "А",
			self::HISTORY	=> "Ис",
			self::SOCIETY	=> "О",
			self::CHEMISTRY	=> "Х",
			self::BIOLOGY	=> "Б",
			self::COMPUTER	=> "Ин",
		];
		
		static $three_letters = [
			self::MATH 		=> "МАТ",
			self::PHYSICS	=> "ФИЗ",
			self::RUSSIAN	=> "РУС",
			self::LITERATURE=> "ЛИТ",
			self::ENGLISH	=> "АНГ",
			self::HISTORY	=> "ИСТ",
			self::SOCIETY	=> "ОБЩ",
			self::CHEMISTRY	=> "ХИМ",
			self::BIOLOGY	=> "БИО",
			self::COMPUTER	=> "ИНФ",	
		];
		
		static $short_eng = [
			self::MATH 		=> "math",
			self::PHYSICS	=> "phys",
			self::RUSSIAN	=> "rus",
			self::LITERATURE=> "lit",
			self::ENGLISH	=> "eng",
			self::HISTORY	=> "his",
			self::SOCIETY	=> "soc",
			self::CHEMISTRY	=> "chem",
			self::BIOLOGY	=> "bio",
			self::COMPUTER	=> "inf",	
		];
		
		# Время тестирования
		static $minutes_9 = [
			self::MATH 		=> 235,
			self::PHYSICS	=> 180,
			self::RUSSIAN	=> 235,
			self::LITERATURE=> 180,
			self::ENGLISH	=> 120,
			self::HISTORY	=> 180,
			self::SOCIETY	=> 180,
			self::CHEMISTRY	=> 120,
			self::BIOLOGY	=> 180,
			self::COMPUTER	=> 180,		
		];
		
		static $minutes_11 = [
			self::MATH 		=> 235,
			self::PHYSICS	=> 235,
			self::RUSSIAN	=> 210,
			self::LITERATURE=> 235,
			self::ENGLISH	=> 180,
			self::HISTORY	=> 235,
			self::SOCIETY	=> 235,
			self::CHEMISTRY	=> 210,
			self::BIOLOGY	=> 180,
			self::COMPUTER	=> 235,		
		];
		
		# Заголовок
		static $title = "предмет";

        public static function json()
        {
            return json_encode(static::$all);
        }

		/**
		 * Создает col-sm-6 селектор (предметы в два стобца по col-sm-3).
		 * $selected_array - массив отмеченых предметов
		 * $name - имя для формы
		 */
		public static function buildColSelector($selected_array, $name)
		{
			$subjects = static::$all;	// Получаем все предметы
			// ksort($subjects); 			// Сортируем предметы по цифрам

			echo '<div class="col-sm-4">';
			$count = 1;
            foreach ($subjects as $id => $subject) { 
                echo "<div class='checkbox'>
                		<label class='ios7-switch' style='padding-left: 0; font-size: 16px'>
                        	<input ".(in_array($id, $selected_array) ? "checked" : "")." type='checkbox' name='{$name}[{$id}]' value='$id'>
							<span class='switch' style='top: 1px'></span>
							<span class='text-primary' style='font-size: 14px'>$subject</span>
						</div>";
                // На ID 5 открываем новый див, старый закрываем
                if ($count == round(count(static::$all) / 2)) {
					echo "</div><div class='col-sm-4'>";
                }
                $count++;
            }   
            echo '</div>';

		}
		
		
				/**
		 * Построить селектор с кружочками метро
		 * $multiple - множественный выбор
		 */
		public static function buildMultiSelector($selected = false, $attrs, $mode = 'all')
		{
			$multiple = true;
			echo "<select ".($multiple ? "multiple" : "")." class='form-control' ".Html::generateAttrs($attrs).">";
			
			switch($mode) {
				case 'all': {
					$options = static::$all;
					break;
				}
				case 'three_letters': {
					$options = static::$three_letters;
					break;
				}
			}
			
			// Заголовок
			if (!$multiple) {
				echo "<option selected style='cursor: default; outline: none' value=''>". static::$title ."</option>";
				echo "<option disabled style='cursor: default' value=''>──────────────</option>";
			}
						
			foreach ($options as $id_subject => $name) {
				echo "<script>console.log($id_subject)</script>";
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