<div class="row" ng-show="current_menu == 4">
    <div class="col-sm-12">
	    <?= globalPartial('loading', ['model' => 'Reports', 'message' => 'нет отчетов']) ?>
		<?= globalPartial('reports') ?>
    </div>
</div>