<div ng-app="Payments" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>" class="row">

<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
<div class="lightbox-new lightbox-addpayment">

    <h4 style="display: inline-block">{{new_payment.id ? "Редактировать" : "Добавить"}} платеж</h4>
    <span class="small" ng-show="new_payment.entity_type == 'STUDENT' && new_payment.id_status == <?= Payment::PAID_CASH ?> && new_payment.id_type == <?= PaymentTypes::PAYMENT ?>">{{ new_payment.document_number ? 'номер ПКО: ' + new_payment.document_number : 'будет присвоен номер ПКО' }}</span>

    <div class="form-group payment-line">
		<div class="form-group inline-block">
			<?= Payment::buildSelector(false, false, [
                "ng-model" => "new_payment.id_status",
                "style" => "width: 180px"
            ]) ?>
	    </div>
		<div class="form-group inline-block" ng-show="new_payment.Entity.type == 'STUDENT'">
			<?= PaymentTypes::buildSelector(false, false, ["ng-model" => "new_payment.id_type"]) ?>
	    </div>
		<div class="form-group inline-block">
			на сумму
			<input type="text" class="form-control digits-only" id="payment-sum" ng-model="new_payment.sum"  ng-keydown="watchEnter($event)"> от
		</div>
		<div class="form-group inline-block">
			<input class="form-control bs-date" id="payment-date" ng-model="new_payment.date">
		</div> за
        <div class="form-group inline-block">
            <select class="form-control" ng-model="new_payment.year" style='width: 130px'>
                <option value="">выберите встречу</option>
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
		<button class="btn btn-primary" ng-click="addPayment()">{{new_payment.id ? "Редактировать" : "Добавить"}}</button>
	</center>
</div>
<!-- /ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
    <style>
        .dropdown-menu > li > a {
            padding: 3px 45px 3px 10px;
        }
    </style>
    <div class="col-sm-12">
		<div class="row" style="margin-bottom: 15px">
			<div class="col-sm-2">
				<select class="watch-select single-select form-control" ng-model="search.mode" ng-change='filter()'>
					<option value="STUDENT"  data-subtext="{{ counts.mode.STUDENT || '' }}">клиенты</option>
					<option value="TEACHER" data-subtext="{{ counts.mode.TEACHER || '' }}">преподаватели</option>
				</select>
			</div>
			<div class="col-sm-2">
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
			<div class="col-sm-2">
				<select id='subjects-select' class="watch-select form-control single-select" ng-model="search.confirmed" ng-change='filter()'>
					<option value="" data-subtext="{{ counts.confirmed.all || ''}}" >все типы платежей</option>
					<option disabled>──────────────</option>
					<option data-subtext="{{ counts.confirmed[1] || ''}}" value="1">подтвержденные</option>
					<option data-subtext="{{ counts.confirmed[0] || '' }}" value="0">не подтвержденные</option>
				</select>
			</div>
			<div class="col-sm-2">
				<select id='subjects-select' class="watch-select form-control single-select" ng-model="search.type" ng-change='filter()'>
					<option value="" data-subtext="{{ counts.type.all || ''}}" >все операции</option>
					<option disabled>──────────────</option>
					<option data-subtext="{{ counts.type[1] || ''}}" value="1">платеж</option>
					<option data-subtext="{{ counts.type[2] || '' }}" value="2">возврат</option>
				</select>
			</div>
			<div class="col-sm-2">
				<select id='years-select' class="watch-select form-control single-select" ng-model="search.year" ng-change='filter()'>
					<option value="" data-subtext="{{ counts.year.all || ''}}" >все годы</option>
					<option disabled>──────────────</option>
                    <option ng-repeat="year in <?= Years::json() ?>"
                        data-subtext="{{ counts.year[year] || '' }}"
                        value="{{year}}">{{ yearLabel(year) }}</option>
				</select>
			</div>
            <div class="col-sm-2">
				<select id='years-select' class="watch-select form-control single-select year-fix" ng-model="search.category" ng-change='filter()'>
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
