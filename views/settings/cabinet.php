<div ng-app="Settings" ng-controller="CabinetsCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="row" style="position: relative">
        <div class="col-sm-12">
            <div class="branches" ng-repeat="Branch in Branches" ng-show="Cabinets[Branch.id] && Cabinets[Branch.id].length">
                <p style="margin-bottom:7px;"><span ng-bind-html="Branch.svg"></span>{{ Branch.name }}</p>
                <table class="table table-divlike">
                    <tr ng-repeat="Cabinet in Cabinets[Branch.id]">
                        <td width="200px" style="padding-left: 20px;">{{ Cabinet.number }}</td>
                        <td width="150">
							    <span ng-repeat="(day, data) in Cabinet.bar" class="group-freetime-block">
									<span ng-repeat="bar in data" class="bar {{bar}}"></span>
								</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
	</div>
</div>