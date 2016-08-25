<?php
	/*
		Передать:
			* GroupLevels
			* Subjects::$three_letters
			* dateToStart()
	*/
?>
<table class="table table-hover border-reverse" style="position: relative">
	<?php if ($loading) :?>
	<div id="frontend-loading" style="display: block">Загрузка...</div>
	<?php endif ?>
	<tr ng-repeat="Group in Groups <?= ($filter ? '| filter:groupsFilter': "" ) ?>"
		class="group-list" data-id="{{Group.id}}">
		<td width="120">
			<a href="groups/edit/{{Group.id}}">Группа №{{Group.id}}</a>
		</td>
		<td>
			<span ng-show="Group.id_branch" ng-bind-html="Group.branch | to_trusted" ng-class="{'mr3' : !$last}" class="center-label"></span>
		</td>
		<td width="150">
			{{Subjects[Group.id_subject]}}{{Group.grade ? '-' + Group.grade : ''}}{{Group.level ? '-' + <?= GroupLevels::json() ?>[Group.level] : ''}}{{Group.is_special ? " (спец.)" : ""}}
		</td>
		<td>
			{{Group.students.length}} <ng-pluralize count="Group.students.length" when="{
				'one': 'ученик',
				'few': 'ученика',
				'many': 'учеников'
			}"></ng-pluralize>
		</td>
<!--
		<td width="40">
			<span class="glyphicon glyphicon-envelope" ng-class="{
				'group-student-sms-sent': Group.notified_students_count > 0,
				'quater-black'			: Group.notified_students_count == 0,
			}"></span><span ng-class="{
				'text-success'			: Group.notified_students_count > 0,
				'quater-black'			: Group.notified_students_count == 0,
			}">{{Group.notified_students_count}}</span>
		</td>
-->
		<td>
			<span ng-show="Group.first_schedule">
				<span ng-show="!Group.past_lesson_count">1-й урок {{Group.first_schedule | date:"dd.MM"}}</span><span ng-show="Group.past_lesson_count">было {{Group.past_lesson_count}} <ng-pluralize count="Group.past_lesson_count" when="{
					'one': 'занятие',
					'few': 'занятия',
					'many': 'занятий'
				}"></ng-pluralize></span></span><span ng-show="Group.first_schedule && Group.schedule_count.paid > 0">, </span><span ng-show="Group.schedule_count.paid > 0">всего 
                {{Group.schedule_count.paid}}<span ng-show='Group.schedule_count.free' class="text-gray">+{{Group.schedule_count.free}}
                </span>
            </span>
		</td>
		<td>
			<span ng-repeat="(day, day_data) in Group.day_and_time">
				{{weekdays[day - 1].short}}
				<span ng-repeat="dd in day_data">
					в {{dd}}{{$last ? "" : ","}}</span>{{$last ? "" : ","}}
			</span>
			<span ng-show="Group.day_and_time.length !== undefined">без расписания</span>
<!-- 			{{weekdays[Group.day - 1].short}} <span ng-show="Group.start">в {{Group.start}}</span> -->
		</td>
		<td>
			<span ng-show="Group.id_teacher" ng-init="_Teacher = Group.Teacher || getTeacher(Group.id_teacher)">
				{{_Teacher.last_name}}
				{{_Teacher.first_name[0]}}. {{_Teacher.middle_name[0]}}.
			</span>
		</td>
		<td>
			<span ng-show="Group.days_before_exam !== false">
				<span ng-show="Group.days_before_exam > 0">
					запас {{Group.days_before_exam}} <ng-pluralize count="Group.days_before_exam" when="{
						'one': 'день',
						'few': 'дня',
						'many': 'дней'
					}"></ng-pluralize>
				</span>
				<span ng-show="Group.days_before_exam <= 0">запаса нет</span>
			</span>
		</td>
		<td>
			<span ng-show='Group.ended'>заархивирована</span>
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