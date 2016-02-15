<div class="panel panel-primary" ng-app="Teacher" ng-controller="ListCtrl"
		ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">
		Преподаватели
		<div class="pull-right">
			<span class="link-like link-reverse link-white" ng-click="smsDialog()" style="margin-right: 7px">
					групповое SMS</span>
			<a href='teachers/add'>добавить преподавателя</a>
		</div>
	</div>
	<div class="panel-body">

		<div class="row" style="position: relative">
			<div id="frontend-loading"></div>
			<div class="col-sm-12">
				<table class="table table-divlike" id="teachers-list">
					<tr>
						<td colspan="3">
						</td>
						<td><span class="review-small">5</span></td>
						<td><span class="review-small">4</span></td>
						<td><span class="review-small">3</span></td>
						<td><span class="review-small">2</span></td>
						<td><span class="review-small">1</span></td>
						<td><span class="review-small gray">?</span></td>
					</tr>
					<tr ng-repeat="Teacher in Teachers" ng-hide="!Teacher.had_lesson">
						<td width='20'>
							<span ng-show="Teacher.has_photo" class="glyphicon glyphicon-camera"></span>
						</td>
						<td width="400">
							<a href="teachers/edit/{{Teacher.id}}">
								<span ng-show="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
									{{Teacher.last_name}} {{Teacher.first_name}} {{Teacher.middle_name}}
								</span>
								<span ng-hide="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
									Неизвестно
								</span>
							</a>
							 (<span ng-repeat="id_subject in Teacher.subjects">{{subjects[id_subject]}}{{$last ? "" : "+"}}</span>)
						</td>
						<td>
							<span ng-show="Teacher.banned" class="glyphicon glyphicon-lock text-danger"></span>
						</td>
						<td><span style='margin-left: 6px'>{{ Teacher.statuses[5] }}</span></td>
						<td><span style='margin-left: 6px'>{{ Teacher.statuses[4] }}</span></td>
						<td><span style='margin-left: 6px'>{{ Teacher.statuses[3] }}</span></td>
						<td><span style='margin-left: 6px'>{{ Teacher.statuses[2] }}</span></td>
						<td><span style='margin-left: 6px'>{{ Teacher.statuses[1] }}</span></td>
						<td width='100' class="text-gray"><span style='margin-left: 6px'>{{ Teacher.statuses[0] }}</span></td>
						<td>
							<span class="label label-danger-red" ng-show="Teacher.student_subject_counts.red">
								требуется создать {{Teacher.student_subject_counts.red}} <ng-pluralize count="Teacher.student_subject_counts.red" when="{
									'one': 'отчет',
									'few': 'отчета',
									'many': 'отчетов',
								}"></ng-pluralize>
							</span>
						</td>
						<td>
							<span ng-show="Teacher.schedule_date">{{Teacher.schedule_date}}</span>
						</td>
					</tr>
				</table>


				<fieldset class="hidden-thoughts" id="hidden-teachers-button">
				    <legend ng-click="showHidden()">Остальные: {{othersCount()}}</legend>
				</fieldset>




				<table class="table table-divlike" ng-show="show_others" style="margin-top: 20px">
					<tr ng-repeat="Teacher in Teachers" ng-show="!Teacher.had_lesson">
						<td width="400">
							<a href="teachers/edit/{{Teacher.id}}">
								<span ng-show="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
									{{Teacher.last_name}} {{Teacher.first_name}} {{Teacher.middle_name}}
								</span>
								<span ng-hide="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
									Неизвестно
								</span>
							</a>
							 (<span ng-repeat="id_subject in Teacher.subjects">{{subjects[id_subject]}}{{$last ? "" : "+"}}</span>)
						</td>
						<td style="width: 20%">
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
						<td style="width: 10%">
							<span ng-show="Teacher.schedule_date">{{Teacher.schedule_date}}</span>
						</td>
					</tr>
				</table>



			</div>
		</div>
	</div>
</div>
