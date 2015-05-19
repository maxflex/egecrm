<?php
	/**
	 * Данные по предметам.
	 */
	class Subjects extends Factory {
		
		# Список предметов
		const MATH 		= 1;
		const RUSSIAN	= 2;
		const PHYSICS	= 3;
		const LITERATURE= 4;
		const CHEMISTRY	= 5;
		const SOCIETY	= 6;
		const BIOLOGY	= 7;
		const HISTORY	= 8;
		const COMPUTER	= 9;
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
			self::HISTORY	=> "химия",
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
			echo '<div class="col-sm-3">';
            foreach (static::$all as $id => $subject) {
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