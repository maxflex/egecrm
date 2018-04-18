<div ng-app="Settings" ng-controller="RecommendedCtrl" ng-init="<?= $ang_init_data ?>">
	<div ng-repeat="year in years">
		<h4>{{ yearLabel(year) }}</h4>
	</div>
</div>
