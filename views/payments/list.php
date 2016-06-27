<div ng-app="Payments" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>" class="row">

<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
<div class="lightbox-new lightbox-addpayment">
	<h4>{{new_payment.id ? "Редактировать" : "Добавить"}} платеж</h4>
	<div class="form-group payment-line">
		<div class="form-group inline-block">
			<?= Payment::buildSelector(false, false, ["ng-model" => "new_payment.id_status", "style" => "width: 180px"]) ?>
	    </div>
		<div class="form-group inline-block" ng-show="new_payment.Entity.type == 'student'">
			<?= PaymentTypes::buildSelector(false, false, ["ng-model" => "new_payment.id_type"]) ?>
	    </div>
		<div class="form-group inline-block">
			на сумму
			<input type="text" class="form-control digits-only" id="payment-sum" ng-model="new_payment.sum"  ng-keydown="watchEnter($event)"> от
		</div>
		<div class="form-group inline-block">
			<input class="form-control bs-date" id="payment-date" ng-model="new_payment.date">
		</div>
	</div>
	<div class="form-group payment-inline" ng-show="new_payment.id_status == <?= Payment::PAID_CARD ?>">
		<h4>Номер карты</h4>
		<div class="form-group inline-block">
			<input class="form-control" disabled placeholder="XXXX" style="width: 60px; display: inline-block; margin-left: 5px"> -
			<input class="form-control" disabled placeholder="XXXX" style="width: 60px; display: inline-block"> -
			<input class="form-control" disabled placeholder="XXXX" style="width: 60px; display: inline-block"> -
			<input class="form-control digits-only" maxlength="4" ng-model="new_payment.card_number"
				style="width: 60px; display: inline-block">
		</div>
	</div>
	<center>
		<button class="btn btn-primary" ng-click="addPayment()">{{new_payment.id ? "Редактировать" : "Добавить"}}</button>
	</center>
</div>
<!-- /ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->

    <div class="col-sm-12">
		<div class="row" style="margin-bottom: 15px">
			<div class="col-sm-2">
				<select class="watch-select single-select form-control" ng-model="search.mode" ng-change='filter()'>
					<option value="client"  data-subtext="{{ counts.mode.client || '' }}">клиенты</option>
					<option value="teacher" data-subtext="{{ counts.mode.teacher || '' }}">преподаватели</option>
				</select>
			</div>
			<div class="col-sm-2">
				<select class="watch-select single-select form-control" ng-model="search.payment_type"  ng-change='filter()'>
					<option value="" data-subtext="{{ counts.payment_type.all || ''}}">все виды платежей</option>
					<option disabled>──────────────</option>
					<option ng-repeat="(id_status, label) in payment_statuses"
							value="{{ id_status }}"
							data-subtext="{{ counts.payment_type[id_status] || '' }}">{{ label }}</option>
				</select>
			</div>
			<div class="col-sm-2">
				<select id='subjects-select' class="watch-select form-control single-select" ng-model="search.confirmed" ng-change='filter()'>
					<option value="" data-subtext="{{ counts.confirmed.all || ''}}" >все типы платежей</option>
					<option disabled>──────────────</option>
					<option data-subtext="{{ counts.confirmed[1] || ''}}" value="1">подтвержденные</option>
					<option data-subtext="{{ counts.confirmed[0] || '' }}" value="0">не подтвержденные</option>
				</select>
			</div>
		</div>
		<div id="frontend-loading"></div>
	    <div class="loading-ajax" ng-show="payments === undefined">загрузка...</div>
	    <div class="form-group payment-line" style="margin-bottom: 40px;">
            <?= globalPartial("payments_list") ?>
        </div>

        <pagination
			ng-show='(payments && payments.length) && (counts.mode[search.mode?search.mode:"client"] > <?= Payment::PER_PAGE ?>)'
			ng-model="search.current_page"
			ng-change="pageChanged()"
			total-items="counts.mode[search.mode ? search.mode : 'client']"
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
