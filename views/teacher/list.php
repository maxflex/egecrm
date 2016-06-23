<div class="panel panel-primary" ng-app="Teacher" ng-controller="ListCtrl"
		ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">
		Преподаватели
		<div class="pull-right">
			<span class="link-like link-reverse link-white" ng-click="smsDialog()" style="margin-right: 7px">
					групповое SMS</span>
		</div>
	</div>
	<div class="panel-body">

		<div class="row" style="position: relative">
			<div id="frontend-loading"></div>
			<div class="col-sm-12">
				<div class="row" style="margin-bottom: 20px">
					<div class="col-sm-4">
						<select id='state-select' class="form-control" ng-model='in_egecentr' ng-change='changeState()'>
							<option value='1' data-subtext="{{ getCount(1, filter_subjects) }}">активен в системе ЕГЭ-Центра</option>
							<option value='2' data-subtext="{{ getCount(2, filter_subjects) }}">ведет занятия в ЕГЭ-Центре</option>
							<option value='3' data-subtext="{{ getCount(3, filter_subjects) }}">ранее работал в ЕГЭ-Центре</option>
						</select>
					</div>
					<div class="col-sm-4">
						<select class='form-control' id='subjects-select' ng-model='filter_subjects' multiple ng-change='changeSubjects()'>
							<option ng-repeat='(key, name) in three_letters' 
								ng-selected="subjectSelected(key)"
								value='{{key}}' 
								data-subtext="{{ getCount(in_egecentr, key) }}">{{name}}</option>
						</select>
<!--
						<select class='form-control' id='subjects-select' ng-model='filter_subjects' multiple ng-options='key as name for (key, name) in three_letters'>
						</select>
-->
					</div>
				</div>
				<table class="table table-hover border-reverse" id="teachers-list">
					<tr ng-repeat="Teacher in Teachers | filter:teachersFilter">
						<td width="400">
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
							<span ng-repeat="id_subject in Teacher.subjects">{{subjects[id_subject]}}{{$last ? "" : "+"}}</span>
						</td>
					</tr>
				</table>

			</div>
		</div>
	</div>
</div>
