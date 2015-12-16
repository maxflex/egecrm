<div class="panel panel-primary" ng-app="Reports" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">
		Отчёты
		<div class="pull-right">
<!--
			<span class="link-white link-reverse link-like">
				отправить отчет родителю по e-mail
			</span>
-->
		</div>
	</div>
	<div class="panel-body">
		<table class="table">
			<tr ng-repeat="Report in Reports" ng-repeat-end class="inner">
				<td>
					<a href="students/reports/{{Report.id}}">Отчёт №{{Report.id}}</a>
				</td>
				<td>
					{{Report.Teacher.last_name}} {{Report.Teacher.first_name}} {{Report.Teacher.middle_name}}
				</td>
			</tr>
		</table>
	</div>
</div>

<style>
	tr.inner td {
		border-top: none !important;
		padding-top: 0 !important;
	//	padding-bottom: 0 !important;
	}
	tr.inner:hover {
		background: none !important;
	}
</style>