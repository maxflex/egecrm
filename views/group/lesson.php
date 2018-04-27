<div class="panel panel-primary" ng-app="Group" ng-controller="LessonCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">
 		Просмотр занятия {{ Lesson.is_conducted ? 'true' : 'false' }}
	</div>
	<div class="panel-body">
		<div>
			<div class="row">
				<div class="col-sm-9">
					<table class="table table-hover">
						<tbody>
							<tr ng-repeat="Student in Students">
								<td width="300">
									<div class="visit-div-circle" style='margin: 0 5px 0 0'>
										<span class="circle-default"></span>
									</div>
									{{Student.last_name}} {{Student.first_name}}</td>
								<td width="250">
									{{ getPresenceStatus(LessonData[Student.id]) }}
								</td>
								<td width="400">
									{{LessonData[Student.id].comment}}
								</td>
								<td>
									<span class="link-like" ng-click="editStudent(Student)">редактировать</span>
								</td>
							</tr>
							<tr ng-repeat="Student in left_students">
								<td width="300" class="text-gray">{{Student.last_name}} {{Student.first_name}}</td>
								<td colspan='3' class="text-gray" >
									{{ Student.status ? 'перешел в другую группу' : 'прекратил обучение по предмету'}}
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12 text-danger" style='margin-bottom: 20px'>
					{{ Teacher.first_name }} {{ Teacher.middle_name }}, если на занятии присутствуют ученики, не обозначенные
					<div class="visit-div-circle" style='padding: 0 !important; float: none'>
						<span class="circle-default"></span>
					</div>, просьба сообщить об этом в администрацию
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12 center">
					<button class="btn btn-primary ajax-payment-button" ng-click="registerInJournal()" ng-show="until_save === true && Lesson.is_planned"
						ng-disabled="Lesson.is_conducted || saving || students_not_filled">
						<span ng-show="Lesson.is_planned">Записать в журнал</span>
					</button>
					<span ng-show="until_save !== true">
						<button disabled class="btn btn-default">
						регистрация будет доступна через <b>{{until_save.minutes <= 9 ? "0" + until_save.minutes : until_save.minutes}}:{{until_save.seconds <= 9 ? "0" + until_save.seconds : until_save.seconds}}</b>
						</button>
					</span>
				</div>
			</div>

			<?= partial("lesson_edit_student") ?>

		</div>
	</div>
</div>
