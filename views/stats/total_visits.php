<style>
	table tr td:not(:first-child) {
		text-align: center;
	}
/*
	table tr td {
		padding: 200px 0 !important;
	}
*/
	table.left-align tr td {
		text-align: left;
	}
</style>

<div ng-app="Stats" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="top-links">
		<?php if ($_GET["group"] == "d" || empty($_GET["group"])) { ?>
		<span style="margin-right: 15px; font-weight: bold">по дням</span>
		<?php } else { ?>
		<a href="stats/visits/total?group=d" style="margin-right: 15px">по дням</a>
		<?php } ?>
		
		<?php if ($_GET["group"] == "w") { ?>
		<span style="margin-right: 15px; font-weight: bold">по неделям</span>
		<?php } else { ?>
		<a href="stats/visits/total?group=w" style="margin-right: 15px">по неделям</a>
		<?php } ?>
		
		<?php if ($_GET["group"] == "m") { ?>
		<span style="margin-right: 15px; font-weight: bold">по месяцам</span>
		<?php } else { ?>
		<a href="stats/visits/total?group=m" style="margin-right: 15px">по месяцам</a>
		<?php } ?>
		
		<?php if ($_GET["group"] == "y") { ?>
		<span style="margin-right: 15px; font-weight: bold">по годам</span>
		<?php } else { ?>
		<a href="stats/visits/total?group=y" style="margin-right: 15px">по годам</a>
		<?php } ?>
		
		<div class="pull-right">
			<span class="link-like active">по дням</span>
			<a href="stats/visits/students">по ученикам</a>
			<a href="stats/visits/teachers" style="margin-right: 0">по преподавателям</a>
		</div>
		
	</div>
	
	<table class="table table-hover">
		<thead style="font-weight: bold">
			<tr>
				<td>
					<span ng-show="days_mode" ng-click="plusDays()" class="half-black pointer" style="font-weight: normal">+3 дня</span>
				</td>
				<td>
					кол-во занятий
				</td>
				<td>
					пришли вовремя
				</td>
				<td>
					опоздали
				</td>
				<td>
					отсутствовали
				</td>
				<td>
					доля пропуска
				</td>
			</tr>
		</thead>
		<tbody>
			<tr ng-repeat-start="stat in sortByDate(stats)" ng-class="{'pointer': days_mode}" ng-click="dateLoad(stat.date)">
				<td>
					{{formatDate(stat.date)}}
					<span ng-show="errors[stat.date].length > 0" class="badge badge-danger" style="margin-left: 10px">{{errors[stat.date].length}}</span>
				</td>
				<td>
					{{stat.lesson_count ? stat.lesson_count : ''}}
				</td>
				<td>
					{{stat.in_time ? stat.in_time : ''}}
				</td>
				<td>
					{{stat.late_count ? stat.late_count : ''}}
				</td>
				<td>
					{{stat.abscent_count ? stat.abscent_count : ''}}
				</td>
				<td>
					{{stat.abscent_percent ? (stat.abscent_percent + '%') : ''}}
				</td>
			</tr>
			<tr id="{{stat.date}}" style="display: none" class="no-hover" ng-repeat-end>
				<td colspan="6">
					<table class="table table-divlike left-align" style="margin: 0; width: 90%">
						<tr ng-repeat="Schedule in Schedules[stat.date]">
							<td>
								<span ng-class="{
									'text-gray'		: isFutureLesson(Schedule),
									'text-danger'	: isMissingLesson(Schedule),
								}">
									{{Schedule.time}}
								</span></td>
							<td>
								<div ng-bind-html="Schedule.Group.branch | to_trusted"></div>
							</td>
							<td>
								<a ng-class="{
									'gray-link'		: isFutureLesson(Schedule),
									'text-danger'	: isMissingLesson(Schedule),
								}" href="groups/edit/{{Schedule.id_group}}" target="_blank">Группа {{Schedule.id_group}}</a>
							</td>
							<td width="150">
								<span ng-class="{
									'text-gray'		: isFutureLesson(Schedule),
									'text-danger'	: isMissingLesson(Schedule),
								}">
									{{Subjects[Schedule.Group.id_subject]}}{{Schedule.Group.grade ? '-' + Schedule.Group.grade : ''}}
								</span>
							</td>
							<td>
								<a ng-class="{
									'gray-link'		: isFutureLesson(Schedule),
									'text-danger'	: isMissingLesson(Schedule),
								}" target="_blank" href="groups/edit/{{Schedule.id_group}}/schedule">расписание</a>
							</td>
							<td>
								<span ng-class="{
									'text-gray'		: isFutureLesson(Schedule),
									'text-danger'	: isMissingLesson(Schedule),
								}">
									{{Schedule.Group.students.length}} <ng-pluralize count="Schedule.Group.students.length" when="{
										'one': 'ученик',
										'few': 'ученика',
										'many': 'учеников',
									}"></ng-pluralize>
								</span>
							</td>
							<td>
								<a class="pointer" ng-class="{
									'gray-link'		: isFutureLesson(Schedule),
									'text-danger'	: isMissingLesson(Schedule),
								}" target="_blank" ng-click="callSip(Schedule.Group.Teacher.phone)">{{Schedule.Group.Teacher.last_name}} {{Schedule.Group.Teacher.first_name}} {{Schedule.Group.Teacher.middle_name}}</a>
							</td>
							<td>
								<span ng-class="{
									'label-red-visits': Schedule.lesson_number == 1,
								}">
									<span ng-class="{
										'text-gray'		: isFutureLesson(Schedule),
										'text-danger'	: isMissingLesson(Schedule),	
									}">{{Schedule.lesson_number}} урок</span>
								</span>
							</td>
						</tr>
					</table>
					<span class="vertical-margin small" ng-show="Schedules[stat.date] === undefined">загрузка...</span>
					<span class="vertical-margin small" ng-show="Schedules[stat.date] === false">нет групп на эту дату</span>
				</td>
			</tr>
		</tbody>
	</table>
	
	<?php if ($_GET["group"] == "d" || empty($_GET["group"])) :?>
<!--
	<pagination
	  ng-model="currentPage"
	  ng-change="pageStudentChanged()"
	  total-items="<?= round(VisitJournal::fromFirstLesson() / StatsController::PER_PAGE) ?>"
	  max-size="10"
	  items-per-page="<?= StatsController::PER_PAGE ?>"
	  first-text="«"
	  last-text="»"
	  previous-text="«"
	  next-text="»"
	>
	</pagination>
-->
	<?php endif ?>
</div>