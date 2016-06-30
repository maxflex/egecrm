<div class="row" style="margin-top: 10px">
	<div class="col-sm-4">
		<div class="half-black pull-left" ng-show="mode=='request'" style="margin-left: 15px; margin-top: 7px">
			создана {{users[<?= $Request->id_user_created ?>].login || "system"}} <?= date("y.m.d в H:i", strtotime($Request->date)) ?>
		</div>
	</div>
	<div class="col-sm-4 center">
    	<button class="btn btn-primary save-button" ng-disabled="saving || !form_changed" ng-hide="<?= $Request->adding ?>" style="width: 100px">
    		<span ng-show="form_changed">Сохранить</span>
    		<span ng-show="!form_changed && !saving">Сохранено</span>
    	</button>

    	<!-- ДОБАВЛЕНИЕ ЗАЯВКИ В ПРОФИЛЬ УЧЕНИКА -->
    	<button class="btn btn-primary" ng-click="addAndRedirect()" ng-disabled="saving" ng-show="<?= ($Request->adding && $_GET["id_student"]) ?>">Добавить заявку</button>

    	<!-- СОЗДАНИЕ НОВОЙ ЗАЯВКИ -->
    	<div class="add-request-buttons" ng-show="<?= ($Request->adding && !$_GET["id_student"]) ?>">

	    	<div class="add-request-buttons-regular" ng-hide="id_student_phone_exists">
		    	<button class="btn btn-primary" ng-click="addAndRedirect()" ng-disabled="saving">
		    		Добавить
		    	</button>
	    	</div>

	    	<div class="add-request-buttons-split" ng-show="id_student_phone_exists">
		    	<button class="btn btn-primary" ng-click="addAndRedirect()" ng-disabled="saving">
		    		Сохранить как новый профиль
		    	</button>
				<button class="btn btn-primary" ng-click="addRequestToExisting()" ng-disabled="saving">
		    		Добавить заявку к существующему
		    	</button>
	    	</div>
    	</div>
	</div>
	<div class="col-sm-4">
		<div class="half-black pull-right" ng-show="mode=='request'" style="margin-right: 15px; margin-top: 7px">
			ответственный: <span class="user-pick" ng-click="toggleUser()" style="color: {{responsible_user.color || 'black' }}">{{ responsible_user.login || "system"}}
		</div>
	</div>
</div>