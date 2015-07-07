<div ng-app="Search" ng-controller="ResultsCtrl" ng-init="<?= $ang_init_data ?>">
	<h4>Заявки</h4>
	<?php globalPartial("request_list") ?>
	
	<div ng-show="requests.length <= 0">
		заявок не найдено
	</div>
</div>