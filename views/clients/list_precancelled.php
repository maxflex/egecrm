<style>
	.table tr td {
		padding-top: 20px !important;
	}
</style>
<div class="panel panel-primary" ng-app="Clients" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">Клиенты с предварительным расторжением
		<div class="pull-right">
			<span style="margin-right: 7px">сортировать по:</span>
			<span class="link-reverse pointer" ng-click="setOrder(1)" style="margin-right: 7px">имя</span>
			<span class="link-reverse pointer" ng-click="setOrder(2)" style="margin-right: 7px">номер договора</span>
			<span class="link-reverse pointer" ng-click="setOrder(3)">дата заключения</span>
		</div>
	</div>
	<div class="panel-body">
		<table class="table table-divlike">
			<tr ng-repeat="Student in Students | orderBy:orderStudents():asc | filter:clientsFilter">
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
					{{Student.Groups.length}} <ng-pluralize count="Student.Groups.length" when="{
						'one': 'группа',
						'few': 'группы',
						'many': 'групп'
					}"></ng-pluralize>
				</td>
				<td>
					{{Student.login_count}}
				</td>
				<td>
					
					<div ng-show="Student.Groups" ng-repeat="Group in Student.Groups">
						<span ng-bind-html="Group.branch | to_trusted" style="position: relative; top: -3px; width: 50px; display: inline-block"></span>
						<span ng-repeat="weekday in weekdays" class="group-freetime-block">
							<span class="freetime-bar" ng-repeat="time in weekday.schedule track by $index" 
								ng-class="{
									'empty'				: !inFreetime(time, Group, $parent.$index + 1),
									'red-gray-empty' 	: !inFreetime(time, Group, $parent.$index + 1) && justInDayFreetimeObject($parent.$index + 1, time, Group.day_and_time),
									'red-gray' 		: inFreetime(time, Group, $parent.$index + 1) && justInDayFreetimeObject($parent.$index + 1, time, Group.day_and_time),
									'red' 			: justInDayFreetimeObject($parent.$index + 1, time, Group.day_and_time) && Group.student_agreed,
								}" ng-hide="time == ''">
<!--
																	'red-gray-empty' 	: !inFreetime(time, Group, $parent.$index + 1) && justInDayFreetime($parent.$index + 1, time, Group.day_and_time),
									'red-gray' 			: inFreetime(time, Group, $parent.$index + 1) && justInDayFreetime($parent.$index + 1, time, Group.day_and_time),
									'red'				: inRedFreetime(time, Group, $parent.$index + 1),
-->
							</span>
						</span>
					</div>
				</td>
			</tr>
		</table>
		
		<div class="pull-right">
			<b class="text-success">+<?= $without_contract ?></b> <?= pluralize('ученик', 'ученика', 'учеников', $without_contract) ?> без договора
		</div>
	</div>
</div>