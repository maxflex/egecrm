<div class="row" ng-show="current_menu == 0">
	<div class="col-sm-12">
		<?= globalPartial('loading', ['model' => 'Groups', 'message' => 'нет групп']) ?>
        <?= globalPartial("groups_list", ['group_by_year' => true]) ?>
	</div>
</div>