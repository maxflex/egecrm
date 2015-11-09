<div ng-app="Group" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="row" style="position: relative">
		<div class="col-sm-12">
			
			
			<table class="table table-divlike" style="position: relative">
	<tr ng-repeat="Group in Groups" 
		class="group-list" data-id="{{Group.id}}">
		<td width="100">
			<a href="students/groups/edit/{{Group.id}}/schedule">Группа №{{Group.id}}</a>
		</td>
		<td>
			<span>ЕГЭ-Центр-{{Branches[Group.id_branch]}}</span>
		</td>
		<td>
			{{Subjects[Group.id_subject]}}
		</td>
		<td>
			{{Group.grade}} класс
		</td>
		<td>
			{{Group.Schedule.length}} <ng-pluralize count="Group.Schedule.length" when="{
				'one': 'занятие',
				'few': 'занятия',
				'many': 'занятий'
			}"></ng-pluralize>
		</td>
		<td>
			{{Group.is_special ? " (спец.)" : ""}}
		</td>
	</tr>
</table>
			
			<div ng-show="Groups.length == 0" class="center half-black small" style="margin-bottom: 30px">список групп пуст</div>
		</div>
	</div>
</div>
