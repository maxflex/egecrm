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

	<table class="table table-hover table-with-padding left-align">
		<thead style="font-weight: bold">
			<tr>
				<td>
					<span ng-show="days_mode" ng-click="plusDays()" class="half-black pointer" style="font-weight: normal">+3 дня</span>
				</td>
				<td>
					занятия
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
					не установлено
				</td>
				<td>
					доля пропуска
				</td>
			</tr>
		</thead>
		<tbody>
			<tr ng-repeat-start="stat in sortByDate(stats)" ng-class="{
				'pointer': days_mode, 
				'visits-weekend': isWeekend(stat.date) && days_mode
			}" ng-click="dateLoad(stat.date)">
				<td ng-class="{'text-gray': isFuture(stat.date), 'bold-red': missing[stat.date] > 0}">
					{{formatDate(stat.date)}} <span ng-show='isToday(stat.date)'>(сегодня)</span>
				</td>
				<td>
					{{stat.lesson_count ? stat.lesson_count : ''}}<span ng-show="missing[stat.date] > 0" class="text-danger">{{stat.lesson_count ? '+' : ''}}{{ missing[stat.date] }}</span><span ng-show="stat.planned_lesson_count" class="text-gray">{{ stat.lesson_count ? '+' : ''}}{{ stat.planned_lesson_count }}</span>
				</td>
				<td>
					{{stat.in_time || ''}}
				</td>
				<td>
					{{stat.late_count || ''}}
				</td>
				<td>
					{{stat.abscent_count || ''}}
				</td>
				<td>
					{{stat.unset_count || ''}}
				</td>
				<td>
					{{stat.abscent_percent ? (stat.abscent_percent + '%') : ''}}
				</td>
			</tr>
			<tr id="{{stat.date}}" style="display: none" class="no-hover" ng-repeat-end>
				<td colspan="7">
					<table class="table table-divlike left-align" style="margin: 0; width: 95%">
						<tr ng-repeat="Schedule in Schedules[stat.date]">
							<td width="5%">
								{{ Schedule.was_lesson ? Schedule.Lesson.lesson_time : Schedule.time }}
							</td>
							<td width="7%" ng-init="_cabinet = (Schedule.was_lesson ? Schedule.Lesson.cabinet : Schedule.cabinet)">
								<span style='color: {{ _cabinet.color }}'>{{ _cabinet.label }}</span>
							</td>
							<td width="9%">
								<a href="groups/edit/{{Schedule.id_group}}" target="_blank">Группа {{Schedule.id_group}}</a>
							</td>
							<td width="7%">
								{{Subjects[Schedule.Group.id_subject]}}{{Schedule.Group.grade ? '-' + Schedule.Group.grade : ''}}
							</td>
							<td width="10%">
								<a target="_blank" href="groups/edit/{{Schedule.id_group}}/schedule">расписание</a>
							</td>
							<td width="10%">
								{{Schedule.Group.students.length}} <ng-pluralize count="Schedule.Group.students.length" when="{
									'one': 'ученик',
									'few': 'ученика',
									'many': 'учеников',
								}"></ng-pluralize>
							</td>
							<td width="33%" ng-init="_Teacher = (Schedule.was_lesson ? Schedule.Lesson.Teacher : Schedule.Group.Teacher)">
								<a class="pointer" target="_blank" href="teachers/edit/{{ _Teacher.id }}">
									{{ _Teacher.last_name }} {{ _Teacher.first_name }} {{ _Teacher.middle_name }}
								</a>
								<span class="label label-danger pointer label-transparent" ng-click="PhoneService.call(_Teacher.phone)"
									style="margin-left: 3px">позвонить</span>
							</td>
							<td width="11%">
								<span ng-if='!Schedule.cancelled'>
									{{Schedule.lesson_number}} из {{Schedule.total_lessons}} <ng-pluralize count="Schedule.total_lessons" when="{
										'one': 'урока',
										'few': 'уроков',
										'many': 'уроков',
									}"></ng-pluralize>
								</span>
							</td>
							<td width="2.5%">
								<span class="day-explain cancelled" ng-show="Schedule.cancelled" title="отменено"></span>
								<span class="day-explain was-lesson" ng-show="!Schedule.cancelled && Schedule.was_lesson" title="проведено"></span>
								<span class="day-explain" ng-show="!Schedule.cancelled && !Schedule.was_lesson" title="планируется"></span>
							</td>
							<td width="6.5%">
								<span class="day-explain exam-day-subject" ng-show="Schedule.is_unplanned" title="внеплановое"></span>
								<span class="day-explain exam-day" ng-show="Schedule.lesson_number == 1 && !Schedule.cancelled" title="старт группы"></span>
								<span class="day-explain cancelled" ng-show="Schedule.is_free" title="бесплатное"></span>
								<span class="day-explain vocation"
                                      ng-show="Schedule.cabinetLayered || Schedule.studentLayered"
                                      title="{{ (Schedule.cabinetLayered ? 'Наслоение кабинета:\nКабинет № ' + Schedule.cabinetNumber + '\n': '') +
                                                (Schedule.studentLayered ? 'Наслоение студентов:\n' + Schedule.studentLayered : '') }}" title="наслоение">
								</span>
								<span class="day-explain vocation" ng-show="!Schedule.was_lesson && !Schedule.cancelled && !isFuture(Schedule.date)" title="не зарегистрирован"></span>
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
	<pagination
	  ng-model="currentPage"
	  ng-change="pageStudentChanged()"
	  total-items="<?= round(VisitJournal::fromFirstLesson()) ?>"
	  max-size="10"
	  items-per-page="<?= StatsController::PER_PAGE_STUDENTS ?>"
	  first-text="«"
	  last-text="»"
	  previous-text="«"
	  next-text="»"
	>
	</pagination>
	<?php endif ?>
</div>


<style>
	.no-hover td {
		border-top: none !important;
	}
</style>
