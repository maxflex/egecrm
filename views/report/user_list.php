<div ng-app="Reports" ng-controller="UserListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="top-links">
	    <span class="link-like" ng-click="list = 1" ng-class="{'active': list == 1}">не отправленные</span>
	    <span class="link-like" ng-click="list = 2" ng-class="{'active': list == 2}">отправленные</span>
    </div>
	<table class="table table-divlike">
		<tr ng-repeat="Report in SelectedReports">
			<td>
				<a href="reports/edit/{{Report.id}}">Отчёт №{{Report.id}}</a>
			</td>
			<td>
				<a href="teachers/edit/{{Report.id_teacher}}">{{Report.Teacher.last_name}} {{Report.Teacher.first_name}} {{Report.Teacher.middle_name}}</a>
			</td>
			<td>
				<a href="student/{{Report.id_student}}">{{Report.Student.last_name}} {{Report.Student.first_name}}</a>
			</td>
<!--
			<td>
				{{Subjects[Report.id_subject]}}
			</td>
-->
			<td>
				<span ng-show="Report.available_for_parents">доступен для родителя</span>
			</td>
			<td>
				<span ng-show="Report.email_sent">отчет отправлен</span>
			</td>
		</tr>
	</table>

	<div ng-show="SelectedReports !== false && !SelectedReports.length" style="padding: 100px" class="small half-black center">
		загрузка отчетов...
	</div>
	<div ng-show="SelectedReports === false" style="padding: 100px" class="small half-black center">
		нет отчетов
	</div>

	<div style="margin-top: 20px" ng-show="<?= isset($report_counts['red']) ? $report_counts['red'] : 0 ?> > 0"><span class="glyphicon glyphicon-exclamation-sign text-danger glyphicon-big"></span><span class="text-danger"><?= $report_counts['red'] ?> <ng-pluralize count="<?= $report_counts['red'] ?>" when="{
								'one': 'отчет',
								'few': 'отчета',
								'many': 'отчетов',
							}"></ng-pluralize> требуется создать</span>
	</div>

</div>
