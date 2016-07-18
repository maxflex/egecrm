<div class="row" ng-show="student !== undefined">
    <div class="col-sm-12">
	    <div>
			<?= globalPartial("groups_list", ["filter" => true]) ?>
			<span class="link-like fake-tr" ng-show="Groups && hasHiddenGroups()" ng-click="showHiddenGroups()">показать все группы</span>
	    </div>
    </div>
</div>