<div ng-app="Sms" ng-controller="Main" ng-init="<?= $ang_init_data ?>">
	<div class="row" style='margin-bottom: 20px'>
		<div class="col-sm-6">
			<input class="form-control" placeholder="поиск..." name="search" ng-keyup="filter()" ng-model="search">
		</div>
		<div class="col-sm-6">
			<div class="form-group" style='width: 300px'>
				<phones entity="{}" entity-type="Request"></phones>
			</div>
			<!-- СМС -->
			<sms number='sms_number' templates="full"></sms>
		</div>
	</div>
	<div ng-show="data === undefined" style="padding: 100px" class="small half-black center">
		загрузка...
	</div>
	<div style="position: relative">
		<div id="frontend-loading" style="height: 100%"></div>
		<table class="table table-hover">
			<tbody>
				<tr ng-repeat="sms in data">
					<td class="col-sm-2">
						{{ sms.number_formatted }}
					</td>
					<td class="col-sm-6">
						{{ sms.message }}
					</td>
					<td class="col-sm-1">
						{{ sms.user_login }}
					</td>
					<td class="col-sm-2">
						{{ sms.date | formatDateTime }}
					</td>
					<td class="col-sm-1">
						{{ sms.status }}
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<pagination
		ng-model="current_page"
		ng-change="pageChanged()"
		ng-hide="total < <?= SMS::PER_PAGE ?>"
		total-items="total"
		max-size="10"
		items-per-page="<?= SMS::PER_PAGE ?>"
		first-text="«"
		last-text="»"
		previous-text="«"
		next-text="»"
	>
	</pagination>


</div>
