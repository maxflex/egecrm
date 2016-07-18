<div class="row" ng-show="current_menu == 6">
    <div class="col-sm-12">
	    <?= globalPartial('loading', ['model' => 'Tests', 'message' => 'нет тестов']) ?>
	    <table class="table table-hover vertical-align-table">
		    <tr ng-repeat="Test in Tests" ng-init="_StudentTest = getStudentTest(Test.id)">
			    <td width="35" style="padding: 0">
				    <label class="ios7-switch" style="font-size: 20px; margin: 0; top: -1px" 
				    	ng-hide="_StudentTest && _StudentTest.isFinished"
				    	ng-init="Test.checked = (_StudentTest !== undefined)">
		                <input type="checkbox" ng-model="Test.checked" ng-change="signUpForTest(Test)">
		                <span class="switch"></span>
		            </label>
			    </td>
			    <td width="150">
				    <a href="tests/edit/{{Test.id}}" target="_blank">{{Test.name}}</a>
			    </td>
			    <td width="300">
			    	<span ng-show="Test.checked" class="link-like-nocolor text-gray" ng-click="toggleTestStatus(_StudentTest || Test)">{{ getTestStatus(_StudentTest || Test) }}</span>
			    </td>
			    <td>
					<span ng-show="_StudentTest.isFinished">{{ _StudentTest.final_score }}</span>
				</td>
				<td>
					<span ng-show="_StudentTest.inProgress" class="text-gray">в процессе</span>
					<span ng-show="_StudentTest.isFinished" class="text-success">тест пройден</span>
				</td>
		    </tr>
	    </table>
    </div>
</div>