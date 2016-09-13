<div class="panel panel-primary" ng-app="Group" ng-controller="LessonCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">
<!-- 		Группа №{{id_group}}, занятие {{formatDate(date)}} -->
		<?= User::isStudent(true) ? "Просмотр" : "Редактирование" ?> занятия
	</div>
	<div class="panel-body">
		<div>
			<div class="row">
				<div class="col-sm-9">
					<table class="table table-hover">
						<thead>
							<td colspan="2"></td>
							<td>
								<span class="quater-black" style="font-weight: normal">опоздание</span>
							</td>
						</thead>
						<tbody>
							<tr ng-repeat="Student in Group.Students">
								<td width="300">{{Student.last_name}} {{Student.first_name}}</td>
								<td width="150">
									<span>{{LessonData[Student.id].presence ? lesson_statuses[LessonData[Student.id].presence] : 'не указано'}}</span>
								</td>
								<td width="150">
									<span ng-show="LessonData[Student.id].late">{{LessonData[Student.id].late}} <ng-pluralize count="LessonData[Student.id].late" when="{
										'one': 'минута',
										'few': 'минуты',
										'many': 'минут',
									}"></ng-pluralize></span>
								</td>
								<td width="300">
									<span>{{LessonData[Student.id].comment}}</span>
								</td>
								<td ng-hide="<?= User::isStudent(true) ?>">
									<span class="link-like" ng-click="editStudent(Student)" ng-show="registered_in_journal !== true || isAdmin">редактировать</span>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="row" ng-hide="<?= User::isStudent(true) ?>">
				<div class="col-sm-12 center">

					<button class="btn btn-success ajax-payment-button"
                            ng-click="changeRegisterInJournal()"
                            ng-show="registered_in_journal == true"
                            ng-disabled="saving || students_not_filled">
						    <span>Сохранить без отправки СМС</span>
					</button>

					<button class="btn btn-primary ajax-payment-button" ng-click="registerInJournal()" ng-show="until_save === true && !(registered_in_journal === true)"
						ng-disabled="registered_in_journal === true || saving || students_not_filled">
						<span ng-show="registered_in_journal !== true">Сохранить</span>
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