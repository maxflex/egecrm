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
					<tr ng-repeat="Teacher in Teachers" ng-hide="!Teacher.had_lesson">
						<td width="300">
							<a href="teachers/edit/{{Teacher.id}}">
								<span ng-show="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
									{{Teacher.last_name}} {{Teacher.first_name}} {{Teacher.middle_name}}
								</span>
								<span ng-hide="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
									Неизвестно
								</span>
							</a>
						</td>
						<td>
							<span ng-show="Teacher.statuses[1] > 0" class="text-success">
								{{Teacher.statuses[1]}} нравится
							</span>
						</td>
						<td>
							<span ng-show="Teacher.statuses[2] > 0" class="text-warning">
								{{Teacher.statuses[2]}} средне
							</span>
						</td>
						<td>
							<span ng-show="Teacher.statuses[3] > 0" class="text-danger">
								{{Teacher.statuses[3]}} не нравится
							</span>
						</td>
						<td>
							<span ng-show="Teacher.statuses[0] > 0" class="half-black">
								{{Teacher.statuses[0]}} не установлено
							</span>
						</td>
						<td>
							{{Teacher.student_subject_count}} человеко-<ng-pluralize count="Teacher.student_subject_count" when="{
								'one': 'предмет',
								'few': 'предмета',
								'many': 'предметов',
							}"></ng-pluralize>, 
							{{Teacher.reports_count}} <ng-pluralize count="Teacher.reports_count" when="{
								'one': 'отчет',
								'few': 'отчета',
								'many': 'отчетов',
							}"></ng-pluralize>, 
							{{Teacher.reports_sent_count}} <ng-pluralize count="Teacher.reports_sent_count" when="{
								'one': 'отправлен',
								'few': 'отправлено',
								'many': 'отправлено',
							}"></ng-pluralize> по e-mail
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
					</tr>
				</table>
				
				
				<fieldset class="hidden-thoughts" id="hidden-teachers-button">
				    <legend ng-click="showHidden()">Остальные: {{othersCount()}}</legend>
				</fieldset>
				
				
				
				
				<table class="table table-divlike" ng-show="show_others" style="margin-top: 20px">
					<tr ng-repeat="Teacher in Teachers" ng-show="!Teacher.had_lesson">
						<td width="300">
							<a href="teachers/edit/{{Teacher.id}}">
								<span ng-show="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
									{{Teacher.last_name}} {{Teacher.first_name}} {{Teacher.middle_name}}
								</span>
								<span ng-hide="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
									Неизвестно
								</span>
							</a>
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
							<span ng-repeat="id_subject in Teacher.subjects">{{subjects[id_subject]}}{{$last ? "" : "+"}}</span>
						</td>
						<td style="width: 10%">
							<span ng-show="Teacher.schedule_date">{{Teacher.schedule_date}}</span>
						</td>
						<td style="width: 10%">
							{{Teacher.login_count}}
						</td>
					</tr>
				</table>
				
				
				
			</div>
		</div>
	</div>
</div>
