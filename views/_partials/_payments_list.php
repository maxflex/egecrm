<?= $student_page ? globalPartial("bill_print") : '' ?>
<?= $student_page ? globalPartial("pko_print") : '' ?>
<?= $student_page ? globalPartial("llc_bill_print") : '' ?>

<div class="form-group payment-line" style="margin-bottom: 40px;">
    <table class="table table-hover payments-table" style="font-size: 12px !important">
        <tr ng-repeat="payment in payments" <?= ($student_page ? 'ng-init="payment.Entity = student"' : '') ?>>
	        <?php if (! $student_page) :?>
            <td style='width: 160px' ng-if="payment.Entity && payment.Entity.id">
                <span ng-show="payment.Entity.id"><a href="{{ payment.Entity.profile_link }}">{{ payment.Entity.last_name }} {{ payment.Entity.first_name[0] }}. {{ payment.Entity.middle_name[0] }}.</a></span>
            </td>
            <?php endif ?>
            <td style='width: 100px' >
	            <a class="link-like" ng-class="{
					'text-danger': payment.id_type == 2
				}" ng-click="editPayment(payment)">{{payment.id_type == 2 ? 'возврат' : 'платеж'}}</a>
            </td>
            <td style='width: 160px' >
				<span class="">{{payment_statuses[payment.id_status]}}
					<span ng-show="payment.id_status == <?= Payment::PAID_CARD ?>">
						{{payment.card_number ? payment.card_first_number.replace('XXX','') + "*** " + payment.card_number.trim() : ""}}
					</span>
				</span>
				<?php if ($student_page) :?>
					<span class="remove-space" ng-show="payment.id_status == <?= Payment::PAID_BILL ?>">
                    	: <a class="link-like" ng-click="printLlcBill(payment)">печать</a>
					</span>
                <?php endif ?>

				<span ng-show="payment.document_number > 0 && payment.id_status == <?= Payment::PAID_CASH ?>" class="remove-space">
					:
					<?php if ($student_page) :?>
	                    <a class="link-like" ng-click="printPKO(payment)">{{ 'ПКО ' + payment.document_number }}</a>
	                <?php else :?>
						{{ 'ПКО ' + payment.document_number }}
	                <?php endif ?>
				</span>
            </td>
            <td style='width: 100px' >
                {{payment.sum | number}}
            </td>
            <td style='width: 100px' >
                {{payment.date}}
            </td>
            <td style='width: 150px' >
                {{ yearLabel(payment.year) }}
            </td>
            <td style='width: 150px' >
                {{ payment_categories[payment.category] }}
            </td>
            <td>
                <a class="text-danger pointer"
                   ng-click="confirmPayment(payment)"
                   ng-show="!payment.confirmed">подтвердить</a>
                <span class="text-green pointer" ng-show="payment.confirmed" ng-click="confirmPayment(payment)">подтверждено</span>
            </td>
            <td class="col-sm-2">
                {{payment.user_login}} {{formatDate(payment.first_save_date) | date:'dd.MM.yy в HH:mm'}}
            </td>
        </tr>
        <?php if ($student_page || $teacher_page) :?>
        <tr ng-show='payments !== undefined' ng-init="teacher_page = <?= $teacher_page ? 1 : 0 ?>" class="link-add-payment">
	        <td colspan="11">
                <a class="link-like link-reverse" ng-click="addPaymentDialog()">добавить</a>
	        </td>
        </tr>
        <?php endif ?>
    </table>
</div>
