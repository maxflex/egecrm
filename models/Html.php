<?php 
	
	/**
	 * Класс для генерации HTML-элементов.
	 */
	class Html {
		
		
		/**
		 * Датапикер.
		 * 
		 */
		public static function date($attrs)
		{
			echo "	<div class='input-group date' id='date-{$attrs['id']}'>
						<input ".self::generateAttrs($attrs)." type='text' data-date-format='yyyy.mm.dd' class='form-control'>
						<span class='input-group-addon'><i class='glyphicon glyphicon-th'></i></span>
					</div>";
			echo "<script>$('#date-{$attrs['id']}').datepicker({
				language	: 'ru',
				orientation	: 'top left',
				autoclose	: true
			})</script>";
		}
		
		/**
		 * Таймпикер.
		 * 
		 */
		public static function time($attrs)
		{
			echo "<input ".self::generateAttrs($attrs)." type='text' class='timepair'>";
			echo "<script>$('#{$attrs['id']}').timepicker({
				'timeFormat': 'H:i'
			})</script>";
		}
		
		
		/**
		 * Маска из цифр.
		 * 
		 */
		public static function digitMask($attrs, $mask)
		{
			echo "<input ".self::generateAttrs($attrs)." type='text'>";
			echo "<script>$('#{$attrs['id']}').mask('$mask', { autoclear: false });</script>";
		}
		
		
		
		/**
		 * Генерация строки HTML-атрибутов.
		 * 
		 */
		public static function generateAttrs($attrs)
		{
			foreach ($attrs as $attr_name => $attr_value) {
				$result[] = $attr_name . "='$attr_value'";
			}
			
			return implode(" ", $result); 
		}
	}