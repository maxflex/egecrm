<?php


	/**
	 * Филиалы.
	 *
	 */
	class Branches extends Factory {

		# title
		static $title = "филиал";

		# удаленные станции
		static $deleted = [];

		public static function getField($id, $field)
		{
			return dbFactory()->query("SELECT $field FROM branches WHERE id={$id}")->fetch_object()->{$field};
		}

		public static function getAll($field = 'full', $sort_by_weight = false)
		{
			$result = dbFactory()->query("SELECT id, branches.{$field} FROM branches ORDER BY id ASC");
			while ($row = $result->fetch_object()) {
				if ($field == '*') {
					$return[] = $row;
				} else {
					$return[$row->id] = $row->{$field};
				}
			}

			if ($sort_by_weight) {
				// Сортируем по весу ветки метро
				usort($return, function($a, $b) {
					$lineWeightA = $a->weight;
					$lineWeightB = $b->weight;

					if ($lineWeightA == $lineWeightB) {
						// Внутри одинакового цвета ветки сортируем по ID (чем меньше ID, тем выше)
						return ($a->id < $b->id) ? -1 : 1;
					}

					return ($lineWeightA < $lineWeightB) ? -1 : 1;
				});
			}

			return $return;
		}

		public static function getOne($id)
		{
			return dbFactory()->query("SELECT * FROM branches WHERE id={$id}")->fetch_object();
		}

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
			$branches = self::getAll('*', true);

			foreach ($branches as $branch) {
                if ($params['all_cabinets']) {
                    $isSelected = $selected == $branch->id  && !$cabinet ? "selected" : "";
                    echo "<option ".$isSelected." value='{$branch->id}'
							ng-selected=".($isSelected ? 'true' : 'false')."
							data-content='".
                                ($params['coloured_text'] ? '<span style="color:' . $branch->color . ';">' : '').
                                ($params['without_svg']   ? '' : self::metroSvg($branch->color)).
                                ($params['short'] ? $branch->short . ' (все кабинеты)' : $branch->full).
                                ($params['coloured_text'] ? '</span>' : '').
                            "'></option>";
                }

				$Cabinets = Cabinet::getBranchId($branch->id);
				foreach($Cabinets as $Cabinet) {
					// если это массив выбранных элементов (при $multiple = true)
					if (is_array($selected)) {
						$option_selected = in_array($branch->id, $selected);
					} else {
						$option_selected = ($selected == $branch->id && $cabinet == $Cabinet->id);
					}
					// если опция не удалена (если удалена, то отображается только в том случае, если удаленный вариант был выбран ранее)
					if (!in_array($branch->id, self::$deleted) || ($option_selected)) {
						echo "<option ".($option_selected ? "selected" : "")." value='-{$Cabinet->id}'
							ng-selected='" . ( $option_selected ? 'true' : 'false' ). "'
							ng-class=\"{'half-opacity': free_cabinets[" . $branch->id . "][{$Cabinet->id}]}\"
							data-content='".
                                    ($params['coloured_text'] ? '<span style="color:' . $branch->color . ';">' : '').
                                    ($params['without_svg']   ? '' : self::metroSvg($branch->color)).
                                    ($params['short'] ? $branch->short : $branch->full )."-{$Cabinet->number}".
                                    ($params['coloured_text'] ? '</span>' : '').
                            "'></option>";
					}
					//
				}
			}
			echo "</select>";
			echo "<script>$('#{$attrs['id']}').selectpicker()</script>";
		}

		/**
		 * Вернуть массив кабинетов в формате филиал-кабинет
		 * @branch-refactored
		 */
		public static function allCabinets()
		{
			$branches = self::getAll('*');
			foreach ($branches as $branch) {
				$Cabinets = Cabinet::getBranchId($branch->id);
				foreach($Cabinets as $Cabinet) {
					$return[] = [
						'id' 	=> $Cabinet->id,
						'color' => $branch->color,
						'label'	=> $branch->short . "–" . $Cabinet->number,
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
			$branches = self::getAll('*', true);

			foreach ($branches as $branch) {
				// если это массив выбранных элементов (при $multiple = true)
				if (is_array($selected)) {
					$option_selected = in_array($branch->id, $selected);
				} else {
					$option_selected = ($selected == $branch->id);
				}
				// если опция не удалена (если удалена, то отображается только в том случае, если удаленный вариант был выбран ранее)
				if (!in_array($branch->id, self::$deleted) || ($option_selected)) {
					echo "<option ".($option_selected ? "selected" : "")." value='{$branch->id}' data-content='" . self::metroSvg($branch->color) . $branch->full . "'></option>";
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
			$branches = self::getAll('*', true);

			foreach ($branches as $branch) {
				// если это массив выбранных элементов (при $multiple = true)
				if (is_array($selected)) {
					$option_selected = in_array($branch->id, $selected);
				} else {
					$option_selected = ($selected == $branch->id);
				}
				// если опция не удалена (если удалена, то отображается только в том случае, если удаленный вариант был выбран ранее)
				if (!in_array($branch->id, self::$deleted) || ($option_selected)) {
					echo "<option ".($option_selected ? "selected" : "")." value='{$branch->id}' data-content='" . self::metroSvg($branch->color) . $branch->full . "'></option>";
				}
			}
			echo "</select>";
			echo "<script>$('#{$attrs['id']}').selectpicker()</script>";
		}


		/**
		 * Цвет метро, СВГ-кружок.
		 */
		public static function metroSvg($color)
		{
			return
					'<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="svg-metro">
	            		<circle fill="'.$color.'" r="6" cx="7" cy="7"></circle>
					</svg>';
		}

		// @branch-refactored
		public static function getShortColoredById($id_branch, $additional = false)
		{
			$branch = self::getOne($id_branch);

			return "<span style='color: ". $branch->color . "'>"
				. $branch->short . ($additional ? $additional : "") . "</span>";
		}

		// @branch-refactored
		public static function getShortColored()
		{
			$branches = self::getAll('*');

			foreach($branches as $branch) {
				$return[$branch->id] = "<span style='color: ". $branch->color . "'>"
					. $branch->short . "</span>";
			}

			return $return;
		}
	}
