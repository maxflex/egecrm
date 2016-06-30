<div class="form-group payment-line" style="margin-bottom: 40px;">
    <table class="table table-hover payments-table" style="font-size: 12px">
        <tr ng-repeat="payment in payments">
            <td class="col-sm-3" ng-if="payment.Entity && payment.Entity.id">
                <span ng-show="payment.Entity.id"><a href="{{ payment.Entity.profile_link }}">{{ payment.Entity.last_name }} {{ payment.Entity.first_name }} {{ payment.Entity.middle_name }}</a></span>
            </td>
            <td>
	            {{payment.id_type == 2 ? 'возврат' : 'платеж'}}
            </td>
            <td class="col-sm-1">
						<span class="">{{payment_statuses[payment.id_status]}}
							<span ng-show="payment.id_status == <?= Payment::PAID_CARD ?>">
								{{payment.card_number ? " *" + payment.card_number.trim() : ""}}
							</span>
						</span>
            </td>
            <td class="col-sm-1">
                {{payment.sum | number}}
            </td>
            <td class="col-sm-1">
                {{payment.date}}
            </td>
            <td class="col-sm-1">
                <a class="text-danger pointer"
                   ng-click="confirmPayment(payment)"
                   ng-show="!payment.confirmed">подтвердить</a>
                <span class="text-green pointer" ng-show="payment.confirmed" ng-click="confirmPayment(payment)">подтверждено</span>
            </td>
            <td class="col-sm-1">
                <a class="link-like" ng-click="editPayment(payment)">редактировать</a>
            </td>
            <td>
                <a class="link-like" ng-click="deletePayment($index, payment)">удалить</a>
            </td>
            <td class="col-sm-2">
                {{payment.user_login}} {{formatDate(payment.first_save_date) | date:'dd.MM.yyyy в HH:mm'}}
            </td>
        </tr>
    </table>
</div>