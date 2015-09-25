<div ng-app="Group" ng-controller="ScheduleCtrl" ng-init="<?= $ang_init_data ?>" id="calendar-app">
<div class="panel panel-primary">
	<div class="panel-heading">
		Расписание группы №<?= $Group->id ?>
		<span ng-hide="<?= (User::isTeacher() || User::isStudent() ? 'true' : 'false') ?>" class="link-reverse small pointer" onclick="redirect('groups/edit/<?= $Group->id ?>')">вернуться в группу</span>
		<div class="pull-right">
			<span class="link-reverse pointer" ng-click="setTimeFromGroup(Group)" ng-show="Group.Schedule.length && Group.start" 
				ng-hide="<?= (User::isTeacher() || User::isStudent() ? 'true' : 'false') ?>">
				установить время занятия из настроек группы
			</span>
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<div class="row calendar">
			<div class="col-sm-5" style="position: relative">
				<div style="position: absolute; height: 100%; width: 100%; z-index: 20" ng-show="Group.open == 0 || <?= (User::isTeacher() || User::isStudent() ? 'true' : 'false') ?>"></div>
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
				<div class="row" ng-repeat="Schedule in Group.Schedule | orderBy:'date'" style="height: 30px">
					<div class="col-sm-6">
						<a href='groups/<?= $Group->id ?>/lesson/{{Schedule.date}}'>{{getLine1(Schedule)}}</a>
					</div>
					<div class="col-sm-6">
						<div class="lessons-table">
							<input type="text" style="display: none" class="timemask no-border-outline" ng-value="Schedule.time">
							<span  <?= (User::isTeacher() || User::isStudent() ? '' : 'ng-click="setTime(Schedule, $event)"') ?>>
								{{Schedule.time ? Schedule.time : 'не установлено'}}
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		
	</div>
</div>
</div>