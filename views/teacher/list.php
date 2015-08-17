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
				</tr>
			</table>

		</div>
	</div>
</div>
