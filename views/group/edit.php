<div ng-app="Group" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>">
	<div id="frontend-loading" style="display: block">Загрузка...</div>
	<div class="student-dragout ng-hide" ng-show="is_student_dragging">
		<span class="glyphicon glyphicon-trash"></span>
	</div>

<div class="panel panel-primary">
	<div class="panel-heading">
		<?= $Group->id ? "Группа {$Group->id} " . ($Group->is_special ? "(спецгруппа)" : "") : "Добавление группы" ?>
		<?php if ($Group->id) :?>
			<span ng-show="Group.schedule_count > 0 && !Group.past_lesson_count" style="margin-bottom: 20px">({{Group.schedule_count}} <ng-pluralize count="Group.schedule_count" when="{
				'one': 'занятие',
				'few': 'занятия',
				'many': 'занятий'
			}"></ng-pluralize> (<span ng-repeat="(day, day_data) in Group.day_and_time_2">{{weekdays[day - 1].short}}<span ng-repeat="dd in day_data"> в {{dd}}{{$last ? "" : ","}}</span>{{$last ? "" : " и "}}</span>), первое занятие {{Group.first_schedule | date:"d MMMM yyyy"}})</span>
					
					
			<span ng-show="Group.past_lesson_count" style="margin-bottom: 20px">({{Group.schedule_count}} <ng-pluralize count="Group.schedule_count" when="{
				'one': 'занятие',
				'few': 'занятия',
				'many': 'занятий'
			}"></ng-pluralize>, <span ng-repeat="(day, day_data) in Group.day_and_time_2">{{weekdays[day - 1].short}}<span ng-repeat="dd in day_data"> в {{dd}}{{$last ? "" : ","}}</span>{{$last ? "" : " и "}}</span>, прошло {{Group.past_lesson_count}} <ng-pluralize count="Group.past_lesson_count" when="{
					'one': 'занятие',
					'few': 'занятия',
					'many': 'занятий'
				}"></ng-pluralize>)</span>
				
			<span ng-show="!Group.schedule_count && !Group.past_lesson_count" style="margin-bottom: 20px">(расписание не установлено)</span>
		<?php endif ?>
		<div class="pull-right">		
			 <?php if ($Group->id): ?>
				<a style="margin-right: 12px" class="link-reverse" href="groups/journal/<?= $Group->id ?>">посещаемость</a>
	            <a style="margin-right: 12px" class="link-reverse" href="groups/edit/<?= $Group->id ?>/schedule">расписание</a>
	            <span style="margin-right: 12px" class="link-like link-reverse link-white" ng-click="dayAndTime()">день и время</span>
	        <?php endif ?>
			<span class="link-like link-reverse link-white" ng-click="addGroupsPanel()" style="margin-right: 12px">
					похожие группы</span>							
<!--
			<a class="link-reverse" target="_blank" style="margin-right: 12px"
				href="requests/relevant?subject={{Group.id_subject}}&branch={{Group.id_branch}}&grade={{Group.grade}}">
					релевантные заявки</a>
-->
			<span class="link-like link-reverse link-white" ng-click="smsDialog2(Group.id)">
					групповое сообщение</span>
<!-- 			<span class="link-reverse pointer" ng-click="deleteGroup(Group.id)" ng-show="Group.id">удалить группу</span> -->
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<form id="group-edit" autocomplete='off'>
			
		<div class="top-group-menu">
			<div>
	            <?= 
	                Branches::buildSvgSelector($Group->id_branch, [
		                "id"		=> "group-branch", 
		                "ng-model"	=> "Group.id_branch", 
		                "ng-change"	=> "changeBranch()",
	                ]) 
	            ?>
			</div>
			<div class="form-group">
				<select class="form-control" ng-model="Group.cabinet"
					ng-change="changeCabinet()" id="group-cabinet" ng-disabled="(Cabinets.length == 1 && Group.cabinet)">
					<option selected value="0">№ кабинета</option>
					<option disabled>──────────────</option>
					<option ng-repeat="Cabinet in Cabinets" ng-value="Cabinet.id" ng-class="{'quater-black': cabinetTimeBusy(Cabinet)}"
						ng-selected="Group.cabinet == Cabinet.id">{{Cabinet.number}}</option>
				</select>
            </div>
            <div class="form-group">
				<?= Subjects::buildSelector(false, false, [
					"ng-model" => "Group.id_subject", 
					"ng-change" => "subjectChange()",
				]) ?>
			</div>
			<div class="form-group">
                <?= Grades::buildSelector(false, false, ["ng-model" => "Group.grade"]) ?>
            </div>
			<div class="form-group">
				<select class="form-control" ng-model="Group.id_teacher" ng-change="changeTeacher()">
					<option selected value="">преподаватель</option>
					<option disabled>──────────────</option>
					<option ng-repeat="Teacher in Teachers | filter:teachersFilter" value="{{Teacher.id}}" ng-selected="Teacher.id == Group.id_teacher">
						{{Teacher.last_name}} {{Teacher.first_name[0]}}. {{Teacher.middle_name[0]}}.
					</option>
				</select>
			</div>
			 <div class="form-group">
	            <input class="form-control digits-only" 
	            	ng-model="Group.teacher_price" placeholder="цена преподавателя">
            </div>
            <div class="form-group">
	           <?= GroupLevels::buildSelector(false, false, [
		        	"ng-model" 		=> "Group.level",
		        ]) ?>
            </div>
		</div>
			
			<div class="row">
				<div class="col-sm-9">
					<table class="table table-divlike table-students" style="table-layout: fixed">
						<tr ng-repeat="Student in TmpStudents" class="student-line is-draggable"  data-id="{{Student.id}}">
							<td style="width: 120px !important">
								{{$index + 1}}. 
								<a href="student/{{Student.id}}" ng-class="{
									'text-warning'	: getSubject(Student.Contract.subjects, Group.id_subject).status == 1,
									'text-danger'	: Student.Contract.cancelled
								}">
									{{Student.first_name}}
									{{Student.last_name}}
								</a>
							</td>
							<td width="15">
								<svg class="review-status" style="top: 2px; width: 10px" ng-click="changeReviewStatus(Student.id)"
									ng-show="Student.already_had_lesson"
									ng-class="{
										'not-collected'	: !Student.review_status || Student.review_status == 0,
										'collected'		: Student.review_status == 1,
										'orange'		: Student.review_status == 2,
										'red'			: Student.review_status == 3,
									}">
									<circle r="3" cx="7" cy="7"></circle>
								</svg>
							</td>
							<td width="50">
								<?php if ($Group->getSchedule()) :?>
								<span class="glyphicon glyphicon-envelope gray-hover"  style="margin-right: 0 !important"
									ng-class="{'group-student-sms-sent': Student.notified}" ng-click="smsNotify(Student.id)"></span>
								<?php endif ?>
							</td>
							<td style="width: 70px !important">
								<span class="label group-student-status{{Student.id_status}} s-s-s student-status-span-{{Student.id}}"
									ng-click="setStudentStatus(Student, $event)">
									{{Student.id_status ? GroupStudentStatuses[Student.id_status] : "статус"}}
								</span>
								<select ng-model="Student.id_status" class="student-status-select-{{Student.id}}" 
									style="display: none; width: 150px" data-id="{{Student.id}}">
										<option selected value="">статус</option>
										<option disabled>──────────────</option>
										<option ng-repeat="(id_status, name) in GroupStudentStatuses" ng-value="id_status"
											ng-selected="Student.id_status == id_status">{{name}}</option>
								</select>
							</td>
							<td width="150">
								<span ng-repeat="weekday in weekdays" class="group-freetime-block">
									<span class="freetime-bar empty" ng-repeat="time in weekday.time track by $index" 
										ng-class="{
											'red-gray-empty' 	: justInDayFreetime($parent.$index + 1, time, Student.freetime_red_half),
											'red'				: inRedFreetime(time, Student, $parent.$index + 1),
											'orange-emptygray'  : justInDayFreetime($parent.$index + 1, time, Student.freetime_orange),
											'orange' 			: justInDayFreetime($parent.$index + 1, time, Student.freetime_orange_full),
											'blink'				: (inDayAndTime2(time, Group.day_and_time[$parent.$index + 1]) || Group.cabinet == 0)
																	&& justInDayFreetime($parent.$index + 1, time, Student.freetime_red_half)
																	|| ( (inDayAndTime2(time, Group.day_and_time[$parent.$index + 1]) || Group.cabinet == 0)
																	&& (justInDayFreetime($parent.$index + 1, time, Student.freetime_orange) || justInDayFreetime($parent.$index + 1, time, Student.freetime_orange_full)) ),
											'double-blink'		: justInDayFreetime($parent.$index + 1, time, Student.red_doubleblink)
										}" ng-hide="time == ''">
									</span>
								</span>
							</td>
						</tr>
						<tr>
							<td colspan="4"></td>
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
							<td colspan="2">
								<span style="margin-right: 5px">
									<a href="teachers/edit/{{Group.id_teacher}}" target="_blank">ЕЦ</a>
								</span>
								<a href="https://crm.a-perspektiva.ru/repetitors/edit/?id={{getTeacher(Group.id_teacher).id_a_pers}}" 
									target="_blank">ЕР</a>
							</td>
							<td  style="width: 150px !important">
								<span class="label group-teacher-status{{Group.teacher_status}} t-s-s teacher-status-span-{{Group.id_teacher}}"
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
							<td width="150">
							    <span ng-repeat="weekday in weekdays" class="group-freetime-block"  ng-show="Group.id_teacher && Group.id_teacher != '0'">
									<span class="freetime-bar empty-blue" ng-repeat="time in weekday.time track by $index" 
										ng-class="{
											'red-blue-empty'	: justInDayFreetime($parent.$index + 1, time, teacher_freetime),
											'red'				: justInDayFreetime($parent.$index + 1, time, teacher_freetime_red),
											'orange-emptyblue' 	: justInDayFreetime($parent.$index + 1, time, teacher_freetime_orange_half),
											'orange' 		: justInDayFreetime($parent.$index + 1, time, teacher_freetime_orange_full),
											'blink'			: (inDayAndTime2(time, Group.day_and_time[$parent.$index + 1]) || Group.cabinet == 0)
																	&& justInDayFreetime($parent.$index + 1, time, teacher_freetime)
																|| ( (inDayAndTime2(time, Group.day_and_time[$parent.$index + 1]) || Group.cabinet == 0)
																	&& (justInDayFreetime($parent.$index + 1, time, teacher_freetime_orange_half) || justInDayFreetime($parent.$index + 1, time, teacher_freetime_orange_full)) ),
											'double-blink'		: justInDayFreetime($parent.$index + 1, time, teacher_freetime_doubleblink)
										}" ng-hide="time == ''" style="position: relative; top: 3px">
									</span>
								</span>
							</td>
						</tr>
					</table>
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
			    	<button class="btn btn-primary save-button" ng-disabled="saving || !form_changed || !allStudentStatuses()" ng-hide="!Group.id" style="width: 100px">
			    		<span ng-show="form_changed">Сохранить</span>
			    		<span ng-show="!form_changed && !saving">Сохранено</span>
			    	</button>
			    	
			    	<button class="btn btn-primary save-button" ng-hide="Group.id" style="width: 100px">
						добавить
			    	</button>
			    	
				</div>
			</div>
		</form>
		
		<?= partial("groups_list") ?>
		
</div>
	<?= partial("day_and_time") ?>
</div>
</div>