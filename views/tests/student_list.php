<div class="panel panel-primary form-change-control" ng-app="Tests" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">Тесты
	</div>
	<div class="panel-body" style="position: relative">
		<table class="table table-hover" style="margin: 0">
			<tr ng-repeat="Test in Tests">
				<td width="150">
					{{Test.name}}
				</td>
				<td width="300">
					{{ getTestStatus(Test) }}
				</td>
				<td>
					<span ng-show="Test.isFinished">{{ Test.final_score }}</span>
				</td>
				<td>
					<a ng-show="!Test.isFinished && !Test.inProgress" href="students/tests/start/{{Test.id_test}}">пройти тест</a>
					<a ng-show="Test.inProgress" href="students/tests/start/{{Test.id_test}}">продолжить выполнение</a>
					<span ng-show="Test.isFinished" class="text-success">тест пройден</span>
				</td>
			</tr>
		</table>
	</div>
</div>
