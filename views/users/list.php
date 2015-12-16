<div ng-app="Users" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<table class="table table-hover">
		<tr ng-repeat="User in Users" class="row">
			<td>
				<input class="form-control" ng-model="User.login" placeholder="логин">
			</td>
			<td>
				<input class="form-control" placeholder="пароль" type="password" ng-model="User.new_password">
			</td>
			<td>
				<input class="form-control" ng-model="User.color" style="background-color: {{User.color}}; color: white" placeholder="цвет">
			</td>
			<td>
				<input class="form-control" ng-model="User.agreement" placeholder="соглашение">
			</td>
<!--
			<td>
				<label class="ios7-switch" style="font-size: 24px; top: 1px; margin: 0">
					<input type="checkbox" ng-model="User.worktime" ng-true-value="1">
					<span class="switch"></span>
				</label>
			</td>
-->
			<td>
				<label class="red-switch ios7-switch" style="font-size: 24px; top: 1px; margin: 0">
					<input type="checkbox" ng-model="User.banned" ng-true-value="1">
					<span class="switch"></span>
				</label>
			</td>
		</tr>
	</table>
	<div class="row">
		<div class="col-sm-12 center">
			<button class="btn btn-primary" ng-click="save()" ng-disabled="!form_changed">
				<span ng-show="form_changed">Сохранить</span>
				<span ng-show="!form_changed">Сохранено</span>
			</button>
		</div>
	</div>
</div>