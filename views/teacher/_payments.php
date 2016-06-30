<div class="row" ng-show="current_menu == 3">
	<div class="col-sm-12">
		<?= globalPartial('loading', ['model' => 'payments', 'message' => 'нет платежей']) ?>
		<a ng-show='payments !== undefined' class="link-like link-reverse small" style="margin-bottom: 10px" ng-click="addPaymentDialog()">добавить платеж</a>
	    <div class="form-group payment-line">
            <?= globalPartial("payments_list") ?>
	    </div>
	</div>
</div>