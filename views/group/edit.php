<div ng-app="Group" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>">
<div class="panel panel-primary">
	<div class="panel-heading">
		<?= $Group->id ? "Группа {$Group->id} " . ($Group->is_special ? "(спецгруппа)" : "") : "Добавление группы" ?>
		<div class="pull-right">
			<span class="link-reverse pointer" ng-click="deleteGroup(Group.id)" ng-show="Group.id">удалить группу</span>
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<div id="frontend-loading" style="display: block">Загрузка...</div>
		<form id="group-edit" autocomplete='off'>
			<div class="row">
				<div class="col-sm-9">
					
					<table class="table table-divlike">
						<tr ng-repeat="Student in TmpStudents">
							<td width="300">
								{{$index + 1}}. <a href="student/{{Student.id}}">
									{{Student.last_name}}
									{{Student.first_name}}
									{{Student.middle_name}}
								</a>
							</td>
							<td>
								{{Student.grade}} класс
							</td>
							<td>
								<b>{{Student.Contract.subjects[Group.id_subject].score}}</b>
							</td>
							<td>
								<span ng-show="Student.Contract.cancelled == 1" class="half-black">расторгнут</span>
							</td>
							<td>
								<span ng-repeat="(id_branch, short) in Student.branch_short track by $index" 
										ng-bind-html="short | to_trusted" ng-class="{'mr3' : !$last}"></span>
							</td>
							<td style="width: 200px !important">
								<span class="label group-student-status{{Student.id_status}} s-s-s student-status-span-{{Student.id}}"
									ng-click="setStudentStatus(Student, $event)">
									{{Student.id_status ? GroupStudentStatuses[Student.id_status] : "статус"}}
								</span>
								<select ng-model="Student.id_status" class="student-status-select-{{Student.id}}" 
									style="display: none; width: 150px" data-id="{{Student.id}}">
										<option selected value="">статус</option>
										<option disabled>──────────────</option>
										<option ng-repeat="(id_status, name) in GroupStudentStatuses" ng-value="id_status"
											ng-selected="getStudent(id_student).id_status == id_status">{{name}}</option>
								</select>
							</td>
						</tr>	
					</table>
					<div style="margin: 15px 16px">
						<div ng-show="Students" class="link-like small link-reverse"  style="display: inline-block; margin-right: 7px" 
								ng-click="addClientsPanel()">добавить ученика</div>
								
						<a class="small link-reverse" target="_blank"
							href="requests/relevant?subject={{Group.id_subject}}&branch={{Group.id_branch}}&grade={{Group.grade}}">
								просмотр релевантных заявок
						</a>
					</div>
					<img ng-hide="Students || !Group.id || true" src="img/svg/loading-bubbles.svg" style="margin: 15px 16px">
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						<?= Subjects::buildSelector(false, false, [
							"ng-model" => "Group.id_subject", 
							"ng-change" => "loadStudents()",
							"ng-disabled" => "!Students && Group.id",
						]) ?>
					</div>
					<div class="form-group">
						<select class="form-control" ng-model="Group.id_teacher">
							<option selected>преподаватель</option>
							<option disabled>──────────────</option>
							<option ng-repeat="Teacher in Teachers | filter:teachersFilter" value="{{Teacher.id}}" ng-selected="Teacher.id == Group.id_teacher">
								{{Teacher.last_name}} {{Teacher.first_name[0]}}. {{Teacher.middle_name[0]}}.
							</option>
						</select>
					</div>
					<div class="form-group">
		                <?= 
			                Branches::buildSvgSelector($Group->id_branch, [
				                "id"		=> "group-branch", 
				                "ng-model"	=> "Group.id_branch", 
				                "ng-change"	=> "changeBranch()"
			                ]) 
			            ?>
		            </div>
					<div class="form-group">
		                <?= Grades::buildSelector(false, false, ["ng-model" => "Group.grade"]) ?>
		            </div>
		            <div class="form-group" style="display: inline-block; margin-bottom: 5px">
			            <div class="col-sm-6" style="padding: 0; padding-right: 5px">
			             <select class="form-control" ng-model="Group.day">
				            <option selected>день</option>
							<option disabled>──────────────</option>
							<option ng-repeat="(day_number, weekday) in weekdays" 
								ng-value="(day_number + 1)" ng-selected="(day_number + 1) == Group.day">{{weekday.short}}</option>
			            </select>
			            </div>
			            <div class="col-sm-6" style="padding: 0; padding-left: 5px">
							<input type="text" ng-model="Group.start" class="form-control timemask" placeholder="время" id="group-time">
			            </div>
		            </div>
		            <div class="form-group">
							<select class="form-control" ng-model="Group.cabinet" ng-show="Group.id_branch">
								<option selected>№ кабинета</option>
								<option disabled>──────────────</option>
								<option ng-repeat="Cabinet in Cabinets" 
									ng-value="Cabinet.id" ng-selected="Group.cabinet == Cabinet.id">{{Cabinet.number}}</option>
							</select>
		            </div>
		            <div class="form-group">
			            <input class="form-control bs-date" ng-model="Group.expected_launch_date" placeholder="ожидаемая дата запуска">
		            </div>
		            <div class="form-group">
			            <?php if ($Group->id): ?>
				            <a class="pull-right small link-reverse" style="margin-top: 5px" href="groups/edit/<?= $Group->id ?>/schedule">расписание</a>
				        <?php endif ?>
		            </div>
				</div>
			</div>
			<?php if ($Group->id): ?>
			<div class="row">
				<div class="col-sm-12">
					<div class="comment-block">
						<div id="existing-comments-{{Group.id}}">
							<div ng-repeat="comment in Group.Comments">
								<div id="comment-block-{{comment.id}}">
									<span class="glyphicon glyphicon-stop" style="float: left"></span>
									<div style="display: initial" id="comment-{{comment.id}}" commentid="{{comment.id}}" onclick="editComment(this)">{{comment.comment}}</div>
									<span class="save-coordinates">({{comment.coordinates}})</span>
									<span ng-attr-data-id="{{comment.id}}" 
										class="glyphicon opacity-pointer text-danger glyphicon-remove glyphicon-2px" onclick="deleteComment(this)"></span>
								</div>
							</div>
						</div>
						<div style="height: 25px">
							<span class="glyphicon glyphicon-forward pointer no-margin-right comment-add" id="comment-add-{{Group.id}}"
								place="<?= Comment::PLACE_GROUP ?>" id_place="{{Group.id}}"></span>
							<input class="comment-add-field" id="comment-add-field-{{Group.id}}" type="text"
								placeholder="Введите комментарий..." request="{{Group.id}}" data-place='GROUP_EDIT' >
						</div>
				    </div>
				</div>
			</div>
			<?php endif ?>
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

	<div ng-show="add_clients_panel" class="row">
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
								<span class="link-like small pull-right" ng-click="addStudent(Student.id, $event)" 
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
</div>
	
</div>

</div>