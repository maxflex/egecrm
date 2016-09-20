<div ng-app="Group" ng-controller="ScheduleCtrl" ng-init="<?= $ang_init_data ?>" id="calendar-app">
<div class="panel panel-primary">
	<div class="panel-heading">
		Расписание группы №<?= $Group->id ?>
		(ЕГЭ-Центр-<?= Branches::$all[$Group->id_branch] ?>, <?= Subjects::$all[$Group->id_subject] ?>, <?= $Group->grade ?> класс)
		<div class="pull-right">
			<a class="like-white" href="teachers/groups/journal/{{Group.id}}">журнал посещаемости</a>
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<div class="row calendar">
			<div class="col-sm-5" style="position: relative">
				<div class="row" style="margin-bottom: 15px">
					<div class="col-sm-12" style="white-space: nowrap">
						<div>
							<span class="day-explain"></span> – дни занятий
						</div>
						<div>
							<span class="day-explain was-lesson"></span> – проведенные занятия
						</div>
						<div>
							<span class="day-explain vocation"></span> – дни, считающиеся нерабочими по производственному календарю
						</div>
						<div>
							<span class="day-explain cancelled"></span> – отмененные занятия
						</div>
						<div>
							<span class="day-explain exam-day"></span> – дни экзаменов <?= $Group->grade ?> класса
						</div>
						<div>
							<span class="day-explain exam-day-subject"></span> – дни экзаменов по {{SubjectsDative[Group.id_subject]}}
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
						<div style="position: absolute; height: 100%; width: 100%; z-index: 20"></div>
						<div class="row calendar-row" ng-repeat="month in [9, 10, 11, 12, 1, 2, 3, 4, 5, 6]">
							<div class="col-sm-4 month-name text-primary">
								{{monthName(month)}} {{month == 1 ? <?= $Group->grade + 1; ?> : ""}}
							</div>
							<div class="col-sm-8">
								<div class="calendar-month" month="{{month}}">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-1"></div>
			<div class="col-sm-6">
				<div style="margin-bottom: 15px; font-weight: bold">Текущий состав группы учеников:</div>
				<div ng-repeat="Student in Group.Students">
					{{$index + 1}}. {{Student.last_name}} {{Student.first_name}}
				</div>

				<div style="margin: 15px 0; font-weight: bold">Расписание занятий:</div>

<!--
				<h3 style="font-weight: bold; margin: 10px 0 25px">{{Group.Schedule.length}} <ng-pluralize count="Group.Schedule.length" when="{
						'one': 'занятие',
						'few': 'занятия',
						'many': 'занятий'
					}"></ng-pluralize></h3>
-->
				<table class="table table-divlike">
					<tr ng-repeat="Schedule in Group.Schedule | orderBy:'date'">
						<td>
							{{getLine1(Schedule)}}
						</td>
						<td>
							<div class="lessons-table">
								<input type="text" style="display: none" class="timemask no-border-outline" ng-value="Schedule.time">
								<span>{{Schedule.time ? Schedule.time : 'не установлено'}}</span>
							</div>
						</td>
						<td>
                            <!-- @have-to-refactor -->
							кабинет {{Schedule.Cabinet.number}}
						</td>
						<td>
							<!-- @time-refactored   -->
							<span ng-show="inPastLessons(Schedule.date)">занятие проведено</span>
							<span ng-show="Schedule.cancelled">занятие отменено</span>
							<a href='teachers/groups/<?= $Group->id ?>/lesson/{{Schedule.date}}' ng-show='!inPastLessons(Schedule.date) && lessonStarted(Schedule) && !Schedule.cancelled'
								ng-class="{'add-to-journal': !inPastLessons(Schedule.date) && !Schedule.cancelled}">
								создать запись в журнале
							</a>
						</td>
					</tr>
				</table>

				<div style="margin: 15px 0; font-weight: bold">Итого: {{ countNotCancelled(Group.Schedule) }} <ng-pluralize count="countNotCancelled(Group.Schedule)" when="{
						'one': 'занятие',
						'few': 'занятия',
						'many': 'занятий'
					}"></ng-pluralize></div>
			</div>
		</div>

	</div>
</div>
</div>

<script>
	$(document).ready(function() {
		// ссылка "создать запись в журнале" всегда стоит на следующем занятии по отношению к последнему проведенному... ну или на первом.
		$(".add-to-journal").first().show();
	})
</script>
