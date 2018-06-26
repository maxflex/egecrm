<div class="panel panel-primary" ng-app="Teacher" ng-controller="ListCtrl"
		ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">
		Преподаватели
		<!-- <div class="pull-right">
			<span class="link-like link-reverse link-white" ng-click="PhoneService.sms()" style="margin-right: 7px">
					групповое SMS</span>
		</div> -->
	</div>
	<div class="panel-body">

		<div class="row" style="position: relative">
			<div id="frontend-loading"></div>
			<div class="col-sm-12">
				<div class="row filters" style="margin-bottom: 20px">
					<div class="col-sm-4">
						<select id='state-select' class="form-control" ng-model='in_egecentr' ng-change='changeState()'>
							<option value=""  data-subtext="{{ getCount('', id_subject) }}">все</option>
							<option disabled>──────────────</option>
							<option ng-repeat="(id, label) in Workplaces" value='{{ id }}' data-subtext="{{ getCount(id, id_subject) }}">{{ label }}</option>
							<!-- <option value='1' data-subtext="{{ getCount(1, id_subject) }}">{{ Workplaces[0] }}</option>
							<option value='2' data-subtext="{{ getCount(2, id_subject) }}">ведет занятия в ЕГЭ-Центре</option>
							<option value='3' data-subtext="{{ getCount(3, id_subject) }}">ранее работал в ЕГЭ-Центре</option> -->
						</select>
					</div>
					<div class="col-sm-4">
						<select class='form-control' id='subjects-select' ng-model='id_subject' ng-change='changeSubjects()'>
							<option value=""  data-subtext="{{ getCount(0, id_subject) }}">все</option>
							<option disabled>──────────────</option>
							<option ng-repeat='(key, name) in three_letters'
								value='{{key}}'
								data-subtext="{{ getCount(in_egecentr, key) }}">{{name}}</option>
						</select>
<!--
						<select class='form-control' id='subjects-select' ng-model='id_subject' multiple ng-options='key as name for (key, name) in three_letters'>
						</select>
-->
					</div>
					<div class="col-sm-4">
						<div>
				            <?= Branches::buildSvgSelector([], [
				                "ng-model" => "filter_branch",
								"ng-change" => "refreshCounts()",
				                "id" => "filter-branches",
				            ]) ?>
				        </div>
					</div>
				</div>
				<table class="table table-hover border-reverse" id="teachers-list">
					<tr ng-repeat="Teacher in Teachers | filter:teachersFilter">
						<td>
							<a href="teachers/edit/{{Teacher.id}}">
								<span ng-show="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
									{{Teacher.last_name}} {{Teacher.first_name}} {{Teacher.middle_name}}
								</span>
								<span ng-hide="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
									Неизвестно
								</span>
							</a>
							<span ng-show="Teacher.alerts && Teacher.alerts.length"
								title="{{ Teacher.alerts.join('\n\n') }}"
								class="teacher-alert glyphicon glyphicon-exclamation-sign"></span>
						</td>
						<td>
							<span ng-repeat="id_subject in Teacher.subjects">{{three_letters[id_subject]}}{{$last ? "" : "+"}}</span>
						</td>
						<td>
							<span ng-repeat="branch in Teacher.branches">
								<span ng-click="gmap(Student)" style='color: {{ Branches[branch].color }}; margin-right: 3px'>{{ Branches[branch].short }}</span>
							</span>
						</td>
						<td width="380">
						   <span ng-repeat="(day, data) in Teacher.bar" class="group-freetime-block">
								<span ng-repeat="bar in data track by $index" class="bar {{bar}}"></span>
							</span>
						</td>
                    </tr>
				</table>
				<div ng-show="Teachers === undefined" style="padding: 100px" class="small half-black center">
					загрузка...
				</div>
			</div>
		</div>
	</div>
	<sms mode="teacher" mass="1" counts="getCount(in_egecentr, id_subject)"></sms>
</div>
