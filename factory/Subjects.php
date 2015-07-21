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
		
	}