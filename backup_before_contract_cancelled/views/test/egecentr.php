<style>
	body {
		overflow: visible !important
	}
	.panel-primary {
		display: inline-block;
	}
</style>
<div ng-app="Test" ng-controller="Egecentr" ng-init="<?= $ang_init_data ?>">
	<table class="table">
		<tbody>
			<tr>
				<td></td>
				<td ng-repeat="date in dates">
					{{formatDate(date)}}
				</td>
			</tr>
			<tr ng-repeat="(student_id_subject_id, data) in data_2014">
				<td>
					{{student_id_subject_id}}
				</td>
				<td ng-repeat="date in dates" class="center">
					{{data[date]}}
				</td>
			</tr>
		</tbody>
	</table>
</div>