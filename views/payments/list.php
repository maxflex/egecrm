<div ng-app="Payments" ng-controller="ListCtrl" ng-init="<?= $ang_init_data ?>" class="row">

<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
<div class="lightbox-new lightbox-addpayment">
	<h4>{{new_payment.id ? "Редактировать" : "Добавить"}} платеж</h4>
	<div class="form-group payment-line">
		<div class="form-group inline-block">
			<?= Payment::buildSelector(false, false, ["ng-model" => "new_payment.id_status", "style" => "width: 180px"]) ?>
	    </div>
		<div class="form-group inline-block">
			<?= PaymentTypes::buildSelector(false, false, ["ng-model" => "new_payment.id_type"]) ?> на сумму
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
	    
	    <div class="top-links">
		    <span class="link-like" ng-click="filter = 6" ng-class="{'active': filter == 6}">все платежи</span>
		    <span class="link-like" ng-click="filter = 5" ng-class="{'active': filter == 5}">только неподтвержденные</span>
		    <span class="link-like" ng-click="filter = 4" ng-class="{'active': filter == 4}">платежи по картам</span>
		    <span class="link-like" ng-click="filter = 3" ng-class="{'active': filter == 3}">наличные</span>
		    <span class="link-like" ng-click="filter = 2" ng-class="{'active': filter == 2}">счета</span>
		    <span class="link-like" ng-click="filter = 1" ng-class="{'active': filter == 1}">карты онлайн</span>
	    </div>
	    <div class="loading-ajax" ng-show="payments === undefined">загрузка...</div>
	    <div class="form-group payment-line">
			<div ng-repeat="payment in payments | filter:paymentsFilter" style="margin-bottom: 10px">
				<span ng-show="payment.Student.id">
				<a href="student/{{payment.Student.id}}">{{payment.Student.last_name}} {{payment.Student.first_name}} {{payment.Student.middle_name}}</a>
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
    </div>
</div>
