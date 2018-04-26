<tr ng-repeat="Lesson in <?= $Lessons ?> | orderBy:'date_time'" class="visits-block__elem" ng-class="{
	'visits-block__elem--planned': Lesson.is_planned || Lesson.canceleld
}">
	<td style='width: 30px; margin-right: 0' ng-if="!Lesson.is_report">
		<span ng-show="!Lesson.cancelled">{{ getLessonIndex($index, <?= $Lessons ?>) }}</span>
	</td>
	<td width='150' ng-if="!Lesson.is_report">
		{{ Lesson.lesson_date  | date:"dd.MM.yy" }} в {{ Lesson.lesson_time }}
	</td>
	<td width='110' ng-if="!Lesson.is_report">
		<span ng-show="Lesson.id_group > 0">Группа {{Lesson.id_group}}</span>
		<span ng-show="Lesson.id_group < 0">доп. занятие</span>
	</td>
	<td ng-if="!Lesson.is_report" width='110'>
		<span style='color: {{ getCabinet(Lesson.cabinet).color }}'>{{ getCabinet(Lesson.cabinet).label }}</span>
	</td>
	<td ng-if="!Lesson.is_report" width='100'>
		<span>{{ Subjects[Lesson.id_subject] }}-{{ Lesson.grade_short }}</span>
	</td>
	<td ng-if="!Lesson.is_report" style='width: 150px; margin-right: 0'>
		{{ Lesson.Teacher.last_name }} {{ Lesson.Teacher.first_name[0] }}. {{ Lesson.Teacher.middle_name[0] }}.
	</td>
	<td ng-if="!Lesson.is_report" width='130'>
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
	<td ng-if="!Lesson.is_report">
		{{ Lesson.comment }}
	</td>


	<td ng-if="Lesson.is_report" class="td-light-green">
	</td>
	<td ng-if="Lesson.is_report" class="td-light-green">
		{{ Lesson.date | date:"dd.MM.yy" }}
	</td>
	<td ng-if="Lesson.is_report" colspan="6" class="td-light-green">
		<a href="students/reports/view/{{ Lesson.id }}">{{ Lesson.label }}</a>
	</td>
</tr>
