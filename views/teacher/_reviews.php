<div class="row" ng-show="current_menu == 1">
	<div class="col-sm-12">
		<?= globalPartial('loading', ['model' => 'Reviews', 'message' => 'нет отзывов']) ?>
		<?= globalPartial('reviews') ?>
	</div>
</div>