<style>
    .loading > div {
        margin: 100px!important;
    }
</style>
<div class="panel panel-primary form-change-control" ng-app="Tests" ng-controller="StudentTestsCtrl" ng-init="<?= $ang_init_data ?>">
    <div class="panel-heading">Тесты
        <div class="pull-right">
            <a href="tests/create">добавить тест</a>
        </div>
    </div>
    <div class="panel-body" style="position: relative">
        <div class="row mb">
            <div class="col-sm-12">
                <div class="top-links">
                    <a class="link-like" href="tests">список тестов</a>
                    <span class="link-like active">список тестов по ученикам</span>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="row flex-list" style="width: 60%;margin-bottom: 15px">
                    <div>
                        <select class="watch-select single-select form-control" ng-model="search.state" ng-change='filter()'>
                            <option value=''  data-subtext="{{ counts.state.all || '' }}">все тесты</option>
                            <option disabled>───────</option>
                            <option value='{{id_state}}'  data-subtext='{{ counts.state[id_state] || "" }}' ng-repeat='(id_state, label) in TestStates'>{{label}}</option>
                        </select>
                    </div>

                    <div>
                        <select class="watch-select single-select form-control" ng-model="search.grade" ng-change='filter()'>
                            <option value=''  data-subtext="{{ counts.grade.all || '' }}">класс</option>
                            <option disabled>───────</option>
                            <option value='{{(id_grade +1)}}' data-subtext='{{ counts.grade[id_grade+1] || "" }}' ng-repeat='(id_grade, label) in Grades | toArray'>{{label}}</option>
                        </select>
                    </div>
                    <div>
                        <select class="watch-select single-select form-control" ng-model="search.subject" ng-change='filter()'>
                            <option value=''  data-subtext="{{ counts.subject.all || '' }}">предмет</option>
                            <option disabled>───────</option>
                            <option value='{{id_subject}}' data-subtext='{{ counts.subject[id_subject] || "" }}' ng-repeat='(id_subject, label) in Subjects'>{{label}}</option>
                        </select>
                    </div>
                </div>
                <div class="loading small"><?= globalPartial('loading', ['model' => 'StudentTests', 'message' => 'нет тестов']) ?></div>
                <div style="position: relative;">
                    <div id="frontend-loading"></div>
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
                                <span ng-show='testDisplay(StudentTest)'>набрано {{ StudentTest.final_score }} из 100 баллов</span>
                            </td>
                            <td>
                                <span ng-show='testDisplay(StudentTest)' class="link-like link-reverse pull-right" ng-click="deleteTest(StudentTest)">сбросить тест</span>
                            </td>
                        </tr>
                    </table>
                    <pagination
                        ng-show="(StudentTests && StudentTests.length) && (counts.state[search.state ? search.state : 'all'] > <?= TestStudent::PER_PAGE ?>)"
                        ng-model="current_page"
                        ng-change="pageChanged()"
                        total-items="counts.state[search.state ? search.state : 'all']"
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
        </div>
    </div>
</div>