<div class="row" ng-show="current_menu == 2">
    <div class="col-sm-12">
		<?= globalPartial('loading', ['model' => 'Lessons', 'message' => 'занятий нет']) ?>
		<div ng-show="Lessons">
			<?= globalPartial('lessons_and_reports', ['hide_reports' => true]) ?>
		</div>
    </div>
</div>
