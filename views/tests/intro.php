<div class="panel panel-primary form-change-control" ng-app="Tests" ng-controller="StartCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">
		{{ Test.name }}
		<div class="pull-right">
			время на выполнение: {{Test.minutes}}:00
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<div style="min-height: 300px; display: flex; justify-content: center; align-items: center">
			<div class="center">
				<div ng-bind-html="Test.intro | unsafe"></div>
				<div style="margin-top: 30px">
					<a href="students/tests/start/{{Test.id}}" class="btn btn-primary">начать тестирование</a>
				</div>
			</div>
		</div>
	</div>
</div>
</div>

<style>
p {
	margin: 0;
}
</style>