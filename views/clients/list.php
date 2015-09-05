<div ng-app="Clients" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<table class="table table-divlike">
		<tr>
			<td style="height: 29px">
				<span class="glyphicon glyphicon-sort sort-link" ng-click="setOrder(1)"></span>
			</td>
			<td>
				<span class="glyphicon glyphicon-sort sort-link" ng-click="setOrder(2)"></span>
			</td>
			<td>
			</td>
			<td><span class="glyphicon glyphicon-sort sort-link" ng-click="setOrder(3)"></span></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr ng-repeat="Student in Students | orderBy:orderStudents():asc">
			<td>
				{{$index + 1}}. <a href="student/{{Student.id}}">{{Student.last_name}} {{Student.first_name}} {{Student.middle_name}}</a>
			</td>
			<td>
				<div ng-repeat="Contract in Student.Contracts">
					{{Contract.id}}
				</div>
			</td>
			<td>
				<div ng-repeat="Contract in Student.Contracts">
					{{Contract.grade ? Contract.grade + " класс" : "неизвестно"}}
				</div>
			</td>
			<td>
				<div ng-repeat="Contract in Student.Contracts">
					{{Contract.date ? Contract.date : "неизвестно"}}
				</div>
			</td>
			<td>
				<div ng-repeat="Contract in Student.Contracts">
					{{getSubjectsCount(Contract)}} <ng-pluralize count="getSubjectsCount(Contract)" when="{
						'one': 'предмет',
						'few': 'предмета',
						'many': 'предметов'
					}"></ng-pluralize>
				</div>
			</td>
			<td>
				<div ng-repeat="Contract in Student.Contracts">
					{{Contract.sum | number}} рублей
				</div>
			</td>
			<td>
				{{Student.markers_count}}
			</td>
			<td>
				<div ng-repeat="Contract in Student.Contracts">
					{{Contract.cancelled ? " расторгнут" : ""}}
				</div>
			</td>
			<td>
				<span class="pull-right">
					<div ng-repeat="Contract in Student.Contracts">
						<b>{{getScore(Contract.subjects)}}</b>
					</div>
				</span>
			</td>
		</tr>
	</table>
	
	<div class="pull-right">
		<b class="text-success">+<?= $without_contract ?></b> <?= pluralize('ученик', 'ученика', 'учеников', $without_contract) ?> без договора
	</div>
</div>