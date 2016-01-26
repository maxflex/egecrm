<div ng-show="add_clients_panel && Group.open == 1" class="row">
		<hr>
		<div class="col-sm-12">
			<div class="row" style="margin-bottom: 15px">
				<div class="col-sm-3">
					<?= Grades::buildSelector(false, false, ["ng-model" => "search.grade"]) ?>
				</div>
				<div class="col-sm-3">
	                <?= Branches::buildSvgSelector(false, ["id" => "group-branch-filter", "ng-model" => "search.id_branch"]) ?>
				</div>
				<div class="col-sm-3">
					<?= Subjects::buildSelector(false, false, ["ng-model" => "search.id_subject"]) ?>
				</div>
			</div>
			<div ng-show="!Students" class="center half-black small" style="margin-top: 35px">загрузка учеников...</div>
			<table class="table table-divlike">
				<tbody>
					<tr ng-repeat="Student in Students | filter:clientsFilter">
						<td>
							{{$index + 1}}.
							<a href="student/{{Student.id}}">
							<span ng-show="Student.last_name || Student.first_name || Student.middle_name">{{Student.last_name}} {{Student.first_name}} {{Student.middle_name}}</span>
							<span ng-show="!Student.last_name && !Student.first_name && !Student.middle_name">Неизвестно</span>
							</a>
						</td>
						<td>
							{{Student.Contract.id}}
						</td>
						<td>
							{{Student.Contract.grade}} класс
						</td>
						<td>
							{{Student.Contract.date}}
						</td>
						<td>
							<span ng-repeat="subject in Student.Contract.subjects"><span class="text-danger bold" ng-show="subject.count > 40">{{subject.short}}</span><span ng-show="subject.count <= 40">{{subject.short}}</span>{{$last ? "" : "+"}}</span>
						</td>
						<td>
							<span ng-repeat="(id_branch, short) in Student.branch_short track by $index" 
								ng-bind-html="short | to_trusted" ng-class="{'mr3' : !$last}"></span>
						</td>
						<td>
							<div ng-repeat="subject in Student.Contract.subjects" ng-show="subject.score != ''">
								<b ng-show="subject.id_subject == search.id_subject">{{subject.score}}</b>
							</div>
						</td>
						<td>
							<span ng-hide="Student.in_other_group">
								<span class="small pull-right quater-black" id="student-adding-{{Student.id}}" style="cursor: default; display: none">
									добавить
								</span>
								<span class="small pull-right quater-black" style="cursor: default" 
									ng-show="Student.Contract.cancelled == 1 && !studentAdded(Student.id)">
									расторгнут
								</span>
								<span class="link-like small pull-right" ng-click="addStudent(Student, $event)" 
									ng-hide="studentAdded(Student.id) || Student.Contract.cancelled == 1">добавить</span>
								<span class="link-like small pull-right red" ng-click="removeStudent(Student.id)" ng-show="studentAdded(Student.id)">удалить</span>
							</span>
							<span ng-show="Student.in_other_group" class="quater-black small pull-right">в другой группе</span>
						</td>
					</tr>
				</tbody>
			</table>
			<div ng-show="(Students | filter:clientsFilter).length == 0" class="center half-black small" style="margin-bottom: 15px">не найдено учеников, соответствующих запросу</div>
		</div>
	</div>