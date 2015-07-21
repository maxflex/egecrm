<?php
	
	
	/**
	 * Филиалы.
	 * 
	 */
	class Branches extends Factory {
		
		# Список
		const TRG = 1;
		const PVN = 2;	
		const BGT = 3;
		const STR = 4;	# удалено
		const IZM = 5;
		const OPL = 6;
		const RPT = 7;
		const VKS = 8;
		const ORH = 9;
		const PRR = 10; # удалено
		const PRG = 11;
		const NVG = 12;
		const KLG = 13;
		const BRT = 14;
		const MLD = 15;
		const VLD = 16;
		
		
		
		# Все
		static $all  = [
			self::TRG => "Тургеневская",
			self::PVN => "Проспект Вернадского",
			self::BGT => "Багратионовская",
			self::STR => "Строгино",
			self::IZM => "Измайловская",
			self::OPL => "Октябрьское поле",
			self::RPT => "Рязанский Проспект",
			self::VKS => "Войковская",
			self::ORH => "Орехово",
			self::PRR => "Петровско-Разумовская",
			self::PRG => "Пражская",
			self::NVG => "Новогигеево",
			self::KLG => "Калужская",
			self::BRT => "Братиславская",
			self::MLD => "Молодежная",
			self::VLD => "Владыкино",
		];
		
		# title
		static $title = "филиал";
		
		# удаленные станции
		static $deleted = array(
			self::STR,
			self::PRR,
		);

		
		/**
		 * Построить селектор с кружочками метро
		 * $multiple - множественный выбор
		 */
		public static function buildSvgSelector($selected = false, $attrs, $multiple = false)
		{	
			echo "<select ".($multiple ? "multiple" : "")." class='form-control' ".Html::generateAttrs($attrs).">";
			
			// Заголовок
			if (!$multiple) {
				echo "<option selected style='cursor: default; outline: none' value=''>". static::$title ."</option>";
				echo "<option disabled style='cursor: default' value=''>──────────────</option>";
			}
			
			// Получаем филиалы
			$branches = self::getBranches();
			
			foreach ($branches as $branch) {
				// если это массив выбранных элементов (при $multiple = true)
				if (is_array($selected)) {
					$option_selected = in_array($branch["id"], $selected);
				} else {
					$option_selected = ($selected == $branch["id"]);
				}
				// если опция не удалена (если удалена, то отображается только в том случае, если удаленный вариант был выбран ранее)
				if (!in_array($branch["id"], self::$deleted) || ($option_selected)) {
					echo "<option ".($option_selected ? "selected" : "")." value='{$branch['id']}' data-content='{$branch['svg']}{$branch['name']}'></option>";	
				}
			}
			echo "</select>";
			echo "<script>$('#{$attrs['id']}').selectpicker()</script>";
		}
		
		
		/**
		 * Цвет метро, СВГ-кружок.
		 * 
		 * $return - возвратить вес линии для сортировки
		 * $return_color_only – возвратить только цвет вместо SVG
		 */
		public static function metroSvg($id_branch, $return = false, $return_color_only = false)
		{
			switch ($id_branch) {
				# Оранжевый
				case self::TRG: case self::KLG: {
					if ($return) {
						return 1;
					}
					$color = "#FBAA33";
					break;
				}
				# Красный
				case self::PVN: {
					if ($return) {
						return 2;
					}
					$color = "#EF1E25";
					break;
				}
				# Голубой
				case self::BGT: {
					if ($return) {
						return 3;
					}
					$color = "#019EE0";
					break;
				}
				# Синий
				case self::STR:
				case self::IZM:
				case self::MLD: {
					if ($return) {
						return 4;
					}
					$color = "#0252A2";
					break;
				}
				# Фиолетовый
				case self::OPL:
				case self::RPT: {
					if ($return) {
						return 5;
					}
					$color = "#B61D8E";
					break;
				}
				# Зеленый
				case self::VKS:
				case self::ORH: {
					if ($return) {
						return 6;
					}
					$color = "#029A55";
					break;
				}
				# Серый
				case self::PRR:
				case self::VLD:
				case self::PRG: {
					if ($return) {
						return 8;
					}
					$color = "#ACADAF";
					break;
				}
				# Желтый
				case self::NVG: {
					if ($return) {
						return 9;
					}
					$color = "#FFD803";
					break;
				}
				# Салатовый
				case self::BRT: {
					if ($return) {
						return 7;
					}
					$color = "#B1D332";
					break;
				}
			}
			
			if ($return_color_only) {
				return $color;
			} else {
				return
					'<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="svg-metro">
	            		<circle fill="'.$color.'" r="6" cx="7" cy="7"></circle>
					</svg>';	
			}
		}
		
		
		/**
		 * Получить отсортированные по весу линий филиалы с другими параметрами (имя, свг и тд)
		 * 
		 */
		public static function getBranches()
		{
			$branches = static::$all;
			
			// Генерируем филиалы
			foreach ($branches as $id => $branch) {
				$return[] = [
					"id"	=> $id,
					"name"	=> $branch,
					"line"	=> self::metroSvg($id, true),
					"svg"	=> self::metroSvg($id)
				];
			}
			
			// Сортируем по весу ветки метро
			usort($return, function($a, $b) {
				$lineWeightA = $a["line"];
				$lineWeightB = $b["line"];

				if ($lineWeightA == $lineWeightB) {
					// Внутри одинакового цвета ветки сортируем по ID (чем меньше ID, тем выше)
					return ($a["id"] < $b["id"]) ? -1 : 1;
				}
				
				return ($lineWeightA < $lineWeightB) ? -1 : 1;
			});
			
			return $return;
		}
	}