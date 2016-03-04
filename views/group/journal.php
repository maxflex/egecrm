<div ng-app="Group" ng-controller="JournalCtrl" ng-init="<?= $ang_init_data ?>">
	
	<style>
		.panel-body {
			overflow: scroll;
		}
	</style>

	<table class="table table-journal">
		<thead>
			<tr>
				<th style="border: none !important"></th>
				<th ng-repeat="Schedule in Group.Schedule" ng-hide="Schedule.cancelled" style="height: 70px; position: relative" ng-class="{'gray-bg': grayMonth(Schedule.date)}">
					<span>{{formatDate(Schedule.date)}}</span>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr ng-repeat="Student in Group.Students">
				<td style="text-align: left; width: 250px">
					<a style="white-space: nowrap" href="student/{{Student.id}}">{{Student.first_name}} {{Student.last_name}}</a>
				</td>
				<td ng-repeat="Schedule in Group.Schedule" ng-class="{'gray-bg': grayMonth(Schedule.date)}"
					ng-hide="Schedule.cancelled">
					<span class="circle-default"
						ng-class="{
							'circle-red'	: getInfo(Student.id, Schedule.date).presence == 2,
							'circle-orange'	: getInfo(Student.id, Schedule.date).presence == 1 && getInfo(Student.id, Schedule.date).late > 0,
							'invisible'		: getInfo(Student.id, Schedule.date) === undefined,
						}"></span>
				</td>
			</tr>
		</tbody>
	</table>
</div>