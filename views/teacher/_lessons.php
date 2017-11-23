<div class="row" style="position: relative" ng-show="current_menu == 2">
	<div class="col-sm-12">
		<?= globalPartial('loading', ['model' => 'Lessons', 'message' => 'занятий нет']) ?>
		<?= globalPartial('teacher_balance', ['credentials' => true]) ?>
	</div>
</div>
