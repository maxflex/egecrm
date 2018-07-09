<form id="teacher-edit" ng-app="Teacher" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>" autocomplete='off'>
	<div class="row">
		<div class="col-sm-3" style="width: 13%">
			<div class='tutor-img-new'>
				<img ng-src="{{Teacher.has_photo ? 'http://static.a-perspektiva.ru/img/tutors/' + Teacher.id + '.' + Teacher.photo_extension : 'img/teachers/no-profile-img.gif'}}">
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
				<?= Subjects::buildMultiSelector($Teacher->subjects_ec, ["id" => "subjects-select", 'disabled' => 'disabled'], 'three_letters') ?>
			</div>
			<div class="form-group">
				<?= Grades::buildMultiSelector($Teacher->grades, ["id" => "public-grades", 'disabled' => 'disabled']) ?>
			</div>
		</div>
		<div class="col-sm-3" style="width: 46%">
			<phones entity="Teacher" entity-type="Teacher" disabled with-comment></phones>
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
				<select class="form-control" placeholder="место работы" disabled>
					<option ng-selected="{{Teacher.in_egecentr == workplace}}" ng-repeat="(workplace, label) in Workplaces" value="{{workplace}}">{{label}}</option>
				</select>
			</div>
			<div class="form-group">
				<select class="form-control" ng-model="Teacher.id_head_teacher" ng-disabled="is_teacher">
					<option selected value="0">доступность информации в профиле классного руководителя</option>
					<option disabled>──────────────</option>
					<option ng-repeat="T in Teachers" value="{{T.id}}" ng-selected="T.id == Teacher.id_head_teacher">
						{{T.last_name}} {{T.first_name[0]}}. {{T.middle_name[0]}}.
					</option>
				</select>
			</div>
			<div class="form-group">
				<?= Branches::buildMultiSelector($Teacher->branches, ["id" => "teacher-branches", 'readonly' => 'true']) ?>
			</div>
		</div>
	</div>

	<!-- /Публичная информация -->
	<?php if ($Teacher->id) :?>
	<div class="top-links wide" style="margin-top: 20px">
	    <span class="link-like menu-link menu-0" ng-click="setMenu(0)" ng-class="{'active': current_menu == 0}">
	    	ГРУППЫ
	    </span>
	    <span class="link-like menu-link menu-1" ng-click="setMenu(1)" ng-class="{'active': current_menu == 1}">
	    	ОТЗЫВЫ
	    </span>
	    <span class="link-like menu-link menu-2" ng-click="setMenu(2, true)" ng-class="{'active': current_menu == 2}">
			БАЛАНС СЧЕТА
	    </span>
	    <span class="link-like menu-link menu-3" ng-click="setMenu(3, true)" ng-class="{'active': current_menu == 3}">
	    	ПЛАТЕЖИ
	    </span>
	    <span class="link-like menu-link menu-4" ng-click="setMenu(4)" ng-class="{'active': current_menu == 4}" style='position: relative'>
	    	ОТЧЕТЫ
			<label class="circle-label" ng-show="Teacher.reports_needed">
				<span>{{ Teacher.reports_needed }}</span>
			</label>
	    </span>
	    <span class="link-like menu-link menu-5" ng-click="setMenu(5)" ng-class="{'active': current_menu == 5}">
	    	СТАТИСТИКА
	    </span>
	    <span class="link-like menu-link menu-6" ng-click="setMenu(6)" ng-class="{'active': current_menu == 6}">
	    	ГРАФИК
	    </span>
	    <span class="link-like menu-link menu-7" ng-click="setMenu(7, true)" ng-class="{'active': current_menu == 7}">
	    	ДОПОЛНИТЕЛЬНЫЕ УСЛУГИ
	    </span>
    </div>

	<?= partial('groups') ?>
	<?= partial('reviews') ?>
	<?= partial('lessons') ?>
	<?= partial('payments') ?>
	<?= partial('reports') ?>
	<?= partial('stats') ?>
	<?= partial("freetime") ?>
	<?= partial('additional') ?>

	<?= User::isTeacher() ? globalPartial('email') : '' ?>

	<?php endif ?>
	<!-- СМС -->
	<sms number='sms_number' templates="full"></sms>
	<!-- /СМС -->
</form>

<style>
    .form-group .dropdown-menu {
        pointer-events: none;
    }
</style>
