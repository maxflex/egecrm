<div ng-app="Payments" ng-controller="LkTeacherCtrl" ng-init="<?= $ang_init_data ?>" style="min-height: 500px">
	<div class="row" ng-show="password_correct === false">
		<div class="col-sm-12">
			<h4 class="text-danger center" style="margin: 200px 0">доступ ограничен</h4>
		</div>
	</div>
	<div ng-show="password_correct === true">
		<div class="row" style="position: relative">
			<div class="col-sm-12" ng-show="!loaded">
				<div class="center half-black small" style="margin: 200px 0">
					загрузка...
				</div>
			</div>
			<div class="col-sm-12" ng-show="loaded">
				<?= globalPartial('teacher_balance', ['credentials' => false]) ?>
			</div>
		</div>
	</div>
</div>
