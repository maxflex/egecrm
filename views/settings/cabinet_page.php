<div ng-app="Settings" ng-controller="CabinetsPageCtrl" ng-init="<?= $ang_init_data ?>">
	<div ng-repeat="(cabinet, GroupData) in Groups">
		<h5>Кабинет №{{cabinet}}</h5>
		<table class="table table-divlike">
		<tr ng-repeat="Group in GroupData">
			<td width="100">
				<a href="groups/edit/{{Group.id}}">Группа №{{Group.id}}</a>
			</td>
			<td width="100">
				<span ng-show="Group.id_branch" ng-bind-html="Group.branch | to_trusted" ng-class="{'mr3' : !$last}"></span>
			</td>
			<td width="210">
				{{Subjects[Group.id_subject]}} {{Group.is_special ? "(спецгруппа)" : ""}}
			</td>
			<td width="100">
				{{Grades[Group.grade]}}
			</td>
			<td width="120">
				{{Group.students.length}} <ng-pluralize count="Group.students.length" when="{
					'one': 'ученик',
					'few': 'ученика',
					'many': 'учеников'
				}"></ng-pluralize>
			</td>
			<td width="200">
				<span ng-show="Group.first_schedule">первое занятие {{Group.first_schedule | date:"dd.MM.yyyy"}}</span>
				<span ng-show="!Group.first_schedule && Group.expected_launch_date" class="quater-black">
					примерно {{dateToStart(Group.expected_launch_date)}}
				</span>
			</td>
			<td width="150">
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

		</tr>
		</table>
	</div>
</div>