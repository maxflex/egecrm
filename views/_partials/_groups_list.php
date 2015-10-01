<?php
	/*
		Передать:
			* GroupLevels
			* Subjects::$three_letters
			* dateToStart()
	*/	
?>
<table class="table table-divlike" style="position: relative">
	<?php if ($loading) :?>
	<div id="frontend-loading" style="display: block">Загрузка...</div>
	<?php endif ?>
	<tr ng-repeat="Group in Groups <?= ($filter ? '| filter:groupsFilter': "" ) ?>" 
		class="group-list" data-id="{{Group.id}}">
		<td width="100">
			<a ng-class="{
				'text-danger'	: Group.schedule_count == 24,
				'bright-green'	: Group.schedule_count == 28,
			}" href="groups/edit/{{Group.id}}">Группа №{{Group.id}}</a>
		</td>
		<td>
			<span ng-show="Group.id_branch" ng-bind-html="Group.branch | to_trusted" ng-class="{'mr3' : !$last}"></span>
		</td>
		<td width="150">
			{{Subjects[Group.id_subject]}}{{Group.grade ? '-' + Group.grade : ''}}{{Group.level ? '-' + GroupLevels[Group.level] : ''}}{{Group.is_special ? " (спец.)" : ""}}
		</td>
		<td>
			{{Group.students.length}}/<span style="color: #62CB64">{{Group.agreed_students_count}}</span> 
		</td>
		<td width="40">
			<span class="glyphicon glyphicon-envelope" ng-class="{
				'group-student-sms-sent': Group.notified_students_count > 0,
				'quater-black'			: Group.notified_students_count == 0,
			}"></span><span ng-class="{
				'text-success'			: Group.notified_students_count > 0,
				'quater-black'			: Group.notified_students_count == 0,
			}">{{Group.notified_students_count}}</span>
		</td>
		<td>
			<span ng-show="Group.first_schedule">
				<span ng-show="!Group.past_lesson_count">1-й урок {{Group.first_schedule | date:"dd.MM"}}</span>
				<span ng-show="Group.past_lesson_count">было {{Group.past_lesson_count}} <ng-pluralize count="Group.past_lesson_count" when="{
					'one': 'занятие',
					'few': 'занятия',
					'many': 'занятий'
				}"></ng-pluralize></span>
			</span>
<!--
			<span ng-show="!Group.first_schedule && Group.expected_launch_date" class="quater-black">
				примерно {{dateToStart(Group.expected_launch_date)}}
			</span>
-->
		</td>
		<td>
			<div ng-repeat="(day, day_data) in Group.day_and_time">
				{{weekdays[day - 1].short}}
				<span ng-repeat="dd in day_data">
					в {{dd}}{{$last ? "" : ","}}
				</span>
			</div>
<!-- 			{{weekdays[Group.day - 1].short}} <span ng-show="Group.start">в {{Group.start}}</span> -->
		</td>
		<td>
			<span ng-show="Group.Teacher">
				{{Group.Teacher.last_name}}
				{{Group.Teacher.first_name[0]}}. {{Group.Teacher.middle_name[0]}}.
			</span>
		</td>
		<td>
			<span ng-show="Group.schedule_count > 0">{{Group.schedule_count}} <ng-pluralize count="Group.schedule_count" when="{
				'one': 'занятие',
				'few': 'занятия',
				'many': 'занятий'
			}"></ng-pluralize></span>
		</td>
		<td>
			<span class="label group-teacher-status<?= GroupTeacherStatuses::AGREED ?>" ng-show="Group.teacher_agreed">
				<?= GroupTeacherStatuses::$all[GroupTeacherStatuses::AGREED] ?>
			</span>
			<span class="label group-student-status3" ng-show="Group.approved" style="margin-left: 5px">
				OK
			</span>
		</td>
		<td>
			<svg ng-show="Group.ready_to_start == 0" xmlns="http://www.w3.org/2000/svg" version="1.1" class="svg-metro">
        		<circle fill="transparent" r="6" cx="7" cy="7" stroke="#C0C0C0" stroke-width="1"></circle>
			</svg>
			<svg ng-show="Group.ready_to_start > 0" xmlns="http://www.w3.org/2000/svg" version="1.1" class="svg-metro">
        		<circle r="6" cx="7" cy="7" class="ready-to-start"></circle>
			</svg>
		</td>
		<td>
			<span class="glyphicon glyphicon-exclamation-sign text-danger" ng-show="!Group.lesson_days_match" style="top: 2px"></span>
		</td>
		<td>
			<span ng-show="!Group.open" class="half-black">
				набор закрыт
			</span>
		</td>
<!--
		<td width="150">
		    <span ng-repeat="weekday in weekdays" class="group-freetime-block">
				<span class="freetime-bar green" ng-repeat="time in weekday.schedule track by $index" 
					ng-class="{
						'empty-green'	: !inDayAndTime2(time, Group.day_and_time[$parent.$index + 1]),
					}" ng-hide="time == ''" style="position: relative; top: 3px">
				</span>
			</span>
		</td>
-->
	</tr>
</table>
<?php if ($filter) : ?>
<div ng-show="Groups.length > 0 && (Groups | filter:groupsFilter).length == 0" class="center half-black small" style="margin-bottom: 30px">
	не найдено групп, соответствующих запросу
</div>
<?php endif ?>