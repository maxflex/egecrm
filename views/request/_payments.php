<div class="row" ng-show="current_menu == 1">
    <div class="col-sm-12" style='margin-bottom: 10px'>
        <select class='form-control' ng-model='student.payment_status' ng-change='changePaymentStatus()' style='width: 250px'>
            <?php foreach(StudentPaymentStatuses::$all as $id => $label) :?>
                <option value="<?= $id ?>"><?= $label ?></option>
            <?php endforeach ?>
        </select>
    </div>
    <div class="col-sm-12 student-payments">


	    <?= globalPartial('loading', ['model' => 'PaymentsByYear', 'message' => 'нет платежей']) ?>

		<div style='margin-bottom: 15px' ng-repeat="(year, payments) in PaymentsByYear">
			<h4 class="row-header default-case">Платежи {{ yearLabel(year, true) }} учебного года</h4>
			<?= globalPartial("payments_list", ["student_page" => true]) ?>
		</div>
    </div>
</div>
<style>
.student-payments .payments-table:last-of-type {

}
</style>
