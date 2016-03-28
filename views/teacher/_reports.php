<div class="row" ng-show="Reports.length > 0">
    <div class="col-sm-6">
		<h4 class="row-header" ng-model="report_collapsed" ng-click="report_collapsed = Reports.length && !report_collapsed">ОТЧЕТЫ</h4>
		<table class="table table-divlike">
			<tr ng-repeat="Report in Reports" ng-show="report_collapsed">
				<td width="100">
					<a href="reports/edit/{{Report.id}}">Отчёт №{{Report.id}}</a>
				</td>
				<td>
					<a href="student/{{Report.id_student}}">{{Report.Student.last_name}} {{Report.Student.first_name}} {{Report.Student.middle_name}}</a>
				</td>
				<td>
					{{SubjectsFull[Report.id_subject]}}
				</td>
				<td>
					{{Report.date}}
				</td>
			</tr>
		</table>
    </div>
</div>