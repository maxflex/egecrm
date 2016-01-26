<div ng-app="Templates" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div ng-repeat="Template in Templates" class="row">
		<hr ng-show="$index == 4">
		<div class="col-sm-12">
			<div class="form-group">
				<b class="from-capital" style="margin: 0 0 3px">{{Template.name}}</b>
				<textarea ng-model="Template.text" class="form-control"></textarea>
				<div class="pull-right" ng-show="Template.type == 2">
					<span style="margin-right: 8px">
						<input type="checkbox"  ng-click="toggle(1, Template)"  ng-checked="inWho(1, Template)" ng-true-value="1" ng-false-value="0"> ученикам
					</span>
					<span style="margin-right: 8px">
						<input type="checkbox"  ng-click="toggle(2, Template)"  ng-checked="inWho(2, Template)" ng-true-value="1" ng-false-value="0"> представителям
					</span>
					<span>
						<input type="checkbox" ng-click="toggle(3, Template)"  ng-checked="inWho(3, Template)" ng-true-value="1" ng-false-value="0"> преподавателям
					</span>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12 center">
			<button class="btn btn-primary" ng-click="save()" ng-disabled="!form_changed">
				<span ng-show="form_changed">Сохранить</span>
				<span ng-show="!form_changed">Сохранено</span>
			</button>
		</div>
	</div>
</div>