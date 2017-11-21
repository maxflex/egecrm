<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<div class="panel panel-primary" ng-app="Clients" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<div class="panel-heading">Клиенты с договорами
		<div class="pull-right">
			<span class="link-like link-reverse link-white" ng-click="PhoneService.sms()">групповое SMS</span>
			<span style="display: inline; margin-left: 10px;">общая сумма задолженностей: {{ total_debt | number }} руб.</span>
		</div>
	</div>
	<div class="panel-body">
		<div class="row" style="margin-bottom: 15px">
			<div class="col-sm-2">
				<select class="watch-select single-select form-control" ng-model="search.year" ng-change='filter()'>
					<option value="" data-subtext="{{ counts.year[''] || '' }}">все годы</option>
					<option disabled>────────</option>
					<option ng-repeat="year in <?= Years::json() ?>"
							data-subtext="{{ counts.year[year] || '' }}"
							value="{{year}}">{{ yearLabel(year) }}</option>
				</select>
			</div>
			<div class="col-sm-2">
				<select class="watch-select single-select form-control" ng-model="search.error" ng-change='filter()'>
					<option value=""  data-subtext="{{ counts.error[''] || '' }}">все</option>
					<option disabled>───────</option>
					<option value="0"  data-subtext="{{ counts.error[0] || '' }}">без фото</option>
					<option value="1"  data-subtext="{{ counts.error[1] || '' }}">фото не обрезано</option>
					<option value="2"  data-subtext="{{ counts.error[2] || '' }}">свободный график не указан</option>
					<option value="3"  data-subtext="{{ counts.error[3] || '' }}">экстернат</option>
				</select>
	        </div>
		</div>

		<div style="position: relative">
			<div id="frontend-loading" style="height: 100%"></div>
			<table class="table small table-hover border-reverse">
				<thead>
					<tr>
						<td>
						</td>
						<td>
							дебет
						</td>
						<td>
							<span class="pointer" ng-click="sort()">неосвоенная сумма</span>
							<i class="fa" aria-hidden="true" ng-class="{
								'fa-long-arrow-up': search.order == 'asc',
								'fa-long-arrow-down': search.order == 'desc'
							}" ng-show="search.order !== undefined"></i>
						</td>
					</tr>
				</thead>
				<tr ng-repeat="Student in Students">
					<td>
						{{getNumber($index)}}. <a href="student/{{Student.id}}" ng-class="{
							'text-danger': Student.status == 'red',
							'text-warning': Student.status == 'yellow',
						}">
							<span ng-show='Student.last_name'>{{Student.last_name}} {{Student.first_name}} {{Student.middle_name}}</span>
							<span ng-hide='Student.last_name'>имя не указано</span>
						</a>
					</td>
					<td width="20%">
						<span ng-show="Student.debt">{{ Student.debt | number }} руб.</span>
					</td>
					<td width="20%">
						<span ng-show="Student.sum">{{ Student.sum | number }} руб.</span>
					</td>
				</tr>
				<tr class="last-row">
					<td>
						<b>итого на странице</b>
					</td>
					<td>
						<b>{{ totals.debt | number }} руб.</b>
					</td>
					<td>
						<b>{{ totals.sum | number }} руб.</b>
					</td>
				</tr>
			</table>
		</div>


		<div ng-show="Students === undefined" style="padding: 100px" class="small half-black center">
			загрузка клиентов...
		</div>
		<div ng-show="Students === null" style="padding: 100px" class="small half-black center">
			нет клиентов
		</div>
	</div>
	<sms templates="full" mode="client" mass="1" counts="counts.all"></sms>
</div>
<style>
.table thead {
    font-weight: bold;
    text-transform: uppercase;
    color: #ddd;
}
tr.last-row td {
	border-bottom: none !important;
}
</style>
