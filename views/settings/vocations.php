<div ng-app="Settings" ng-controller="VocationsCtrl" ng-init="<?= $ang_init_data ?>" id="calendar-app">
<div class="panel panel-primary">
	<div class="panel-heading">
		Выходные дни и праздники
		<div class="pull-right">
<!-- 			<span class="link-reverse pointer" ng-click="deleteGroup(Group.id)" ng-show="Group.id">удалить даты из настроек группы</span> -->
		</div>
	</div>
	<div class="panel-body" style="position: relative">
				
		<div class="row calendar">
			<div class="col-sm-5">
				<div class="row calendar-row" ng-repeat="month in [9, 10, 11, 12, 1, 2, 3, 4, 5, 6]">
					<div class="col-sm-4 month-name text-primary">
						{{monthName(month)}} {{month == 1 ? "2016" : ""}}
					</div>
					<div class="col-sm-8">
						<div class="calendar-month" month="{{month}}">
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-5">
				<h4>Дни экзаменов:</h4>
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