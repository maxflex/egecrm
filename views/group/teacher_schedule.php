<div ng-app="Schedule" ng-controller="MainCtrl" ng-init="<?= $ang_init_data ?>">
<div class="panel panel-primary">
	<div class="panel-heading">
		Расписание группы №<?= $Group->id ?>
		| <?= Subjects::$three_letters[$Group->id_subject] ?>-<?= $Group->grade ?>
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
							<span class="day-explain exam-day"></span> – дни экзаменов <?= $Group->grade ?> класса
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
					<tr ng-repeat="Schedule in Group.Schedule | orderBy:'date'">
						<td style="padding:2px 4px 2px 0px;">
							<span class="day-explain"
								  ng-class="{
									'was-lesson': Schedule.was_lesson,
									'cancelled': Schedule.cancelled
								  }"
							></span>
						</td>
						<td width="30%">
							{{ formatDate(Schedule.date) }}
						</td>
						<td width="20%">
                            <div ng-show="!Schedule.was_lesson">
                                {{ Schedule.time }}
							</div>
							<div ng-show="Schedule.was_lesson">
								{{ getPastLesson(Schedule).lesson_time }}
							</div>
						</td>
						<td width="15%">
                            <div ng-show="Schedule.was_lesson">
                                {{ getCabinet(getPastLesson(Schedule).cabinet).label }}
                            </div>
                            <div ng-show="!Schedule.was_lesson">
                                {{ getCabinet(Schedule.cabinet).label }}
                            </div>
						</td>
						<td width="35%">
							<span ng-show="Schedule.was_lesson">урок проведен</span>
							<span ng-show="Schedule.cancelled">урок отменен</span>
							<a href='teachers/lesson/{{ Schedule.id }}' ng-show='!Schedule.was_lesson && !Schedule.cancelled'>зарегистрировать урок</a>
						</td>
					</tr>
				</table>

				<div style="margin: 15px 0; font-weight: bold">Итого: {{ countNotCancelled(Group.Schedule) }} <ng-pluralize count="countNotCancelled(Group.Schedule)" when="{
						'one': 'занятие',
						'few': 'занятия',
						'many': 'занятий'
					}"></ng-pluralize></div>
			</div>
		</div>

	</div>
</div>
</div>
