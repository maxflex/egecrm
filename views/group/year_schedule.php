<div ng-app="Group" ng-controller="YearCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="top-links">
		<span ng-click="setLessonsYear(year)" class="link-like" ng-class="{'active': year == selected_lesson_year}" ng-repeat="year in lesson_years">{{ yearLabel(year) }}</span>
	</div>

	<div ng-repeat="month in [9, 10, 11, 12, 1, 2, 3, 4, 5, 6, 7]" ng-if="Lessons[selected_lesson_year][month]" class="visits-block">
		<h4>{{ months[month] }} {{ month >= 9 ? selected_lesson_year : selected_lesson_year + 1 }}</h4>
		<table class="table table-hover border-reverse last-item-no-border">
			<tr ng-repeat="Lesson in Lessons[selected_lesson_year][month] | orderBy:'date_time'" class="visits-block__elem" ng-class="{
				'visits-block__elem--planned': Lesson.is_planned || Lesson.canceleld
			}">
				<td style='width: 50px; margin-right: 0'>
					{{ $index + 1 }}
				</td>
				<td width='200'>
					{{ Lesson.lesson_date  | date:"dd.MM.yy" }} в {{ Lesson.lesson_time }}
				</td>
				<td width='150'>
					Группа {{ Lesson.id_group }}
				</td>
				<td width='150'>
					<span style='color: {{ getCabinet(Lesson.cabinet).color }}'>{{ getCabinet(Lesson.cabinet).label }}</span>
				</td>
				<td width='100'>
					<span>{{ Subjects[Lesson.id_subject] }}-{{ Lesson.grade_short }}</span>
				</td>
				<td style='width: 150px; margin-right: 0'>
					{{ Lesson.Teacher.last_name }} {{ Lesson.Teacher.first_name[0] }}. {{ Lesson.Teacher.middle_name[0] }}.
				</td>
				<td>
					<span ng-show="!Lesson.cancelled">
						<span ng-show="Lesson.is_conducted">
							<span ng-show="Lesson.presence == 1">
								<span ng-show="Lesson.late">опоздал на {{ Lesson.late }} мин.</span>
								<span ng-show="!Lesson.late">был</span>
							</span>
							<span ng-show="Lesson.presence == 2">не был</span>
						</span>
						<span ng-show="Lesson.is_planned">
							планируется
						</span>
					</span>
					<span ng-show="Lesson.cancelled">отменено</span>
				</td>
			</tr>
		</table>
	</div>
</div>
