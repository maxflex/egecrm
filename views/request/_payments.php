<div class="row" ng-show="current_menu == 1">
    <div class="col-sm-12">
	    <?= globalPartial('loading', ['model' => 'payments', 'message' => 'нет платежей']) ?>
	    <div class="form-group payment-line">
            <?= globalPartial("payments_list", ["student_page" => true]) ?>
	    </div>
    </div>
</div>