<div class="panel panel-primary form-change-control" ng-app="Tests" ng-controller="StartCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">
		<span ng-show='current_problem >= 0 && notFinished()'>Вопрос {{ (current_problem * 1) + 1}} из {{ Test.Problems.length }}</span>
		<span class="ng-hide" ng-show='!notFinished()'>Тест завершен</span>
		<div class="pull-right">
			осталось времени: {{ counter() }}
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<div ng-show="notFinished()">
			<div style="text-align: center">
				<span style="margin: 0 5px 15px 0" ng-repeat="Problem in Test.Problems" class="pointer circle-default {{ server_answers[Problem.id] !== undefined ? 'circle-dark-gray' : 'circle-gray' }}" ng-click="setProblem($index)"></span>
			</div>
			<div class="row" style="margin-bottom: 15px">
				<div class="col-sm-12" style="margin-bottom: 30px">
					<div ng-bind-html="Problem.problem | unsafe"></div>
				</div>
				<div class="col-sm-12">
					<div ng-repeat="answer in Problem.answers track by $index">
						<label class="control control--radio">
							<div ng-bind-html="answer | unsafe"></div>
					      <input type="radio" name="answer-{{Problem.id}}" ng-model="answers[Problem.id]" value="{{ $index }}">
					      <div class="control__indicator"></div>
					    </label>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-sm-12 center">
					<button class="btn btn-default" style="width: 200px"
						ng-click="prevProblem()" ng-show='current_problem > 0'>предыдущее задание</button>
					<button class="btn {{ ((1 * current_problem) + 1) < Test.Problems.length ? 'btn-primary' : 'btn-success' }}" ng-click="nextProblem()" ng-disabled="finishing" style="width: 200px">
						<span ng-show='((1 * current_problem) + 1) < Test.Problems.length'>следующее задание</span>
						<span ng-show='((1 * current_problem) + 1) >= Test.Problems.length'>завершить тест</span>
					</button>
				</div>
			</div>
		</div>
		<div ng-show="!notFinished()" style="height: 300px; display: flex; justify-content: center; align-items: center">
			<div class="center">
				<span class="text-gray">ваш результат</span>
				<h3 style="margin: 5px 0 50px">{{ final_score }}</h3>
				<div>
					<button class="btn btn-primary" ng-click="back()">вернуться</button>
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