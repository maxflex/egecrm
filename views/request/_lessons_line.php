<tr ng-repeat="Lesson in <?= $Lessons ?> | orderBy:'date_time'" class="visits-block__elem" ng-class="{
	'visits-block__elem--planned': Lesson.is_planned || Lesson.canceleld
}">
	<td style='width: 30px; margin-right: 0'>
		<span ng-show="!Lesson.cancelled" ng-class="{
			'link-like': Lesson.is_conducted
		}" ng-click="editLessonModal(Lesson)">{{ getLessonIndex($index, <?= $Lessons ?>) }}</span>
	</td>
	<td width='110'>
		<span ng-show="Lesson.id_group > 0">Группа {{ Lesson.id_group }}</span>
		<span ng-show="Lesson.id_group < 0">доп. занятие</span>
	</td>
	<td width='100'>
		<span style='color: {{ getCabinet(Lesson.cabinet).color }}'>{{ getCabinet(Lesson.cabinet).label }}</span>
	</td>
	<td width='100'>
		<span>{{ Subjects[Lesson.id_subject] }}-{{ Lesson.grade_short }}</span>
	</td>
	<td style='width: 150px; margin-right: 0'>
		{{ Lesson.Teacher.last_name }} {{ Lesson.Teacher.first_name[0] }}. {{ Lesson.Teacher.middle_name[0] }}.
	</td>
	<td width='150'>
		{{ Lesson.lesson_date  | date:"dd.MM.yy" }} в {{ Lesson.lesson_time }}
	</td>
	<td style='width: 160px; margin-right: 0'>
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
	<td style='width: 100px; margin-right: 0' >
		<span ng-show="Lesson.is_conducted">{{ Lesson.price }} руб.</span>
	</td>
	<td>
		{{ Lesson.comment }}
	</td>
</tr>
