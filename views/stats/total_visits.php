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
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
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
					{{stat.lesson_count ? stat.lesson_count : ''}}
					<span ng-show="stat.planned_lesson_count" class="text-gray">{{ stat.lesson_count ? '+' : ''}}{{ stat.planned_lesson_count }}</span>
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
						<tr ng-repeat="Lesson in Lessons[stat.date]">
							<td width="5%">
								{{ Lesson.lesson_time }}
							</td>
							<td width="7%">
								<span style='color: {{ Lesson.cabinet.color }}'>{{ Lesson.cabinet.label }}</span>
							</td>
							<td width="12%">
								<span ng-show="!Lesson.Group.is_unplanned">
									<a href="groups/edit/{{ Lesson.id_group }}" target="_blank">Группа {{ Lesson.id_group }}</a>
									<a target="_blank" href="groups/edit/{{ Lesson.id_group }}/schedule" style='margin-left: 3px'>
										<i class="fa fa-calendar" aria-hidden="true"></i>
									</a>
								</span>
								<a ng-show="Lesson.Group.is_unplanned" href="teachers/edit/{{ Lesson.Teacher.id }}#additional" target="_blank">доп. занятие</a>
							</td>
							<td width="7%">
                                {{ Subjects[Lesson.id_subject] }}-{{ Lesson.grade_short }}
							</td>
							<td width="10%">
								{{Lesson.Group.students.length}} <ng-pluralize count="Lesson.Group.students.length" when="{
									'one': 'ученик',
									'few': 'ученика',
									'many': 'учеников',
								}"></ng-pluralize>
							</td>
							<td width="33%">
								<a class="pointer" target="_blank" href="teachers/edit/{{ Lesson.Teacher.id }}">
									{{ Lesson.Teacher.last_name }} {{ Lesson.Teacher.first_name }} {{ Lesson.Teacher.middle_name }}</a>
								<i class="fa fa-phone-square opacity-pointer text-danger" aria-hidden="true" style="margin-left: 3px; font-size: 16px"
									ng-click="PhoneService.call(Lesson.Teacher.phone)"></i>
							</td>
							<td width="11%">
								<span ng-if='!Lesson.cancelled'>
									{{ Lesson.number }} из {{Lesson.total_lessons}} <ng-pluralize count="Lesson.total_lessons" when="{
										'one': 'урока',
										'few': 'уроков',
										'many': 'уроков',
									}"></ng-pluralize>
								</span>
							</td>
							<td width="2.5%" ng-class="{'blink': Lesson.in_progress}">
								<span class="day-explain cancelled" ng-show="Lesson.cancelled" title="отменено"></span>
								<span class="day-explain was-lesson" ng-show="!Lesson.cancelled && Lesson.is_conducted" title="проведено"></span>
								<span class="day-explain" ng-show="!Lesson.cancelled && Lesson.is_planned" title="планируется"></span>
							</td>
							<td width="6.5%">
								<span class="day-explain exam-day-subject" ng-show="Lesson.is_unplanned && !Lesson.Group.is_unplanned" title="внеплановое"></span>
								<span class="day-explain" style='background-color: #f690e9' ng-show="Lesson.Group.is_unplanned" title="внеплановое"></span>
								<span class="day-explain exam-day" ng-show="Lesson.number == 1 && !Lesson.cancelled && !Lesson.Group.is_unplanned" title="старт группы"></span>
								<span class="day-explain vocation" ng-show="Lesson.not_registered" title="не зарегистрирован"></span>
							</td>
						</tr>
					</table>
					<span class="vertical-margin small" ng-show="Lessons[stat.date] === undefined">загрузка...</span>
					<span class="vertical-margin small" ng-show="Lessons[stat.date] === false">нет групп на эту дату</span>
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
