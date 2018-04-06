<div class="row" ng-show="current_menu == 4">
    <div class="col-sm-12">
	    <?= globalPartial('loading', ['model' => 'ReportsByYear', 'message' => 'нет отчетов']) ?>

		<div ng-repeat="(year, Reports) in ReportsByYear">
			<h4>{{ yearLabel(year)}}</h4>
			<?= globalPartial('reports') ?>
		</div>
    </div>
</div>
