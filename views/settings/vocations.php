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
			</div>
		</div>		
	</div>
</div>
</div>