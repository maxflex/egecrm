<div class="panel panel-primary <?= ($mode != 'student' ? 'ng-hide' : '') ?>" ng-show="mode == 'student'">
		<div class="panel-heading">
			Редактирование профиля ученика №<?= $Request->Student->id ?>
			<div class="pull-right">
				<?php if (User::byType($Request->Student->id, Student::USER_TYPE, 'count')) :?>
				<a style="margin-left: 10px" class="like-white" href="as/student/<?= $Request->Student->id ?>">режим просмотра</a>
				<?php endif ?>

				<?php if (!$Request->adding) :?>
				<span style="margin-left: 10px" class='link-reverse pointer' id='delete-student' onclick='deleteStudent(<?= $Request->Student->id ?>)'>удалить профиль</span>
				<?php endif ?>
			</div>
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-sm-9">
					<div class="top-links">
						<span class="link-like" ng-click="setMenu(0)" ng-class="{'active': current_menu == 0}">
					    	основные данные
					    </span>
					    <span class="link-like" ng-click="setMenu(1)" ng-class="{'active': current_menu == 1}">
					    	платежи
					    </span>
					    <span class="link-like" ng-click="setMenu(2)" ng-class="{'active': current_menu == 2}">
					    	посещаемость
					    </span>
					    <span class="link-like" ng-click="setMenu(3)" ng-class="{'active': current_menu == 3}">
					    	отзывы
					    </span>
					    <span class="link-like" ng-click="setMenu(4)" ng-class="{'active': current_menu == 4}">
					    	отчеты
					    </span>
					    <span class="link-like" ng-click="setMenu(5)" ng-class="{'active': current_menu == 5}">
					    	комментарии
					    </span>
					    <span class="link-like" ng-click="setMenu(6)" ng-class="{'active': current_menu == 6}">
					    	тесты
					    </span>
						<span class="link-like" ng-click="setMenu(7)" ng-class="{'active': current_menu == 7}">
					    	фото ученика
					    </span>
						<span class="link-like" ng-click="setMenu(8)" ng-class="{'active': current_menu == 8}">
					    	график
					    </span>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="top-links pull-right">
					    <span class="link-like" ng-click="setMode('request')">заявки</span>
					    <span class="link-like active">клиент</span>
				    </div>
				</div>
			</div>


	<?= partial('general', compact('Request')) ?>
	<?= partial('payments', compact('Request')) ?>
	<?= partial("visits") ?>
    <?= partial("reviews") ?>
    <?= partial("reports") ?>
    <?= partial("comments") ?>
    <?= partial("tests") ?>
    <?= partial("photo") ?>
	<?= partial("freetime") ?>

    <?= partial("save_button", ["Request" => $Request]) ?>
	<?= partial("bill_print") ?>
</div></div>
