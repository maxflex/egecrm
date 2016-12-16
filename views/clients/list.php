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
			<table class="table table-hover border-reverse">
				<tr ng-repeat="Student in Students">
					<td>
						{{getNumber($index)}}. <a href="student/{{Student.id}}">
							<span ng-show='Student.last_name'>{{Student.last_name}} {{Student.first_name}} {{Student.middle_name}}</span>
							<span ng-hide='Student.last_name'>имя не указано</span>
						</a>
					</td>
					<td width="10%" class="text-right">
						<span ng-show="Student.debt">{{ Student.debt | number }} руб.</span>

					</td>
				</tr>
			</table>
		</div>
		
		<pagination
			ng-show='(Students && Students.length) && (counts.all > <?= Student::PER_PAGE ?>)'
			ng-model="current_page"
			ng-change="pageChanged()"
			total-items="counts.all"
			max-size="10"
			items-per-page="<?= Student::PER_PAGE ?>"
			first-text="«"
			last-text="»"
			previous-text="«"
			next-text="»"
		>
		</pagination>

 		
		<div ng-show="Students === undefined" style="padding: 100px" class="small half-black center">
			загрузка клиентов...
		</div>
		<div ng-show="Students === null" style="padding: 100px" class="small half-black center">
			нет клиентов
		</div>
	</div>
	<sms templates="full" mode="client" mass="1" counts="counts.all"></sms>
</div>
