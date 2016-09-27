<div class="row" ng-show="current_menu == 6">
	<div class="col-sm-12">
		<?= globalPartial('loading', ['model' => 'Bars']) ?>
		<div ng-if="Bars !== undefined">
			<div class="row" style="width: 350px">
	            <div class="col-sm-4">
		            свободно:
	            </div>
	            <div class="col-sm-8" style="text-align: right">
		            <span ng-repeat="(day, data) in Bars.Freetime" class="group-freetime-block">
						<span ng-repeat="(id_time, bar) in data | toArray track by $index" ng-click="toggleFreetime(day, id_time)" class="pointer bar {{bar}}"></span>
					</span>
	            </div>
	        </div>
			<div class="row" style="margin-top: 10px; width: 350px">
	            <div class="col-sm-4" style="white-space: nowrap">
		            занято в группах:
	            </div>
	            <div class="col-sm-8" style="text-align: right">
		            <span ng-repeat="(day, data) in Bars.Group" class="group-freetime-block">
						<span ng-repeat="bar in data | toArray track by $index" class="bar {{bar}}"></span>
					</span>
	            </div>
	        </div>
		</div>
	</div>

	<div class="col-sm-12" style="margin-top: 13px">
		<?= Html::comments('Bars', Comment::PLACE_TEACHER) ?>
	</div>
</div>
