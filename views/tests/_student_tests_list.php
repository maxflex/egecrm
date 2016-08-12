<div class="row" ng-show="current_tab == 'students'">
    <div class="col-sm-12">
        <div class="row flex-list" style="margin-bottom: 15px">
            <div style="max-width:250px;">
                <select class="watch-select single-select form-control" ng-model="current_filter" ng-change='loadTests()'>
                    <option value=''  data-subtext="{{ counts.students.all || '' }}">все тесты</option>
                    <option disabled>───────</option>
                    <option value='not_started'  data-subtext="{{ counts.students.not_started || '' }}">не приступали</option>
                    <option value='in_process'  data-subtext="{{ counts.students.in_process || '' }}">в процессе</option>
                    <option value='finished'  data-subtext="{{ counts.students.finished || '' }}">пройденные</option>
                </select>
            </div>
        </div>

        <?= globalPartial('loading', ['model' => 'StudentTests', 'message' => 'нет тестов']) ?>
        <table class="table table-hover vertical-align-table border-reverse small">
            <tr ng-repeat="StudentTest in StudentTests">
                <td>
                    <a href="student/{{StudentTest.id_student}}" target="_blank">{{StudentTest.Student.last_name}} {{StudentTest.Student.first_name}}</a>
                </td>
                <td>
					{{StudentTest.Test.name}}
                </td>
                <td>
					{{ getTestStatus(StudentTest || Test) }}
                </td>
                <td>
                    <span ng-show="StudentTest.isFinished">тест пройден {{ formatTestDate(StudentTest) }}</span>
                    <span ng-show="StudentTest.inProgress">в процессе, осталось {{ timeLeft(StudentTest, StudentTest.Test) }}</span>
                </td>
                <td>
                    <div ng-show='testDisplay(StudentTest)'>
						<span ng-repeat="Problem in StudentTest.Test.Problems">
							<span class="circle-default {{ getStudentAnswerClass(Problem, StudentTest) }}" title="{{ getTestHint(Problem, StudentTest) }}"></span>
						</span>
                    </div>
                </td> 
                <td>
                    <span ng-show='testDisplay(StudentTest)'>набрано {{ getCurrentScore(StudentTest.Test, StudentTest) }} из 100 баллов</span>
                </td>
                <td>
                    <span ng-show='testDisplay(StudentTest)' class="link-like link-reverse pull-right" ng-click="deleteTest(StudentTest)">сбросить тест</span>
                </td>
            </tr>
        </table>
        
        <pagination
        ng-show="(StudentTests && StudentTests.length) && (counts['students'][current_filter || 'all'] > <?= TestStudent::PER_PAGE ?>)"
        ng-show="1"
        ng-model="current_page"
        ng-change="pageChanged()"
        total-items="counts['students'][current_filter || 'all']"
        max-size="10"
        items-per-page="<?= TestStudent::PER_PAGE ?>"
        first-text="«"
        last-text="»"
        previous-text="«"
        next-text="»"
    >
    </pagination>
    
    </div>


</div>