<div ng-app="Group" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="row" style="position: relative">
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
				<div class="col-sm-3">
					<select class="form-control" ng-model="search.id_teacher">
						<option selected value="">преподаватель</option>
						<option disabled>──────────────</option>
						<option ng-repeat="Teacher in Teachers" value="{{Teacher.id}}">
							{{Teacher.last_name}} {{Teacher.first_name[0]}}. {{Teacher.middle_name[0]}}.
						</option>
					</select>
				</div>
			</div>
			
			<?= globalPartial("groups_list", ["filter" => true, "loading" => true]) ?>
			
			<div ng-show="Groups.length == 0" class="center half-black small" style="margin-bottom: 30px">список групп пуст</div>
		</div>
	</div>
	
	<div class="center" ng-hide="students_picker" style="margin: 10px 0">
		<span class="link-like small link-reverse" ng-click="loadStudentPicker()">подобрать учеников</span>
	</div>
	
	<?php if ($mode == 1): ?>
	<div class="row" ng-show="students_picker">
		<div class="col-sm-12">
			<div class="row" style="margin-bottom: 15px">
				<div class="col-sm-3">
					<?= Grades::buildSelector(false, false, ["ng-model" => "search_student.grade"]) ?>
				</div>
				<div class="col-sm-3">
	                <?= Branches::buildSvgSelector(false, ["id" => "student-branch-filter", "ng-model" => "search_student.id_branch"]) ?>
				</div>
				<div class="col-sm-3">
					<?= Subjects::buildSelector(false, false, ["ng-model" => "search_student.id_subject"]) ?>
				</div>
				<div class="col-sm-3">
					<select class="form-control" ng-model="change_mode" ng-change="changeMode()">
						<option value="2">ученики без групп</option>
						<option value="1">ученики</option>
					</select>
				</div>
			</div>
			
			<div class="center half-black small" style="margin: 50px 0 40px" ng-hide="Students">
				загрузка учеников...
			</div>
			
			<table class="table table-divlike ng-hide" ng-show="Students">
				<tbody>
					<tr ng-repeat="Student in Students | filter:clientsFilter"  class="request-main-list" data-id="{{Student.id}}">
						<td width="300">
							{{$index + 1}}.
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
						<td width="150">
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
					</tr>
				</tbody>
			</table>
			<div ng-show="(Students | filter:clientsFilter).length == 0" class="center half-black small" style="margin-bottom: 15px">не найдено учеников, соответствующих запросу</div>
		</div>
	</div>
	<?php elseif ($mode == 2): ?>
	<div ng-show="students_picker">
		<div class="row" style="margin-bottom: 15px">
					<div class="col-sm-3">
						<?= Grades::buildMultiSelector(false, ["ng-model" => "search2.grades", "id" => "grades-select2"]) ?>
					</div>
					<div class="col-sm-3">
		                <?= Branches::buildMultiSelector(false, ["id" => "group-branch-filter2", "ng-model" => "search2.branches"]) ?>
					</div>
					<div class="col-sm-3">
						<?= Subjects::buildSelector(false, false, ["ng-model" => "search2.id_subject"]) ?>
					</div>
					<div class="col-sm-3">
						<select class="form-control" ng-model="change_mode" ng-change="changeMode()">
							<option value="2">ученики без групп</option>
							<option value="1">ученики</option>
						</select>
					</div>
				</div>
				
				<div class="center half-black small" style="margin: 50px 0 40px" ng-hide="Groups2">
					формирование групп...
				</div>
				
				<div ng-repeat="Group in Groups2 | filter:groupsFilter2" ng-show="Groups2" class="ng-hide group-list-2" 
					ng-class="{'mt10': !$first, 'last': Group.Students.length == 0}" data-index="{{$index}}" id="group-index-{{$index}}">
	<!--		
					<h5>
						<span ng-bind-html="Group.branch_svg | to_trusted"></span>, {{Group.grade}} класс, {{Subjects[Group.subject]}}
					</h5>
	-->
					<table class="table table-divlike">
						<tbody>
							<tr ng-repeat="Student in Group.Students" class="student-line is-draggable"
								data-group-index="{{$parent.$index}}" data-student="{{Student}}" data-id="{{Student.id}}">
	<!-- 							<td width="50"></td> -->
								<td width="300">
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
				<div ng-show="(Groups2 | filter:groupsFilter2).length == 0" class="center half-black small" style="margin: 30px 0 15px">не найдено групп, соответствующих запросу</div>
		</div>
	</div>
	<?php endif ?>
</div>
