<div ng-app="Group" ng-controller="ScheduleCtrl" ng-init="<?= $ang_init_data ?>" id="calendar-app">
<div class="panel panel-primary">
	<div class="panel-heading">
		Расписание группы №<?= $Group->id ?>
		<div class="pull-right">
			<span class="link-reverse pointer" ng-click="setTimeFromGroup(Group)" ng-show="Group.Schedule.length && Group.start">установить время занятия из настроек группы</span>
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<div class="row calendar">
			<div class="col-sm-5">
				<div class="row calendar-row" ng-repeat="month in [9, 10, 11, 12, 1, 2, 3, 4, 5, 6]">
					<div class="col-sm-4 month-name text-primary">
						{{monthName(month)}} {{month == 1 ? "2016" : ""}}
					</div>
					<div class="col-sm-8">
						<div class="calendar-month" month="{{month}}">
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-2"></div>
			<div class="col-sm-5">
				<h3 style="font-weight: bold; margin: 10px 0 25px">{{Group.Schedule.length}} <ng-pluralize count="Group.Schedule.length" when="{
						'one': 'занятие',
						'few': 'занятия',
						'many': 'занятий'
					}"></ng-pluralize></h3>
				<div class="row" ng-repeat="Schedule in Group.Schedule" style="height: 30px">
					<div class="col-sm-6">
						{{getLine1(Schedule)}}
					</div>
					<div class="col-sm-6">
						<div class="lessons-table">
							<input type="text" style="display: none" class="timemask no-border-outline" ng-value="Schedule.time">
							<span ng-click="setTime(Schedule, $event)">{{Schedule.time ? Schedule.time : 'не установлено'}}</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		
	</div>
</div>
</div>