<div ng-app="Settings" ng-controller="CabinetsCtrl" ng-init="<?= $ang_init_data ?>">
	<div ng-repeat="Branch in Branches" style="margin-bottom: 20px">
		<a href="settings/cabinets/{{Branch.id}}"><h5 ng-bind-html="Branch.svg | to_trusted"></h5></a>
			<div ng-repeat="Cabinet in Branch.Cabinets" style="margin-left: 20px">
				<span style="width:110px; display: inline-block">Кабинет №{{Cabinet.number}}</span>
				<span class="link-like small red" ng-click="removeCabinet(Branch.id, $index)" style="margin-left: 10px">удалить</span>
			</div>
	</div>
	
	<div class="row">
		<div class="col-sm-12 cabinet-add">
			<h4 class="row-header">ДОБАВИТЬ КАБИНЕТ</h4>
			<?= Branches::buildSvgSelector(false, ["id" => "add-branch", "ng-model" => "cabinet_add.id_branch"]) ?>
			<input class="form-control digits-only" placeholder="№ кабинета" ng-model="cabinet_add.number" id="add-number">
			<button class="btn btn-default" ng-click="addCabinet()">Добавить</button>
		</div>
	</div>
</div>