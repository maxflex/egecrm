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
				<!-- 		CALENDAR BLOCKER		 -->
				<div style="position: absolute; height: 100%; width: 100%; z-index: 20" ng-show="false"></div>
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
			<div class="col-sm-7">
				<h3 style="font-weight: bold; margin: 10px 0 25px">{{Group.Schedule.length}} <ng-pluralize count="Group.Schedule.length" when="{
						'one': 'занятие',
						'few': 'занятия',
						'many': 'занятий'
					}"></ng-pluralize></h3>
					
				<table class="table table-divlike">
					<tr ng-repeat="Schedule in Group.Schedule | orderBy:'date'" style="height: 30px">
						<td>
							<a href='groups/<?= $Group->id ?>/lesson/{{Schedule.date}}'>{{getLine1(Schedule)}}</a>
						</td>
						<td>
							<div class="lessons-table">
								<input type="text" style="display: none" class="timemask no-border-outline" ng-value="Schedule.time">
								<span  <?= (User::isTeacher() || User::isStudent() ? '' : 'ng-click="setTime(Schedule, $event)"') ?>>
									{{Schedule.time ? Schedule.time : 'не установлено'}}
								</span>
							</div>
						</td>
						<td>
                            <!-- branches selector -->
                            <select ng-model="Schedule.id_branch" style="width: 130px" ng-change="changeBranch(Schedule)">
                                <option ng-repeat="Branch in Branches" value="{{Branch.id}}" ng-selected="Branch.id == Schedule.id_branch">
                                    {{Branch.name}}
                                </option>
                            </select>
                            <!-- /branches selector -->

                            <?= partial("_cabinets_list") ?>

							<select ng-model="Schedule.cabinet" style="width: 130px" ng-change="changeCabinet(Schedule)">
								<option selected value="">выберите кабинет</option>
								<option disabled>──────────────</option>
								<option ng-repeat="Cabinet in Cabinets" value="{{Cabinet.id}}" ng-selected="Cabinet.id == Schedule.cabinet">
									{{Cabinet.number}}
								</option>
							</select>
						</td>
						<td>
							<input type="checkbox" ng-true-value="1" ng-false-value="0" ng-model="Schedule.is_free" ng-change="changeFree(Schedule)"> 
							бесплатное занятие
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
</div>