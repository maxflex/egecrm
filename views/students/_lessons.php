<div class="row" style="position: relative" ng-show="current_menu == 0">
	<div class="col-sm-12">
		<?= globalPartial('loading', ['model' => 'Lessons', 'message' => 'занятий нет']) ?>
		<div ng-show="Lessons">
			<?= globalPartial('lessons_and_reports', ['is_teacher' => true]) ?>
		</div>
	</div>
</div>
