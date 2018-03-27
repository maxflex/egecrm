<div class="row" ng-show="current_menu == 2">
    <div class="col-sm-12">
	    <?= globalPartial('loading', ['model' => 'Journal']) ?>
			<div ng-if="Groups !== undefined" ng-show="Journal !== undefined">
				<div ng-repeat="id_group in getStudentGroups()" class="visits-block"
					ng-init='_Groups[id_group] = getGroup(id_group)'>


					<!-- проведенные занятия -->
					<div ng-repeat="Visit in getVisitsByGroup(id_group)" class="visits-block__elem">
						<div style='width: 30px; margin-right: 0'>
							{{ $index + 1}}
						</div>
						<div>
							Группа {{id_group}}
						</div>
						<div>
							<span>{{ Subjects[Visit.id_subject] }}-{{ Visit.grade_short }}</span>
						</div>
						<div style='width: 150px; margin-right: 0'>
							{{ Visit.Teacher.last_name }} {{ Visit.Teacher.first_name[0] }}. {{ Visit.Teacher.middle_name[0] }}.
						</div>
						<div>
							{{ Visit.lesson_date  | date:"dd.MM.yy" }}
						</div>
						<div style='width: 160px; margin-right: 0'>
							<span ng-show="Visit.presence == 1">
								<span ng-show="Visit.late">опоздал на {{ Visit.late }} мин.</span>
								<span ng-show="!Visit.late">был</span>
							</span>
							<span ng-show="Visit.presence == 2">не был</span>
						</div>
						<div style='width: 100px; margin-right: 0'>
							{{ Visit.price }} руб.
						</div>
						<div>
							<span class="link-like" ng-click="editLessonModal(Visit)">редактировать</a>
						</div>
					</div>


					<!-- планируемые занятия -->
					<div ng-repeat="Visit in _Groups[id_group].Schedule" ng-init="_visits_len = getVisitsByGroup(id_group).length"
						class="visits-block__elem visits-block__elem--planned">
						<div style='width: 30px; margin-right: 0'>
							{{ _visits_len + $index + 1}}
						</div>
						<div>
							Группа {{id_group}}
						</div>
						<div>
							<span>{{ Subjects[_Groups[id_group].id_subject] }}-{{ _Groups[id_group].grade_short }}</span>
						</div>
						<div style='width: 150px; margin-right: 0'>
							{{ _Groups[id_group].Teacher.last_name }} {{ _Groups[id_group].Teacher.first_name[0] }}. {{ _Groups[id_group].Teacher.middle_name[0] }}.
						</div>
						<div>
							{{ Visit.date  | date:"dd.MM.yy" }}
						</div>
						<div>
							планируется
						</div>
					</div>


				</div>
			</div>
    </div>
</div>
