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

    <table class="table table-journal">
        <thead>
            <tr>
                <th style="border: none !important"></th>
                <th ng-repeat="Schedule in Group.Schedule" style="height: 70px; position: relative" ng-class="{'gray-bg': grayMonth(Schedule.date)}">
                    <span>{{formatDate(Schedule.date)}}</span>
                    <span class='lesson-cancelled-journal ng-hide' ng-show='Schedule.cancelled'>занятие отменено</span>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr ng-repeat="Student in Group.Students">
                <td style="text-align: left; width: 250px">
                    {{Student.first_name}} {{Student.last_name}}
                </td>
                <td ng-repeat="Schedule in Group.Schedule" ng-class="{'gray-bg': grayMonth(Schedule.date)}">
                        <span class="circle-default"
                              ng-class="{
                                'circle-red'	: getInfo(Student.id, Schedule).presence == 2,
                                'circle-orange'	: getInfo(Student.id, Schedule).presence == 1 && getInfo(Student.id, Schedule).late > 0,
                                'invisible'		: getInfo(Student.id, Schedule) === undefined,
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
                <td ng-repeat="Schedule in Group.Schedule" ng-class="{'gray-bg': grayMonth(Schedule.date)}">
                        <span class="circle-default"
                              ng-class="{
                                'circle-red'	: getInfo(Teacher.id, Schedule).presence == 2,
                                'circle-orange'	: getInfo(Teacher.id, Schedule).presence == 1 && getInfo(Teacher.id, Schedule).late > 0,
                                'invisible'		: getInfo(Teacher.id, Schedule) === undefined,
                            }"></span>
                </td>
            </tr>
        </tbody>
    </table>
</div>
