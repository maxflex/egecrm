<div ng-app="Payments" ng-controller="LkTeacherCtrl" ng-init="<?= $ang_init_data ?>" style="min-height: 500px">
	<div class="row" ng-show="password_correct === false">
		<div class="col-sm-12">
			<h4 class="text-danger center" style="margin: 200px 0">доступ ограничен</h4>
		</div>
	</div>
	<div ng-show="password_correct === true">
		<div class="row" style="position: relative">
			<div class="col-sm-12" ng-show="!loaded">
				<div class="center half-black small" style="margin: 200px 0">
					загрузка...
				</div>
			</div>
			<div class="col-sm-12" ng-show="loaded">
				<h4 class="row-header">
					<span ng-show="Lessons.length">ПРОВЕДЕННЫЕ ЗАНЯТИЯ</span>
					<span ng-show="!Lessons.length">НЕТ ПРОВЕДЕННЫХ ЗАНЯТИЙ</span>
				</h4>
				<table class="table table-hover border-reverse" style="position: relative" ng-show="Lessons.length">
					<tr ng-repeat-start="Lesson in Lessons">
						<td width="10%">
							<a href="groups/edit/{{Lesson.id_group}}">Группа №{{Lesson.id_group}}</a>
						</td>
						<td width="10%">
							{{Subjects[Lesson.id_subject]}}{{Lesson.grade ? '-' + Lesson.grade : ''}}{{Lesson.group_level ? '-' + <?= GroupLevels::json() ?>[Lesson.group_level] : ''}}
						</td>
						<td width="20%">
							{{formatDate(Lesson.lesson_date, true)}} в {{formatTime(Lesson.lesson_time)}}
						</td>
						<td width="10%">
							{{ Lesson.cabinet.label }}
						</td>
						<td>
							{{Lesson.teacher_price | number}} руб.
						</td>
					</tr>
					<tr ng-repeat-end ng-if="Lesson.payments" ng-repeat="payment in Lesson.payments" class="text-gray">
						<td colspan="2">{{ payment_types[payment.id_type] }}</td>
						<td colspan="2">от {{ dateFromCustomFormat(payment.date) }}</td>
						<td>{{ payment.sum + ' руб. (' + payment_statuses[payment.id_status] + ')' }}</td>
					</tr>
					<tr class="text-gray no-border">
						<td colspan="4">Всего проведено {{ Lessons.length }} занятий на сумму</td>
						<td colspan="1">{{ lessonsTotalSum() }} руб.</td>
					</tr>
					<tr class="text-gray no-border">
						<td colspan="4">Всего выплачено</td>
						<td colspan="1">{{ lessonsTotalPaid(true) }} руб.</td>
					</tr>
					<tr class="text-gray no-border">
						<td colspan="4">Итого к выплате</td>
						<td colspan="1">{{toBePaid(true) | number}} рублей</td>
					</tr>
				</table>
			</div>
		</div>
	</div>

</div>