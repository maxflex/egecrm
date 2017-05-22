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
		const IZM = 5;
		const OPL = 6;
		const RPT = 7;
		const SKL = 8;
		const ORH = 9;
		const UJN = 11;
		const PER = 12;
		const KLG = 13;
		const BRT = 14;
		const MLD = 15;
		const VLD = 16;
		const BEL = 17;



		# Все
		static $all  = [
			self::TRG => "Тургеневская",
			self::PVN => "Проспект Вернадского",
			self::BGT => "Багратионовская",
			self::IZM => "Измайловская",
			self::OPL => "Октябрьское поле",
			self::RPT => "Рязанский Проспект",
			self::SKL => "Сокол",
			self::ORH => "Орехово",
			self::UJN => "Южная",
			self::PER => "Перово",
			self::KLG => "Калужская",
			self::BRT => "Братиславская",
			self::MLD => "Молодежная",
			self::VLD => "Владыкино",
			self::BEL => "Беляево",
		];

		# Короткие
		static $short  = [
			self::TRG => "ТУР",
			self::PVN => "ВЕР",
			self::BGT => "БАГ",
			self::IZM => "ИЗМ",
			self::OPL => "ОКТ",
			self::RPT => "РЯЗ",
			self::SKL => "СОК",
			self::ORH => "ОРЕ",
			self::UJN => "ЮЖН",
			self::PER => "ПЕР",
			self::KLG => "КЛЖ",
			self::BRT => "БРА",
			self::MLD => "МОЛ",
			self::VLD => "ВЛА",
			self::BEL => "БЕЛ",
		];

		# Короткие
		static $address  = [
			self::TRG => "Мясницкая 40с1",
			self::PVN => "",
			self::BGT => "",
			self::IZM => "",
			self::OPL => "",
			self::RPT => "",
			self::SKL => "Ленинградский проспект, 68с24",
			self::ORH => "",
			self::UJN => "",
			self::PER => "",
			self::KLG => "Научный проезд 8с1",
			self::BRT => "",
			self::MLD => "",
			self::VLD => "",
		];

		# title
		static $title = "филиал";

		# удаленные станции
		static $deleted = [];

		/**
		 * Построить селектор с кружочками метро
		 * $multiple - множественный выбор
		 */
		public static function buildSvgSelectorCabinets($selected = false, $cabinet = false, $attrs, $params = [])
		{

			echo "<select ".($params['multiple'] ? "multiple" : "")." class='form-control' ".Html::generateAttrs($attrs).">";

			// Заголовок
			if (!$params['multiple']) {
				echo "<option selected style='cursor: default; outline: none' value=''>". ($params['title'] ? $params['title'] : static::$title ) ."</option>";
				echo "<option disabled style='cursor: default' value=''>──────────────</option>";
			}

			// Получаем филиалы
			$branches = self::getBranches();

			foreach ($branches as $branch) {
                if ($params['all_cabinets']) {
                    $isSelected = $selected == $branch["id"]  && !$cabinet ? "selected" : "";
                    echo "<option ".$isSelected." value='{$branch['id']}'
							ng-selected=".($isSelected ? 'true' : 'false')."
							data-content='".
                                ($params['coloured_text'] ? '<span style="color:'.$branch['color'].';">' : '').
                                ($params['without_svg']   ? '' : $branch['svg']).
                                ($params['short'] ? $branch['short'].' (все кабинеты)' : $branch['name']).
                                ($params['coloured_text'] ? '</span>' : '').
                            "'></option>";
                }

				$Cabinets = Cabinet::getBranchId($branch['id']);
				foreach($Cabinets as $Cabinet) {
					// если это массив выбранных элементов (при $multiple = true)
					if (is_array($selected)) {
						$option_selected = in_array($branch["id"], $selected);
					} else {
						$option_selected = ($selected == $branch["id"] && $cabinet == $Cabinet->id);
					}
					// если опция не удалена (если удалена, то отображается только в том случае, если удаленный вариант был выбран ранее)
					if (!in_array($branch["id"], self::$deleted) || ($option_selected)) {
						echo "<option ".($option_selected ? "selected" : "")." value='-{$Cabinet->id}'
							ng-selected='" . ( $option_selected ? 'true' : 'false' ). "'
							ng-class=\"{'half-opacity': free_cabinets[" . $branch["id"] . "][{$Cabinet->id}]}\"
							data-content='".
                                    ($params['coloured_text'] ? '<span style="color:'.$branch['color'].';">' : '').
                                    ($params['without_svg']   ? '' : $branch['svg']).
                                    ($params['short'] ? $branch['short'] : $branch['name'])."-{$Cabinet->number}".
                                    ($params['coloured_text'] ? '</span>' : '').
                            "'></option>";
					}
					//
				}
			}
			echo "</select>";
			echo "<script>$('#{$attrs['id']}').selectpicker()</script>";
		}


		public static function cabinetsSelector($attrs = [])
		{

			echo "<select class='branch-cabinet' ".Html::generateAttrs($attrs).">";
			echo "<option selected style='cursor: default; outline: none' value=''>кабинет</option>";
			echo "<option disabled style='cursor: default' value=''>──────────────</option>";
			// Получаем филиалы
			$branches = self::getBranches();

			foreach ($branches as $branch) {
				$Cabinets = Cabinet::getBranchId($branch['id']);
				foreach($Cabinets as $Cabinet) {
					echo "<option value='{$Cabinet->id}'>" . $branch['short'] . "–" . $Cabinet->number ."</option>";
				}
			}
			echo "</select>";
		}

		/**
		 * Вернуть массив кабинетов в формате филиал-кабинет
		 */
		public static function allCabinets()
		{
			$branches = self::getBranches();
			foreach ($branches as $branch) {
				$Cabinets = Cabinet::getBranchId($branch['id']);
				foreach($Cabinets as $Cabinet) {
					$return[] = [
						'id' 	=> $Cabinet->id,
						'color' => static::metroSvg($Cabinet->id_branch, false, true),
						'label'	=> $branch['short'] . "–" . $Cabinet->number,
						'number'	=> $Cabinet->number,
					];
				}
			}
			return $return;
		}


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
		 * Построить селектор с кружочками метро
		 * $multiple - множественный выбор
		 */
		public static function buildMultiSelector($selected = false, $attrs, $none_selected = '')
		{
			$multiple = true;
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
			if (!empty($none_selected)) {
				echo "<script>$('#{$attrs['id']}').selectpicker({noneSelectedText: '$none_selected'})</script>";
			}
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
				case self::TRG:
                case self::BEL:
                case self::KLG: {
					if ($return) {
						return 1;
					}
					if ($id_branch == self::TRG) {
						$color = "#FBAA33";
					} else {
						$color = "#C07911";
					}
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
//				case self::STR:
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
				case self::SKL:
				case self::ORH: {
					if ($return) {
						return 6;
					}
					$color = "#029A55";
					break;
				}
				# Серый
//				case self::PRR:
				case self::UJN:
				case self::VLD: {
					if ($return) {
						return 8;
					}
					$color = "#ACADAF";
					break;
				}
				# Желтый
				case self::PER: {
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


		public static function getName($id_branch) {
			return self::metroSvg($id_branch) .  self::getById($id_branch);
		}

		public static function getShortColoredById($id_branch, $additional = false)
		{
			$name = self::$short[$id_branch];

			return "<span style='color: ". self::metroSvg($id_branch, false, true) . "'>"
				. $name . ($additional ? $additional : "") . "</span>";
		}


		public static function getShortColored()
		{
			foreach (self::$all as $id_branch => $name) {
				$return[$id_branch] = self::getShortColoredById($id_branch);
			}

			return $return;
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
					"svg"	=> self::metroSvg($id),
					"short"	=> self::$short[$id],
					"color"	=> self::metroSvg($id, false, true)
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
