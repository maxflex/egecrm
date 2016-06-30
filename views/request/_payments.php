<div class="row" ng-show="current_menu == 3">
    <div class="col-sm-12">
	    <div style="margin-bottom: 20px; display: block">
			<a class="link-like link-reverse" ng-click="addPaymentDialog()">добавить</a>
	    </div>
		
	    <div class="form-group payment-line">
            <?= globalPartial("payments_list", ["show_print" => true]) ?>

			<div class="half-black small" ng-show="objectLength(remainder)">
				<?php if (User::fromSession()->edit_payment) :?>
					Остаточный платеж <input class="no-border-outline digits-only-minus" ng-model="remainder.remainder" ng-keydown="checkRemainderSave($event)"
					style="margin: 0; width: {{calculateRemainderWidth()}}px">
					<ng-pluralize count="remainder.remainder" when="{
						'one' : 'рубль',
						'few' : 'рубля',
						'many': 'рублей',
					}"></ng-pluralize>
					<span class="text-danger opacity-pointer" style="margin-left: 5px" ng-click="deleteRemainder()">удалить</span>
				<?php else :?>
					Остаточный платеж {{remainder.remainder}}
					<ng-pluralize count="remainder.remainder" when="{
						'one' : 'рубль',
						'few' : 'рубля',
						'many': 'рублей',
					}"></ng-pluralize>
				<?php endif ?>
			</div>
			<?php if (User::fromSession()->edit_payment) :?>
				<div class="half-black small" ng-show="!objectLength(remainder)">
					<span class="underline-hover pointer" style="color: #999999" ng-click="addRemainder()">добавить остаточный платеж</span>
				</div>
			<?php endif ?>
	    </div>
    </div>
</div>