<div ng-app="Teacher" ng-controller="SalaryCtrl"
	ng-init="<?= $ang_init_data ?>">
		
		<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
		<div class="lightbox-new lightbox-addpayment" style="width: 551px; left: calc(50% - 275px)">
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
		
	<div class="row" style="position: relative">
		<div class="col-sm-12">
			<table class="table table-divlike">
				<tr ng-repeat="d in Data">
					<td>
						<a href="groups/edit/{{d.id_group}}">Группа №{{d.id_group}}</a>
					</td>
					<td>
						{{formatDate(d.lesson_date)}}
					</td>
					<td>
						{{formatTime(d.lesson_time)}}
					</td>
					<td>
						{{d.teacher_price | number}} рублей
					</td>
				</tr>
			</table>

		</div>
	</div>
	
	<div class="row">
		<div class="col-sm-12">
			<h4 class="row-header">ПЛАТЕЖИ
			    <a class="link-like link-reverse link-in-h" ng-click="addPaymentDialog()">добавить</a>
		    </h4>
		    <div class="form-group payment-line">
				<div ng-repeat="payment in payments | reverse" style="margin-bottom: 5px"> 
					<span class="label label-success" ng-class="{'label-danger' : payment.id_status == <?= Payment::NOT_PAID_BILL ?>}">
					{{payment_statuses[payment.id_status]}}<span ng-show="payment.id_status == <?= Payment::PAID_CARD ?>">{{payment.card_number ? " *" + payment.card_number.trim() : ""}}</span></span>
					
					<span class="capitalize">{{payment_types[payment.id_type]}}</span>
					Платеж на сумму {{payment.sum}} <ng-pluralize count="payment.sum" when="{
						'one' : 'рубль',
						'few' : 'рубля',
						'many': 'рублей',
					}"></ng-pluralize> от {{payment.date}}
						<span class="save-coordinates">({{payment.user_login}} {{formatDate2(payment.first_save_date) | date:'yyyy.MM.dd в HH:mm'}})
						</span>
						<a class="link-like link-reverse small" ng-click="confirmPayment(payment)" ng-show="!payment.confirmed">подтвердить</a>
						<span class="label pointer label-success" ng-show="payment.confirmed" ng-click="confirmPayment(payment)">подтвержден</span>
						<a class="link-like link-reverse small" ng-click="editPayment(payment)">редактировать</a>
						<a class="link-like link-reverse small" ng-click="deletePayment($index, payment)">удалить</a>
				</div>
		    </div>
		</div>
	</div>
</div>
