<div ng-app="Request" ng-controller="ListCtrl"
	ng-init="<?= $ang_init_data ?>">
	<div class="row">
		<div class="col-sm-12" style="padding-right: 0">
			<ul class="nav nav-tabs">
				<li ng-repeat="request_status in request_statuses" data-id="{{request_status.id}}"
					ng-class="{'active' : chosen_list == request_status.id, 'request-status-li': request_status.id != 8 && (chosen_list != request_status.id)}" 
					ng-hide="request_status.id == <?= RequestStatuses::SPAM ?> && request_statuses_count[request_status.id] == 0"
				>
					<a class="list-link" href="#{{request_status.id}}" ng-click="changeList(request_status, true)" data-toggle="tab" aria-expanded="{{$index == 0}}">
						{{request_status.name}} ({{request_statuses_count[request_status.id]}})
					</a></li>
			</ul>
		</div>
	</div>

	<div class="row" style="margin-top: 10px; position: relative">
		<div id="frontend-loading"></div>
		<div class="col-sm-12">
			<div ng-show="!requests.length">
				<h3 style="text-align: center; margin: 50px 0">Список заявок пуст</h3>
			</div>

			<?php globalPartial("request_list") ?>

			<div ng-hide="request_statuses_count[chosen_list] <= <?= Request::PER_PAGE ?>">
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
