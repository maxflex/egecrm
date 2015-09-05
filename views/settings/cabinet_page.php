<div ng-app="Settings" ng-controller="CabinetsPageCtrl" ng-init="<?= $ang_init_data ?>">
	<div ng-repeat="(cabinet, GroupData) in Groups">
		<h5>Кабинет №{{cabinet}}</h5>
		<table class="table table-divlike">
		<tr ng-repeat="Group in GroupData">
			<td>
				<span>{{weekdays[Group.day - 1].short}}</span>
			</td>
			<td>{{Group.start}}</td>
			<td>Группа №{{Group.id}}</td>
			<td>
				{{Subjects[Group.id_subject]}}
			</td>
			<td>
				{{Grades[Group.grade]}}
			</td>
			<td>
				<span ng-show="Group.Teacher">
					{{Group.Teacher.last_name}}
					{{Group.Teacher.first_name[0]}}. {{Group.Teacher.middle_name[0]}}.
				</span>
			</td>
			<td>
				{{Group.students.length}} <ng-pluralize count="Group.students.length" when="{
					'one': 'ученик',
					'few': 'ученика',
					'many': 'учеников'
				}"></ng-pluralize>
			</td>
		</tr>
		</table>
	</div>
</div>