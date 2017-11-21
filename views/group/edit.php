<div ng-app="Group" ng-controller="EditCtrl" ng-init="<?= $ang_init_data ?>">
	<div id="fe-loading" class="frontend-loading" style="display: block">Загрузка...</div>
	<div class="panel panel-primary">
		<div class="panel-heading">
			<?= $Group->id ? "Группа {$Group->id}" : "Добавление группы" ?>
			<div class="pull-right">
				<?php if ($Group->id): ?>
					<a style="margin-right: 12px" class="link-reverse" href="groups/journal/<?= $Group->id ?>">посещаемость</a>
					<a style="margin-right: 12px" class="link-reverse" href="groups/edit/<?= $Group->id ?>/schedule"><span ng-show="Group.schedule_count.paid">{{Group.schedule_count.paid}}<span ng-show='Group.schedule_count.free'>+{{Group.schedule_count.free}}</span>
					<ng-pluralize count="Group.schedule_count.paid" when="{'one': 'занятие','few': 'занятия','many': 'занятий'}"></ng-pluralize></span><span ng-show="!Group.schedule_count.paid">установить расписание</span></a>
					<span style="margin-right: 12px" ng-click="dayAndTime()">
						<span class="link-like link-reverse link-white" ng-show='hasDayAndTime() && Group.is_dump == 0'>
							<span ng-repeat="(day, data) in Group.day_and_time">
								{{ weekdays[day] }} в <span ng-repeat='d in data'>{{ d.time.time }}{{$last ? '' : ', '}}</span>{{ $last ? '' : ', '}}
							</span>
						</span>
						<span class="link-like link-reverse link-white" ng-show='hasDayAndTime() && Group.is_dump == 1'>болото</span>
						<span class="link-like link-reverse link-white" ng-show='!hasDayAndTime()'>установить день и время</span>
					</span>
				<?php endif ?>
				<span class="link-like link-reverse link-white" ng-click="addGroupsPanel()" style="margin-right: 12px">
						похожие группы</span>
				<span class="link-like link-reverse link-white" ng-click="PhoneService.sms()">групповое SMS</span>
				<?php if (User::fromSession()->allowed(Shared\Rights::EDIT_GROUPS)) :?>
				<span style="margin-left: 12px" class="link-reverse pointer" ng-click="deleteGroup(Group.id)" ng-show="Group.id">удалить группу</span>
				<?php endif ?>
			</div>
		</div>
		<div class="panel-body" style="position: relative">
			<form id="group-edit" autocomplete='off'>
				<div class="top-group-menu-thin">
					<?php if (! allowed(Shared\Rights::EDIT_GROUPS)) :?>
					<div class='div-blocker'></div>
					<?php endif ?>
					<div class="form-group">
						<?= Subjects::buildSelector(false, false, [
							"ng-model" => "Group.id_subject",
							"ng-change" => "subjectChange()",
						], true) ?>
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
							ng-model="Group.teacher_price" ng-model-options="{debounce: 1000}" placeholder="цена преподавателя">
					</div>
					<div class="form-group">
					   <?= GroupLevels::buildSelector(false, false, [
							"ng-model" 		=> "Group.level",
						]) ?>
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
										<span ng-show="Student.Test && Student.Test.isFinished">({{ Student.Test.final_score }}%)</span>
									</td>
									<td>
										<span ng-hide="!enoughSmsParams() || Student.already_had_lesson >= 2">
											<span class="glyphicon glyphicon-envelope text-gray quater-opacity pointer" ng-click="smsNotify(Student, $event)" ng-hide="Student.sms_notified"></span>
											<span class="glyphicon glyphicon-envelope text-gray default" ng-show="Student.sms_notified"></span>
										</span>
									</td>
									<td>
										<span ng-repeat="branch in Student.branches">
											<span class="link-like" ng-click="gmap(Student)" style='color: {{ Branches[branch].color }}; margin-right: 3px'>{{ Branches[branch].short }}</span>
										</span>
										<span class="text-gray">({{ Student.markers.length }})</span>
									</td>
									<td>
										<span ng-show='Student.already_had_lesson'>
											<span class="review-small" ng-class="{
												'bg-red': Student.teacher_like_status <= 3,
												'bg-orange': Student.teacher_like_status == 4,
												'gray': Student.teacher_like_status == 6
											}" ng-if='Student.teacher_like_status'>{{ Student.teacher_like_status == '6' ? '0' : Student.teacher_like_status }}</span>
											<span class="review-small gray" ng-if="!Student.teacher_like_status">?</span>
										</span>
									</td>
									<td width="380">
										<span ng-repeat="(day, data) in Student.bar" class="group-freetime-block">
											<span ng-repeat="bar in data track by $index" class="bar {{bar}}"></span>
										</span>
									</td>
								</tr>
								<tr ng-show="Group.id_teacher" class="increased-padding">
									<td width="380" colspan="4">
										Преподаватель: <a href="teachers/edit/{{Group.id_teacher}}" target="_blank">{{getTeacher(Group.id_teacher).last_name}} {{getTeacher(Group.id_teacher).first_name}} {{getTeacher(Group.id_teacher).middle_name}}</a>
									</td>
									<td width="380">
									   <span ng-repeat="(day, data) in getTeacher(Group.id_teacher).bar" class="group-freetime-block">
											<span ng-repeat="bar in data track by $index" class="bar {{bar}}"></span>
										</span>
									</td>
								</tr>
								<tr ng-repeat="(id_cabinet, cabinet_bar) in cabinet_bars" ng-show='id_cabinet > 0'>
									<td colspan="4">Загрузка кабинета <span style='color: {{ getCabinet(id_cabinet).color }}'>{{ getCabinet(id_cabinet).label }}</span></td>
									<td width="380">
										<span ng-repeat="(day, data) in cabinet_bar" class="group-freetime-block">
											<span ng-repeat="bar in data track by $index" class="bar {{bar}}"></span>
										</span>
									</td>
								</tr>
								<tr>
									<td colspan="4">Загрузка группы</td>
									<td width="380">
										<span ng-repeat="(day, data) in Group.bar" class="group-freetime-block">
											<span ng-repeat="bar in data track by $index" class="bar {{bar}}"></span>
										</span>
									</td>
								</tr>
								<tr>
									<td colspan="5">
										Готова к запуску: <span class="link-like" ng-click="toggleBoolean('ready_to_start')"> {{ Group.ready_to_start ? 'да' : 'нет' }}</span>
									</td>
								</tr>
								<tr>
									<td colspan="5">
										Договор подписан: <span <?php if (allowed(Shared\Rights::EC_EDIT_GROUP_CONTRACT)) :?>class="link-like" ng-click="toggleBoolean('contract_signed')"<?php endif ?>> {{ Group.contract_signed ? 'да' : 'нет' }}</span>
									</td>
								</tr>
							</table>
						</div>
					</div>
				<?php if ($Group->id): ?>
				<div class="row">
					<div class="col-sm-12">
						<comments entity-id="Group.id" entity-type="GROUP" user="user"></comments>
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
			<?= partial("groups_list", ['teacher_comment' => true]) ?>
		</div>
		<?= partial("day_and_time") ?>
	</div>
	<sms templates="short" mode="group" mass="1" group-id="Group.id"></sms>
</div>

<!-- ЛАЙТБОКС КАРТА -->
<div class="lightbox-element lightbox-map">
	<div id='gmap'></div>
</div>
<!-- КОНЕЦ /КАРТА И ЛАЙТБОКС -->

<style>
	.bootstrap-select.btn-group .btn .filter-option {
		white-space: nowrap !important;
	}
	.increased-padding td {
		padding-bottom: 20px !important;
	}
	#gmap {
		height: 100%;
		width: 100%;
	}
</style>
