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
		
		<hr>
		<table class="table">
			<tr ng-repeat-start="Student in Students">
				<td>
					{{Student.last_name}} {{Student.first_name}}
				</td>
				<td>
					{{Subjects[Student.id_subject]}}
				</td>
				<td>
					{{Student.visit_count}} <ng-pluralize count="Student.visit_count" when="{
					'one': 'занятие',
					'few': 'занятия',
					'many': 'занятий'
				}"></ng-pluralize>
				</td>
				<td>
					<span ng-show="Student.Reports.length > 0">{{Student.Reports.length}} <ng-pluralize count="Student.Reports.length" when="{
					'one': 'отчет',
					'few': 'отчета',
					'many': 'отчетов'
				}"></ng-pluralize></span>
					<span ng-show="!Student.Reports.length">отчетов нет</span>
				</td>
				<td>
					<a href="teachers/reports/add/{{Student.id}}/{{Student.id_subject}}/">добавить отчет</a>
				</td>
			</tr>
			<tr ng-repeat="Report in Student.Reports" ng-repeat-end class="inner">
				<td>
					<a href="teachers/reports/edit/{{Report.id}}">Отчёт №{{Report.id}}</a>
				</td>
				<td>
					{{Report.date}}
				</td>
				<td colspan="1">
					<span ng-show="Report.available_for_parents" class="half-black">опубликован</span>
					<span ng-show="!Report.available_for_parents"></span>
				</td>
			</tr>
		</table>
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