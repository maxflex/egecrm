<?php 
	
	/**
	 * Класс для генерации HTML-элементов.
	 */
	class Html {
		
		public static function teacherImg($model, $class, $attrs)
		{
			echo "<img " .self::generateAttrs($attrs) . " class='teacher-img {$class}' 
				src=\"{{{$model}.has_photo ? 'img/teachers/' + {$model}.id + '_2x.jpg' : 'img/teachers/no-profile-img.gif'}}\">";
		}
		
		/**
		 * Датапикер.
		 * 
		 */
		public static function date($attrs, $bs_date_addon = false)
		{
			echo "	<div class='input-group date bs-date".($bs_date_addon ? "-$bs_date_addon" : "")."' id='date-{$attrs['id']}'>
						<input ".self::generateAttrs($attrs)." type='text' data-date-format='yyyy.mm.dd' class='form-control'>
						<span class='input-group-addon'><i class='glyphicon glyphicon-th'></i></span>
					</div>";
		}
		
		/**
		 * Таймпикер.
		 * 
		 */
		public static function time($attrs)
		{
			echo "<input ".self::generateAttrs($attrs)." type='text' class='timepair'>";
			echo "<script>$('#{$attrs['id']}').timepicker({
				'timeFormat': 'H:i',
				'scrollDefault'	: '09:30'
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
		
		
		/**
		 * Номера телефонов с функционалом добавления и отравки смс.
		 * 
		 */
		public static function phones($who)
		{
			$Model = ucfirst($who);
echo <<<HTML
<div class="form-group">
    <div class="input-group" 
        ng-class="{'input-group-with-hidden-span' : !phoneCorrect('{$who}-phone') || (!isMobilePhone('{$who}-phone') && {$who}_phone_level >= 2) }">
    	<input ng-keyup id="{$who}-phone" type="text"
    		placeholder="телефон" class="form-control phone-masked"  ng-model="{$Model}.phone">
    	<div class="input-group-btn">
	    	<button class="btn btn-default" ng-show="phoneCorrect('{$who}-phone')" ng-click="callSip('{$who}-phone')" ng-class="{
					'addon-bordered' : {$who}_phone_level >= 2  && !isMobilePhone('{$who}-phone')
				}">
				<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
			</button>
			<button ng-show="phoneCorrect('{$who}-phone') && isMobilePhone('{$who}-phone')" ng-class="{
					'addon-bordered' : {$who}_phone_level >= 2 || !phoneCorrect('{$who}-phone')
				}" class="btn btn-default" type="button" onclick="smsDialog('{$who}-phone')">
					<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
			</button>
	    	<button ng-hide="{$who}_phone_level >= 2 || !phoneCorrect('{$who}-phone')" class="btn btn-default" type="button" ng-click="{$who}_phone_level = {$who}_phone_level + 1">
	    		<span class="glyphicon glyphicon-plus no-margin-right small"></span>
	    	</button>
        </div>
	</div>
</div>
<div class="form-group" ng-show="{$who}_phone_level >= 2">
    <div class="input-group" 
        ng-class="{'input-group-with-hidden-span' : !phoneCorrect('{$who}-phone-2')  || (!isMobilePhone('{$who}-phone') && {$who}_phone_level >= 3) }">
    	<input ng-keyup id="{$who}-phone-2" type="text"
    		placeholder="телефон 2" class="form-control phone-masked" ng-model="{$Model}.phone2">
    	<div class="input-group-btn">
    		<button class="btn btn-default" ng-show="phoneCorrect('{$who}-phone-2')" ng-click="callSip('{$who}-phone-2')"  ng-class="{
					'addon-bordered' : {$who}_phone_level >= 3  && !isMobilePhone('{$who}-phone-2')
				}">
				<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
			</button>
			<button ng-show="phoneCorrect('{$who}-phone-2') && isMobilePhone('{$who}-phone-2')" ng-class="{
					'addon-bordered' : {$who}_phone_level >= 3 || !phoneCorrect('{$who}-phone-2')
				}" class="btn btn-default" type="button"  onclick="smsDialog('{$who}-phone-2')">
					<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
			</button>
        	<button ng-hide="{$who}_phone_level >= 3 || !phoneCorrect('{$who}-phone-2')" class="btn btn-default" type="button" ng-click="{$who}_phone_level = {$who}_phone_level + 1">
        		<span class="glyphicon glyphicon-plus no-margin-right small"></span>
        	</button>
        </div>
	</div>
</div>
<div class="form-group" ng-show="{$who}_phone_level >= 3">
	<div class="input-group" 
		ng-class="{'input-group-with-hidden-span' : !phoneCorrect('{$who}-phone-3')  || !isMobilePhone('{$who}-phone-3') }">
        <input type="text" id="{$who}-phone-3" placeholder="телефон 3" 
        	class="form-control phone-masked" ng-model="{$Model}.phone3">
        	<div class="input-group-btn">
	        	<button class="btn btn-default" ng-show="phoneCorrect('{$who}-phone-3')" ng-click="callSip('{$who}-phone-3')"  ng-class="{
					'addon-bordered' : !isMobilePhone('{$who}-phone-3')
				}">
					<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
				</button>
				<button ng-show="phoneCorrect('{$who}-phone-3') && isMobilePhone('{$who}-phone-3')" ng-class="{
						!phoneCorrect('{$who}-phone-3')
					}" class="btn btn-default" type="button"  onclick="smsDialog('{$who}-phone-3')">
						<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
				</button>
            </div>
	</div>
</div>
HTML;
		}
	}