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
						<td>Занятий всего</td>
						<td>Начислено</td>
						<td>Выплачено</td>
						<td>К выплате</td>
						<td>Планируемый дебет</td>
						<td>Планируется занятий</td>
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
						</td>
						<td class="center">
							<span ng-hide="!d.sum">{{d.sum | number}}</span>
						</td>
						<td class="center">
							<span ng-hide="!d.payment_sum">{{d.payment_sum | number}}</span>
						</td>
						<td class="center">
							<span ng-hide="(d.real_sum - d.payment_sum) == 0">{{ (d.real_sum - d.payment_sum).toFixed(2) | number }}</span>
						</td>
						<td class="center">
							<span ng-hide="!d.planned_debt">{{ d.planned_debt | number }}</span>
						</td>
						<td class="center">
							<span ng-hide="!d.planned_lessons">{{ d.planned_lessons | number }}</span>
						</td>
					</tr>

					<tr>
						<td class="half-black">

						</td>
						<td class="center">
							<b>{{lesson_count}}</b>
						</td>
						<td class="center">
							<b>{{total_sum | number}}</b>
						</td>
						<td class="center">
							<b>{{total_payment_sum | number}}</b>
						</td>
						<td class="center">
							<b>{{ toBePaid() | number}}</b>
						</td>
						<td class="center">
							<b>{{planned_debt_sum | number}}</b>
						</td>
						<td class="center">
							<b>{{planned_lessons_sum | number}}</b>
						</td>
					</tr>
				</tbody>
			</table>

		</div>
	</div>
</div>
