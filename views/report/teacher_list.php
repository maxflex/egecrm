<div class="panel panel-primary" ng-app="Reports" ng-controller="TeacherListCtrl" ng-init="<?= $ang_init_data ?>">
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
			Внимание!<br />
			Каждый родитель хочет знать как проходит обучение не только со слов своего ребенка, но и преподавателя. Отчет преподавателя - один из главных факторов, дающий родителям понимание стоит ли продолжать обучение в ЕГЭ-Центре. Пожалуйста, заполняйте отчеты тщательно и развернуто.
		</div>
		<div style='width: 300px'>
			<select class="watch-select single-select form-control" ng-model="year" ng-change='changeYear()'>
				<option ng-repeat="y in <?= Years::json() ?>" value="{{ y }}" ng-selected="y == year">{{ yearLabel(y) }}</option>
			</select>
		</div>
		<div ng-show="data === undefined" style="padding: 100px" class="small half-black center">
			загрузка...
		</div>
		<div ng-show="data === false" style="padding: 100px" class="small half-black center">
			нет отчетов в этом учебном году
		</div>
		<table class="table table-hover" style='margin-top: 30px'>
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
