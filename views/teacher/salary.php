<style>
	table thead tr td {
		text-align: center;
		font-weight: 16px;
		text-transform: uppercase;
	}
</style>
<div ng-app="Teacher" ng-controller="SalaryCtrl"
	ng-init="<?= $ang_init_data ?>">
		
	<div class="row" style="position: relative">
		<div class="col-sm-12">
			<table class="table table-divlike">
				<thead>
					<tr style="height: 35px">
						<td style="text-align: left">Преподаватель</td>
						<td>Занятий всего</td>
						<td>Общая сумма</td>
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
							{{d.count | number}} 
						</td>
						<td class="center">
							{{d.sum | number}} 
						</td>
						<td class="center">
							<span ng-hide="!d.payment_sum">{{d.payment_sum}}</span>
						</td>
						<td class="center">
							<span ng-hide="(d.sum - d.payment_sum) == 0">{{(d.sum - d.payment_sum) | number}}</span>
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
							<b>{{(total_sum - total_payment_sum) | number}}</b>
						</td>
					</tr>
				</tbody>
			</table>

		</div>
	</div>
</div>
