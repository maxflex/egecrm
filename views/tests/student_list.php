<div class="panel panel-primary form-change-control" ng-app="Tests" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">Тесты
	</div>
	<div class="panel-body" style="position: relative">
		<table class="table table-hover" style="margin: 0">
			<tr ng-repeat="Test in Tests">
				<td width="220">
					{{Test.Test.name}}
				</td>
				<td width="150">
					{{ getTestStatus(Test) }}
				</td>
				<td>
					<span ng-show="Test.inProgress">в процессе, осталось {{ timeLeft(Test, Test.Test) }}</span>
					<span ng-show="Test.isFinished">тест пройден {{ formatTestDate(Test) }}</span>
				</td>
				<td>
					<div ng-show='testDisplay(Test)'>
						<span ng-repeat="Problem in Test.Test.Problems">
							<span class="circle-default {{ getStudentAnswer(Problem, Test) !== undefined ? 'circle-dark-gray' : 'circle-gray' }}" title="{{ getTestHint(Problem, Test) }}"></span>
						</span>
					</div>
				</td>
				<td>
					<span ng-show='testDisplay(Test)'>набрано {{ getCurrentScore(Test.Test, Test) }} из {{ Test.Test.max_score }} баллов</span>
				</td>
				<td>
					<a class="pull-right" ng-show="!Test.isFinished && !Test.inProgress" href="students/tests/intro/{{Test.id_test}}">пройти тест</a>
					<a class="pull-right" ng-show="Test.inProgress" href="students/tests/start/{{Test.id_test}}">продолжить выполнение</a>
				</td>
			</tr>
		</table>
	</div>
</div>
