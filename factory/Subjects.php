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
		
		# Заголовок
		static $title = "предмет";
		
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