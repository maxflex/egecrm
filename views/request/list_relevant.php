<div ng-app="Request" ng-controller="ListCtrl"
	ng-init="<?= $ang_init_data ?>">

	<div class="row" style="margin-top: 10px; position: relative">
		<div id="frontend-loading"></div>
		<div class="col-sm-12">
			<div class="row" style="margin-bottom: 20px">
				<div class="col-sm-3">
					<?= Grades::buildSelector(false, false, ["ng-model" => "search.grade", "ng-change" => "pageChangedRelevant()"]) ?>
				</div>
				<div class="col-sm-3">
	                <?= Branches::buildSvgSelector(false, ["id" => "group-branch-filter", "ng-model" => "search.id_branch", "ng-change" => "pageChangedRelevant()"]) ?>
				</div>
				<div class="col-sm-3">
					<?= Subjects::buildSelector(false, false, ["ng-model" => "search.id_subject", "ng-change" => "pageChangedRelevant()"]) ?>
				</div>
			</div>
			
			<div ng-show="!requests.length">
				<h3 style="text-align: center; margin: 50px 0">Список заявок пуст</h3>
			</div>
			
			<?php globalPartial("request_list") ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div ng-hide="requests_count <= <?= Request::PER_PAGE ?>">
				<pagination
			      ng-model="currentPage"
			      ng-change="pageChangedRelevant()"
			      total-items="requests_count"
			      max-size="10"
			      items-per-page="<?= Request::PER_PAGE ?>"
			      first-text="«"
			      last-text="»"
			      previous-text="«"
			      next-text="»"
			    >
			    </pagination>
			</div>
		</div>
	</div>
</div>
