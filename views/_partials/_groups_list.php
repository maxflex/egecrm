<table class="table table-divlike">
	<tr ng-repeat="Group in Groups <?= ($filter ? '| filter:groupsFilter': "" ) ?> " class="group-list" data-id="{{Group.id}}">
		<td>
			<a href="groups/edit/{{Group.id}}">Группа №{{Group.id}}</a>
		</td>
		<td>
			<span ng-show="Group.id_branch" ng-bind-html="Group.branch | to_trusted" ng-class="{'mr3' : !$last}"></span>
		</td>
		<td width="210">
			{{Subjects[Group.id_subject]}} {{Group.is_special ? "(спецгруппа)" : ""}}
		</td>
		<td>
			{{Grades[Group.grade]}}
		</td>
		<td>
			{{Group.students.length}} <ng-pluralize count="Group.students.length" when="{
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
			{{weekdays[Group.day - 1].short}} <span ng-show="Group.start">в {{Group.start}}</span>
		</td>
		<td>
			<span ng-show="Group.Teacher">
				{{Group.Teacher.last_name}}
				{{Group.Teacher.first_name[0]}}. {{Group.Teacher.middle_name[0]}}.
			</span>
		</td>
	</tr>
</table>
<?php if ($filter) : ?>
<div ng-show="Groups.length > 0 && (Groups | filter:groupsFilter).length == 0" class="center half-black small" style="margin-bottom: 30px">
	не найдено групп, соответствующих запросу
</div>
<?php endif ?>