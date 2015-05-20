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
		const STR = 4;
		const IZM = 5;
		const OPL = 6;
		const RPT = 7;
		const VKS = 8;
		const ORH = 9;
		const PRR = 10;
		const PRG = 11;
		const NVG = 12;
		const KLG = 13;
		
		
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
		];
		
		# title
		static $title = "филиал";
		
		
		/**
		 * Построить селектор с кружочками метро
		 * $multiple - множественный выбор
		 */
		public static function buildSvgSelector($selected = false, $attrs, $multiple = false)
		{	
			echo "<select ".($multiple ? "multiple" : "")." class='form-control' ".Html::generateAttrs($attrs).">";
			
			// Заголовок
			if (!$multiple) {
				echo "<option selected disabled style='cursor: default; outline: none'>". static::$title ."</option>";
				echo "<option disabled style='cursor: default'>──────────────</option>";
			}

			foreach (static::$all as $id => $value) {
				// если это массив выбранных элементов (при $multiple = true)
				if (is_array($selected)) {
					$option_selected = in_array($id, $selected);
				} else {
					$option_selected = ($selected == $id);
				}
				echo "<option ".($option_selected ? "selected" : "")." value='$id' data-content='".self::metroSvg($id)."$value'></option>";
			}
			echo "</select>";
			echo "<script>$('#{$attrs['id']}').selectpicker()</script>";
		}
		
		
		/**
		 * Цвет метро, СВГ-кружок.
		 * 
		 */
		public static function metroSvg($id_branch)
		{
			switch ($id_branch) {
				# Оранжевый
				case self::TRG: case self::KLG: {
					$color = "#FBAA33";
					break;
				}
				# Красный
				case self::PVN: {
					$color = "#EF1E25";
					break;
				}
				# Голубой
				case self::BGT: {
					$color = "#019EE0";
					break;
				}
				# Синий
				case self::STR:
				case self::IZM: {
					$color = "#0252A2";
					break;
				}
				# Фиолетовый
				case self::OPL:
				case self::RPT: {
					$color = "#B61D8E";
					break;
				}
				# Зеленый
				case self::VKS:
				case self::ORH: {
					$color = "#029A55";
					break;
				}
				# Серый
				case self::PRR:
				case self::PRG: {
					$color = "#ACADAF";
					break;
				}
				# Желтый
				case self::NVG: {
					$color = "#FFD803";
					break;
				}
			}
			
			return
				'<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="svg-metro">
            		<circle fill="'.$color.'" r="6" cx="7" cy="7"></circle>
				</svg>';
		}
		
	}