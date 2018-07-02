<div ng-app="TeacherProfile" ng-controller="MainCtrl" ng-init="<?= $ang_init_data ?>">
	<table class="table reverse-borders">
		<tr ng-repeat="Student in Students">
			<td>
				<a href="/teachers/student/{{ Student.id }}">
					{{ Student.last_name }}
					{{ Student.first_name[0] }}.
					{{ Student.middle_name[0] }}.
				</a>
			</td>
		</tr>
	</table>
</div>
