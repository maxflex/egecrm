<div ng-app="Group" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>">
	<div id="frontend-loading" style="display: block">Загрузка...</div>
<!--
	<div class="student-dragout ng-hide" ng-show="is_student_dragging">
		<span class="glyphicon glyphicon-trash"></span>
	</div>
-->

<div class="panel panel-primary">
	<div class="panel-heading">
		<?= $Group->id ? "Группа {$Group->id} " . ($Group->is_special ? "(спецгруппа)" : "") : "Добавление группы" ?>
		<?php if ($Group->id) :?>
			<span ng-show="Group.schedule_count.paid > 0 && !Group.past_lesson_count" style="margin-bottom: 20px">({{Group.schedule_count.paid}}<span ng-show='Group.schedule_count.free'>+{{Group.schedule_count.free}}</span> (<span ng-repeat="(day, day_data) in Group.day_and_time_2">{{weekdays[day - 1].short}}<span ng-repeat="dd in day_data"> в {{dd}}{{$last ? "" : ","}}</span>{{$last ? "" : " и "}}</span>), первое занятие {{Group.first_schedule | date:"d MMMM yyyy"}})</span>


			<span ng-show="Group.past_lesson_count" style="margin-bottom: 20px">
				({{Group.schedule_count.paid}}<span ng-show='Group.schedule_count.free'>+{{Group.schedule_count.free}}</span>
				<ng-pluralize count="Group.schedule_count.paid" when="{'one': 'занятие','few': 'занятия','many': 'занятий'}"></ng-pluralize>,
				<span ng-repeat="(day, day_data) in Group.day_and_time_2">{{weekdays[day - 1].short}}<span ng-repeat="dd in day_data"> в {{dd}}{{$last ? "" : ","}}</span>{{$last ? "" : " и "}}</span>, прошло {{Group.past_lesson_count}} <ng-pluralize count="Group.past_lesson_count" when="{
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
			<span class="link-like link-reverse link-white" ng-click="smsDialog2(Group.id)">групповое SMS</span>

			<span style="margin-left: 12px" class="link-reverse pointer" ng-click="deleteGroup(Group.id)" ng-show="Group.id">удалить группу</span>
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<form id="group-edit" autocomplete='off'>

		<div class="top-group-menu-thin">
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
                <?= Grades::buildSelector(false, false, ["ng-model" => "Group.grade", "ng-change" => "reloadTests()"]) ?>
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
            <div class="form-group">
	           <select class="form-control" ng-model="Group.duration" ng-options="min as min + ' мин.' for min in duration">
	           </select>
            </div>
			<div class="form-group">
				<select class="form-control"
					ng-model="Group.year"
					ng-change="changeYear()"
					ng-options="year as year + '-' + (year + 1) + ' уч. г.' for year in <?= Years::json() ?>">
				</select>
			</div>
			<div class="form-group">
				<select class="form-control" ng-model="Group.ended">
					<option value="0">активная</option>
					<option value="1">занятия завершены</option>
				</select>
			</div>
            <span ng-show="is_student_dragging" class="student-dragout ng-hide">удалить</span>
		</div>

			<div class="row">
				<div class="col-sm-12">
					<table class="table table-divlike table-students" style="table-layout: fixed">
						<tr ng-repeat="Student in TmpStudents">
							<td>
								{{$index + 1}}.
								<a class="student-line is-draggable"  data-id="{{Student.id}}" href="student/{{Student.id}}" ng-class="{
									'text-warning'	: getSubject(Student.Contract.subjects, Group.id_subject).status == 2,
									'text-danger'	: getSubject(Student.Contract.subjects, Group.id_subject).status == 1
								}">
									{{Student.first_name}}
									{{Student.last_name}}
								</a>
							</td>
							<td>
								<span ng-show='Student.already_had_lesson'>
									<span class="review-small" ng-class="{
										'bg-red': Student.teacher_like_status <= 3,
										'bg-orange': Student.teacher_like_status == 4
									}" ng-if='Student.teacher_like_status'>{{ Student.teacher_like_status }}</span>
									<span class="review-small gray" ng-if="!Student.teacher_like_status">?</span>
									<!-- <span class="text-success"	ng-show="Student.teacher_like_status == 1">нравится</span>
									<span class="text-warning" 	ng-show="Student.teacher_like_status == 2">средне</span> -->
									<!-- <span class="text-danger" ng-if="Student.teacher_like_status == 3">не нравится</span> -->
								</span>
							</td>
							<td>
								<span ng-show="Student.Test">
									<span ng-show="Student.Test.notStarted" class="quater-black">к тесту не приступал</span>
									<span ng-show="Student.Test.inProgress" class="quater-black">тест в процессе</span>
									<span ng-show="Student.Test.isFinished">{{ Student.Test.final_score }} <ng-pluralize count="Student.Test.final_score" when="{
										'one': 'балл',
										'few': 'балла',
										'many': 'баллов'
									}"></ng-pluralize></span>
								</span>
							</td>
							<td>
								<span ng-hide="Student.already_had_lesson >= 2">
									<span class="half-black pointer" ng-click="smsNotify(Student, $event)" ng-hide="Student.sms_notified">отправить смс</span>
									<span class="text-success default" ng-show="Student.sms_notified">смс отправлено</span>
								</span>
							</td>
							<td width="150">
								<span ng-repeat="(day, data) in Student.bar" class="group-freetime-block">
									<span ng-repeat="bar in data" class="bar {{bar}}"></span>
								</span>
							</td>
						</tr>
						<tr>
							<td colspan="4"></td>
							<td width="150">
							    <span ng-repeat="(day, data) in cabinet_bar" class="group-freetime-block">
									<span ng-repeat="bar in data" class="bar {{bar}}"></span>
								</span>
							</td>
						</tr>
						<tr ng-show="Group.id_teacher">
							<td width="250">
								{{getTeacher(Group.id_teacher).last_name}} {{getTeacher(Group.id_teacher).first_name}} {{getTeacher(Group.id_teacher).middle_name}}
							</td>
							<td colspan="3">
								<span style="margin-right: 5px">
									<a href="teachers/edit/{{Group.id_teacher}}" target="_blank">ЕЦ</a>
								</span>
								<a href="https://crm.a-perspektiva.ru/repetitors/edit/?id={{getTeacher(Group.id_teacher).id_a_pers}}"
									target="_blank">ЕР</a>
							</td>
							<td width="150">
							   <span ng-repeat="(day, data) in getTeacher(Group.id_teacher).bar" class="group-freetime-block">
									<span ng-repeat="bar in data" class="bar {{bar}}"></span>
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
									<span style="color: {{comment.User.color}}" class="comment-login">{{comment.User.login}}: </span>
									<div style="display: initial" id="comment-{{comment.id}}" commentid="{{comment.id}}" onclick="editComment(this)">{{comment.comment}}</div>
									<span class="save-coordinates">{{comment.coordinates}}</span>
									<span ng-attr-data-id="{{comment.id}}"
										class="glyphicon opacity-pointer text-danger glyphicon-remove glyphicon-2px" onclick="deleteComment(this)"></span>
								</div>
							</div>
						</div>
						<div style="height: 25px">
							<span class="pointer no-margin-right comment-add" id="comment-add-{{Group.id}}"
								place="<?= Comment::PLACE_GROUP ?>" id_place="{{Group.id}}">комментировать</span>

							<span class="comment-add-hidden">
								<span class="comment-add-login comment-login" id="comment-add-login-{{Group.id}}" style="color: <?= User::fromSession()->color ?>"><?= User::fromSession()->login ?>: </span>
								<input class="comment-add-field" id="comment-add-field-{{Group.id}}" type="text"
									placeholder="введите комментарий..." request="{{Group.id}}" data-place='GROUP_EDIT' >
							</span>
						</div>
				    </div>
				</div>
			</div>
			<?php endif ?>
			<div class="row" style="margin-top: 10px">
				<div class="col-sm-12 center">
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
