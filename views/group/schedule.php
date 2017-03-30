<div ng-app="Schedule" ng-controller="MainCtrl" ng-init="<?= $ang_init_data ?>">
<div class="panel panel-primary">
	<div class="panel-heading">
		Расписание группы №<?= $Group->id ?>

			<span ng-show="Group.past_lesson_count" style="margin-bottom: 20px">
				({{Group.schedule_count.paid}}<span ng-show='Group.schedule_count.free'>+{{Group.schedule_count.free}}</span>
				<ng-pluralize count="Group.schedule_count.paid" when="{'one': 'занятие','few': 'занятия','many': 'занятий'}"></ng-pluralize>, прошло {{Group.past_lesson_count}} <ng-pluralize count="Group.past_lesson_count" when="{
					'one': 'занятие',
					'few': 'занятия',
					'many': 'занятий'
				}"></ng-pluralize>)</span>

		<span class="link-reverse small pointer" onclick="redirect('groups/edit/<?= $Group->id ?>')">вернуться в группу</span>
		<div class="pull-right">
			<span class="link-reverse pointer" ng-click="setParamsFromGroup(Group)" ng-show="Group.Schedule.length">
				установить время занятий, филиал и кабинет из настроек группы
			</span>
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<div class="row">
			<div class="col-sm-6" style="position: relative">
                <?= globalPartial('calendar') ?>
			</div>
			<div class="col-sm-6">
				<h3 style="font-weight: bold; margin: 10px 0 25px">{{ countNotCancelled(Group.Schedule) }} <ng-pluralize count="countNotCancelled(Group.Schedule)" when="{
						'one': 'занятие',
						'few': 'занятия',
						'many': 'занятий'
					}"></ng-pluralize></h3>

				<table class="table table-divlike">
					<tr ng-repeat="Schedule in Group.Schedule | orderBy:'date'" style="height: 30px"
                        ng-class="Schedule.title ? 'students-11' : '';"
                        ng-attr-title="{{Schedule.title || undefined}}">
						<td>
							<span class="text-gray" ng-show='Schedule.cancelled'>{{ formatDate(Schedule.date) }}</span>
							<a href='lesson/{{ Schedule.id }}' ng-hide='Schedule.cancelled'>{{ formatDate(Schedule.date) }}</a>
						</td>
						<td>
							<div class="lessons-table" ng-show="!Schedule.was_lesson">
                                {{ Schedule.time }}
							</div>
							<div class="lessons-table" ng-show="Schedule.was_lesson">
								{{ getPastLesson(Schedule.date).lesson_time }}
							</div>
						</td>
						<td>
                            <div ng-show="Schedule.was_lesson">
                                {{ getCabinet(getPastLesson(Schedule.date).cabinet).label }}
                            </div>
                            <div ng-show="!Schedule.was_lesson">
                                {{ getCabinet(Schedule.cabinet).label }}
                            </div>
						</td>
						<td>
							<span ng-show='Schedule.is_free'>бесплатное</span>
						</td>
                        <td style='text-align: right'>
                            <?php if (allowed(Shared\Rights::EDIT_GROUP_SCHEDULE)) :?>
                            <span ng-show="!Schedule.was_lesson && !Schedule.cancelled">
                                <span class='link-like link-offset-right' ng-click='scheduleModal(Schedule)'>редактировать</span>
                                <span class='link-like red' ng-click='deleteSchedule(Schedule)'>удалить</span>
                            </span>
                            <?php endif ?>
                        </td>
					</tr>
				</table>
                <span class='link-like' ng-click='scheduleModal()'>добавить</span>
			</div>
		</div>
	</div>
</div>

<!-- schedule modal -->
<div id="schedule-modal" class="modal" role="dialog" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title center">{{ modal_schedule.id ? 'Редактировать занятие' : 'Добавить занятие' }}</h4>
      </div>
      <div class="modal-body">
          <div class="form-group">
              <div class="input-group custom" style="position: relative">
                  <span class="input-group-addon">дата занятия - </span>
                  <input class="form-control bs-date-clear pointer" readonly ng-model="modal_schedule.date">
              </div>
          </div>
          <div class="form-group">
              <div class="input-group custom" style="position: relative">
                  <span class="input-group-addon">время занятия - </span>
                  <input type="text" class="form-control timemask" ng-model="modal_schedule.time">
              </div>
          </div>
          <div class="form-group">
              <select class='form-control full-width branch-cabinet' ng-model='modal_schedule.cabinet'>
                  <option selected value=''>кабинет</option>
                  <option disabled>──────────────</option>
                  <option ng-repeat='cabinet in all_cabinets' value="{{ cabinet.id }}">{{ cabinet.label}}</option>
              </select>
          </div>
          <div class="form-group">
              <input type="checkbox" ng-true-value="1" ng-false-value="0" ng-model="modal_schedule.is_free">
              бесплатное занятие
          </div>
          <div class="form-group">
              <input type="checkbox" ng-true-value="1" ng-false-value="0" ng-model="modal_schedule.cancelled">
              отмененное занятие
          </div>
      </div>
      <div class="modal-footer center">
        <button type="button" class="btn btn-primary" ng-click="saveSchedule()"
            ng-disabled="!modal_schedule.date || !modal_schedule.time || !modal_schedule.cabinet">
            {{ modal_schedule.id ? 'редактировать' : 'добавить' }}</button>
      </div>
    </div>
  </div>
</div>

</div>
