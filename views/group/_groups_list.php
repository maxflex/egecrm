<div ng-show="add_groups_panel" class="row">
		<hr>
		<div class="col-sm-12">
			<div class="row" style="margin-bottom: 15px">
				<div class="col-sm-3">
					<?= Grades::buildSelector(false, false, ["ng-model" => "search_groups.grade"]) ?>
				</div>
				<div class="col-sm-3">
					<select ng-model='search_groups.id_cabinet' id='groups-cabinet-filter' class="full-width">
						<option selected value=''>кабинет</option>
						<option disabled>──────────────</option>
						<option ng-repeat='cabinet in all_cabinets' value="{{ cabinet.id }}"
							ng-class="{'half-opacity': free_cabinets[d.id][cabinet.id]}"
							style='color: {{ cabinet.color }}'>
							{{ cabinet.label}}
						</option>
					</select>
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
