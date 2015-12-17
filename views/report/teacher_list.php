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
		<table class="table table-divlike">
			<tr ng-repeat="Student in Students">
				<td>
					<a href="teachers/reports/add/{{Student.id}}">{{Student.last_name}} {{Student.first_name}}</a>
				</td>
				<td>
					<span ng-show="Student.Reports.length > 0">{{Student.Reports.length}} <ng-pluralize count="Student.Reports.length" when="{
					'one': 'отчет',
					'few': 'отчета',
					'many': 'отчетов'
				}"></ng-pluralize></span>
					<span ng-show="!Student.Reports.length">отчетов нет</span>
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