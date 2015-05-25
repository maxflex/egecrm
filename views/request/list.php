<div ng-app="Request" ng-controller="ListCtrl" 
	ng-init="<?= $ang_init_data ?>">
	<div class="row">
		<div class="col-sm-12">
			<ul class="nav nav-tabs">
				<li ng-class="{'active' : $index == 0}" ng-repeat="(key, value) in request_statuses">
					<a href="#{{key}}" ng-click="changeList(key)" data-toggle="tab" aria-expanded="{{$index == 0}}">
						{{value}} ({{request_statuses_count[key]}})
					</a></li>
			</ul>
		</div>
	</div>

	<div class="row" style="margin-top: 10px">
		<div class="col-sm-12">
			<div ng-repeat="request in requests | reverse | filter:{id_status : chosen_list}">
				<a href="requests/edit/{{request.id}}">Заявка #{{request.id}}</a>
			</div>
		</div>
	</div>
</div>

