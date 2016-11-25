<div ng-app="Request" ng-controller="ListCtrl"
	ng-init="<?= $ang_init_data ?>">
	<div class="row">
		<div class="col-sm-10" style="padding-right: 0">
			<ul class="request-list-nav nav nav-tabs nav-tabs-links" style="margin-bottom: 20px">
				<li ng-repeat="request_status in request_statuses" data-id="{{request_status.id}}"
					ng-class="{'active' : chosen_list == request_status.id, 'request-status-li': request_status.id != 8 && (chosen_list != request_status.id)}" 
					ng-hide="request_status.id == <?= RequestStatuses::SPAM ?> && counts.requests[request_status.id] == 0"
				>
					<a class="list-link" href="#{{request_status.id}}" ng-click="changeList(request_status, true)" data-toggle="tab" aria-expanded="{{$index == 0}}">
						{{request_status.name}}
					</a> <span class='text-gray' style='font-size: 10px; margin-left: 3px'>{{ counts.requests[request_status.id] }}</span></li>
				<li class="delete-request-li" ng-show="dragging">
					<a class="text-danger">удалить</a>
				</li>
			</ul>
		</div>
		<div class="col-sm-2" id="user-list-fix">
			<select class="form-control watch-select" ng-model='id_user_list' ng-change="filter()" id='user-filter'>
				<option value=''>пользователь</option>
				<option disabled>──────────────</option>
				<option
					ng-repeat="user in UserService.getWithSystem()"
					ng-show='counts.users[user.id]'
					value="{{ user.id }}"
					data-content="<span style='color: {{ user.color || 'black' }}'>{{ user.login }} {{ $var }}</span><small class='text-muted'>{{ counts.users[user.id] || '' }}</small>"
				></option>
				<option disabled ng-show="UserService.getBannedHaving(counts.users).length || UserService.getUser(id_user_list).banned">──────────────</option>
				<option
					ng-show='id_user_list == user.id || counts.users[user.id]'
					ng-selected="id_user_list == user.id"
                    ng-repeat="user in UserService.getBannedUsers()"
					value="{{ user.id }}"
					data-content="<span style='color: {{ user.color || 'black' }}'>{{ user.login }} {{ $var }}</span><small class='text-muted'>{{ counts.users[user.id] || '' }}</small>"
				></option>
			</select>
		</div>
	</div>

	<div class="row" style="margin-top: 10px; position: relative">
		<div id="frontend-loading"></div>
		<div class="col-sm-12">
			<div ng-show="!requests.length">
				<h3 style="text-align: center; margin: 50px 0">Список заявок пуст</h3>
			</div>

			<?php globalPartial("request_list") ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<div ng-hide="counts.requests[chosen_list] <= <?= Request::PER_PAGE ?>">
				<pagination
			      ng-model="currentPage"
			      ng-change="pageChanged()"
			      total-items="counts.requests[chosen_list]"
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
