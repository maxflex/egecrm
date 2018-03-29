<div ng-app="Schedule" ng-controller="MainCtrl" ng-init="<?= $ang_init_data ?>">
<div class="panel panel-primary">
	<div class="panel-heading">
		Расписание группы <a href="groups/edit/<?= $Group->id ?>">№<?= $Group->id ?></a>

			<span ng-show="Group.lesson_count.conducted" style="margin-bottom: 20px">
				({{Group.lesson_count.all}}
				<ng-pluralize count="Group.lesson_count.all" when="{'one': 'занятие','few': 'занятия','many': 'занятий'}"></ng-pluralize>, прошло {{ Group.lesson_count.conducted }} <ng-pluralize count="Group.lesson_count.conducted" when="{
					'one': 'занятие',
					'few': 'занятия',
					'many': 'занятий'
				}"></ng-pluralize>)</span>
	</div>
	<div class="panel-body" style="position: relative">
		<div class="row">
			<div class="col-sm-6" style="position: relative; width: 48%; margin-right: 2%">
                <?= globalPartial('calendar') ?>
			</div>
			<div class="col-sm-6" style='width: 48%; margin-left: 2%'>
				<h3 style="font-weight: bold; margin: 10px 0 25px">{{ countNotCancelled() }} <ng-pluralize count="countNotCancelled()" when="{
						'one': 'занятие',
						'few': 'занятия',
						'many': 'занятий'
					}"></ng-pluralize></h3>

				<table class="table table-divlike">
					<tr ng-repeat="Lesson in Lessons | orderBy:'date_time' track by $index">
						<td style="padding:2px 4px 2px 0px;">
							<span class="day-explain"
								  ng-class="{
									'was-lesson': Lesson.is_conducted,
									'cancelled': Lesson.cancelled
								  }"
							></span>
						</td>
						<td>
							<span class="text-gray" ng-show='Lesson.cancelled'>{{ formatDate(Lesson.lesson_date) }}</span>
							<a href='lesson/{{ Lesson.id }}' ng-hide='Lesson.cancelled'>{{ formatDate(Lesson.lesson_date) }}</a>
						</td>
						<td>
							{{ Lesson.lesson_time }}
						</td>
						<td>
							{{ getCabinet(Lesson.cabinet).label }}
						</td>
						<td>
							<span ng-show='Lesson.is_free'>бесплатное</span>
						</td>
						<td style='text-align: right'>
                            <?php if (allowed(Shared\Rights::EDIT_GROUP_SCHEDULE)) :?>
                            <span ng-show="Lesson.is_planned">
                                <span class='link-like link-offset-right' ng-click='lessonModal(Lesson)'>редактировать</span>
                                <span class='link-like red' ng-click='deleteLesson(Lesson)'>удалить</span>
                            </span>
                            <?php endif ?>
                        </td>
					</tr>
				</table>
                <span class='link-like smooth-font' ng-click='lessonModal()'>добавить</span>
				<span class='link-like smooth-font' style='margin-left: 25px' ng-click='duplicateLessons()'
					ng-show='Lessons && Lessons.length && Lessons[Lessons.length - 1].lesson_date && Lessons[Lessons.length - 1].cabinet && Lessons[Lessons.length - 1].lesson_time'>проставить занятия до 1 июня</span>
			</div>
		</div>
	</div>
</div>

<!-- schedule modal -->
<div id="schedule-modal" class="modal" role="dialog" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title center">{{ modal_lesson.id ? 'Редактировать занятие' : 'Добавить занятие' }}</h4>
      </div>
      <div class="modal-body">
          <div class="form-group">
              <div class="input-group custom" style="position: relative">
                  <span class="input-group-addon">дата занятия - </span>
                  <input class="form-control bs-date-clear pointer" readonly ng-model="modal_lesson.lesson_date">
              </div>
          </div>
          <div class="form-group">
              <div class="input-group custom" style="position: relative">
                  <span class="input-group-addon">время занятия - </span>
                  <input type="text" class="form-control timemask" ng-model="modal_lesson.lesson_time">
              </div>
          </div>
          <div class="form-group">
              <select class='form-control full-width branch-cabinet' ng-model='modal_lesson.cabinet'>
                  <option selected value=''>кабинет</option>
                  <option disabled>──────────────</option>
                  <option ng-repeat='cabinet in all_cabinets' value="{{ cabinet.id }}">{{ cabinet.label}}</option>
              </select>
          </div>
          <div class="form-group">
              <input type="checkbox" ng-true-value="1" ng-false-value="0" ng-model="modal_lesson.is_free">
              бесплатное занятие
          </div>
          <div class="form-group">
              <input type="checkbox" ng-true-value="1" ng-false-value="0" ng-model="modal_lesson.cancelled">
              отмененное занятие
          </div>
      </div>
      <div class="modal-footer center">
        <button type="button" class="btn btn-primary" ng-click="saveLesson()"
            ng-disabled="!modal_lesson.lesson_date || !modal_lesson.lesson_time || !modal_lesson.cabinet">
            {{ modal_lesson.id ? 'редактировать' : 'добавить' }}</button>
      </div>
    </div>
  </div>
</div>
</div>
