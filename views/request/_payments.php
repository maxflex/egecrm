<div class="row" ng-show="current_menu == 1">
    <div class="col-sm-12" style='margin-bottom: 10px'>
        <select class='form-control' ng-model='student.payment_status' ng-change='changePaymentStatus()' style='width: 250px'>
            <?php foreach(StudentPaymentStatuses::$all as $id => $label) :?>
                <option value="<?= $id ?>"><?= $label ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="col-sm-12">
	    <?= globalPartial('loading', ['model' => 'payments', 'message' => 'нет платежей']) ?>
	    <div class="form-group payment-line">
            <?= globalPartial("payments_list", ["student_page" => true]) ?>
	    </div>
    </div>
</div>