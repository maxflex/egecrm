<style>
	.dropdown-menu > li > a {
		padding: 3px 45px 3px 20px;
	}
	.bootstrap-select.btn-group .dropdown-menu li small {
		right: 10px;
	}
</style>
<div ng-app="Reports" ng-controller="UserListCtrl" ng-init="<?= $ang_init_data ?>" >
	<div class="panel panel-primary">
	<div class="panel-heading">
		Отчёты
		<div class='pull-right'>
			обновлено {{ formatDateTime(reports_updated) }}
			<span class="glyphicon glyphicon-refresh opacity-pointer" ng-click='!helper_updating && updateHelperTable()' ng-class="{
		        'spinning': helper_updating
		    }" style="margin: 0 0 0 5px"></span>
		</div>
	</div>
	<div class="panel-body">
		<?= globalPartial('reports_module') ?>
	</div>
</div>
