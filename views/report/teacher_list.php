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
		<div class="alert alert-danger" role="alert">
			Каждый ученик, обучающийся в ЕГЭ-Центре оплачивает обучение двумя платежами. Первый платеж производится при заключении договора, второй - в январе 2017 года. Отчет преподавателя - один из главных факторов, влияющий на желание родителей продолжать обучение, дающий родителям понимание за что он заплатил и стоит ли ему оплачивать дальнейшее обучение. Пожалуйста, заполняйте отчет каждого ученика тщательно и подробно.
		</div>
        <div class="top-links pull-left">
			<a ng-class="{'active': year == <?= $year ?>}" href='teachers/reports/{{ year }}'
                ng-repeat='year in <?= Years::json() ?>'>{{ year + '-' + (year + 1)  }}</a>
		</div>
		<table class="table table-hover">
			<tr ng-repeat="d in data">
				<td style="width: 20%">
					<a href="teachers/reports/student/{{ d.Student.id }}/{{ d.id_subject }}">{{d.Student.last_name}} {{d.Student.first_name}}</a>
				</td>
				<td style="width: 15%">
					{{ d.lessons_count }} <ng-pluralize count="d.lessons_count" when="{
						'one': 'занятие',
						'few': 'занятия',
						'many': 'занятий'
					}"></ng-pluralize>
				</td>
                <td style="width: 10%">
                    {{ Subjects[d.id_subject]}}
                </td>
                <td style="width: 15%">
                    {{ d.Student.grade_label }}
                </td>
                <td style="width: 15%">
                    <span ng-if='d.group.id'>группа {{ d.group.id }} ({{ d.group.grade_label }})</span>
                </td>
				<td style="width: 10%">
					<span ng-show="d.reports_count">
                        {{ d.reports_count }}
                        <ng-pluralize count="d.reports_count" when="{
							'one': 'отчет',
							'few': 'отчета',
							'many': 'отчетов'
						}"></ng-pluralize>
                    </span>
					<span ng-show="!d.reports_count">отчетов нет</span>
				</td>
				<td style="width: 15%">
					<span class="text-danger" ng-show="d.report_required">требуется отчет</span>
				</td>
			</tr>
		</table>
	</div>
</div>
