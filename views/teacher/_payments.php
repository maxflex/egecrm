<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
<div id="addpayment" class="lightbox-new lightbox-addpayment">
	<h4>{{new_payment.id ? "Редактировать" : "Добавить"}} платеж</h4>
	<div class="form-group payment-line">
		<div class="form-group inline-block">
			<?= Payment::buildSelector(false, false, ["ng-model" => "new_payment.id_status", "style" => "width: 180px"]) ?>
		</div>
		<div class="form-group inline-block">
			на сумму
		</div>
		<div class="form-group inline-block">
			<input type="text" class="form-control digits-only" id="payment-sum" ng-model="new_payment.sum"  ng-keydown="watchEnter($event)"> от
		</div>
		<div class="form-group inline-block">
			<input class="form-control bs-date" id="payment-date" ng-model="new_payment.date">
		</div> за
        <div class="form-group inline-block">
            <select class="form-control" ng-model="new_payment.year" style='width: 130px'>
                <option value="">выберите год</option>
                <option disabled>──────────────</option>
                <option ng-repeat="year in <?= Years::json() ?>"
                    data-subtext="{{ counts.year[year] || '' }}"
                    value="{{year}}">{{ yearLabel(year) }}</option>
            </select>
		</div>
        <div class="form-group inline-block">
            <select ng-init='payment_categories = <?= PaymentTypes::categories() ?>' id="payment-category" class="form-control" ng-model="new_payment.category" style='width: 130px'>
                <option value="0">категория</option>
                <option disabled>──────────────</option>
                <option ng-repeat="(id, label) in payment_categories"
                    value="{{id}}">{{ label }}</option>
            </select>
        </div>
	</div>
    <div class="form-group" ng-show="new_payment.id_status == <?= Payment::MUTUAL_DEBTS ?>" ng-if='mutual_accounts && mutual_accounts.length'>
        <h4>Выберите встречу</h4>
        <select class="form-control" ng-model="new_payment.account_id" style='width: 180px; margin: 0 5px'>
            <option value="">выберите встречу</option>
            <option disabled>──────────────</option>
            <option ng-repeat="account in mutual_accounts" value="{{ account.id }}" ng-selected="account.id == new_payment.account_id">{{ account.date }}</option>
        </select>
    </div>
	<div class="form-group payment-inline" ng-show="new_payment.id_status == <?= Payment::PAID_CARD ?>">
		<h4>Номер карты</h4>
		<div class="form-group inline-block">
			<input class="form-control card-first-number" placeholder="_XXX" id="payment-card-first-number" ng-model="new_payment.card_first_number" style="width: 70px; display: inline-block; margin-left: 5px"> -
			<input class="form-control" disabled placeholder="XXXX" style="width: 70px; display: inline-block"> -
			<input class="form-control" disabled placeholder="XXXX" style="width: 70px; display: inline-block"> -
			<input class="form-control digits-only" maxlength="4" id="payment-card-number" ng-model="new_payment.card_number"
				   style="width: 70px; display: inline-block">
		</div>
	</div>
	<center>
		<button ng-show="new_payment.id" class="btn btn-primary btn-danger ajax-payment-delete" ng-click="deletePayment()">Удалить</button>
		<button class="btn btn-primary" ng-click="addPayment()">{{new_payment.id ? "Редактировать" : "Добавить"}}</button>
	</center>
</div>
<!-- /ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
<div class="row" ng-show="current_menu == 3">
	<div class="col-sm-12">
		<?= globalPartial('loading', ['model' => 'payments', 'message' => 'нет платежей']) ?>
	    <div class="form-group payment-line">
            <?= globalPartial("payments_list", ['teacher_page' => true]) ?>
	    </div>
	</div>
</div>
