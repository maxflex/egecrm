<div class="top-links">
	<span ng-click="setLessonsYear(year)" class="link-like" ng-class="{'active': year == selected_lesson_year}" ng-repeat="year in lesson_years">блоками {{ yearLabel(year) }}</span>
	<span ng-click="setLessonsYear(-1)" class="link-like" ng-class="{'active': selected_lesson_year == -1}">сквозная по месяцам</span>
</div>

<div ng-show="selected_lesson_year != -1">
	<h4>Отчеты</h4>
	<div class="visits-block">
		<table class="table small table-hover border-reverse last-item-no-border" ng-if="Lessons.reports[selected_lesson_year] && Lessons.reports[selected_lesson_year].length">
			<tr ng-repeat="Lesson in Lessons.reports[selected_lesson_year] | orderBy:'date_time'">
				<td style='width: 30px; margin-right: 0'>
					{{ $index + 1 }}
				</td>
				<td width='150'>
					{{ Lesson.date | date:"dd.MM.yy" }}
				</td>
				<td>
					<a href="teachers/reports/view/{{ Lesson.id }}">{{ Lesson.label }}</a>
				</td>
			</tr>
		</table>
		<div ng-if="!Lessons.reports[selected_lesson_year] || !Lessons.reports[selected_lesson_year].length" class="text-gray">
			Отчетов пока нет, они будут появляться в процессе обучения
		</div>
	</div>
</div>

<div ng-show="selected_lesson_year != -1">
	<div ng-if="Lessons.by_year[selected_lesson_year][-1]">
		<h4>Дополнительные занятия</h4>
		<div class="visits-block">
			<table class="table small table-hover border-reverse last-item-no-border">
				<?= partial('lessons_line', ['Lessons' => 'Lessons.by_year[selected_lesson_year][-1]']) ?>
			</table>
		</div>
	</div>

	<h4>Занятия в группах</h4>
	<div ng-repeat="(id_group, GroupLessons) in Lessons.by_year[selected_lesson_year]" ng-show="id_group != -1" class="visits-block">
		<table class="table small table-hover border-reverse last-item-no-border">
			<?= partial('lessons_line', ['Lessons' => 'GroupLessons']) ?>
		</table>
	</div>
</div>

<div ng-show="selected_lesson_year == -1">
	<div ng-repeat="year in lesson_years">
		<div ng-repeat="month in [9, 10, 11, 12, 1, 2, 3, 4, 5, 6, 7]" ng-if="Lessons.by_month[year][month]" class="visits-block">
			<h4>{{ months[month] }} {{ month >= 9 ? year : year + 1 }}</h4>
			<table class="table small table-hover border-reverse last-item-no-border">
				<?= partial('lessons_line', ['Lessons' => 'Lessons.by_month[year][month]', 'is_teacher' => $is_teacher]) ?>
			</table>
		</div>
	</div>
</div>
