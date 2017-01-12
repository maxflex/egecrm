<div ng-app="Group" ng-controller="ScheduleCtrl" ng-init="<?= $ang_init_data ?>" id="calendar-app">
<div class="panel panel-primary">
	<div class="panel-heading">
		Расписание группы №<?= $Group->id ?>

			<span ng-show="Group.past_lesson_count" style="margin-bottom: 20px">
				({{Group.schedule_count.paid}}<span ng-show='Group.schedule_count.free'>+{{Group.schedule_count.free}}</span>
				<ng-pluralize count="Group.schedule_count.paid" when="{'one': 'занятие','few': 'занятия','many': 'занятий'}"></ng-pluralize>, прошло {{Group.past_lesson_count}} <ng-pluralize count="Group.past_lesson_count" when="{
					'one': 'занятие',
					'few': 'занятия',
					'many': 'занятий'
				}"></ng-pluralize>)</span>

		<span class="link-reverse small pointer" onclick="redirect('groups/edit/<?= $Group->id ?>')">вернуться в группу</span>
		<div class="pull-right">
			<span class="link-reverse pointer" ng-click="setParamsFromGroup(Group)" ng-show="Group.Schedule.length">
				установить время занятий, филиал и кабинет из настроек группы
			</span>
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<div class="row calendar">
			<div class="col-sm-5" style="position: relative">
				<!-- 		CALENDAR BLOCKER		 -->
                <?php if (! allowed(Shared\Rights::EDIT_GROUP_SCHEDULE)) :?>
                <div class='div-blocker'></div>
                <?php endif ?>
				<div class="row calendar-row" ng-repeat="month in [9, 10, 11, 12, 1, 2, 3, 4, 5, 6]">
					<div class="col-sm-4 month-name text-primary">
						{{monthName(month)}} {{month == 1 ? Group.year + 1 : ""}}
					</div>
					<div class="col-sm-8">
						<div class="calendar-month" month="{{month}}">
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-7">
				<h3 style="font-weight: bold; margin: 10px 0 25px">{{ countNotCancelled(Group.Schedule) }} <ng-pluralize count="countNotCancelled(Group.Schedule)" when="{
						'one': 'занятие',
						'few': 'занятия',
						'many': 'занятий'
					}"></ng-pluralize></h3>

				<table class="table table-divlike">
					<tr ng-repeat="Schedule in Group.Schedule | orderBy:'date'" style="height: 30px"
                        ng-class="Schedule.title ? 'students-11' : '';"
                        ng-attr-title="{{Schedule.title || undefined}}">
						<td>
							<span class="text-gray" ng-show='Schedule.cancelled'>{{getLine1(Schedule)}}</span>
							<a href='groups/<?= $Group->id ?>/lesson/{{Schedule.date}}' ng-hide='Schedule.cancelled'>{{getLine1(Schedule)}}</a>
						</td>
						<td>
							<div class="lessons-table" ng-show="!inPastLessons(Schedule.date)">
								<input type="text" style="display: none" class="timemask no-border-outline" ng-value="Schedule.time">
                                <?php if (User::fromSession()->allowed(Shared\Rights::EDIT_GROUP_SCHEDULE)) :?>
								    <span ng-click="setTime(Schedule, $event)">
                                <?php else :?>
                                    <span>
                                <?php endif ?>
									{{Schedule.time ? Schedule.time : 'не установлено'}}
								</span>
							</div>
							<div class="lessons-table" ng-show="inPastLessons(Schedule.date)">
								{{ getPastLesson(Schedule.date).lesson_time }}
							</div>
						</td>
						<td>
							<select ng-disabled="inPastLessons(Schedule.date) || !<?= allowed(Shared\Rights::EDIT_GROUP_SCHEDULE, true) ?>" class='branch-cabinet' ng-model='Schedule.cabinet' ng-change='changeCabinet(Schedule)'>
								<option selected value=''>кабинет</option>
								<option disabled>──────────────</option>
							  	<option ng-repeat='cabinet in all_cabinets' value="{{ cabinet.id }}" ng-selected="(inPastLessons(Schedule.date) ? getPastLesson(Schedule.date).cabinet : Schedule.cabinet) == cabinet.id">{{ cabinet.label}}</option>
							</select>
						</td>
						<td>
							<input ng-disabled="!<?= allowed(Shared\Rights::EDIT_GROUP_SCHEDULE, true) ?>" type="checkbox" ng-true-value="1" ng-false-value="0" ng-model="Schedule.is_free" ng-change="changeFree(Schedule)">
							бесплатное занятие
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>
</div>
