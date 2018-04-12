<div class="panel panel-primary" ng-app="Payments" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>">
	<?= partial('modal') ?>
	<div class="panel-heading">
		Платежи
		<div class="pull-right">
			<span class="link-like link-white link-reverse" ng-click="addPaymentDialog()">добавить анонимный платеж</span>
		</div>
	</div>
	<div class="panel-body">
		<div class="row">
		    <style>
		        .dropdown-menu > li > a {
		            padding: 3px 45px 3px 10px;
		        }
		    </style>
		    <div class="col-sm-12">
				<div class="row flex-list" style="margin-bottom: 15px">
					<div>
						<select multiple data-none-selected-text="все плательщики" data-multiple-separator=", " class="watch-select single-select form-control" ng-model="search.mode" ng-change='filter()'>
							<!-- <option value="STUDENT"  data-subtext="{{ counts.mode.STUDENT || '' }}">клиенты</option> -->
							<option value="STUDENT">клиенты</option>
							<option value="TEACHER">преподаватели</option>
							<option value="ANONYMOUS">анонимные</option>
						</select>
					</div>
					<div>
						<select class="watch-select single-select form-control" ng-model="search.payment_type"  ng-change='filter()'>
							<option value="" data-subtext="{{ counts.payment_type.all || ''}}">все виды платежей</option>
							<option disabled>──────────────</option>
							<option ng-repeat="(id_status, label) in payment_statuses"
									value="{{ id_status }}"
									data-subtext="{{ counts.payment_type[id_status] || '' }}">{{ label }}</option>
		                    <option value="-1"
									data-subtext="{{ counts.payment_type[-1] || '' }}">неассоциированные взаимозачеты</option>
						</select>
					</div>
					<div>
						<select id='subjects-select' class="watch-select form-control single-select" ng-model="search.confirmed" ng-change='filter()'>
							<option value="" data-subtext="{{ counts.confirmed.all || ''}}" >все типы платежей</option>
							<option disabled>──────────────</option>
							<option data-subtext="{{ counts.confirmed[1] || ''}}" value="1">подтвержденные</option>
							<option data-subtext="{{ counts.confirmed[0] || '' }}" value="0">не подтвержденные</option>
						</select>
					</div>
					<div>
						<select id='subjects-select' class="watch-select form-control single-select" ng-model="search.type" ng-change='filter()'>
							<option value="" data-subtext="{{ counts.type.all || ''}}" >все операции</option>
							<option disabled>──────────────</option>
							<option data-subtext="{{ counts.type[1] || ''}}" value="1">платеж</option>
							<option data-subtext="{{ counts.type[2] || '' }}" value="2">возврат</option>
						</select>
					</div>
					<div>
						<select id='years-select' class="watch-select form-control single-select" ng-model="search.year" ng-change='filter()'>
							<option value="" data-subtext="{{ counts.year.all || ''}}" >все годы</option>
							<option disabled>──────────────</option>
		                    <option ng-repeat="year in <?= Years::json() ?>"
		                        data-subtext="{{ counts.year[year] || '' }}"
		                        value="{{year}}">{{ yearLabel(year) }}</option>
						</select>
					</div>
		            <div>
						<select id='years-select' class="watch-select form-control single-select dropdown-viewport-fix" ng-model="search.category" ng-change='filter()'>
							<option value="" data-subtext="{{ counts.category.all || ''}}" >все категории</option>
							<option disabled>──────────────</option>
		                    <option ng-repeat="(id, category) in payment_categories"
		                        data-subtext="{{ counts.category[id] || '' }}"
		                        value="{{id}}">{{ category }}</option>
						</select>
					</div>
				</div>
				<div id="frontend-loading"></div>
			    <div class="loading-ajax" ng-show="payments === undefined">загрузка...</div>
			    <div class="form-group payment-line" style="margin-bottom: 40px;">
		            <?= globalPartial("payments_list") ?>
		        </div>

		        <pagination
					ng-show='(payments && payments.length) && (counts.mode[search.mode?search.mode:"STUDENT"] > <?= Payment::PER_PAGE ?>)'
					ng-model="search.current_page"
					ng-change="pageChanged()"
					total-items="counts.mode[search.mode ? search.mode : 'STUDENT']"
					max-size="10"
					items-per-page="<?= Payment::PER_PAGE ?>"
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
