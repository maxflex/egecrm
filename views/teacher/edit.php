<form id="teacher-edit" ng-app="Teacher" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>" autocomplete='off'>
	<div class="row">
		<div class="col-sm-3" style="width: 13%">
			<div class='tutor-img-new'>
				<img src="{{Teacher.has_photo ? 'http://static.a-perspektiva.ru/img/tutors/' + Teacher.id + '.' + Teacher.photo_extension : 'img/teachers/no-profile-img.gif'}}">
			</div>
		</div>
		<div class="col-sm-3" style="width: 20%">
			<div class="form-group" style="position: relative">
				<input class="form-control" ng-model="Teacher.birth_year" placeholder="год рождения" disabled>
				<span class="inside-input">– {{ yearDifference(Teacher.birth_year) }} <ng-pluralize count="yearDifference(Teacher.birth_year)" when="{
                    'one': 'год',
                    'few': 'года',
                    'many': 'лет',
                }">лет</ng-pluralize></span>
			</div>
			<div class="form-group" style="position: relative">
				<input class="form-control" ng-model="Teacher.start_career_year" placeholder="стаж" disabled>
				<span class="inside-input">– стаж {{ yearDifference(Teacher.start_career_year) }} <ng-pluralize count="yearDifference(Teacher.start_career_year)" when="{
                    'one': 'год',
                    'few': 'года',
                    'many': 'лет',
                }">лет</ng-pluralize></span>
			</div>
			<div class="form-group">
				<?= Subjects::buildMultiSelector($Teacher->subjects, ["id" => "subjects-select", 'disabled' => 'disabled'], 'three_letters') ?>
			</div>
			<div class="form-group">
				<?= Grades::buildMultiSelector($Teacher->grades, ["id" => "public-grades", 'disabled' => 'disabled']) ?>
			</div>
		</div>
		<div class="col-sm-3" style="width: 46%">




<div class="form-group">
	<div class="input-group"
		 ng-class="{'input-group-with-hidden-span' : !PhoneService.isFull(Teacher.phone) || (!PhoneService.isMobile('teacher-phone') && teacher_phone_level >= 2) }">
		<input id="teacher-phone" type="text" disabled placeholder="телефон" class="form-control phone-masked"
			   ng-model="Teacher.phone"
			   ng-value="PhoneService.format(Teacher.phone)"
		>
    	<div class='comment-inside-input'>
			<span class="glyphicon glyphicon-pencil text-gray" ng-show='!Teacher.phone_comment'></span>
			<input type="text" class='no-border-outline phone-comment' ng-model='Teacher.phone_comment' disabled="">
    	</div>

    	<div class="input-group-btn">
	    	<button class="btn btn-default" ng-show="PhoneService.isFull(Teacher.phone)"
					ng-click="PhoneService.call('teacher-phone')"
					ng-class="{'addon-bordered' : teacher_phone_level >= 2  && !PhoneService.isMobile(Teacher.phone)}"
			>
				<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
			</button>
			<button class="btn btn-default" type="button"
					ng-show="PhoneService.isFull(Teacher.phone) && PhoneService.isMobile(Teacher.phone)"
					ng-class="{'addon-bordered' : teacher_phone_level >= 2 || !PhoneService.isFull(Teacher.phone)}"
					ng-click="PhoneService.sms(Teacher.phone)"
			>
				<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
			</button>
			<button disabled class="btn btn-default" style='background: #EEE; color: black' type="button"
					ng-hide="teacher_phone_level >= 2 || !PhoneService.isFull(Teacher.phone)"
					ng-click="teacher_phone_level = teacher_phone_level + 1">
				<span class="glyphicon glyphicon-plus no-margin-right small"></span>
			</button>
		</div>
	</div>
</div>
<div class="form-group" ng-show="teacher_phone_level >= 2">
	<div class="input-group"
        ng-class="{'input-group-with-hidden-span' : !PhoneService.isFull(Teacher.phone2)  || (!PhoneService.isMobile(Teacher.phone2) && teacher_phone_level >= 3) }">
		<input id="teacher-phone-2" type="text" disabled placeholder="телефон 2" class="form-control phone-masked"
			   ng-model="Teacher.phone2"
			   ng-value="PhoneService.format(Teacher.phone2)"
		>
		<div class='comment-inside-input'>
			<span class="glyphicon glyphicon-pencil text-gray" ng-show='!Teacher.phone2_comment'></span>
			<input type="text" class='no-border-outline phone-comment' ng-model='Teacher.phone2_comment' disabled="">
		</div>
		<div class="input-group-btn">
			<button class="btn btn-default"
					ng-show="PhoneService.isFull(Teacher.phone2)"
					ng-click="PhoneService.call(Teacher.phone2)"
					ng-class="{'addon-bordered' : teacher_phone_level >= 3  && !PhoneService.isMobile(Teacher.phone2)}"
			>
				<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
			</button>
			<button class="btn btn-default" type="button"
					ng-show="PhoneService.isFull(Teacher.phone2) && PhoneService.isMobile(Teacher.phone2)"
					ng-class="{'addon-bordered' : teacher_phone_level >= 3 || !phoneCorrect(Teacher.phone2)}"
					ng-click="PhoneService.sms(Teacher.phone2)"
			>
				<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
			</button>
			<button style='background: #EEE; color: black;' disabled class="btn btn-default" type="button"
					ng-hide="teacher_phone_level >= 3 || !PhoneService.isFull(Teacher.phone2)"
					ng-click="teacher_phone_level = teacher_phone_level + 1"
			>
				<span class="glyphicon glyphicon-plus no-margin-right small"></span>
			</button>
		</div>
	</div>
</div>
<div class="form-group" ng-show="teacher_phone_level >= 3">
	<div class="input-group" 
		ng-class="{'input-group-with-hidden-span' : !PhoneService.isFull(Teacher.phone3)  || !PhoneService.isMobile(Teacher.phone3) }">
		<input type="text" id="teacher-phone-3" placeholder="телефон 3"  disabled class="form-control phone-masked"
			   ng-model="Teacher.phone3"
			   ng-value="PhoneService.format(Teacher.phone3)"
		>
		<div class='comment-inside-input'>
			<span class="glyphicon glyphicon-pencil text-gray" ng-show='!Teacher.phone3_comment'></span>
			<input type="text" class='no-border-outline phone-comment' ng-model='Teacher.phone3_comment' disabled="">
		</div>
		<div class="input-group-btn">
			<button class="btn btn-default"
					ng-show="PhoneService.isFull(Teacher.phone3)"
					ng-click="PhoneService.call(Teacher.phone3)"
					ng-class="{'addon-bordered' : !PhoneService.isMobile(Teacher.phone3)}"
			>
				<span class="glyphicon glyphicon-earphone no-margin-right small"></span>
			</button>
			<button style='background: #EEE; color: black' disabled class="btn btn-default" type="button"
					ng-show="PhoneService.isFull(Teacher.phone3) && PhoneService.isMobile(Teacher.phone3)"
					ng-class="{'addon-bordered' : !PhoneService.isFull(Teacher.phone3)}"
					ng-click="PhoneService.sms(Teacher.phone3)"
			>
				<span class="glyphicon glyphicon-envelope no-margin-right small"></span>
			</button>
		</div>
	</div>
</div>
			<div class="input-group fakeInput">
                <input placeholder="email" ng-model="Teacher.email" disabled class="form-control no-border-outline">

                <div class='comment-inside-input'>
                    <span class="glyphicon glyphicon-pencil text-gray" ng-show='!Teacher.email_comment'></span>
                    <input type="text" class='no-border-outline phone-comment' ng-model='Teacher.email_comment' disabled="">
                </div>


                <div class="input-group-btn" style="box-sizing: border-box;">
                    <button class="btn btn-default" type="button" ng-disabled="!Teacher.email" ng-click="emailDialog(Teacher.email)">
                        <span class="glyphicon glyphicon-envelope no-margin-right small" ></span>
                    </button>
                </div>
            </div>


        </div>
		<div class="col-sm-3"  style="width: 20%">
			<div class="form-group">
				<select class="form-control" ng-model="Teacher.in_egecentr" placeholder="пол" disabled>
					<option value='0'>не работает в ЕГЭ-Центре</option>
					<option value='1'>работает в ЕГЭ-Центре</option>
				</select>
			</div>
			
			<div class="form-group">
				<div class="input-group">
			      <input placeholder="логин" disabled ng-model="Teacher.login" class="form-control">
			      <span class="input-group-addon">
			      	<span class="glyphicon glyphicon-lock no-margin-right small" ng-class="{
				      	'text-danger': Teacher.banned
			      	}"></span>
<!-- 			        <input type="checkbox" aria-label="заблокирован"> -->
			      </span>
			    </div>
			</div>
			<div class="form-group">
				<input placeholder="пароль" disabled type="text" ng-model="Teacher.password" class="form-control">
			</div>
			
			<div class="form-group">
				<?= Branches::buildMultiSelector($Teacher->branches, ["id" => "teacher-branches", 'disabled' => 'disabled']) ?>
			</div>
		</div>
<!--
		<div class="col-sm-3">
			<div class="form-group">
				<?= Subjects::buildMultiSelector($Teacher->subjects, ["id" => "subjects-select"]) ?>
			</div>
			<div class="form-group">
				<input placeholder="оценка эксперта" ng-model="Teacher.expert_mark" class="form-control">
			</div>
			<div class="form-group">
				<div class="input-group">
					<input placeholder="ID в базе" ng-model="Teacher.id_a_pers" class="form-control digits-only">
					<span class="input-group-btn">
			        	<button class="btn btn-default" type="button" ng-disabled="!Teacher.id_a_pers" ng-click="goToTutor()">
			        		<span class="glyphicon glyphicon-user no-margin-right"></span>
			        	</button>
					</span>
				</div>
			</div>
		</div>
-->
	</div>

	<!-- /Публичная информация -->
	<?php if ($Teacher->id) :?>
	<div class="top-links wide" style="margin-top: 20px">
	    <span class="link-like" ng-click="setMenu(0)" ng-class="{'active': current_menu == 0}">
	    	ГРУППЫ
	    </span>
	    <span class="link-like" ng-click="setMenu(1)" ng-class="{'active': current_menu == 1}">
	    	ОТЗЫВЫ
	    </span>
	    <span class="link-like" ng-click="setMenu(2)" ng-class="{'active': current_menu == 2}">
			ПРОВЕДЕННЫЕ ЗАНЯТИЯ
	    </span>
	    <span class="link-like" ng-click="setMenu(3, true)" ng-class="{'active': current_menu == 3}">
	    	ПЛАТЕЖИ
	    </span>
	    <span class="link-like" ng-click="setMenu(4)" ng-class="{'active': current_menu == 4}">
	    	ОТЧЕТЫ
	    </span>
	    <span class="link-like" ng-click="setMenu(5)" ng-class="{'active': current_menu == 5}">
	    	СТАТИСТИКА
	    </span>
	    <span class="link-like" ng-click="setMenu(6)" ng-class="{'active': current_menu == 6}">
	    	ГРАФИК
	    </span>
    </div>
    
	<?= partial('groups') ?>
	<?= partial('reviews') ?>
	<?= partial('lessons') ?>
	<?= partial('payments') ?>
	<?= partial('reports') ?>
	<?= partial('stats') ?>
	<?= partial("freetime") ?>
	<?php endif ?>
	<!-- СМС -->
	<sms number='sms_number' templates="full"></sms>
	<!-- /СМС -->
</form>