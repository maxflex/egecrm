<style>
	table thead tr td {
		text-align: center;
/* 		font-weight: 16px; */
		text-transform: uppercase;
		font-weight: bold;
	}
</style>
<div ng-app="Teacher" ng-controller="SalaryCtrl"
	ng-init="<?= $ang_init_data ?>">

	<div class="row" style="position: relative">
		<div class="col-sm-12">
			<div class="top-links">
				<a class="link-like"
					ng-repeat="year in <?= Years::json() ?>"
					ng-href="{{ year != active_year ? 'teachers/salary/' + year : '' }}"
					ng-class="{ 'active': year == active_year }"
				>{{ year + '-' + (year + 1) }}</a>
			</div>

			<table class="table table-hover">
				<thead>
					<tr style="height: 35px">
						<td style="text-align: left">Преподаватель</td>
						<td>Занятий </td>
						<td>Услуг</td>
						<td>Начислено за занятия</td>
						<td>Начислено за услуги</td>
						<td>Выплачено</td>
						<td>К выплате</td>
					</tr>
				</thead>
				<tbody>
					<tr ng-repeat="d in Data">
						<td width="300">
							<a href="teachers/edit/{{d.Teacher.id}}">
								<span ng-show="d.Teacher.last_name || d.Teacher.first_name || d.Teacher.middle_name">
									{{d.Teacher.last_name}} {{d.Teacher.first_name}} {{d.Teacher.middle_name}}
								</span>
								<span ng-hide="d.Teacher.last_name || d.Teacher.first_name || d.Teacher.middle_name">
									Неизвестно
								</span>
							</a>
						</td>
						<td class="center">
							<span ng-show='d.count'>{{d.count | number}}</span>
							<span ng-show="d.planned_lessons" class="quater-black"> + {{ d.planned_lessons | number }}</span>
						</td>
						<td class="center">
							<span ng-show='d.service_count'>{{d.service_count | number}}</span>
						</td>
						<td class="center">
							<span ng-hide="!d.sum">{{d.sum | number}}</span>
							<span ng-show="d.planned_debt" class="quater-black"> + {{ d.planned_debt | number }}</span>
						</td>
						<td class="center">
							<span ng-show='d.service_sum'>{{d.service_sum | number}}</span>
						</td>
						<td class="center">
							<span ng-hide="!d.payment_sum">{{d.payment_sum | number}}</span>
						</td>
						<td class="center">
							<span ng-hide="(d.real_sum - d.payment_sum) == 0">{{ (d.real_sum - d.payment_sum).toFixed(2) | number }}</span>
						</td>
					</tr>

					<tr>
						<td class="half-black">

						</td>
						<td class="center">
							<b>
								{{lesson_count}}
								<span ng-show="planned_lessons_sum" class="quater-black"> + {{ planned_lessons_sum | number }}</span>
							</b>
						</td>
						<td class="center">
							<b>{{ total_service_count | number}}</b>
						</td>
						<td class="center">
							<b>
								{{total_sum | number}}
								<span ng-show="planned_debt_sum" class="quater-black"> + {{ planned_debt_sum | number }}</span>
							</b>
						</td>
						<td class="center">
							<b>{{ total_service_sum | number}}</b>
						</td>
						<td class="center">
							<b>{{total_payment_sum | number}}</b>
						</td>
						<td class="center">
							<b>{{ toBePaid() | number}}</b>
						</td>
					</tr>
				</tbody>
			</table>

		</div>
	</div>
</div>
