<div ng-app="Group" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>">
<div class="panel panel-primary">
	<div class="panel-heading">
		<?= $Group->id ? "Группа {$Group->id}" : "Добавление группы" ?>
		<div class="pull-right">
			<span class="link-reverse pointer" ng-click="deleteGroup(Group.id)" ng-show="Group.id">удалить группу</span>
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<div id="frontend-loading" style="display: block">Загрузка...</div>
		<form id="group-edit" autocomplete='off'>
			<div class="row">
				<div class="col-sm-9">
					<div ng-repeat="id_student in Group.students">
						{{$index + 1}}. <a href="student/{{id_student}}">{{getStudent(id_student)}}</a>
					</div>
					<div class="link-like small link-reverse" style="margin: 15px 16px" ng-click="add_clients_panel = !add_clients_panel">добавить ученика</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						<?= Subjects::buildSelector(false, false, ["ng-model" => "Group.id_subject"]) ?>
					</div>
					<div class="form-group">
						<select class="form-control" ng-model="Group.id_teacher">
							<option selected>преподаватель</option>
							<option disabled>──────────────</option>
							<option ng-repeat="Teacher in Teachers" value="{{Teacher.id}}" ng-selected="Teacher.id == Group.id_teacher">
								{{Teacher.last_name}} {{Teacher.first_name[0]}}. {{Teacher.middle_name[0]}}.
							</option>
						</select>
					</div>
					<div class="form-group">
		                <?= Branches::buildSvgSelector($Group->id_branch, ["id" => "group-branch", "ng-model" => "Group.id_branch"]) ?>
		            </div>
					<div class="form-group">
		                <?= Grades::buildSelector(false, false, ["ng-model" => "Group.grade"]) ?>
		            </div>
		            <div class="form-group">
			            <div class="col-sm-6" style="padding: 0; padding-right: 5px">
			             <select class="form-control" ng-model="Group.day">
				            <option selected value=''>день</option>
							<option disabled value=''>──────────────</option>
							<option ng-repeat="(day_number, weekday) in weekdays"
								ng-value="(day_number + 1)" ng-selected="day_number == Group.day">{{weekday.short}}</option>
			            </select>
			            </div>
			            <div class="col-sm-6" style="padding: 0; padding-left: 5px">
							<?=
							   Html::time([
								   	"id"			=> "grade-start-time",
									"class"			=> "form-control",
									"placeholder"	=> "время",
									"ng-model"		=> "Group.start"
				               ]);
				            ?>
			            </div>
		            </div>
		            <div class="form-group">
			            <a class="pull-right small link-reverse" style="margin-top: 5px" href="groups/edit/<?= $Group->id ?>/schedule">расписание</a>
		            </div>
				</div>
			</div>

			<div class="row" style="margin-top: 10px">
				<div class="col-sm-12 center">
			    	<button class="btn btn-primary save-button" ng-disabled="saving || !form_changed" ng-hide="!Group.id" style="width: 100px">
			    		<span ng-show="form_changed">Сохранить</span>
			    		<span ng-show="!form_changed && !saving">Сохранено</span>
			    	</button>

			    	<button class="btn btn-primary save-button" ng-hide="Group.id" style="width: 100px">
						добавить
			    	</button>

				</div>
			</div>
		</form>
	</div>
</div>

<div class="panel panel-primary ng-hide" ng-show="add_clients_panel">
	<div class="panel-heading">
		Добавить ученика к группе
	</div>
	<div class="panel-body">
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
                        {{ Student.Contract.grade_label }}
					</td>
					<td>
						{{Student.Contract.date}}
					</td>
					<td>
						{{countSubjects(Student.Contract)}}
						<ng-pluralize count="countSubjects(Student.Contract)" when="{
							'one' : 'предмет',
							'few' : 'предмета',
							'many': 'предметов'
						}">
					</td>
					<td>
						<span ng-repeat="branch_svg in Student.branch_svg" ng-bind-html="branch_svg | to_trusted"></span>
					</td>
					<td>
						<div ng-repeat="subject in Student.Contract.subjects" ng-show="subject.score != ''">
							<b ng-show="subject.id_subject == search.id_subject">{{subject.score}}</b>
						</div>
					<td>
						<span class="link-like small pull-right" ng-click="addStudent(Student.id)" ng-hide="studentAdded(Student.id)">добавить</span>
						<span class="link-like small pull-right red" ng-click="removeStudent(Student.id)" ng-show="studentAdded(Student.id)">удалить</span>
					</td>
				</tr>
			</tbody>
		</table>
		<div ng-show="(Students | filter:clientsFilter).length == 0" class="center half-black small" style="margin-bottom: 15px">не найдено учеников, соответствующих запросу</div>
	</div>
</div>

</div>
