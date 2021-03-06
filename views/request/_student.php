<div class="panel panel-primary <?= ($mode != 'student' ? 'ng-hide' : '') ?>" ng-show="mode == 'student'">
		<div class="panel-heading">
			<?= User::isAdmin() ? 'Редактирование' : 'Просмотр' ?> профиля ученика №<?= $Request->Student->id ?>
			<div class="pull-right">
				<?php if (User::byType($Request->Student->id, Student::USER_TYPE, 'count')) :?>
				<a style="margin-left: 10px" class="like-white view-mode-link" href="as/student/<?= $Request->Student->id ?>">режим просмотра</a>
				<?php endif ?>

				<?php if (!$Request->adding) :?>
				<span style="margin-left: 10px" class='link-reverse pointer' id='delete-student' onclick='deleteStudent(<?= $Request->Student->id ?>)'>удалить профиль</span>
				<?php endif ?>
			</div>
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-sm-9">
					<div class="top-links" style='white-space: nowrap'>
						<span class="link-like menu-link menu-s-0" ng-click="setMenu(0)" ng-class="{'active': current_menu == 0}">
					    	основное
					    </span>
					    <span class="link-like menu-link menu-s-1" ng-click="setMenu(1)" ng-class="{'active': current_menu == 1}">
					    	платежи
					    </span>
						<span class="link-like menu-link menu-s-10" ng-click="setMenu(10)" ng-class="{'active': current_menu == 10}">
					    	дополнительные услуги
					    </span>
						<span class="link-like menu-link menu-s-9" ng-click="setMenu(9)" ng-class="{'active': current_menu == 9}">
					    	баланс счета
					    </span>
					    <span class="link-like menu-link menu-s-2" ng-click="setMenu(2)" ng-class="{'active': current_menu == 2}">
					    	посещаемость
					    </span>
					    <span class="link-like menu-link menu-s-3" ng-click="setMenu(3)" ng-class="{'active': current_menu == 3}">
					    	отзывы
					    </span>
					    <span class="link-like menu-link menu-s-4" ng-click="setMenu(4)" ng-class="{'active': current_menu == 4}">
					    	отчеты
					    </span>
					    <span class="link-like menu-link menu-s-5" ng-click="setMenu(5)" ng-class="{'active': current_menu == 5}">
					    	комментарии
					    </span>
					    <span class="link-like menu-link menu-s-6" ng-click="setMenu(6)" ng-class="{'active': current_menu == 6}">
					    	тесты
					    </span>
						<span class="link-like menu-link menu-s-8" ng-click="setMenu(8)" ng-class="{'active': current_menu == 8}">
					    	график
					    </span>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="top-links pull-right top-links-request">
					    <span class="link-like" ng-click="setMode('request')">заявки</span>
					    <span class="link-like active">клиент</span>
				    </div>
				</div>
			</div>


	<?= partial('general', compact('Request')) ?>
	<?= partial('payments', compact('Request')) ?>
	<?= partial("lessons") ?>
    <?= partial("reviews") ?>
    <?= partial("reports") ?>
    <?= partial("comments") ?>
    <?= partial("tests") ?>
	<?= partial("freetime") ?>
	<?= partial("balance") ?>
	<?= partial("additional_payments") ?>

	<?= globalPartial('email') ?>

	<?php if (! User::isTeacher()) :?>
	    <?= partial("save_button", ["Request" => $Request]) ?>
		<?= partial("bill_print") ?>
	<?php endif ?>
</div></div>
