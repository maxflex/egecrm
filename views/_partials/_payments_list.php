<?= $student_page ? globalPartial("bill_print") : '' ?>
<?= $student_page ? globalPartial("pko_print") : '' ?>

<div class="form-group payment-line" style="margin-bottom: 40px;">
    <table class="table table-hover payments-table" style="font-size: 12px !important">
        <tr ng-repeat="payment in payments" <?= ($student_page ? 'ng-init="payment.Entity = student"' : '') ?>>
	        <?php if (! $student_page) :?>
            <td class="col-sm-3" ng-if="payment.Entity && payment.Entity.id">
                <span ng-show="payment.Entity.id"><a href="{{ payment.Entity.profile_link }}">{{ payment.Entity.last_name }} {{ payment.Entity.first_name }} {{ payment.Entity.middle_name }}</a></span>
            </td>
            <?php endif ?>
            <td>
	            {{payment.id_type == 2 ? 'возврат' : 'платеж'}}
            </td>
            <td>
						<span class="">{{payment_statuses[payment.id_status]}}
							<span ng-show="payment.id_status == <?= Payment::PAID_CARD ?>">
								{{payment.card_number ? " *" + payment.card_number.trim() : ""}}
							</span>
						</span>
            </td>
            <td>
                <?php if ($student_page) :?>
                    <a style='margin-left: 10px' class="link-like" ng-click="printPKO(payment)" ng-show="payment.id_status == <?= Payment::PAID_CASH ?>">{{ payment.document_number ? 'ПКО ' + payment.document_number :  '' }}</a>
                <?php else :?>
                    {{ payment.document_number ? 'ПКО ' + payment.document_number :  '' }}
                <?php endif ?>
            </td>
            <td class="col-sm-1">
                {{payment.sum | number}}
            </td>
            <td>
                {{payment.date}}
            </td>
            <td>
                <a class="text-danger pointer"
                   ng-click="confirmPayment(payment)"
                   ng-show="!payment.confirmed">подтвердить</a>
                <span class="text-green pointer" ng-show="payment.confirmed" ng-click="confirmPayment(payment)">подтверждено</span>
            </td>
            <td>
                <a class="link-like" ng-click="editPayment(payment)">редактировать</a>
            </td>
            <td style="white-space: nowrap">
                <a class="link-like" ng-click="deletePayment($index, payment)">удалить</a>
                <?php if ($student_page) :?>
                    <a style='margin-left: 10px' class="link-like" ng-click="printBill(payment)" ng-show="payment.id_status == <?= Payment::PAID_BILL ?>">печать счета</a>
                <?php endif ?>
            </td>
            <td class="col-sm-2">
                {{payment.user_login}} {{formatDate(payment.first_save_date) | date:'dd.MM.yyyy в HH:mm'}}
            </td>
        </tr>
        <?php if ($student_page) :?>
        <tr ng-show='payments !== undefined'>
	        <td colspan="10">
		        <a class="link-like link-reverse" ng-click="addPaymentDialog()">добавить</a>
	        </td>
        </tr>
        <?php endif ?>
    </table>
</div>