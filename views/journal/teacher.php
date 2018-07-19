<div ng-app="Group" ng-controller="JournalCtrl" ng-init="<?= $ang_init_data ?>">

    <style>
        .panel-body {
            overflow: scroll;
        }
        #k_u td {
            padding:5px 10px 5px 0;
        }
        .table-journal .border-top {
            border-top: 2px solid #aaa;
        }
    </style>

    <table class="table table-journal" ng-show="Lessons !== undefined">
        <thead>
            <tr>
                <th style="border: none !important"></th>
                <th ng-repeat="Lesson in Lessons" style="height: 70px; position: relative" ng-class="{'gray-bg': grayMonth(Lesson.lesson_date)}">
                    <span>{{formatDate(Lesson.lesson_date)}}</span>
                    <span class='lesson-cancelled-journal ng-hide' ng-show='Lesson.cancelled'>занятие отменено</span>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr ng-repeat="Student in Group.Students">
                <td style="text-align: left; width: 250px">
					<a ng-show="<?= User::id() ?> == Student.id_head_teacher" href="/teachers/student/{{ Student.id }}">{{Student.last_name}} {{Student.first_name}}</a>
					<span ng-show="!<?= User::id() ?> == Student.id_head_teacher">{{Student.last_name}} {{Student.first_name}}</span>
                </td>
                <td ng-repeat="Lesson in Lessons" ng-class="{'gray-bg': grayMonth(Lesson.lesson_date)}">
                        <span class="circle-default"
                              ng-class="{
                                'circle-red'	: getInfo(Student.id, Lesson).presence == 2,
                                'circle-orange'	: getInfo(Student.id, Lesson).presence == 1 && getInfo(Student.id, Lesson).late > 0,
                                'invisible'		: getInfo(Student.id, Lesson) === undefined,
                            }"></span>
                </td>
            </tr>
            <tr>
                <td></td>
            </tr>
            <tr ng-repeat="Teacher in Teachers">
                <td style="text-align: left; width: 250px">
                    {{Teacher.last_name}} {{Teacher.first_name}} {{Teacher.middle_name}}
                </td>
                <td ng-repeat="Lesson in Lessons" ng-class="{'gray-bg': grayMonth(Lesson.lesson_date)}">
                        <span class="circle-default"
                              ng-class="{
                                'circle-red'	: getInfo(Teacher.id, Lesson).presence == 2,
                                'circle-orange'	: getInfo(Teacher.id, Lesson).presence == 1 && getInfo(Teacher.id, Lesson).late > 0,
                                'invisible'		: getInfo(Teacher.id, Lesson) === undefined,
                            }"></span>
                </td>
            </tr>
        </tbody>
    </table>
	<div ng-show="Lessons === undefined" style="padding: 100px" class="small half-black center">
		загрузка журнала...
	</div>
</div>
