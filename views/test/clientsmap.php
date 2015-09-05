<div ng-app="Test" ng-controller="ClientsMapCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="row">
		<div class="col-sm-3">
			<div ng-repeat="(id_branch, name) in Branches">
				<div class="checkbox-wrap">
					<input type="checkbox" ng-click="toggleFilter('branch_invert', id_branch)"
						ng-disabled="inArray(id_branch, filters.branches)" >
				</div>
				<span ng-class="{'quater-black': inArray(id_branch, filters.branches)}">{{name}}</span>
			</div>
		</div>
		<div class="col-sm-3">
			<div ng-repeat="(id_branch, name) in Branches">
				<div class="checkbox-wrap">
					<input type="checkbox" ng-click="toggleFilter('branch', id_branch)"
						ng-disabled="inArray(id_branch, filters.branches_invert)">
				</div>
				<span ng-class="{'quater-black': inArray(id_branch, filters.branches_invert)}">{{name}}</span>
			</div>
		</div>
		<div class="col-sm-2">
			<div ng-repeat="n in [] | range:11">
				<div class="checkbox-wrap">
					<input type="checkbox" ng-click="toggleFilter('grade', n)">
				</div>
				{{n}} класс
			</div>
		</div>
		<div class="col-sm-2">
			<div ng-repeat="(id_subject, name) in Subjects">
				<div class="checkbox-wrap">
					<input type="checkbox" ng-click="toggleFilter('subject', id_subject)">
				</div>
				{{name}}
			</div>
		</div>
		<div class="col-sm-2">
			<div>
				<div class="checkbox-wrap">
					<input type="checkbox" ng-model="filters.marker_school" ng-click="runRequest()">
				</div>
				Школа
			</div>
			<div>
				<div class="checkbox-wrap">
					<input type="checkbox" ng-model="filters.marker_home" ng-click="runRequest()">
				</div>
				Дом
			</div>
		</div>
	</div>
	<div class="row" style="margin-top: 10px">
		<div class="col-sm-12">
			<map zoom="10" disable-default-u-i="true" scale-control="true" zoom-control="true" 
				zoom-control-options="{style:'SMALL'}" style="height: 750px; position: relative">
				<div id="frontend-loading"></div>
				<transit-layer></transit-layer>
			</map>
		</div>
	</div>	
</div>