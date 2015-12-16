<div class="row" ng-show="Reports.length > 0">
    <div class="col-sm-6">
		<h4 class="row-header">ОТЧЕТЫ</h4>
		<table class="table table-divlike">
			<tr ng-repeat="Report in Reports">
				<td width="100">
					<a href="reports/edit/{{Report.id}}">Отчёт №{{Report.id}}</a>
				</td>
				<td>
					<a href="student/{{Report.id_student}}">{{Report.Student.last_name}} {{Report.Student.first_name}} {{Report.Student.middle_name}}</a>
				</td>
				<td>
					{{Report.date}}
				</td>
				<td colspan="3">
					<span ng-show="Report.available_for_parents" class="half-black">опубликован</span>
					<span ng-show="!Report.available_for_parents"></span>
				</td>
			</tr>
		</table>
    </div>
</div>