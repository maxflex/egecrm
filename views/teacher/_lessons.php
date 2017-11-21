<div class="row" style="position: relative" ng-show="current_menu == 2">
	<div class="col-sm-12">
		<?= globalPartial('loading', ['model' => 'Lessons', 'message' => 'занятий нет']) ?>

		<div ng-show="Lessons && Lessons.length">
			<span class="link-like" ng-show="getLessons().length != Lessons.length" ng-click="show_all_lessons = true"
				style='-webkit-font-smoothing: antialiased; margin-bottom: 10px; display: inline-block'>
				показать занятия и платежи предыдущих учебных лет
			</span>
			<table class="table table-hover border-reverse" style="position: relative">
				<tr ng-repeat-start="Lesson in getLessons()">
					<td>
						<a href="groups/edit/{{Lesson.id_group}}">Группа №{{Lesson.id_group}}</a>
					</td>
					<td>
						{{Subjects[Lesson.id_subject]}}{{Lesson.grade ? '-' + Lesson.grade_short : ''}}{{Lesson.group_level ? '-' + <?= GroupLevels::json() ?>[Lesson.group_level] : ''}}
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
					<td colspan="4">Всего проведено за {{ academicYear(<?= academicYear() ?>) }} {{ current_year_lessons_count }} занятий на сумму</td>
					<td colspan="2">{{ current_year_to_be_paid | number }} руб.</td>
				</tr>
				<tr class="text-gray no-border">
					<td colspan="4">Всего выплачено</td>
					<td colspan="2">{{ current_year_paid | number }} руб.</td>
				</tr>
				<tr class="text-gray no-border">
					<td colspan="4">Итого к выплате</td>
					<td colspan="2">{{ (current_year_to_be_paid - current_year_paid).toFixed(2) | number}} рублей</td>
				</tr>
			</table>
		</div>
	</div>
</div>
