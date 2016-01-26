<div ng-app="Stats" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<form method="get">
	<div class="row" style="margin-bottom: 15px">
		<div class="col-sm-2">
			<input class="form-control bs-date-default" placeholder="2015-09-01" ng-model="date_start" id="date-start">
		</div>
		<div class="col-sm-2">
			<input class="form-control bs-date-default" placeholder="2015-09-31" ng-model="date_end" id="date-end">
		</div>
		<div class="col-sm-1">
			<button class="btn btn-primary" ng-click="goDates()">ОК</button>
		</div>
	</div>
	</form>
	<table class="table table-hover">
		<tr ng-repeat="User in Users" class="row">
			<td>
				{{User.login}}
			</td>
			<td>
				{{User.total_requests}}
			</td>
			<td>
				<div class="pointer" ng-click="toggleDiv(User.id)">{{User.total_success_requests}}</div>
				<div class="user-{{User.id}}" style="display: none">
					<div class="small half-black" ng-repeat="(id, count) in User.counts">
						{{Sources[id]}}: {{count}}
					</div>
				</div>
			</td>
			<td>
				{{User.student_count}}
				<div class="user-{{User.id}}" style="display: none">
					<div class="small half-black" ng-repeat="(id, count) in User.count_students">
						{{Sources[id]}}: {{count}}
					</div>
				</div>
			</td>
			<td>
				{{User.total_contracts}}
				<div class="user-{{User.id}}" style="display: none">
					<div class="small half-black" ng-repeat="(id, count) in User.count_contracts">
						{{Sources[id]}}: {{count}}
					</div>
				</div>
			</td>
			<td>
				{{User.total_contract_sum | number}}
				<div class="user-{{User.id}}" style="display: none">
					<div class="small half-black" ng-repeat="(id, count) in User.count_contracts_sum">
						{{Sources[id]}}: {{count | number}}
					</div>
				</div>
			</td>
			<td>
				<span ng-show="User.total_contracts > 0">
					{{round1(User.total_contracts / User.student_count * 100)}}%
					<div class="user-{{User.id}}" style="display: none">
						<div class="small half-black" ng-repeat="(id, count) in User.count_contracts">
							{{Sources[id]}}: {{round1(count / User.count_students[id] * 100)}}%
						</div>
					</div>
				</span>
			</td>
			<td>
				<span ng-show="User.total_contracts > 0">
					{{round2(User.total_contract_sum / User.student_count)}}
					<div class="user-{{User.id}}" style="display: none">
						<div class="small half-black" ng-repeat="(id, count) in User.count_contracts_sum">
							{{Sources[id]}}: {{round2(count / User.count_students[id]) | number}}
						</div>
					</div>
				</span>
			</td>
	</table>
</div>