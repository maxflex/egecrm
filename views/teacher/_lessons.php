<div class="row" style="position: relative" ng-show="current_menu == 2">
	<div class="col-sm-12">
		<?= globalPartial('loading', ['model' => 'Lessons', 'message' => 'занятий нет']) ?>
		<table class="table table-hover border-reverse" style="position: relative" ng-show="Lessons.length">
			<tr ng-repeat-start="Lesson in Lessons">
				<td>
					<a href="groups/edit/{{Lesson.id_group}}">Группа №{{Lesson.id_group}}</a>
				</td>
				<td>
					{{Subjects[Lesson.id_subject]}}{{Lesson.grade ? '-' + Lesson.grade : ''}}{{Lesson.group_level ? '-' + <?= GroupLevels::json() ?>[Lesson.group_level] : ''}}
				</td>
				<td>
					{{formatDateMonthName(Lesson.lesson_date, true)}} в {{formatTime(Lesson.lesson_time)}}
				</td>
				<td>
					{{ Lesson.cabinet.label }}
				</td>
				<td>
					{{Lesson.teacher_price | number}} руб.
				</td>
				<td>
					{{ Lesson.login_user_saved }} {{formatDate(Lesson.date) | date:'dd.MM.yy в HH:mm'}}
				</td>
			</tr>
			<tr ng-repeat-end ng-if="Lesson.payments" ng-repeat="payment in Lesson.payments" class="text-gray">
				<td colspan="2">{{ payment_types[payment.id_type] }}</td>
				<td colspan="2">от {{ dateFromCustomFormat(payment.date) }}</td>
				<td>{{ payment.sum + ' руб. (' + payment_statuses[payment.id_status] + ')' }}</td>
				<td>{{ payment.user_login}} {{formatDate(payment.first_save_date) | date:'dd.MM.yyyy в HH:mm'}}</td>
			</tr>
			<tr class="text-gray no-border">
				<td colspan="4">Всего проведено {{ Lessons.length }} занятий на сумму</td>
				<td colspan="2">{{ lessonsTotalSum() }} руб.</td>
			</tr>
			<tr class="text-gray no-border">
				<td colspan="4">Всего выплачено</td>
				<td colspan="2">{{ lessonsTotalPaid(true) }} руб.</td>
			</tr>
			<tr class="text-gray no-border">
				<td colspan="4">Итого к выплате</td>
				<td colspan="2">{{toBePaid(true) | number}} рублей</td>
			</tr>
		</table>

	</div>
</div>