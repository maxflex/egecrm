<div class="row" ng-show="current_menu == 2">
    <div class="col-sm-12">
	    <?= globalPartial('loading', ['model' => 'Lessons']) ?>
			<div ng-if="Groups !== undefined" ng-show="Lessons !== undefined">
				<div class="top-links">
			        <span ng-click="setLessonsYear(year)" class="link-like" ng-class="{'active': year == selected_lesson_year}" ng-repeat="year in lesson_years">{{ yearLabel(year) }}</span>
			    </div>

				<div ng-repeat="(id_group, GroupLessons) in Lessons[selected_lesson_year]" class="visits-block">
					<table class="table table-hover border-reverse last-item-no-border">
						<tr ng-repeat="Lesson in GroupLessons | orderBy:'date_time'" class="visits-block__elem" ng-class="{
							'visits-block__elem--planned': Lesson.is_planned || Lesson.canceleld
						}">
							<td style='width: 30px; margin-right: 0'>
								{{ $index + 1}}
							</td>
							<td width='150'>
								Группа {{id_group}}
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
								<span ng-if="Lesson.is_conducted" class="link-like" ng-click="editLessonModal(Lesson)">редактировать</a>
							</td>
						</tr>
					</table>
				</div>
			</div>
    </div>
</div>
