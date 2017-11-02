<div ng-show="add_groups_panel" class="row">
		<hr>
        <!-- @time-refactored @time-checked -->
		<div class="col-sm-12">
			<div class="row" style="margin-bottom: 15px">
				<div class="col-sm-4">
					<select class="watch-select form-control search-grades selectpicker" ng-model="search_groups.grades" ng-change='loadGroups()' multiple title='классы'  data-multiple-separator=", ">
						<option ng-hide='grade < 8' ng-repeat="(grade, label) in Grades | toArray" value="{{(grade + 1)}}">{{label}}</option>
					</select>
				</div>
				<div class="col-sm-4">
					<select id='subjects-select' multiple class="watch-select form-control single-select selectpicker" ng-model="search_groups.subjects" ng-change='loadGroups()' title='предметы'  data-multiple-separator=", ">
						<option ng-repeat="(id_subject, name) in Subjects"
							value="{{id_subject}}">{{ name }}</option>
					</select>
				</div>
				<div class="col-sm-4">
					<select class="watch-select single-select form-control selectpicker" ng-model="search_groups.year" ng-change='loadGroups()'>
						<option value="">все годы</option>
						<option disabled>────────</option>
						<option ng-repeat="year in <?= Years::json() ?>" value="{{year}}">{{ yearLabel(year) }}</option>
					</select>
				</div>
			</div>
		</div>
		<div ng-show="Groups === undefined" style="padding: 100px" class="small half-black center">
			загрузка групп...
		</div>
		<div ng-show="Groups == -1" style="padding: 100px" class="small half-black center">выберите фильтр</div>
		<div ng-show="Groups === null" style="padding: 100px" class="small half-black center">
			нет групп
		</div>
		<div class="col-sm-12">
			<?= globalPartial("groups_list", ["filter" => true, "teacher_comment" => true]) ?>
		</div>
</div>
