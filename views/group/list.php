<div class="panel panel-primary" ng-app="Group" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<?= partial("create_helper") ?>
	<div class="panel-heading">
		Группы
		<div class="pull-right">
			<span class="link-like link-white link-reverse" ng-click="updateCache()" style="margin-right: 10px">обновить кеш</span>
			<span class="link-like link-white link-reverse" ng-click="createHelper()" style="margin-right: 10px">помощник при создании групп</span>
			<a href="groups/schedule/download" style="margin-right: 10px">скачать расписание</a>
			<a href='groups/add'>добавить группу</a>
		</div>
	</div>
	<div class="panel-body">
			<div class="row" style="position: relative">
				<div class="col-sm-12">

					<div class="row flex-list" style="margin-bottom: 15px">
						<div>
							<select class="watch-select single-select form-control" ng-model="search.grade" ng-change='filter()'>
								<option value=""  data-subtext="{{ counts.grade[''] || '' }}">все классы</option>
								<option disabled>──────────────</option>
								<option ng-repeat="(grade, label) in Grades | toArray" value="{{(grade + 1)}}" data-subtext="{{ counts.grade[grade] || '' }}">{{label}}</option>
							</select>
				        </div>
						<div>
							 <?= Branches::buildSvgSelector($search->id_branch, [
				                "id" => "group-branch-filter",
				                "class" => "watch-select",
				                "ng-model" => "search.id_branch",
				                "ng-change" => "filter()"
				            ]) ?>
						</div>
				        <div>
							<select id='subjects-select' class="watch-select form-control single-select" ng-model="search.id_subject" ng-change='filter()'>
								<option value="" data-subtext="{{ counts.subject[''] || '' }}">все предметы</option>
								<option disabled>──────────────</option>
								<option
									data-subtext="{{ counts.subject[id_subject] || '' }}"
									ng-repeat="(id_subject, name) in Subjects"
									value="{{id_subject}}">{{ name }}</option>
							</select>
						</div>
						<div>
							<select id='testy-select' class="watch-select single-select form-control" ng-model="search.id_teacher" ng-change='filter()'>
								<option value="">все преподаватели</option>
								<option disabled>──────────────</option>
								<option ng-repeat="Teacher in Teachers | filter:teachersFilter2"
									value="{{Teacher.id}}">{{ Teacher.last_name }} {{ Teacher.first_name }} {{ Teacher.middle_name }}</option>
							</select>
				        </div>
				        <div>
							<select id='subjects-select' class="watch-select form-control single-select" ng-model="search.cabinet" ng-change='filter()'>
								<option value="" data-subtext="{{ counts.cabinet[''] || '' }}">№ кабинета</option>
								<option disabled>──────────────</option>
								<option
									data-subtext="{{ counts.cabinet[id_subject] || '' }}"
									ng-repeat="Cabinet in Cabinets"
									value="{{Cabinet.id}}">{{Cabinet.number}}</option>
							</select>
						</div>
						<div>
							<?= Freetime::buildMultiSelector($search->time, [
								"id" => "time-select",
								"ng-model" 	=> "search.time",
								"ng-change"	=> "filter()"
							]) ?>
						</div>
				        <div id='year-fix'>
							<select class="watch-select single-select form-control" ng-model="search.year" ng-change='filter()'>
								<option value="" data-subtext="{{ counts.year[''] || '' }}">все годы</option>
								<option disabled>────────</option>
								<option ng-repeat="year in <?= Years::json() ?>"
									data-subtext="{{ counts.year[year] || '' }}"
									value="{{year}}">{{ yearLabel(year) }}</option>
							</select>
						</div>
					</div>

					<div ng-show="Groups === undefined" style="padding: 100px" class="small half-black center">
						загрузка групп...
					</div>
					<div ng-show="Groups === null" style="padding: 100px" class="small half-black center">
						нет групп
					</div>
					<?= globalPartial("groups_list", ["filter" => false]) ?>

					<div ng-show="Groups.length == 0" class="center half-black small" style="margin-bottom: 30px">список групп пуст</div>
				</div>
			</div>

			<div class="center" ng-hide="students_picker" style="margin: 10px 0">
				<span class="link-like small link-reverse" ng-click="loadStudentPicker()">подобрать учеников</span>
			</div>

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
						</div>

						<div class="center half-black small" style="margin: 50px 0 40px" ng-show="StudentsWithNoGroup === undefined">
							загрузка учеников...
						</div>

						<div class="center half-black small " style="margin: 50px 0 40px" ng-show="StudentsWithNoGroup === null">
							не найдено учеников без групп
						</div>

						<table class="table table-divlike">
							<tbody>
								<tr ng-repeat="Student in StudentsWithNoGroup" class="student-line is-draggable"
									data-group-index="{{$parent.$index}}" data-student="{{Student}}" data-id="{{Student.id}}">
									<td width="300">
										<a href="student/{{Student.id}}" ng-class="{
											'text-warning': Student.status == 2
										}">
										<span ng-show="Student.last_name || Student.first_name || Student.middle_name">{{Student.last_name}} {{Student.first_name}} {{Student.middle_name}}</span>
										<span ng-show="!Student.last_name && !Student.first_name && !Student.middle_name">Неизвестно</span>
										</a>
									</td>
									<td width="100">
										{{Student.id_contract}}
									</td>
									<td width="100">
										{{Student.grade}} класс
									</td>
									<td width="100">
										{{Student.date}}
									</td>
									<td width="100">
										<span ng-class="{'text-danger bold': Student.count > 40}">{{SubjectsShort[Student.id_subject]}}</span>
									</td>
									<td width="300">
										<span ng-repeat="(id_branch, short) in Student.branch_short track by $index"
											ng-bind-html="short | to_trusted" ng-class="{'mr3' : !$last}"></span>
									</td>
									<td style="text-align: right">
										<b ng-show="Student.score != ''">{{Student.score}}</b>
									</td>
								</tr>
							</tbody>
						</table>

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
											<a href="student/{{Student.id}}" ng-class="{
												'text-warning': getSubject(Student.Contract.subjects, Group.subject).status == 2
											}">
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
									</tr>
								</tbody>
							</table>
						</div>
						<div ng-show="(Groups2 | filter:groupsFilter2).length == 0" class="center half-black small" style="margin: 30px 0 15px">не найдено групп</div>
				</div>
			</div>
		</div>
	</div>
</div>
