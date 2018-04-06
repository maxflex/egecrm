<div class="row" ng-show="current_menu == 3">
	<div class="col-sm-12">
		<?= globalPartial('loading', ['model' => 'Reviews', 'message' => 'нет отзывов']) ?>
		<?= globalPartial('reviews', ['review_by_year' => true]) ?>

		<div style="margin-top: 15px" class="pull-right">
            ответственный:

            <span class="user-pick" ng-click="toggleReviewUser()" style="color: {{findUser(id_user_review).color || 'black' }}">{{ findUser(id_user_review).login || "system"}}</span>
        </div>

	</div>
</div>
