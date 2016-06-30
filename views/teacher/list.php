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
							<option value=""  data-subtext="{{ getCount(0, id_subject) }}">все</option>
							<option disabled>──────────────</option>
							<option value='1' data-subtext="{{ getCount(1, id_subject) }}">активен в системе ЕГЭ-Центра</option>
							<option value='2' data-subtext="{{ getCount(2, id_subject) }}">ведет занятия в ЕГЭ-Центре</option>
							<option value='3' data-subtext="{{ getCount(3, id_subject) }}">ранее работал в ЕГЭ-Центре</option>
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
				</div>
				<table class="table table-hover border-reverse" id="teachers-list">
					<tr ng-repeat="Teacher in Teachers | filter:teachersFilter">
						<td class="col-sm-4">
							<a href="teachers/edit/{{Teacher.id}}">
								<span ng-show="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
									{{Teacher.last_name}} {{Teacher.first_name}} {{Teacher.middle_name}}
								</span>
								<span ng-hide="Teacher.last_name || Teacher.first_name || Teacher.middle_name">
									Неизвестно
								</span>
							</a>
						</td>
						<td class="col-sm=2">
							<span ng-repeat="id_subject in Teacher.subjects">{{subjects[id_subject]}}{{$last ? "" : "+"}}</span>
						</td>
                        <td ng-repeat-start="grade in [9, 10, 11]" width="50px">
                            <span ng-show="Teacher.hold_coeff_by_grade[grade]">{{ Teacher.hold_coeff_by_grade[grade] }}%</span>
                        </td>
                        <td ng-repeat-end class="col-sm-1">
                            {{ Teacher.fact_lesson_cnt_by_grade[grade] | hideZero }}
                        </td>
                        <!-- сумма по всем группам -->
                        <td width="50px">
                            <span ng-show="Teacher.hold_coeff">{{ Teacher.hold_coeff }}%</span>
                        </td>
                        <td class="col-sm-1">
                            {{ Teacher.fact_lesson_total_cnt | hideZero }}
                        </td>
                    </tr>

                    <tr style="font-weight: bold">
                        <td colspan="2"></td>
                        <td ng-repeat-start="grade in [9, 10, 11]" width="50px">
                                {{ totalHold(grade) }}%
                        </td>
                        <td ng-repeat-end class="col-sm-1">
                            {{ totalLessons(grade) | hideZero }}
                        </td>
                        <td width="50px">
                            {{ totalHold() }}%
                        </td>
                        <td class="col-sm-1">
                            {{ totalLessons() | hideZero }}
                        </td>
					</tr>
				</table>

			</div>
		</div>
	</div>
</div>
