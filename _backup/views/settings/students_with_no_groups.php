<div ng-app="Settings" ng-controller="StudentsWithNoGroupCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel panel-primary">
		<div class="panel-heading">
			Ученики без групп <a class="small" href="settings/students" style="margin-left: 10px">ученики</a>
		</div>
		<div class="panel-body">
			
			<div class="row" style="margin-bottom: 15px">
				<div class="col-sm-3">
					<?= Grades::buildMultiSelector(false, ["ng-model" => "search.grades", "id" => "grades-select"]) ?>
				</div>
				<div class="col-sm-3">
	                <?= Branches::buildMultiSelector(false, ["id" => "group-branch-filter", "ng-model" => "search.branches"]) ?>
				</div>
				<div class="col-sm-3">
					<?= Subjects::buildSelector(false, false, ["ng-model" => "search.id_subject"]) ?>
				</div>
			</div>
			
			<div class="center half-black small" style="margin: 50px 0 40px" ng-hide="Groups">
				формирование групп...
			</div>
			
			<div ng-repeat="Group in Groups | filter:groupsFilter" ng-show="Groups" class="ng-hide group-list-2" 
				ng-class="{'mt10': !$first, 'last': Group.Students.length == 0}" data-index="{{$index}}" id="group-index-{{$index}}">
<!--		
				<h5>
					<span ng-bind-html="Group.branch_svg | to_trusted"></span>, {{Group.grade}} класс, {{Subjects[Group.subject]}}
				</h5>
-->
				<table class="table table-divlike">
					<tbody>
						<tr ng-repeat="Student in Group.Students" class="student-line is-draggable" 
							data-group-index="{{$parent.$index}}" data-student="{{Student}}">
<!-- 							<td width="50"></td> -->
							<td width="300">
<!-- 								{{$index + 1}}. -->
								<a href="student/{{Student.id}}">
								<span ng-show="Student.last_name || Student.first_name || Student.middle_name">{{Student.last_name}} {{Student.first_name}} {{Student.middle_name}}</span>
								<span ng-show="!Student.last_name && !Student.first_name && !Student.middle_name">Неизвестно</span>
								</a>
							</td>
							<td width="100">
								{{Student.Contract.id}}
							</td>
							<td width="100">
								{{Student.Contract.grade}} класс
							</td>
							<td width="100">
								{{Student.Contract.date}}
							</td>
							<td width="100">
								
								<span ng-repeat="subject in Student.Contract.subjects" ng-show="subject.id_subject == Group.subject"><span class="text-danger bold" ng-show="subject.count > 40">{{subject.short}}</span><span ng-show="subject.count <= 40">{{subject.short}}</span></span>
							</td>
							<td width="300">
								<span ng-repeat="(id_branch, short) in Student.branch_short track by $index" 
									ng-bind-html="short | to_trusted" ng-class="{'mr3' : !$last}"></span>
							</td>
							<td style="text-align: right">
								<div ng-repeat="subject in Student.Contract.subjects" ng-show="subject.score != ''">
									<b ng-show="subject.id_subject == Group.subject">{{subject.score}}</b>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div ng-show="(Groups | filter:groupsFilter).length == 0" class="center half-black small" style="margin: 30px 0 15px">не найдено групп, соответствующих запросу</div>
		</div>
	</div>
</div>