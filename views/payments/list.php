<div ng-app="Payments" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>" class="row">

<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
<div class="lightbox-new lightbox-addpayment">
	<h4>{{new_payment.id ? "Редактировать" : "Добавить"}} платеж</h4>
	<div class="form-group payment-line">
		<div class="form-group inline-block">
			<?= Payment::buildSelector(false, false, ["ng-model" => "new_payment.id_status", "style" => "width: 180px"]) ?>
	    </div>
		<div class="form-group inline-block">
			<?= PaymentTypes::buildSelector(false, false, ["ng-model" => "new_payment.id_type", "ng-if" => "new_payment.type == 'student'"]) ?> на сумму
	    </div>
		<div class="form-group inline-block">
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
		<div class="row flex-list" style="margin-bottom: 15px">
			<div>
				<select class="watch-select single-select form-control" ng-model="search.mode" ng-change='filter()'>
					<option value="client"  data-subtext="{{ counts.mode.client || '' }}">клиенты</option>
					<option value="teacher" data-subtext="{{ counts.mode.teacher || '' }}">преподаватели</option>
				</select>
			</div>
			<div>
				<select class="watch-select single-select form-control" ng-model="search.payment_type"  ng-change='filter()'>
					<option value="" data-subtext="{{ counts.payment_type.all }}">все виды платежа</option>
					<option disabled>──────────────</option>
					<option ng-repeat="(id_status, label) in payment_statuses"
							value="{{ id_status }}"
							data-subtext="{{ counts.payment_type[id_status] || '' }}">{{ label }}</option>
				</select>
			</div>
			<div>
				<select id='subjects-select' class="watch-select form-control single-select" ng-model="search.confirmed" ng-change='filter()'>
					<option value="">все</option>
					<option disabled>──────────────</option>
					<option data-subtext="{{ counts.confirmed[1] || ''}}" value="1">подтвержденные</option>
					<option data-subtext="{{ counts.confirmed[0] || '' }}" value="0">не подтвержденные</option>
				</select>
			</div>
		</div>
		<div id="frontend-loading"></div>
	    <div class="loading-ajax" ng-show="payments === undefined">загрузка...</div>
	    <div class="form-group payment-line" style="margin-bottom: 40px;">
			<div ng-repeat="payment in payments" style="margin-bottom: 10px">  <!-- | filter:paymentsFilter -->
				<span ng-show="payment.Entity.id">
					<a href="{{ payment.Entity.profile_link }}">{{ payment.Entity.last_name }} {{ payment.Entity.first_name }} {{ payment.Entity.middle_name }}</a>
				</span>

				<span class="label label-success" ng-class="{'label-danger' : payment.id_status == <?= Payment::NOT_PAID_BILL ?>}">
				{{payment_statuses[payment.id_status]}}<span ng-show="payment.id_status == <?= Payment::PAID_CARD ?>">{{payment.card_number ? " *" + payment.card_number.trim() : ""}}</span></span>

				<span class="capitalize">{{payment_types[payment.id_type]}}</span>
				на сумму {{payment.sum}} <ng-pluralize count="payment.sum" when="{
					'one' : 'рубль',
					'few' : 'рубля',
					'many': 'рублей',
				}"></ng-pluralize> от {{payment.date}}
					<span class="save-coordinates">({{payment.user_login}} {{formatDate(payment.first_save_date) | date:'yyyy.MM.dd в HH:mm'}})
					</span>
					 <a class="link-like link-reverse small" ng-click="confirmPayment(payment)" ng-show="!payment.confirmed">подтвердить</a>
					 <span class="label pointer label-success" ng-show="payment.confirmed" ng-click="confirmPayment(payment)">подтвержден</span>
					 <a class="link-like link-reverse small" ng-click="editPayment(payment)">редактировать</a>
					 <a class="link-like link-reverse small" ng-click="deletePayment($index, payment)">удалить</a>
			</div>
	    </div>

		<pagination
			ng-show='(payments && payments.length) && (counts.mode[search.mode?search.mode:"client"] > <?= Payment::PER_PAGE ?>)'
			ng-model="current_page"
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
