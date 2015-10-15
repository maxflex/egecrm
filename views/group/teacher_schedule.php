<div ng-app="Group" ng-controller="ScheduleCtrl" ng-init="<?= $ang_init_data ?>" id="calendar-app">
<div class="panel panel-primary">
	<div class="panel-heading">
		Расписание группы №<?= $Group->id ?>
		<span ng-hide="<?= (User::isTeacher() || User::isStudent() ? 'true' : 'false') ?>" class="link-reverse small pointer" onclick="redirect('groups/edit/<?= $Group->id ?>')">вернуться в группу</span>
		<div class="pull-right">
			<span class="link-reverse pointer" ng-click="setTimeFromGroup(Group)" ng-show="Group.Schedule.length && Group.start" 
				ng-hide="Group.open == 0 || <?= (User::isTeacher() || User::isStudent() ? 'true' : 'false') ?>">
				установить время занятия из настроек группы
			</span>
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<div class="row">
			<div class="col-sm-12">
				<div>
					Место проведения занятий: ЕГЭ-Центр-<?= Branches::$all[$Group->id_branch] ?> (<?= Cabinet::findById($Group->cabinet)->number ?> каб.)
					<? partial("how_to_get", ["Group" => $Group]) ?>
				</div>
				<div>
					Преподаватель: 
					<span ng-show="Teacher">
						{{Teacher.last_name + " " + Teacher.first_name + " " + Teacher.middle_name}} 
					</span>
					<span ng-show="!Teacher">
						пока не назначен
					</span>
				</div>
				<div>
					Расписание: {{lessonCount()}} <ng-pluralize count="lessonCount()" when="{
						'one': 'раз',
						'few': 'раза',
					}"></ng-pluralize> в неделю 
					<span ng-show="Group.day_and_time">
						
						<span ng-show="lessonCount() == 1">
							(<span ng-repeat="(day, day_data) in Group.day_and_time">{{weekdays[day - 1].short}}<span ng-repeat="dd in day_data"> в {{dd}}{{$last ? "" : ","}}</span>{{$last ? "" : ", "}}</span>). 
<!--
							<span ng-show="!Group.approved">Расписание может быть изменено.</span>
							<span ng-show="Group.approved">Расписание подтверждено</span>
-->
						</span>
						
						<span ng-show="lessonCount() > 1">
							(<span ng-repeat="(day, day_data) in Group.day_and_time">{{weekdays[day - 1].short}}<span ng-repeat="dd in day_data"> в {{dd}}{{$last ? "" : ","}}</span>{{$last ? "" : ", "}}</span>). 
<!--
							<span ng-show="!Group.approved">Расписание может быть изменено.</span>
							<span ng-show="Group.approved">Расписание подтверждено</span>
-->
						</span>
						
<!-- 						<span ng-show="!Group.approved"> (может быть изменено)</span> -->
					</span>
					<span ng-show="!Group.day_and_time">
						формируется
					</span>
				</div>
				<div>
					<span ng-show="!Group.Schedule">
						Дата первого занятия: пока не определена (группа ждет доукомплектования). Занятия начнутся до 30 сентября 2015 г. 
					</span>
					<span ng-show="Group.Schedule">
						Дата первого занятия: {{formatDate(Group.Schedule[0].date)}}
					</span>
				</div>
				<div>
					<div>
						<span class="day-explain"></span> – дни занятий
					</div>
					<div>
						<span class="day-explain was-lesson"></span> – проведенные занятия
					</div>
					<div>
						<span class="day-explain vocation"></span> – дни, считающиеся нерабочими по производственному календарю
					</div>
				</div>
			</div>
		</div>
		<div class="row calendar">
			<div class="col-sm-5" style="position: relative">
				<div style="position: absolute; height: 100%; width: 100%; z-index: 20"></div>
				<div class="row calendar-row" ng-repeat="month in [9, 10, 11, 12, 1, 2, 3, 4, 5, 6]">
					<div class="col-sm-4 month-name text-primary">
						{{monthName(month)}} {{month == 1 ? "2016" : ""}}
					</div>
					<div class="col-sm-8">
						<div class="calendar-month" month="{{month}}">
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-2"></div>
			<div class="col-sm-5">
				<h3 style="font-weight: bold; margin: 10px 0 25px">{{Group.Schedule.length}} <ng-pluralize count="Group.Schedule.length" when="{
						'one': 'занятие',
						'few': 'занятия',
						'many': 'занятий'
					}"></ng-pluralize></h3>
				<div class="row" ng-repeat="Schedule in Group.Schedule | orderBy:'date'" style="height: 30px">
					<div class="col-sm-6">
						<a href='groups/<?= $Group->id ?>/lesson/{{Schedule.date}}'>{{getLine1(Schedule)}}</a>
					</div>
					<div class="col-sm-6">
						<div class="lessons-table">
							<input type="text" style="display: none" class="timemask no-border-outline" ng-value="Schedule.time">
							<span  <?= (User::isTeacher() || User::isStudent() ? '' : 'ng-click="setTime(Schedule, $event)"') ?>>
								{{Schedule.time ? Schedule.time : 'не установлено'}}
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		
	</div>
</div>
</div>