<div ng-app="Teacher" ng-controller="ListCtrl"
	ng-init="<?= $ang_init_data ?>">
		
	<div class="row" style="position: relative">
		<div id="frontend-loading"></div>
		<div class="col-sm-12">
			<table class="table table-divlike">
				<tr ng-repeat="Teacher in Teachers">
					<td>
						{{Teacher.id}}. <a href="teachers/edit/{{Teacher.id}}">
							<span ng-show="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
								{{Teacher.last_name}} {{Teacher.first_name}} {{Teacher.middle_name}}
							</span>
							<span ng-hide="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
								Неизвестно
							</span>
						</a>
					</td>
					<td>
						<span ng-repeat="(id_branch, short) in Teacher.branch_short track by $index" 
							ng-bind-html="short | to_trusted" ng-class="{'mr3' : !$last}"></span>
					</td>
					<td>
						<span ng-repeat="id_subject in Teacher.subjects">{{subjects[id_subject]}}{{$last ? "" : "+"}}</span>
					</td>
					<td style="text-align: right">
						<span class="link-like small" ng-click="deleteTeacher(Teacher.id, $index)">удалить</span>
					</td>
				</tr>
			</table>

		</div>
	</div>
</div>
