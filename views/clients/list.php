<div class="panel panel-primary" ng-app="Clients" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">Клиенты с договорами
		<div class="pull-right">
			<span style="margin-right: 7px">сортировать по:</span>
			<span class="link-reverse pointer" ng-click="setOrder(1)" style="margin-right: 7px">имя</span>
			<span class="link-reverse pointer" ng-click="setOrder(2)" style="margin-right: 7px">номер договора</span>
			<span class="link-reverse pointer" ng-click="setOrder(3)">дата заключения</span>
		</div>
	</div>
	<div class="panel-body">
		<div class="top-links">
		    <span class="link-like" ng-click="filter_cancelled = 0" ng-class="{'active': filter_cancelled == 0}">договоры в работе</span>
		    <span class="link-like" ng-click="filter_cancelled = 1" ng-class="{'active': filter_cancelled == 1}">расторгнутые договоры</span>
	    </div>
		<table class="table table-divlike">
			<tr ng-repeat="Student in Students | orderBy:orderStudents():asc | filter:{Contract: {cancelled : filter_cancelled} }">
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
					{{Student.groups_count}} <ng-pluralize count="Student.groups_count" when="{
						'one': 'группа',
						'few': 'группы',
						'many': 'групп'
					}"></ng-pluralize>
				</td>
			</tr>
		</table>
		
		<div class="pull-right">
			<b class="text-success">+<?= $without_contract ?></b> <?= pluralize('ученик', 'ученика', 'учеников', $without_contract) ?> без договора
		</div>
	</div>
</div>