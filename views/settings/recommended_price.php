<div ng-app="Settings" ng-controller="RecommendedCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="top-links">
		<span ng-click="setYear(year)" class="link-like" ng-class="{'active': year == selected_year}" ng-repeat="year in years">{{ yearLabel(year) }}</span>
	</div>
	<div class="recommended-prices">
		<div>
			9 класс
		</div>
		<div>
			<input class="form-control" placeholder="цена за 1 предмет" ng-model="prices[selected_year][9]" />
		</div>
	</div>
	<div class="recommended-prices">
		<div>
			10 класс
		</div>
		<div>
			<input class="form-control" placeholder="цена за 1 предмет" ng-model="prices[selected_year][10]" />
		</div>
	</div>
	<div class="recommended-prices">
		<div>
			11 класс
		</div>
		<div>
			<input class="form-control" placeholder="цена за 1 предмет" ng-model="prices[selected_year][11]" />
		</div>
	</div>
	<div class="recommended-prices">
		<div>
			экстернат
		</div>
		<div>
			<input class="form-control" placeholder="цена за 1 предмет" ng-model="prices[selected_year][<?= Grades::EXTERNAL ?>]" />
		</div>
	</div>
	<center style='margin-top: 20px'>
		<button class="btn btn-primary" ng-click="save()" ng-disabled="saving">сохранить</button>
	</center>

</div>
<style>
	.recommended-prices {
		display: flex;
		align-items: center;
		margin-bottom: 15px;
	}
	.recommended-prices  > div:first-child {
		width: 100px;
	}
</style>
