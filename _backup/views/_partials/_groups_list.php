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
			<a href="groups/edit/{{Group.id}}<?= User::isTeacher() || User::isStudent() ? "/schedule" : "" ?>">Группа №{{Group.id}}</a>
		</td>
		<td>
			<span ng-show="Group.id_branch" ng-bind-html="Group.branch | to_trusted" ng-class="{'mr3' : !$last}"></span>
		</td>
		<td width="150">
			{{Subjects[Group.id_subject]}}{{Group.grade ? '-' + Group.grade : ''}}{{Group.level ? '-' + GroupLevels[Group.level] : ''}}{{Group.is_special ? " (спец.)" : ""}}
		</td>
		<td>
			{{Group.students.length}}/<span style="color: #62CB64">{{Group.agreed_students_count}}</span> <ng-pluralize count="Group.students.length" when="{
				'one': 'ученик',
				'few': 'ученика',
				'many': 'учеников'
			}"></ng-pluralize>
		</td>
		<td>
			<span ng-show="Group.first_schedule">первое занятие {{Group.first_schedule | date:"dd.MM.yyyy"}}</span>
			<span ng-show="!Group.first_schedule && Group.expected_launch_date" class="quater-black">
				примерно {{dateToStart(Group.expected_launch_date)}}
			</span>
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
			{{Group.Schedule.length}} <ng-pluralize count="Group.Schedule.length" when="{
				'one': 'занятие',
				'few': 'занятия',
				'many': 'занятий'
			}"></ng-pluralize>
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