<div class="row" ng-show="current_menu == 4">
    <div class="col-sm-12">
	    <?= globalPartial('loading', ['model' => 'ReportsByYear', 'message' => 'нет отчетов']) ?>

		<div style='margin-bottom: 15px' ng-repeat="(year, Reports) in ReportsByYear">
			<h4 class="row-header default-case">Отчёты {{ yearLabel(year, true) }} учебного года</h4>
			<?= globalPartial('reports') ?>
		</div>
    </div>
</div>
