<div ng-app="Group" ng-controller="TeacherListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="row" style="position: relative">
		<div class="col-sm-12">


<table class="table table-divlike" style="position: relative; width: 90%">
	<tr ng-repeat="Group in Groups"
		class="group-list" data-id="{{Group.id}}">
		<td>
			Группа №{{Group.id}}
		</td>
		<td>
			{{Subjects[Group.id_subject]}}
		</td>
		<td>
			{{Group.grade}} класс
		</td>
		<td>
			{{Group.students.length}} <ng-pluralize count="Group.students.length" when="{
				'one': 'ученик',
				'few': 'ученика',
				'many': 'учеников',
			}"></ng-pluralize>
		</td>
		<td>
			<!-- @time-refactored -->
			<span ng-repeat="data in Group.day_and_time">
				<span ng-repeat="d in data">{{ d.time.weekday_name }} в {{ d.time.time }}{{$last ? '' : ', '}}</span>
				{{ $last ? '' : ' и '}}
			</span>
		</td>
		<td>
			<!-- @time-refactored -->
			<span ng-repeat='cabinet in Group.cabinets'>
				<span style='color: {{ cabinet.color }}'>{{ cabinet.label }}</span>
				<span class="remove-space">{{$last ? '' : ', '}}</span>
			</span>
		</td>
		<td>
			<a href="teachers/groups/edit/{{Group.id}}/schedule">расписание</a>
		</td>
		<td>
			<a href="teachers/groups/journal/{{Group.id}}">посещаемость</a>
		</td>
	</tr>
</table>

			<div ng-show="Groups.length == 0" class="center half-black small" style="margin-bottom: 30px">список групп пуст</div>
		</div>
	</div>
</div>
