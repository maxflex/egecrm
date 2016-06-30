<div class="panel panel-primary panel-edit ng-hide" ng-show="mode == 'student'">
		<div class="panel-heading">
			Редактирование профиля ученика №<?= $Request->Student->id ?>
			<div class="pull-right">

				<a style="margin-right: 10px" class="like-white" href="reviews/<?= $Request->Student->id ?>">отзывы</a>

				<?php if (!empty($Request->Student->login)) :?>
				<a style="margin-left: 10px" class="like-white" href="as/student/<?= $Request->Student->id ?>">режим просмотра</a>
				<?php endif ?>

				<?php if (!$Request->adding) :?>
				<span style="margin-left: 10px" class='link-reverse pointer' id='delete-student' onclick='deleteStudent(<?= $Request->Student->id ?>)'>удалить профиль</span>
				<?php endif ?>
			</div>
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-sm-8">
					<div class="top-links">
						<span class="link-like" ng-click="setMenu(0)" ng-class="{'active': current_menu == 0}">
					    	основные данные
					    </span>
					    <span class="link-like" ng-click="setMenu(1)" ng-class="{'active': current_menu == 1}">
					    	договоры <span ng-show="contracts && contracts.length">({{ contracts.length }})</span>
					    </span>
					    <span class="link-like" ng-click="setMenu(2)" ng-class="{'active': current_menu == 2}">
							группы <span ng-show="Groups && Groups.length">({{ Groups.length }})</span>
					    </span>
					    <span class="link-like" ng-click="setMenu(3)" ng-class="{'active': current_menu == 3}">
					    	платежи <span ng-show="payments && payments.length">({{ payments.length }})</span>
					    </span>
					    <span class="link-like" ng-click="setMenu(4)" ng-class="{'active': current_menu == 4}">
					    	посещаемость
					    </span>
					    <span class="link-like" ng-click="setMenu(5)" ng-class="{'active': current_menu == 5}">
					    	отзывы <span ng-show="teacher_likes && teacher_likes.length">({{ teacher_likes.length }})</span>
					    </span>
					    <span class="link-like" ng-click="setMenu(6)" ng-class="{'active': current_menu == 6}">
					    	отчеты <span ng-show="Reports && Reports.length">({{ Reports.length }})</span>
					    </span>
					</div>
				</div>
				<div class="col-sm-4">
					<div class="top-links pull-right">
					    <span class="link-like" ng-click="mode = 'request'">заявки</span>
					    <span class="link-like active">клиент</span>
				    </div>
				</div>
			</div>
			
			
	<?= partial('general', compact('Request')) ?>
	<?= partial('contracts', compact('Request')) ?>
	<?= partial('groups', compact('Request')) ?>
	<?= partial('payments', compact('Request')) ?>
	<?= partial("visits") ?>
    <?= partial("teacher_likes") ?>
    <?= partial("reports") ?>

    <?= partial("save_button", ["Request" => $Request]) ?>
	<?= partial("bill_print") ?>
</div></div>