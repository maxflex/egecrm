<div ng-app="Group" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>">
<div class="panel panel-primary">
	<div class="panel-heading">
		<?= $Group->id ? "Группа {$Group->id} " . ($Group->is_special ? "(спецгруппа)" : "") : "Добавление группы" ?>
		<div class="pull-right">		
			<span class="link-like link-reverse link-white" ng-click="addGroupsPanel()" style="margin-right: 12px">
					похожие группы</span>							
			<a class="link-reverse" target="_blank" style="margin-right: 12px"
				href="requests/relevant?subject={{Group.id_subject}}&branch={{Group.id_branch}}&grade={{Group.grade}}">
					релевантные заявки</a>
			<span class="link-like link-reverse link-white" ng-click="emailDialog()" style="margin-right: 12px">
					групповое сообщение</span>
			<span class="link-reverse pointer" ng-click="deleteGroup(Group.id)" ng-show="Group.id">удалить группу</span>
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<div id="frontend-loading" style="display: block">Загрузка...</div>
		<form id="group-edit" autocomplete='off'>
			<div class="row">
				<div class="col-sm-9">
					
					<table class="table table-divlike table-students">
						<tr ng-repeat="Student in TmpStudents" class="student-line is-draggable"  data-id="{{Student.id}}">
							<td width="250">
								{{$index + 1}}. <a href="student/{{Student.id}}">
									{{Student.first_name}}
									{{Student.last_name}}
								</a>
							</td>
							<td width="75">
								{{Student.grade}} класс
							</td>
							<td width="50">
								<b>{{Student.Contract.subjects[Group.id_subject].score}}</b>
							</td>
							<td>
								<span ng-show="Student.Contract.cancelled == 1" class="half-black">расторгнут</span>
							</td>
							<td width="120">
								<span ng-repeat="(id_branch, short) in Student.branch_short track by $index" 
										ng-bind-html="short | to_trusted" ng-class="{'mr3' : !$last}"></span>
							</td>
							<td style="width: 150px !important">
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
							<td width="150" title="Актуальность: {{Student.schedule_date ? Student.schedule_date : 'не установлено'}}">
								<span ng-repeat="weekday in weekdays" class="group-freetime-block">
									<span class="freetime-bar" ng-repeat="time in weekday.schedule track by $index" 
										ng-class="{
											'empty'				: !inFreetime(time, Student, $parent.$index + 1),
											'red-gray-empty' 	: !inFreetime(time, Student, $parent.$index + 1) && justInDayFreetime($parent.$index + 1, time, Student.freetime_red_half),
											'red-gray' 			: inFreetime(time, Student, $parent.$index + 1) && justInDayFreetime($parent.$index + 1, time, Student.freetime_red_half),
											'red'				: inRedFreetime(time, Student, $parent.$index + 1),
											'orange-emptygray'  : justInDayFreetime($parent.$index + 1, time, Student.freetime_orange)
																	&& !inFreetime(time, Student, $parent.$index + 1),
											'orange-gray' 		: justInDayFreetime($parent.$index + 1, time, Student.freetime_orange)
																	&& !(!inFreetime(time, Student, $parent.$index + 1)),
											'orange' 			: justInDayFreetime($parent.$index + 1, time, Student.freetime_orange_full),
											'blink'				: (inDayAndTime2(time, Group.day_and_time[$parent.$index + 1]) || Group.cabinet == 0)
																	&& justInDayFreetime($parent.$index + 1, time, Student.freetime_red_half)
																	|| ( (inDayAndTime2(time, Group.day_and_time[$parent.$index + 1]) || Group.cabinet == 0)
																	&& (justInDayFreetime($parent.$index + 1, time, Student.freetime_orange) || justInDayFreetime($parent.$index + 1, time, Student.freetime_orange_full)) )
										}" ng-hide="time == ''">
									</span>
								</span>
							</td>
						</tr>
						<tr>
							<td colspan="6"></td>
							<td width="150">
							    <span ng-repeat="weekday in weekdays" class="group-freetime-block" ng-show="Group.cabinet && Group.cabinet != '0'">
									<span class="freetime-bar green" ng-repeat="time in weekday.schedule track by $index" 
										ng-class="{
											'empty-green'	: !inDayAndTime2(time, Group.day_and_time[$parent.$index + 1]) || Group.cabinet == 0,
											'red'			: (!inDayAndTime2(time, Group.day_and_time[$parent.$index + 1]) || Group.cabinet == 0) 
																&& inCabinetFreetime(time, cabinet_freetime[$parent.$index + 1]),
											'red-green'		: !(!inDayAndTime2(time, Group.day_and_time[$parent.$index + 1]) || Group.cabinet == 0) 
																&& (inCabinetFreetime(time, cabinet_freetime[$parent.$index + 1]))
										}" ng-hide="time == ''" style="position: relative; top: 3px">
									</span>
								</span>
							</td>
						</tr>
						<tr ng-show="Group.id_teacher">
							<td width="250">
								{{getTeacher(Group.id_teacher).last_name}} {{getTeacher(Group.id_teacher).first_name}} {{getTeacher(Group.id_teacher).middle_name}}
							</td>
							<td colspan="4">
								<span style="margin-right: 5px">
									<a href="teachers/edit/{{Group.id_teacher}}" target="_blank">ЕЦ</a>
								</span>
								<a href="https://crm.a-perspektiva.ru/repetitors/edit/?id={{getTeacher(Group.id_teacher).id_a_pers}}" 
									target="_blank">ЕР</a>
							</td>
							<td  style="width: 150px !important">
								<span class="label group-student-status{{Group.teacher_status}} t-s-s teacher-status-span-{{Group.id_teacher}}"
									ng-click="setTeacherStatus(getTeacher(Group.id_teacher), $event)">
									{{Group.teacher_status ? GroupTeacherStatuses[Group.teacher_status] : "статус"}}
								</span>
								<select ng-model="getTeacher(Group.id_teacher).id_status" class="teacher-status-select-{{Group.id_teacher}}" 
									style="display: none; width: 150px" data-id="{{Group.id_teacher}}">
										<option selected value="">статус</option>
										<option disabled>──────────────</option>
										<option ng-repeat="(id_status, name) in GroupTeacherStatuses" ng-value="id_status"
											ng-selected="Group.teacher_status == id_status">{{name}}</option>
								</select>
							</td>
							<td width="150" title="Актуальность: {{getTeacher(Group.id_teacher).schedule_date ? getTeacher(Group.id_teacher).schedule_date : 'не установлено'}}">
							    <span ng-repeat="weekday in weekdays" class="group-freetime-block"  ng-show="Group.id_teacher && Group.id_teacher != '0'">
									<span class="freetime-bar blue" ng-repeat="time in weekday.schedule track by $index" 
										ng-class="{
											'empty-blue'	: !inDayAndTime2(time, teacher_freetime_green[$parent.$index + 1]) || Group.cabinet == 0,
											'red-blue-empty': (!inDayAndTime2(time, teacher_freetime_green[$parent.$index + 1]) || Group.cabinet == 0) 
																	&& justInDayFreetime($parent.$index + 1, time, teacher_freetime),
											'red-blue' 		: !(!inDayAndTime2(time, teacher_freetime_green[$parent.$index + 1]) || Group.cabinet == 0) 
																	&& justInDayFreetime($parent.$index + 1, time, teacher_freetime),
											'red'			: justInDayFreetime($parent.$index + 1, time, teacher_freetime_red),
											'orange-emptyblue' : justInDayFreetime($parent.$index + 1, time, teacher_freetime_orange_half)
																	&& (!inDayAndTime2(time, teacher_freetime_green[$parent.$index + 1]) || Group.cabinet == 0),
											'orange-blue' 	: justInDayFreetime($parent.$index + 1, time, teacher_freetime_orange_half)
																	&& !(!inDayAndTime2(time, teacher_freetime_green[$parent.$index + 1]) || Group.cabinet == 0),
											'orange' 		: justInDayFreetime($parent.$index + 1, time, teacher_freetime_orange_full),
											'blink'			: (inDayAndTime2(time, Group.day_and_time[$parent.$index + 1]) || Group.cabinet == 0)
																	&& justInDayFreetime($parent.$index + 1, time, teacher_freetime)
																|| ( (inDayAndTime2(time, Group.day_and_time[$parent.$index + 1]) || Group.cabinet == 0)
																	&& (justInDayFreetime($parent.$index + 1, time, teacher_freetime_orange_half) || justInDayFreetime($parent.$index + 1, time, teacher_freetime_orange_full)) )
										}" ng-hide="time == ''" style="position: relative; top: 3px">
										<!--
											'red'			: inCabinetFreetime(time, teacher_freetime[$parent.$index + 1]),
											'red-blue'		: !(!inDayAndTime2(time, teacher_freetime_green[$parent.$index + 1]) || Group.cabinet == 0)
																&& (inCabinetFreetime(time, teacher_freetime[$parent.$index + 1]))
-->
									</span>
								</span>
							</td>
						</tr>
					</table>
					<div style="margin: 15px 16px">
						<div class="link-like small link-reverse" ng-hide="!Group.id" style="display: inline-block; margin-right: 7px" 
								ng-click="addClientsPanel()">добавить ученика</div>
					</div>
					<img ng-hide="Students || !Group.id || true" src="img/svg/loading-bubbles.svg" style="margin: 15px 16px">
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						<select ng-model="Group.open" class="form-control">
							<option value="1">набор открыт</option>
							<option value="0">набор закрыт</option>
						</select>
					</div>
					<div class="form-group">
						<?= Subjects::buildSelector(false, false, [
							"ng-model" => "Group.id_subject", 
							"ng-change" => "subjectChange()",
							"ng-disabled" => "loading_students",
						]) ?>
					</div>
					<div class="form-group">
						<select class="form-control" ng-model="Group.id_teacher" ng-change="changeTeacher()">
							<option selected value="">преподаватель</option>
							<option disabled>──────────────</option>
							<option ng-repeat="Teacher in Teachers | filter:teachersFilter" value="{{Teacher.id}}" ng-selected="Teacher.id == Group.id_teacher">
								{{Teacher.last_name}} {{Teacher.first_name[0]}}. {{Teacher.middle_name[0]}}.
							</option>
						</select>
<!--
						<div class="small" style="text-align: right" ng-show="Group.id_teacher && Group.id_teacher != '0'">
							<a href="teachers/edit/{{Group.id_teacher}}" target="_blank">егэ-центр</a> | 
							<a href="https://crm.a-perspektiva.ru/repetitors/edit/?id={{getTeacher(Group.id_teacher).id_a_pers}}" 
								target="_blank">егэ-репетитор</a>
						</div>
-->
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
		            <div class="form-group">
							<select class="form-control" ng-model="Group.cabinet" ng-show="Group.id_branch" 
								ng-change="changeCabinet()" id="group-cabinet" ng-disabled="Cabinets.length == 1 && Group.cabinet">
								<option selected value="0">№ кабинета</option>
								<option disabled>──────────────</option>
								<option ng-repeat="Cabinet in Cabinets" 
									ng-value="Cabinet.id" ng-selected="Group.cabinet == Cabinet.id">Кабинет №{{Cabinet.number}}</option>
							</select>
		            </div>
		            <div class="form-group">
			            <input class="form-control bs-date" ng-model="Group.expected_launch_date" placeholder="ожидаемая дата запуска">
		            </div>
		            <div class="form-group">
			            <?php if ($Group->id): ?>
				            <a class="pull-left small link-reverse" style="margin-top: 5px" href="groups/edit/<?= $Group->id ?>/schedule">расписание</a>
				            <span class="pull-right small link-like link-reverse" style="margin-top: 5px" ng-click="dayAndTime()">день и время занятий</a>
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
		
		<?= partial("students_list") ?>
		
		<?= partial("groups_list") ?>
		
</div>
	<?= partial("day_and_time") ?>
</div>
</div>