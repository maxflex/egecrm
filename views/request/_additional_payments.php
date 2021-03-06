<div class="row" style="position: relative" ng-show="current_menu == 10">
	<div class="col-sm-12">
		<?= globalPartial('loading', ['model' => 'StudentAdditionalPayments']) ?>
		<h4 style='margin-bottom: 10px'>Дополнительные услуги</h4>
		<table class="table">
			<tr ng-repeat="payment in StudentAdditionalPayments">
				<td width='150'>
					{{ payment.date }}
				</td>
				<td width='150'>
					{{ payment.sum | number }} руб.
				</td>
				<td>
					{{ payment.purpose }}
				</td>
				<td>
	                {{payment.user_login}} {{formatDate(payment.created_at) | date:'dd.MM.yy в HH:mm'}}
	            </td>
	            <td style="text-align: right">
					<a class="link-like" ng-click="editPaymentAdditional(payment)" style='margin-right: 10px'>редактировать</a>
	            </td>
			</tr>
			<tr>
				<td colspan="5">
					<span class="link-like" ng-click="addAdditionalPaymentDialog()">добавить услугу</span>
				</td>
			</tr>
		</table>
	</div>
</div>

<!-- ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
<div class="lightbox-new lightbox-additional-payment">
	<h4 style="display: inline-block">{{new_additional_payment.id ? "Редактировать" : "Добавить"}} услугу</h4>
	<div class="form-group payment-line">
		<div class="form-group">
			<input style='margin: 0' type="text" placeholder="сумма" class="form-control digits-only full-width" id="add-payment-sum" ng-model="new_additional_payment.sum">
		</div>
		<div class="form-group">
            <select class="form-control" ng-model="new_additional_payment.year" style='margin: 0; width: 100%'>
                <option value="">выберите год</option>
                <option disabled>──────────────</option>
                <option ng-repeat="year in <?= Years::json() ?>"
                    value="{{year}}">{{ yearLabel(year) }}</option>
            </select>
		</div>
		<div class="form-group">
			<input style='margin: 0' placeholder="дата" class="form-control bs-date full-width" id="add-payment-date" ng-model="new_additional_payment.date">
		</div>
		<div class="form-group">
			<textarea maxlength="255" placeholder="назначение" class="form-control full-width" id="add-payment-purpose" ng-model="new_additional_payment.purpose"></textarea>
		</div>
	</div>
	<center>
		<button class="btn btn-primary ajax-payment-button full-width" ng-click="addAdditionalPayment()">{{new_additional_payment.id ? "Редактировать" : "Добавить"}}</button>
		<button class="btn btn-primary btn-danger full-width" ng-show="new_additional_payment.id"
			style="margin-top: 10px" ng-click="deletePaymentAdditional()">Удалить</button>
	</center>
</div>
<!-- /ЛАЙТБОКС ДОБАВЛЕНИЕ ПЛАТЕЖА -->
