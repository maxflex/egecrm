<div class="row" ng-show="current_menu == 6">
    <div class="col-sm-12">
	    <?= globalPartial('loading', ['model' => 'Tests', 'message' => 'нет тестов']) ?>
	    <table class="table table-hover vertical-align-table">
		    <tr ng-repeat="Test in Tests" ng-init="_StudentTest = getStudentTest(Test.id)">
			    <td width="35" style="padding: 0">
				    <label class="ios7-switch" style="font-size: 20px; margin: 0; top: -1px" 
				    	ng-hide="testDisplay(_StudentTest)"
				    	ng-init="Test.checked = (_StudentTest !== undefined)">
		                <input type="checkbox" ng-model="Test.checked" ng-change="signUpForTest(Test)">
		                <span class="switch"></span>
		            </label>
			    </td>
			    <td width="220">
				    {{Test.name}}
			    </td>
			    <td width="120">
			    	<span ng-show="Test.checked" class="link-like-nocolor text-gray" ng-click="toggleTestStatus(_StudentTest || Test)">{{ getTestStatus(_StudentTest || Test) }}</span>
			    </td>
				<td>
					<span ng-show="_StudentTest.inProgress">в процессе, осталось {{ timeLeft(_StudentTest, Test) }}</span>
					<span ng-show="_StudentTest.isFinished">тест пройден {{ formatTestDate(_StudentTest) }}</span>
				</td>
				<td>
					<div ng-show='testDisplay(_StudentTest)'>
						<span ng-repeat="Problem in Test.Problems">
							<span class="circle-default {{ getStudentAnswer(Problem, _StudentTest) }}" title="{{ getTestHint(Problem, _StudentTest) }}"></span>
						</span>
					</div>
				</td>
				<td>
					<span ng-show='testDisplay(_StudentTest)'>набрано {{ getCurrentScore(Test, _StudentTest) }} из {{ Test.max_score }} баллов</span>
				</td>
				<td>
					<span ng-show='testDisplay(_StudentTest)' class="link-like link-reverse pull-right" ng-click="deleteTest(_StudentTest)">сбросить тест</span>
				</td>
		    </tr>
	    </table>
    </div>
</div>