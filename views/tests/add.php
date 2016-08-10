<div class="panel panel-primary form-change-control" ng-app="Tests" ng-controller="AddCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading"><?= $Test->isNewRecord ? 'Новый тест' : 'Редактирование теста №' . $Test->id ?>
		<div class="pull-right">
			<span class="link-reverse pointer" ng-click="addProblem()">добавить задание</span>
			<?php if (!$Test->isNewRecord) :?>
				<span style="margin-left: 10px" class="link-reverse pointer" ng-click="deleteTest()">удалить тест</span>
			<?php endif ?>
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<div class="row" style="margin-bottom: 15px">
			<div class="col-sm-4">
				<input class="form-control" placeholder="название теста" ng-model="Test.name">
			</div>
			<div class="col-sm-3">
				<div class="form-group" style="position: relative">
					<input class="form-control digits-only" ng-model="Test.minutes">
					<span style="left: 42px" class="inside-input" ng-show="Test.minutes">– <ng-pluralize count="Test.minutes" when="{
	                    'one': 'минута',
	                    'few': 'минуты',
	                    'many': 'минут',
	                }"></ng-pluralize> на выполнение</span>
				</div>
			</div>
		</div>
		<div class="row" style="margin-bottom: 15px">
			<div class="col-sm-12">
				<div ng-bind-html="Test.intro | unsafe" ng-click="editIntro()" name="test-intro"></div>
			</div>
		</div>
		<div class="row" ng-repeat="Problem in Test.Problems track by $index" style="margin-bottom: 15px">
			<div class="col-sm-12">
				<div class="task">
					<div class="row" style="margin-bottom: 10px">
						<div class="col-sm-12">
							<h5 style="margin-top: 0">Задание №{{$index + 1}}</h5>
							<div ng-bind-html="Problem.problem | unsafe" name="problem-{{$index}}" ng-click="editProblem(Problem, $index)"></div>
							<div class="small" style="text-align: right" ng-show="editing_problem == Problem">
								<span class="btn-file link-like link-reverse small" ng-click="deleteProblem(Problem, $index)">удалить задание</span>
							</div>

						</div>
					</div>
					<div class="row">
						<div class="col-sm-9">
							<h5>Ответы</h5>
							<table style="width: 100%">
								<tr ng-repeat="answer in Problem.answers track by $index">
									<td width="25" style="vertical-align: top">
										<span ng-click="setCorrect(Problem, $index)" ng-class="{'correct-answer': $index == Problem.correct_answer}" class="select-answer">{{ $index + 1 }}.</span>
									</td>
									<td>
										<div ng-bind-html="answer | unsafe" name="answer-{{$parent.$index}}-{{$index}}" 
											ng-click="editAnswer(Problem, $parent.$index, $index)"></div>
										<div class="small" style="text-align: right" ng-show="editingAnswer($parent.$index, $index)">
											<span class="btn-file link-like link-reverse small" ng-click="setCorrect(Problem, $index)">назначить ответ верным</span>
											<span class="btn-file link-like link-reverse small" ng-click="deleteAnswer(Problem, $index)">удалить вариант ответа</span>
										</div>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<div>
											<a style="margin-left: 7px" ng-click="addAnswer(Problem, $index)" class="link-like small link-reverse">добавить вариант ответа</a>
										</div>
									</td>
								</tr>
							</table>
		<!--
							<ol style="padding-left: 16px">
								<li ng-repeat="answer in Problem.answers track by $index" ng-class="{'correct-answer': $index == Problem.corrent_answer}">
									<div ng-bind-html="answer | unsafe" name="answer-{{$parent.$index}}-{{$index}}" 
										ng-click="editAnswer(Problem, $parent.$index, $index)"></div>
									<div class="small" style="text-align: right" ng-show="editingAnswer($parent.$index, $index)">
										<span class="btn-file link-like link-reverse small" ng-click="setCorrect(Problem, $index)">назначить ответ верным</span>
										<span class="btn-file link-like link-reverse small" ng-click="deleteAnswer(Problem, $index)">удалить вариант ответа</span>
									</div>
								</li>
								<li>
									<a ng-click="addAnswer(Problem, $index)" class="link-like link-reverse">добавить вариант ответа</a>
							    </li>
							</ol>
		-->
						</div>
						<div class="col-sm-3">
							 <div class="form-group">                            
						        <h5>Кол-во баллов за правильный ответ</h5>
						        <input type="text" ng-model="Problem.score" class="form-control digits-only" placeholder="кол-во баллов">
						    </div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-12 center">
				<?php if ($Test->isNewRecord) :?>
					<button class="btn btn-primary" ng-click="addTest()" ng-disabled="adding">добавить</button>
				<?php else :?>
					<button class="btn btn-primary" ng-click="saveTest()" ng-disabled="saving || !form_changed" style="width: 110px">
						<span ng-show="!saving && form_changed">сохранить</span>
						<span ng-show="!saving && !form_changed">сохранено</span>
						<span ng-show="saving">сохранение</span>
					</button>
				<?php endif ?>
			</div>
		</div>
	</div>
</div>
</div>

<style>
table p {
	margin: 0;
}
</style>