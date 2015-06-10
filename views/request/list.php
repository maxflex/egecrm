<div ng-app="Request" ng-controller="ListCtrl" 
	ng-init="<?= $ang_init_data ?>">
	<div class="row">
		<div class="col-sm-12">
			<ul class="nav nav-tabs">
				<li ng-repeat="request_status in request_statuses" ng-class="{'active' : chosen_list == request_status.id}">
					<a class="list-link" href="#{{request_status.id}}" ng-click="changeList(request_status, true)" data-toggle="tab" aria-expanded="{{$index == 0}}">
						{{request_status.name}} ({{request_statuses_count[request_status.id]}})
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

