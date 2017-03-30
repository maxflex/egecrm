<div ng-app="Settings" ng-controller="VocationsCtrl" ng-init="<?= $ang_init_data ?>">
<div class="panel panel-primary">
	<div class="panel-heading">
		Выходные дни и праздники
		<div class="pull-right">
<!-- 			<span class="link-reverse pointer" ng-click="deleteGroup(Group.id)" ng-show="Group.id">удалить даты из настроек группы</span> -->
		</div>
	</div>
	<div class="panel-body" style="position: relative">
		<div class="row mb">
			<div class="col-sm-12">
				<div class="top-links">
					<span ng-repeat="year in <?= Years::json() ?>" class="link-like" ng-click="setYear(year)" ng-class="{'active': year == current_year}">{{ yearLabel(year) }}</span>
			    </div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-6">
				<?= globalPartial('calendar') ?>
			</div>
			<div class="col-sm-6">
                <table class="table table-divlike">
					<tr ng-repeat="Schedule in Group.Schedule | orderBy:'date'" style="height: 30px">
						<td>
                            {{ formatDate(Schedule.date) }}
						</td>
                        <td style='text-align: right'>
                            <span class='link-like link-offset-right' ng-click='scheduleModal(Schedule)'>редактировать</span>
                            <span class='link-like red' ng-click='deleteSchedule(Schedule)'>удалить</span>
                        </td>
					</tr>
				</table>
                <span class='link-like' ng-click='scheduleModal()'>добавить</span>
                <div class="exam-days-wrapper">
                    <h4 style="text-align: center; margin: 0 0 20px">Дни экзаменов</h4>
                    <div class="row">
                        <div class="col-sm-6">
                            <span ng-repeat="(id_subject, name) in Subjects">
                                <div class="row" style="margin-bottom: 10px" ng-if="id_subject != 10">
                                    <div class="col-sm-5" style="line-height: 34px">
                                        {{name}}-9
                                    </div>
                                    <div class="col-sm-7">
                                        <input class="form-control bs-date" ng-model="exam_days[9][id_subject][0]">
                                    </div>
                                </div>
                                <!-- Английский -->
                                <span ng-if="id_subject == 10">
                                    <div class="row" style="margin-bottom: 10px">
                                        <div class="col-sm-5" style="line-height: 34px">
                                            {{name}}-9-1
                                        </div>
                                        <div class="col-sm-7">
                                            <input class="form-control bs-date" ng-model="exam_days[9][id_subject][1]">
                                        </div>
                                    </div>
                                    <div class="row" style="margin-bottom: 10px">
                                        <div class="col-sm-5" style="line-height: 34px">
                                            {{name}}-9-2
                                        </div>
                                        <div class="col-sm-7">
                                            <input class="form-control bs-date" ng-model="exam_days[9][id_subject][2]">
                                        </div>
                                    </div>
                                </span>
                            </span>
                        </div>
                        <div class="col-sm-6">
                            <span ng-repeat="(id_subject, name) in Subjects">
                                <div class="row" style="margin-bottom: 10px" ng-if="id_subject != 10 && id_subject != 1">
                                    <div class="col-sm-5" style="line-height: 34px">
                                        {{name}}-11
                                    </div>
                                    <div class="col-sm-7">
                                        <input class="form-control bs-date" ng-model="exam_days[11][id_subject][0]">
                                    </div>
                                </div>
                                <!-- Английский -->
                                <span ng-if="id_subject == 10">
                                    <div class="row" style="margin-bottom: 10px">
                                        <div class="col-sm-5" style="line-height: 34px">
                                            {{name}}-11-У
                                        </div>
                                        <div class="col-sm-7">
                                            <input class="form-control bs-date" ng-model="exam_days[11][id_subject][0]">
                                        </div>
                                    </div>
                                    <div class="row" style="margin-bottom: 10px">
                                        <div class="col-sm-5" style="line-height: 34px">
                                            {{name}}-11-У
                                        </div>
                                        <div class="col-sm-7">
                                            <input class="form-control bs-date" ng-model="exam_days[11][id_subject][1]">
                                        </div>
                                    </div>
                                    <div class="row" style="margin-bottom: 10px">
                                        <div class="col-sm-5" style="line-height: 34px">
                                            {{name}}-11-П
                                        </div>
                                        <div class="col-sm-7">
                                            <input class="form-control bs-date" ng-model="exam_days[11][id_subject][2]">
                                        </div>
                                    </div>
                                </span>
                                <!-- Математика -->
                                <span ng-if="id_subject == 1">
                                    <div class="row" style="margin-bottom: 10px">
                                        <div class="col-sm-5" style="line-height: 34px">
                                            {{name}}-11-Б
                                        </div>
                                        <div class="col-sm-7">
                                            <input class="form-control bs-date" ng-model="exam_days[11][id_subject][0]">
                                        </div>
                                    </div>
                                    <div class="row" style="margin-bottom: 10px">
                                        <div class="col-sm-5" style="line-height: 34px">
                                            {{name}}-11-П
                                        </div>
                                        <div class="col-sm-7">
                                            <input class="form-control bs-date" ng-model="exam_days[11][id_subject][1]">
                                        </div>
                                    </div>
                                </span>
                            </span>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 10px">
                        <div class="col-sm-12 center">
                            <button class="btn btn-primary" ng-click="saveExamDays()" ng-disabled="adding">сохранить дни экзаменов</button>
                        </div>
                    </div>
                </div>
			</div>
		</div>
	</div>
</div>


<!-- schedule modal -->
<div id="schedule-modal" class="modal" role="dialog" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title center">{{ modal_schedule.id ? 'Редактировать праздник' : 'Добавить праздник' }}</h4>
      </div>
      <div class="modal-body">
          <div class="form-group">
              <div class="input-group custom" style="position: relative">
                  <span class="input-group-addon">дата праздника - </span>
                  <input class="form-control bs-date-clear pointer" readonly ng-model="modal_schedule.date">
              </div>
          </div>
      </div>
      <div class="modal-footer center">
        <button type="button" class="btn btn-primary" ng-click="saveSchedule()"
            ng-disabled="!modal_schedule.date">
            {{ modal_schedule.id ? 'редактировать' : 'добавить' }}</button>
      </div>
    </div>
  </div>
</div>

</div>
