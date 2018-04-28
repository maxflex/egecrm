<div ng-app="TeacherProfile" ng-controller="MainCtrl" ng-init="<?= $ang_init_data ?>">
	<table class="table reverse-borders">
		<tr ng-repeat="Teacher in Teachers">
			<td>
				<a href="/teachers/edit/{{ Teacher.id }}">
					{{ Teacher.last_name }}
					{{ Teacher.first_name[0] }}.
					{{ Teacher.middle_name[0] }}.
				</a>
			</td>
		</tr>
	</table>
</div>
