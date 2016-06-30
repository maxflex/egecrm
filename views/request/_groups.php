<div class="row" ng-show="current_menu == 2">
    <div class="col-sm-12">
	    <div>
			<?= globalPartial("groups_list", ["filter" => false]) ?>
	    </div>
	    <h4 class="row-header" ng-show="Groups.length == 0">НЕТ ГРУПП</h4>
    </div>
</div>