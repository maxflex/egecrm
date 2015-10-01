<div ng-app="Teacher" ng-controller="ListCtrl"
	ng-init="<?= $ang_init_data ?>">
		
	<div class="row" style="position: relative">
		<div id="frontend-loading"></div>
		<div class="col-sm-12">
			<table class="table table-divlike">
				<tr ng-repeat="Teacher in Teachers">
					<td width="300">
						{{Teacher.id}}. <a href="teachers/edit/{{Teacher.id}}">
							<span ng-show="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
								{{Teacher.last_name}} {{Teacher.first_name}} {{Teacher.middle_name}}
							</span>
							<span ng-hide="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
								Неизвестно
							</span>
						</a>
					</td>
					<td width="500">
						<span ng-repeat="(id_branch, short) in Teacher.branch_short track by $index" 
							ng-bind-html="short | to_trusted" ng-class="{'mr3' : !$last}" style="display: inline-block"></span>
					</td>
					<td>
						<span ng-show="Teacher.gray_count">
							<svg class="review-status not-collected" style="top: 4px; width: 15px">
								<circle r="3" cx="7" cy="7"></circle>
							</svg>{{Teacher.gray_count}}
						</span>
						<span ng-show="Teacher.green_count">
							<svg class="review-status collected" style="top: 4px; width: 15px">
								<circle r="3" cx="7" cy="7"></circle>
							</svg>{{Teacher.green_count}}
						</span>
						<span ng-show="Teacher.orange_count">
							<svg class="review-status orange" style="top: 4px; width: 15px">
								<circle r="3" cx="7" cy="7"></circle>
							</svg>{{Teacher.orange_count}}
						</span>
						<span ng-show="Teacher.red_count">
							<svg class="review-status red" style="top: 4px; width: 15px">
								<circle r="3" cx="7" cy="7"></circle>
							</svg>{{Teacher.red_count}}
						</span>
					</td>
					<td>
						<span ng-repeat="id_subject in Teacher.subjects">{{subjects[id_subject]}}{{$last ? "" : "+"}}</span>
					</td>
					<td>
						<span ng-show="Teacher.schedule_date">{{Teacher.schedule_date}}</span>
					</td>
					<td>
						{{Teacher.login_count}}
					</td>
					<td style="text-align: right">
						<span class="link-like small" ng-click="deleteTeacher(Teacher.id, $index)">удалить</span>
					</td>
				</tr>
			</table>

		</div>
	</div>
</div>
