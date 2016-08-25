<div ng-show="add_groups_panel" class="row">
		<hr>
		<div class="col-sm-12">
			<div class="row" style="margin-bottom: 15px">
				<div class="col-sm-3">
					<?= Grades::buildSelector(false, false, ["ng-model" => "search_groups.grade"]) ?>
				</div>
				<div class="col-sm-3">
	                <?= Branches::buildSvgSelector(false, ["id" => "groups-branch-filter", "ng-model" => "search_groups.id_branch"]) ?>
				</div>
				<div class="col-sm-3">
					<?= Subjects::buildSelector(false, false, ["ng-model" => "search_groups.id_subject"]) ?>
				</div>
				<div class="col-sm-3"> 
					<select class="form-control" 
						ng-model="search_groups.year">
						<option value="">все</option>
						<option disabled>──────────────</option>
						<option ng-repeat="year in <?= Years::json() ?>" 
								value="{{year}}">{{ year + '-' + ((1*year) + 1) + ' уч. г.' }}</option>
					</select> 
				</div>
			</div>
		</div>
		<div ng-show="!Groups" class="center half-black small" style="margin-top: 35px">загрузка групп...</div>
		<div class="col-sm-12">
			<?= globalPartial("groups_list", ["filter" => true]) ?>
		</div>
</div>