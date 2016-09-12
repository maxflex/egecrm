<div class="row" ng-show="current_menu == 3">
	<div class="col-sm-12">
		<?= globalPartial('loading', ['model' => 'payments', 'message' => 'нет платежей']) ?>
	    <div class="form-group payment-line">
            <?= globalPartial("payments_list", ['teacher_page' => true]) ?>
	    </div>
	</div>
</div>