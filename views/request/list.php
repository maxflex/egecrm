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
			<div ng-show="!requests.length">
				<h3 style="text-align: center; margin: 50px 0">Список заявок пуст</h3>
			</div>
			<div ng-repeat="request in requests">
				<a href="requests/edit/{{request.id}}">Заявка #{{request.id}}</a>
			</div>
			
			<div ng-hide="request_statuses_count[chosen_list] <= <?= Request::PER_PAGE ?>">
				<hr style="margin-bottom: 10px">
				<pagination 
			      ng-model="currentPage" 
			      ng-change="pageChanged()" 
			      total-items="request_statuses_count[chosen_list]"
			      max-size="10"
			      boundary-links="true"
			      items-per-page="<?= Request::PER_PAGE ?>"
			      first-text="«"
			      last-text="»"
			      previous-text="<"
			      next-text=">"
			    >
			    </pagination>
			</div>
		</div>
	</div>
</div>

