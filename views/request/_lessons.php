<div class="row" ng-show="current_menu == 2">
    <div class="col-sm-12">
	    <?= globalPartial('loading', ['model' => 'Lessons']) ?>
			<div ng-if="Groups !== undefined" ng-show="Lessons !== undefined">
				<div class="top-links">
			        <span ng-click="setLessonsYear(year)" class="link-like" ng-class="{'active': year == selected_lesson_year}" ng-repeat="year in lesson_years">{{ yearLabel(year) }}</span>
			    </div>

				<div ng-repeat="(id_group, GroupLessons) in Lessons" class="visits-block">
					<div ng-repeat="Lesson in GroupLessons" class="visits-block__elem" ng-class="{
						'visits-block__elem--planned': Lesson.is_planned
					}">
						<div style='width: 30px; margin-right: 0'>
							{{ $index + 1}}
						</div>
						<div>
							Группа {{id_group}}
						</div>
						<div>
							<span>{{ Subjects[Lesson.id_subject] }}-{{ Lesson.grade_short }}</span>
						</div>
						<div style='width: 150px; margin-right: 0'>
							{{ Lesson.Teacher.last_name }} {{ Lesson.Teacher.first_name[0] }}. {{ Lesson.Teacher.middle_name[0] }}.
						</div>
						<div>
							{{ Lesson.lesson_date  | date:"dd.MM.yy" }}
						</div>
						<div style='width: 160px; margin-right: 0'>
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
						</div>
						<div style='width: 100px; margin-right: 0' ng-show="Lesson.is_conducted">
							{{ Lesson.price }} руб.
						</div>
						<div ng-if="Lesson.is_conducted">
							<span class="link-like" ng-click="editLessonModal(Lesson)">редактировать</a>
						</div>
					</div>
				</div>
			</div>
    </div>
</div>
