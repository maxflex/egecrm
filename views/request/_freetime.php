<div class="row" ng-show="current_menu == 8">
	<div class="col-sm-12">
		<div class="row">
			<div class="col-sm-4" style='width: 150px' >
				свободно:
			</div>
			<div class="col-sm-8">
				<span ng-repeat="(day, data) in FreetimeBar" class="group-freetime-block">
					<span ng-repeat="(id_time, bar) in data track by $index" ng-click="toggleStudentFreetime(day, id_time)" class="pointer bar {{bar}}"></span>
				</span>
			</div>
		</div>
		<div class="row" style="margin-top: 10px">
			<div class="col-sm-4" style='width: 150px' style="white-space: nowrap">
				занято в группах:
			</div>
			<div class="col-sm-8">
				<span ng-repeat="(day, data) in GroupsBar" class="group-freetime-block">
					<span ng-repeat="bar in data | toArray track by $index" class="bar {{bar}}"></span>
				</span>
			</div>
		</div>
	</div>
</div>