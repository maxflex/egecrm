<?php
	/**
	 * Данные по предметам.
	 */
	class Subjects extends Factory {
		
		# Список предметов
		const MATH 		= 1;
		const RUSSIAN	= 6;
		const PHYSICS	= 2;
		const LITERATURE= 7;
		const CHEMISTRY	= 3;
		const SOCIETY	= 8;
		const BIOLOGY	= 4;
		const HISTORY	= 9;
		const COMPUTER	= 5;
		const ENGLISH	= 10;
		
		# Все предметы
		static $all = [
			self::MATH 		=> "математика",
			self::RUSSIAN	=> "русский",
			self::PHYSICS	=> "физика",
			self::LITERATURE=> "литература",
			self::CHEMISTRY	=> "химия",
			self::SOCIETY	=> "обществознание",
			self::BIOLOGY	=> "биология",
			self::HISTORY	=> "история",
			self::COMPUTER	=> "информатика",
			self::ENGLISH	=> "английский",
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
			ksort($subjects); 			// Сортируем предметы по цифрам

			echo '<div class="col-sm-3">';
            foreach ($subjects as $id => $subject) {
                echo "<div class='checkbox'><label>
                        <input ".(in_array($id, $selected_array) ? "checked" : "")." type='checkbox' name='{$name}[{$id}]' value='1'> " . $subject . "</label></div>";
                // На ID 5 открываем новый див, старый закрываем
                if ($id == 5) {
				echo "</div><div class='col-sm-3'>";
                }
            }   
            echo '</div>';

		}
		
	}