<div ng-app="Schedule" ng-controller="MainCtrl" ng-init="<?= $ang_init_data ?>">
<div class="panel panel-primary">
	<div class="panel-heading">
		Расписание группы №<?= $Group->id ?>
		| <?= Subjects::$three_letters[$Group->id_subject] ?>-<?= $Group->grade_short ?>
		<div class="pull-right">
			<a class="like-white" href="teachers/groups/journal/{{Group.id}}">журнал посещаемости</a>
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<div class="row">
			<div class="col-sm-6" style="position: relative">
				<div class="row" style="margin-bottom: 15px">
					<div class="col-sm-12" style="white-space: nowrap">
						<div>
							<span class="day-explain"></span> – дни занятий
						</div>
						<div>
							<span class="day-explain was-lesson"></span> – проведенные занятия
						</div>
						<div>
							<span class="day-explain vocation"></span> – дни, считающиеся нерабочими по производственному календарю
						</div>
						<div>
							<span class="day-explain cancelled"></span> – отмененные занятия
						</div>
						<div>
							<span class="day-explain exam-day"></span> – дни экзаменов <?= $Group->grade_label ?>а
						</div>
						<div>
							<span class="day-explain exam-day-subject"></span> – дни экзаменов по {{SubjectsDative[Group.id_subject]}}
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-12">
                        <?= globalPartial('calendar') ?>
					</div>
				</div>
			</div>
			<div class="col-sm-6">
				<div style="margin-bottom: 15px; font-weight: bold">Текущий состав группы учеников:</div>
				<table width="100%">
					<tr ng-repeat="Student in Group.Students">
						<td width="40%">{{$index + 1}}. {{Student.last_name}} {{Student.first_name}}</td>
						<td style="padding-left: 10px;">
							<span ng-show="Student && Student.Test">
								<span ng-show="Student.Test.notStarted" class="quater-black">к тесту не приступал</span>
								<span ng-show="Student.Test.inProgress" class="quater-black">тест в процессе</span>
								<span ng-show="Student.Test.isFinished">
									<span class="text-gray">{{ getTestStatus(Student.Test) }}</span>
									{{ Student.Test.final_score }} <ng-pluralize count="Student.Test.final_score" when="{
									'one': 'балл',
									'few': 'балла',
									'many': 'баллов'
								}"></ng-pluralize></span>
							</span>
							<span ng-show="!Student.Test" class="text-gray">тест не найден</span>
						</td>
					</tr>
				</table>
				<div style="margin: 15px 0; font-weight: bold">Расписание занятий:</div>
				<table class="table table-divlike">
					<tr ng-repeat="Lesson in Lessons | orderBy:'date_time' track by $index">
						<td style="padding:2px 4px 2px 0px;">
							<span class="day-explain"
								  ng-class="{
									'was-lesson': Lesson.is_conducted,
									'cancelled': Lesson.cancelled
								  }"
							></span>
						</td>
						<td width="30%">
							{{ formatDate(Lesson.lesson_date) }}
						</td>
						<td width="20%">
                            {{ Lesson.lesson_time }}
						</td>
						<td width="15%">
                            {{ getCabinet(Lesson.cabinet).label }}
						</td>
						<td width="35%">
							<span ng-show="Lesson.is_conducted">урок проведен</span>
							<span ng-show="Lesson.cancelled">урок отменен</span>
							<a href='teachers/lesson/{{ Lesson.id }}' ng-show='Lesson.in_progress'>зарегистрировать урок</a>
						</td>
					</tr>
				</table>

				<div style="margin: 15px 0; font-weight: bold">Итого: {{ Group.lesson_count.all }} <ng-pluralize count="Group.lesson_count.all" when="{
						'one': 'занятие',
						'few': 'занятия',
						'many': 'занятий'
					}"></ng-pluralize></div>
			</div>
		</div>

	</div>
</div>
</div>
