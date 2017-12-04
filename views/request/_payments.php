<div class="row" ng-show="current_menu == 1">
    <div class="col-sm-12" style='margin-bottom: 10px'>
        <select class='form-control' ng-model='student.payment_status' ng-change='changePaymentStatus()' style='width: 250px'>
            <option value="0">по умолчанию</option>
            <option disabled>──────────────</option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
        </select>
    </div>
    <div class="col-sm-12">
	    <?= globalPartial('loading', ['model' => 'payments', 'message' => 'нет платежей']) ?>
	    <div class="form-group payment-line">
            <?= globalPartial("payments_list", ["student_page" => true]) ?>
	    </div>
    </div>
</div>