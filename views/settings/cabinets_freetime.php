<div ng-app="Settings" ng-controller="CabinetsCtrl" ng-init="<?= $ang_init_data ?>">
	<div ng-repeat="Branch in Branches" ng-show="getBranchCabinets(Branch.id).length" style="margin-bottom: 20px">
		<div class="bold">
			<span ng-bind-html="Branch.svg | to_trusted"></span> {{Branch.name}}
		</div>
		<div ng-repeat="Cabinet in getBranchCabinets(Branch.id)" style="margin-left: 24px">
				<span class="inline-block half-black" style="width: 50px">№{{Cabinet.number}}</span>
				<span ng-repeat="weekday in weekdays" class="group-freetime-block">
					<span class="freetime-bar empty-green" ng-repeat="time in weekday.schedule track by $index" 
						ng-class="{
							'red'			: inCabinetFreetime(time, Cabinet.freetime[$parent.$index + 1])
						}" ng-hide="time == ''" style="position: relative; top: 3px">
					</span>
				</span>
			</div>
	</div>
</div>