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
		<span style="margin-right: 15px; font-weight: bold">дни</span>
		<?php } else { ?>
		<a href="stats/visits/total?group=d" style="margin-right: 15px">дни</a>
		<?php } ?>

		<?php if ($_GET["group"] == "w") { ?>
		<span style="margin-right: 15px; font-weight: bold">недели</span>
		<?php } else { ?>
		<a href="stats/visits/total?group=w" style="margin-right: 15px">недели</a>
		<?php } ?>

		<?php if ($_GET["group"] == "m") { ?>
		<span style="margin-right: 15px; font-weight: bold">месяцы</span>
		<?php } else { ?>
		<a href="stats/visits/total?group=m" style="margin-right: 15px">месяцы</a>
		<?php } ?>

		<?php if ($_GET["group"] == "y") { ?>
		<span style="margin-right: 15px; font-weight: bold">годы</span>
		<?php } else { ?>
		<a href="stats/visits/total?group=y" style="margin-right: 15px">годы</a>
		<?php } ?>

		<?php if ($_GET["group"] == "wd") { ?>
		<span style="margin-right: 15px; font-weight: bold">дни недели</span>
		<?php } else { ?>
		<a href="stats/visits/total?group=wd" style="margin-right: 15px">дни недели</a>
		<?php } ?>

		<?php if ($_GET["group"] == "s") { ?>
		<span style="margin-right: 15px; font-weight: bold">расписание</span>
		<?php } else { ?>
		<a href="stats/visits/total?group=s" style="margin-right: 15px">расписание</a>
		<?php } ?>

		<div class="top-links pull-right">
			<span class="link-like active">хронологически</span>
			<a href="stats/visits/students">ученики</a>
			<a href="stats/visits/teachers">преподаватели</a>
			<a href="stats/visits/grades">классы</a>
			<a href="stats/visits/subjects" style="margin-right: 0">предметы</a>
		</div>

	</div>

	<table class="table table-hover">
		<thead style="font-weight: bold">
			<tr>
				<td>
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
			<tr ng-repeat-start="stat in stats">
				<td>
					{{stat.title}}
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
									{{Subjects[Schedule.Group.id_subject]}}{{Schedule.Group.grade ? '-' + Schedule.Group.grade_short : ''}}
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
								}" target="_blank" ng-click="PhoneService.call(Schedule.Group.Teacher.phone)">{{Schedule.Group.Teacher.last_name}} {{Schedule.Group.Teacher.first_name}} {{Schedule.Group.Teacher.middle_name}}</a>
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
</div>
