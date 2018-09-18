<div ng-app="Group" ng-controller="YearCtrl" ng-init="<?= $ang_init_data ?>" style='position: relative; overflow: hidden'>
	<div class="div-loading" ng-hide='Lessons !== undefined'>
		<span>загрузка...</span>
	</div>
	<?= globalPartial('lessons_and_reports') ?>
</div>
