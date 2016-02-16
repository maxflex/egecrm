<div class="panel panel-primary" ng-app="Reports" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">
		Отчёты
		<div class="pull-right">
<!--
			<span class="link-white link-reverse link-like">
				отправить отчет родителю по e-mail
			</span>
-->
		</div>
	</div>
	<div class="panel-body">
		<div class="alert alert-info" role="alert">
			Каждый ученик, заключающий договор с ЕГЭ-Центром оплачивает обучение двумя платежами. Первый платеж производится при заключении договора, второй - в январе 2016 года. Отчет преподавателя - один из главных факторов, влияющих на долю родителей, желающих продолжать обучение, дающий родителям понимание за что он заплатил и стоит ли ему платить дальше. Пожалуйста, заполняйте отчет каждого ученика внимательно и подробно.
		</div>


		<!-- <span ng-repeat="Group in Groups">
			<span ng-repeat="grade in [9, 10, 11]" ng-show="getByGrade(grade, Group.id).length">
				<h4>{{grade}} класс, группа {{Group.id}}
					(занятия по <span ng-repeat="(day, day_data) in Group.day_and_time">
						{{weekdays[day - 1].short}}
						<span ng-repeat="dd in day_data">
							в {{dd}}{{$last ? "" : " и "}}</span></span>)
				</h4> -->
				<table class="table table-divlike">
					<tr ng-repeat="Student in Students">
						<td style="width: 20%">
							<a href="teachers/reports/add/{{Student.id}}">{{Student.last_name}} {{Student.first_name}}</a>
						</td>
						<td style="width: 50%">
							<span ng-repeat="(id_subject, count) in Student.visit_count">
							{{count}} <ng-pluralize count="count" when="{
							'one': 'занятие',
							'few': 'занятия',
							'many': 'занятий'
						}"></ng-pluralize> по {{Subjects[id_subject]}}{{$last ? '' : ' + '}}
							</span>
						</td>
						<td style="width: 15%">
							<span ng-show="Student.Reports.length > 0">{{Student.Reports.length}} <ng-pluralize count="Student.Reports.length" when="{
							'one': 'отчет',
							'few': 'отчета',
							'many': 'отчетов'
						}"></ng-pluralize></span>
							<span ng-show="!Student.Reports.length">отчетов нет</span>
						</td>
						<td style="width: 15%">
							<span class="label label-danger-red" ng-show="Student.ReportRequired">требуется создание отчета</span>
						</td>
					</tr>
				</table>
			<!-- </span>
		</span> -->


<!-- БЕЗ ГРУПП -->

		<!-- <span ng-repeat="grade in [9, 10, 11]" ng-show="getByGrade(grade, false).length">
			<h4>{{grade}} класс</h4>
			<table class="table table-divlike">
				<tr ng-repeat="Student in getByGrade(grade, false)">
					<td style="width: 20%">
						<a href="teachers/reports/add/{{Student.id}}">{{Student.last_name}} {{Student.first_name}}</a>
					</td>
					<td style="width: 50%">
						<span ng-repeat="(id_subject, count) in Student.visit_count">
						{{count}} <ng-pluralize count="count" when="{
						'one': 'занятие',
						'few': 'занятия',
						'many': 'занятий'
					}"></ng-pluralize> по {{Subjects[id_subject]}}{{$last ? '' : ' + '}}
						</span>
					</td>
					<td style="width: 15%">
						<span ng-show="Student.Reports.length > 0">{{Student.Reports.length}} <ng-pluralize count="Student.Reports.length" when="{
						'one': 'отчет',
						'few': 'отчета',
						'many': 'отчетов'
					}"></ng-pluralize></span>
						<span ng-show="!Student.Reports.length">отчетов нет</span>
					</td>
					<td style="width: 15%">
						<span class="label label-danger-red" ng-show="Student.ReportRequired">требуется создание отчета</span>
					</td>
				</tr>
			</table>
		</span> -->


	</div>
</div>

<style>
	tr.inner td {
		border-top: none !important;
		padding-top: 0 !important;
	//	padding-bottom: 0 !important;
	}
	tr.inner:hover {
		background: none !important;
	}
</style>
